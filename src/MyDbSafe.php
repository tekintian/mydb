<?php
namespace tekintian\mydb;
/**
 * MyDb 安全检查类
 * @Author: tekin
 * @Date:   2020-06-13 13:00:23
 * @Last Modified 2020-06-13
 * @Last Modified time: 2020-06-13 13:18:44
 */
class MyDbSafe {

	protected static $checkcmd = array('SEL'=>1, 'UPD'=>1, 'INS'=>1, 'REP'=>1, 'DEL'=>1);
	protected static $config;
	/**
	 * 数据库查询安全检查
	 * @param  [type] $sql [description]
	 * @return [type]      [description]
	 */
	public static function checkquery($sql) {
		if (self::$config === null) {
			self::$config = self::getConfig('config/security/querysafe');
		}
		if (self::$config['status']) {
			$check = 1;
			$cmd = strtoupper(substr(trim($sql), 0, 3));
			if(isset(self::$checkcmd[$cmd])) {
				$check = self::_do_query_safe($sql);
			} elseif(substr($cmd, 0, 2) === '/*') {
				$check = -1;
			}

			if ($check < 1) {
				throw new DbException('It is not safe to do this query', 0, $sql);
			}
		}
		return true;
	}
	/**
	 * 查询过滤
	 * @param  [type] $sql [description]
	 * @return [type]      [description]
	 */
	private static function _do_query_safe($sql) {
		$sql = str_replace(array('\\\\', '\\\'', '\\"', '\'\''), '', $sql);
		$mark = $clean = '';
		if (strpos($sql, '/') === false && strpos($sql, '#') === false && strpos($sql, '-- ') === false && strpos($sql, '@') === false && strpos($sql, '`') === false && strpos($sql, '"') === false) {
			$clean = preg_replace("/'(.+?)'/s", '', $sql);
		} else {
			$len = strlen($sql);
			$mark = $clean = '';
			for ($i = 0; $i < $len; $i++) {
				$str = $sql[$i];
				switch ($str) {
					case '`':
						if(!$mark) {
							$mark = '`';
							$clean .= $str;
						} elseif ($mark == '`') {
							$mark = '';
						}
						break;
					case '\'':
						if (!$mark) {
							$mark = '\'';
							$clean .= $str;
						} elseif ($mark == '\'') {
							$mark = '';
						}
						break;
					case '/':
						if (empty($mark) && $sql[$i + 1] == '*') {
							$mark = '/*';
							$clean .= $mark;
							$i++;
						} elseif ($mark == '/*' && $sql[$i - 1] == '*') {
							$mark = '';
							$clean .= '*';
						}
						break;
					case '#':
						if (empty($mark)) {
							$mark = $str;
							$clean .= $str;
						}
						break;
					case "\n":
						if ($mark == '#' || $mark == '--') {
							$mark = '';
						}
						break;
					case '-':
						if (empty($mark) && substr($sql, $i, 3) == '-- ') {
							$mark = '-- ';
							$clean .= $mark;
						}
						break;

					default:

						break;
				}
				$clean .= $mark ? '' : $str;
			}
		}

		if(strpos($clean, '@') !== false) {
			return '-3';
		}

		$clean = preg_replace("/[^a-z0-9_\-\(\)#\*\/\"]+/is", "", strtolower($clean));

		if (self::$config['afullnote']) {
			$clean = str_replace('/**/', '', $clean);
		}

		if (is_array(self::$config['dfunction'])) {
			foreach (self::$config['dfunction'] as $fun) {
				if (strpos($clean, $fun . '(') !== false)
					return '-1';
			}
		}

		if (is_array(self::$config['daction'])) {
			foreach (self::$config['daction'] as $action) {
				if (strpos($clean, $action) !== false)
					return '-3';
			}
		}

		if (self::$config['dlikehex'] && strpos($clean, 'like0x')) {
			return '-2';
		}

		if (is_array(self::$config['dnote'])) {
			foreach (self::$config['dnote'] as $note) {
				if (strpos($clean, $note) !== false)
					return '-4';
			}
		}

		return 1;
	}

	public static function setconfigstatus($data) {
		self::$config['status'] = $data ? 1 : 0;
	}

	/**
	 * 获取全局配置
	 * 如
	 * getConfig('config/security/querysafe') 可获取 $_config['security']['querysafe']
	 * 
	 * @param  [type] $key   [description]
	 * @param  [type] $group [description]
	 * @return [type]        [description]
	 */
	public static function getConfig($key, $group = null) {
		global $_G;
		$key = explode('/', $group === null ? $key : $group.'/'.$key);
		$v = &$_G;
		foreach ($key as $k) {
			if (!isset($v[$k])) {
				return null;
			}
			$v = &$v[$k];
		}
		return $v;
	}
}

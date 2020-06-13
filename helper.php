<?php
/**
 * 一些函数参考
 */
!defined('IN_MYDB') && exit('Access Denied'); // 不允许直接访问，必须是在定义了常量 IN_MYDB的页面中访问

$_G = [];

/**
 * 全局配置设置
 * @Author: tekin
 * @Date:   2020-06-12 12:04:38
 * @Last Modified 2020-06-13
 * @Last Modified time: 2020-06-13 14:43:57
 */
function setglobal($key, $value, $group = null) {
	global $_G;
	$key = explode('/', $group === null ? $key : $group . '/' . $key);
	$p = &$_G;
	foreach ($key as $k) {
		if (!isset($p[$k]) || !is_array($p[$k])) {
			$p[$k] = array();
		}
		$p = &$p[$k];
	}
	$p = $value;
	return true;
}
/**
 * 获取全局配置
 * 如
 * getglobal('config/security/querysafe') 可获取 $_config['security']['querysafe']
 *
 * @param  [type] $key   [description]
 * @param  [type] $group [description]
 * @return [type]        [description]
 */
function getglobal($key, $group = null) {
	global $_G;
	$key = explode('/', $group === null ? $key : $group . '/' . $key);
	$v = &$_G;
	foreach ($key as $k) {
		if (!isset($v[$k])) {
			return null;
		}
		$v = &$v[$k];
	}
	return $v;
}

/**
 * 获取 get post cookie的值
 * @param  [type] $k    [description]
 * @param  string $type [需要获取的类型，G GET  P POST C COOKIE]
 * @return [type]       [description]
 */
function getgpc($k, $type = 'GP') {
	$type = strtoupper($type);
	switch ($type) {
	case 'G':$var = &$_GET;
		break;
	case 'P':$var = &$_POST;
		break;
	case 'C':$var = &$_COOKIE;
		break;
	default:
		if (isset($_GET[$k])) {
			$var = &$_GET;
		} else {
			$var = &$_POST;
		}
		break;
	}
	return isset($var[$k]) ? $var[$k] : NULL;
}

/**
 * 字符长度统计
 * @param  [type] $str [description]
 * @return [type]      [description]
 */
function dstrlen($str) {
	if (strtolower(CHARSET) != 'utf-8') {
		return strlen($str);
	}
	$count = 0;
	for ($i = 0; $i < strlen($str); $i++) {
		$value = ord($str[$i]);
		if ($value > 127) {
			$count++;
			if ($value >= 192 && $value <= 223) {
				$i++;
			} elseif ($value >= 224 && $value <= 239) {
				$i = $i + 2;
			} elseif ($value >= 240 && $value <= 247) {
				$i = $i + 3;
			}

		}
		$count++;
	}
	return $count;
}

/**
 * 字符串截取函数
 * @param  [type] $string [description]
 * @param  [type] $length [description]
 * @param  string $dot    [description]
 * @return [type]         [description]
 */
function cutstr($string, $length, $dot = ' ...') {
	if (strlen($string) <= $length) {
		return $string;
	}

	$pre = chr(1);
	$end = chr(1);
	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end), $string);

	$strcut = '';
	if (strtolower(CHARSET) == 'utf-8') {

		$n = $tn = $noc = 0;
		while ($n < strlen($string)) {

			$t = ord($string[$n]);
			if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1;
				$n++;
				$noc++;
			} elseif (194 <= $t && $t <= 223) {
				$tn = 2;
				$n += 2;
				$noc += 2;
			} elseif (224 <= $t && $t <= 239) {
				$tn = 3;
				$n += 3;
				$noc += 2;
			} elseif (240 <= $t && $t <= 247) {
				$tn = 4;
				$n += 4;
				$noc += 2;
			} elseif (248 <= $t && $t <= 251) {
				$tn = 5;
				$n += 5;
				$noc += 2;
			} elseif ($t == 252 || $t == 253) {
				$tn = 6;
				$n += 6;
				$noc += 2;
			} else {
				$n++;
			}

			if ($noc >= $length) {
				break;
			}

		}
		if ($noc > $length) {
			$n -= $tn;
		}

		$strcut = substr($string, 0, $n);

	} else {
		$_length = $length - 1;
		for ($i = 0; $i < $length; $i++) {
			if (ord($string[$i]) <= 127) {
				$strcut .= $string[$i];
			} else if ($i < $_length) {
				$strcut .= $string[$i] . $string[++$i];
			}
		}
	}

	$strcut = str_replace(array($pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

	$pos = strrpos($strcut, chr(1));
	if ($pos !== false) {
		$strcut = substr($strcut, 0, $pos);
	}
	return $strcut . $dot;
}

/**
 *  反引用一个引用字符串
 *
 * @param  [type] $string [description]
 * @see https://www.php.net/manual/zh/function.stripslashes.php
 * @return [type]         [description]
 */
function dstripslashes($string) {
	if (empty($string)) {
		return $string;
	}

	if (is_array($string)) {
		foreach ($string as $key => $val) {
			$string[$key] = dstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}
/**
 * 获取随机数
 * @param  [type]  $length  [description]
 * @param  integer $numeric [description]
 * @return [type]           [description]
 */
function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
	if ($numeric) {
		$hash = '';
	} else {
		$hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
		$length--;
	}
	$max = strlen($seed) - 1;
	for ($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}

/**
 * 字符串查找
 * @param  [type] $string [description]
 * @param  [type] $find   [description]
 * @return [type]         [description]
 */
function strexists($string, $find) {
	return !(strpos($string, $find) === FALSE);
}

function dsetcookie($var, $value = '', $life = 0, $prefix = 1, $httponly = false) {

	global $_G;

	$config = $_G['config']['cookie'];

	$_G['cookie'][$var] = $value;
	$var = ($prefix ? $config['cookiepre'] : '') . $var;
	$_COOKIE[$var] = $value;

	if ($value === '' || $life < 0) {
		$value = '';
		$life = -1;
	}

	if (defined('IN_MOBILE')) {
		$httponly = false;
	}

	$life = $life > 0 ? getglobal('timestamp') + $life : ($life < 0 ? getglobal('timestamp') - 31536000 : 0);
	$path = $httponly && PHP_VERSION < '5.2.0' ? $config['cookiepath'] . '; HttpOnly' : $config['cookiepath'];

	$secure = $_G['isHTTPS'];
	if (PHP_VERSION < '5.2.0') {
		setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure);
	} else {
		setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure, $httponly);
	}
}

function getcookie($key) {
	global $_G;
	return isset($_G['cookie'][$key]) ? $_G['cookie'][$key] : '';
}

function fileext($filename) {
	return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
}

function dhtmlspecialchars($string, $flags = null) {
	if (is_array($string)) {
		foreach ($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val, $flags);
		}
	} else {
		if ($flags === null) {
			$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
		} else {
			if (PHP_VERSION < '5.4.0') {
				$string = htmlspecialchars($string, $flags);
			} else {
				if (strtolower(CHARSET) == 'utf-8') {
					$charset = 'UTF-8';
				} else {
					$charset = 'ISO-8859-1';
				}
				$string = htmlspecialchars($string, $flags, $charset);
			}
		}
	}
	return $string;
}

function clear($message) {
	return str_replace(array("\t", "\r", "\n"), " ", $message);
}

function sql_clear($message) {
	$message = clear($message);
	$message = str_replace(DB::object()->tablepre, '', $message);
	$message = dhtmlspecialchars($message);
	return $message;
}

function write_error_log($message) {

	$message = clear($message);
	$time = time();
	$file = DOCUMENT_ROOT . './data/log/' . date("Ym") . '_errorlog.php';
	$hash = md5($message);

	$uid = getglobal('uid');
	$ip = getglobal('clientip');

	$user = '<b>User:</b> uid=' . intval($uid) . '; IP=' . $ip . '; RIP:' . $_SERVER['REMOTE_ADDR'];
	$uri = 'Request: ' . dhtmlspecialchars(clear($_SERVER['REQUEST_URI']));
	$message = "<?PHP exit;?>\t{$time}\t$message\t$hash\t$user $uri\n";
	if ($fp = @fopen($file, 'rb')) {
		$lastlen = 50000;
		$maxtime = 60 * 10;
		$offset = filesize($file) - $lastlen;
		if ($offset > 0) {
			fseek($fp, $offset);
		}
		if ($data = fread($fp, $lastlen)) {
			$array = explode("\n", $data);
			if (is_array($array)) {
				foreach ($array as $key => $val) {
					$row = explode("\t", $val);
					if ($row[0] != '<?PHP exit;?>') {
						continue;
					}

					if ($row[3] == $hash && ($row[1] > $time - $maxtime)) {
						return;
					}
				}
			}

		}
	}
	error_log($message, 3, $file);
}

/**
 * 获取客户端IP
 * @return [type] [description]
 */
function get_client_ip() {
	$ip = $_SERVER['REMOTE_ADDR'];
	if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
		foreach ($matches[0] AS $xip) {
			if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
				$ip = $xip;
				break;
			}
		}
	}
	return $ip == '::1' ? '127.0.0.1' : $ip;
}
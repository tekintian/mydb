<?php
namespace tekintian\mydb\dirver;

/**
 * mysql 数据库驱动封装
 */
class DriverMysql
{
	var $tablepre;
	var $version = '';
	var $drivertype = 'mysql';
	var $querynum = 0;
	var $slaveid = 0;
	var $curlink;
	var $link = array();
	var $config = array();
	var $sqldebug = array();
	var $map = array();
	/**
	 * 配置设置
	 * @param  array  $config [description]
	 * @return [type]         [description]
	 */
	function db_mysql($config = array()) {
		if(!empty($config)) {
			$this->setConfig($config);
		}
	}
	/**
	 * 信息配置
	 * @param [type] $config [description]
	 */
	function setConfig($config) {
		$this->config = &$config;
		$this->tablepre = $config['1']['tablepre'];
		if(!empty($this->config['map'])) {
			$this->map = $this->config['map'];
		}
	}
	/**
	 * 数据库连接
	 * @param  integer $serverid [description]
	 * @return [type]            [description]
	 */
	function connect($serverid = 1) {

		if(empty($this->config) || empty($this->config[$serverid])) {
			$this->halt('config_db_not_found');
		}

		$this->link[$serverid] = $this->_dbconnect(
			$this->config[$serverid]['dbhost'],
			$this->config[$serverid]['dbuser'],
			$this->config[$serverid]['dbpw'],
			$this->config[$serverid]['dbcharset'],
			$this->config[$serverid]['dbname'],
			$this->config[$serverid]['pconnect']
			);
		$this->curlink = $this->link[$serverid];

	}
	/**
	 * 数据库连接方法
	 * @param  [type]  $dbhost    [description]
	 * @param  [type]  $dbuser    [description]
	 * @param  [type]  $dbpw      [description]
	 * @param  [type]  $dbcharset [description]
	 * @param  [type]  $dbname    [description]
	 * @param  [type]  $pconnect  [description]
	 * @param  boolean $halt      [description]
	 * @return [type]             [description]
	 */
	function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $pconnect, $halt = true) {

		if($pconnect) {
			$link = @mysql_pconnect($dbhost, $dbuser, $dbpw, MYSQL_CLIENT_COMPRESS);
		} else {
			$link = @mysql_connect($dbhost, $dbuser, $dbpw, 1, MYSQL_CLIENT_COMPRESS);
		}
		if(!$link) {
			$halt && $this->halt('notconnect', $this->errno());
		} else {
			$this->curlink = $link;
			if($this->version() > '4.1') {
				$dbcharset = $dbcharset ? $dbcharset : $this->config[1]['dbcharset'];
				$serverset = $dbcharset ? 'character_set_connection='.$dbcharset.', character_set_results='.$dbcharset.', character_set_client=binary' : '';
				$serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',').'sql_mode=\'\'') : '';
				$serverset && mysql_query("SET $serverset", $link);
			}
			$dbname && @mysql_select_db($dbname, $link);
		}
		return $link;
	}
	/**
	 * 获取表明
	 * @param  [type] $tablename [description]
	 * @return [type]            [description]
	 */
	function table_name($tablename) {
		if(!empty($this->map) && !empty($this->map[$tablename])) {
			$id = $this->map[$tablename];
			if(!$this->link[$id]) {
				$this->connect($id);
			}
			$this->curlink = $this->link[$id];
		} else {
			$this->curlink = $this->link[1];
		}
		return $this->tablepre.$tablename;
	}
	/**
	 * 查询db
	 * @param  [type] $dbname [description]
	 * @return [type]         [description]
	 */
	function select_db($dbname) {
		return mysql_select_db($dbname, $this->curlink);
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}

	function result_first($sql) {
		return $this->result($this->query($sql), 0);
	}

	public function query($sql, $silent = false, $unbuffered = false) {
		if(defined('MYDB_DEBUG') && MYDB_DEBUG) {
			$starttime = microtime(true);
		}

		if('UNBUFFERED' === $silent) {
			$silent = false;
			$unbuffered = true;
		} elseif('SILENT' === $silent) {
			$silent = true;
			$unbuffered = false;
		}

		$func = $unbuffered ? 'mysql_unbuffered_query' : 'mysql_query';

		if(!($query = $func($sql, $this->curlink))) {
			if(in_array($this->errno(), array(2006, 2013)) && substr($silent, 0, 5) != 'RETRY') {
				$this->connect();
				return $this->query($sql, 'RETRY'.$silent);
			}
			if(!$silent) {
				$this->halt($this->error(), $this->errno(), $sql);
			}
		}

		if(defined('MYDB_DEBUG') && MYDB_DEBUG) {
			$this->sqldebug[] = array($sql, number_format((microtime(true) - $starttime), 6), debug_backtrace(), $this->curlink);
		}

		$this->querynum++;
		return $query;
	}

	function affected_rows() {
		return mysql_affected_rows($this->curlink);
	}

	function error() {
		return (($this->curlink) ? mysql_error($this->curlink) : mysql_error());
	}

	function errno() {
		return intval(($this->curlink) ? mysql_errno($this->curlink) : mysql_errno());
	}

	function result($query, $row = 0) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function num_fields($query) {
		return mysql_num_fields($query);
	}

	function free_result($query) {
		return mysql_free_result($query);
	}

	function insert_id() {
		return ($id = mysql_insert_id($this->curlink)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}

	function fetch_fields($query) {
		return mysql_fetch_field($query);
	}

	function version() {
		if(empty($this->version)) {
			$this->version = mysql_get_server_info($this->curlink);
		}
		return $this->version;
	}

	function escape_string($str) {
		return mysql_real_escape_string($str);
	}

	function close() {
		return mysql_close($this->curlink);
	}

	function halt($message = '', $code = 0, $sql = '') {
		throw new tekintian\mydb\DbException($message, $code, $sql);
	}

}

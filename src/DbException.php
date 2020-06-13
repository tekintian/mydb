<?php
namespace tekintian\mydb;

/**
 * sql异常捕获类
 * @Author: tekin
 * @Date:   2020-06-12 13:23:51
 * @Last Modified 2020-06-13
 * @Last Modified time: 2020-06-13 13:04:14
 */
class DbException extends \Exception{

	public $sql;

	public function __construct($message, $code = 0, $sql = '') {
		$this->sql = $sql;
		parent::__construct($message, $code);
	}

	public function getSql() {
		return $this->sql;
	}
}
# 支持主从，分库分表的高效安全的mysql/mysqli数据库驱动封装





## 使用方法
composer require tekintian\mydb
~~~php
// 使用自动加载
require_once __DIR__ . '/vendor/autoload.php';
// 利用类继承给类起个简短的别名
class DB extends tekintian\mydb\MyDb {}

// 获取MyDb中的 $db 对象并引用赋值给 $db
$db = & DB::object();
$dbversion = $db->version(); // 使用 $db对象获取mysql版本

// DB静态方法查询数据
// 使用 DB::table('common_district') 来自动加上配置的数据库表前缀
$list = DB::fetch_all("select * from " . DB::table('common_district') . " limit 100");

print_r($list);

~~~



### 使用示例
~~~php
<?php
/**
 * MyDb 数据库类使用示例
 * @Author: tekin
 * @Date:   2020-06-12 10:56:13
 * @Last Modified 2020-06-13
 * @Last Modified time: 2020-06-13 14:43:15
 */
// 定义常量 IN_MYDB
defined('IN_MYDB') or define('IN_MYDB', true);

// // 不允许直接访问， 必须是在定义了常量 IN_MYDB的页面中访问, 在不允许直接访问的页面顶部加上下面的判断即可
// !defined('IN_MYDB') && exit('Access Denied'); 

defined('MYDB_DEBUG') or define('MYDB_DEBUG', true); // 是否开启mysqlt调试模式，true开启， false关闭

// 使用自动加载
require_once __DIR__ . '/vendor/autoload.php';

// 手动加载, 注意文件的路径
// require_once __DIR__ . '/src/MyDb.php';

// 配置文件加载
include_once __DIR__ . '/config.php';

// 利用类继承给类起个简短的别名
class DB extends tekintian\mydb\MyDb {}

// 初始化数据库连接
DB::init($_config['db']);

// 获取MyDb中的 $db 对象并引用赋值给 $db
$db = & DB::object();

$mysql_version = $db->version(); // 获取mysql版本

$db->insert_id(); // 插入的数据ID
$db->affected_rows(); // 数据更新数量

// 获取数据库查询对象
$query = $db->query("select * from " . DB::table('common_district') . " where id <100 "); // 数据更新数量

$list=[];
while ($row = $db->fetch_array($query)) {
	$list[]=$row;
}
pp($list);

// 使用 DB::table('common_district') 来自动加上配置的数据库表前缀
$list = DB::fetch_all("select * from " . DB::table('common_district') . " limit 100");

pp($list);

// 复杂查询示例
// $query = DB::query("SELECT me.id, me.uid, me.medalid, me.dateline, me.expiration, mf.medals
// 					FROM " . DB::table('forum_medallog') . " me
// 					LEFT JOIN " . DB::table('common_member_field_forum') . " mf USING (uid)
// 					WHERE id IN ($ids)");
// while ($vv = DB::fetch($query)) {

// }

// 更多的查询方法，可参考 dz的源码

~~~



## 配置示例

~~~php

/**
 * 数据库配置文件
 * @Author: tekin
 * @Date:   2020-06-12 12:13:29
 * @Last Modified 2020-06-13
 * @Last Modified time: 2020-06-13 14:00:21
 */
$_config = array();

// ----------------------------  CONFIG DB  ----------------------------- //
$_config['db']['1']['dbhost'] = 'localhost';
$_config['db']['1']['dbuser'] = 'ultrax';
$_config['db']['1']['dbpw'] = 'ultraxultrax';
$_config['db']['1']['dbcharset'] = 'utf8';
$_config['db']['1']['pconnect'] = '0';
$_config['db']['1']['dbname'] = 'ultrax';
$_config['db']['1']['tablepre'] = 'pre_';
$_config['db']['slave'] = '';
$_config['db']['common']['slave_except_table'] = '';

// -------------  CONFIG SECURITY  ------------ //
$_config['security']['querysafe']['status'] = 1;
$_config['security']['querysafe']['dfunction']['0'] = 'load_file';
$_config['security']['querysafe']['dfunction']['1'] = 'hex';
$_config['security']['querysafe']['dfunction']['2'] = 'substring';
$_config['security']['querysafe']['dfunction']['3'] = 'if';
$_config['security']['querysafe']['dfunction']['4'] = 'ord';
$_config['security']['querysafe']['dfunction']['5'] = 'char';
$_config['security']['querysafe']['daction']['0'] = '@';
$_config['security']['querysafe']['daction']['1'] = 'intooutfile';
$_config['security']['querysafe']['daction']['2'] = 'intodumpfile';
$_config['security']['querysafe']['daction']['3'] = 'unionselect';
$_config['security']['querysafe']['daction']['4'] = '(select';
$_config['security']['querysafe']['daction']['5'] = 'unionall';
$_config['security']['querysafe']['daction']['6'] = 'uniondistinct';
$_config['security']['querysafe']['dnote']['0'] = '/*';
$_config['security']['querysafe']['dnote']['1'] = '*/';
$_config['security']['querysafe']['dnote']['2'] = '#';
$_config['security']['querysafe']['dnote']['3'] = '--';
$_config['security']['querysafe']['dnote']['4'] = '"';
$_config['security']['querysafe']['dlikehex'] = 1;
$_config['security']['querysafe']['afullnote'] = '0';

~~~




- DZ的数据库分库分表映射配置参考
~~~php
// dz 的数据库map配置参考，
for($i = 1; $i <= 100; $i++) {
	if(isset($this->map['forum_thread'])) {
		$this->map['forum_thread_'.$i] = $this->map['forum_thread'];
	}
	if(isset($this->map['forum_post'])) {
		$this->map['forum_post_'.$i] = $this->map['forum_post'];
	}
	if(isset($this->map['forum_attachment']) && $i <= 10) {
		$this->map['forum_attachment_'.($i-1)] = $this->map['forum_attachment'];
	}
}
if(isset($this->map['common_member'])) {
	$this->map['common_member_archive'] =
	$this->map['common_member_count'] = $this->map['common_member_count_archive'] =
	$this->map['common_member_status'] = $this->map['common_member_status_archive'] =
	$this->map['common_member_profile'] = $this->map['common_member_profile_archive'] =
	$this->map['common_member_field_forum'] = $this->map['common_member_field_forum_archive'] =
	$this->map['common_member_field_home'] = $this->map['common_member_field_home_archive'] =
	$this->map['common_member_validate'] = $this->map['common_member_verify'] =
	$this->map['common_member_verify_info'] = $this->map['common_member'];
}

~~~
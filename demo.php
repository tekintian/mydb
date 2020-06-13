<?php
/**
 * MyDb 数据库类使用示例
 * @Author: tekin
 * @Date:   2020-06-12 10:56:13
 * @Last Modified 2020-06-13
 * @Last Modified time: 2020-06-13 14:58:11
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
$query = $db->query("select * from " . DB::table('common_district') . " where id <10 "); // 数据更新数量

$list=[];
while ($row = $db->fetch_array($query)) {
	$list[]=$row;
}
echo "<pre>";
print_r($list);
echo "</pre>";

// 使用 DB::table('common_district') 来自动加上配置的数据库表前缀
// $list = DB::fetch_all("select * from " . DB::table('common_district') . " limit 10");
// echo "<pre>";
// print_r($list);
// echo "</pre>";

// 复杂查询示例
// $query = DB::query("SELECT me.id, me.uid, me.medalid, me.dateline, me.expiration, mf.medals
// 					FROM " . DB::table('forum_medallog') . " me
// 					LEFT JOIN " . DB::table('common_member_field_forum') . " mf USING (uid)
// 					WHERE id IN ($ids)");
// while ($vv = DB::fetch($query)) {

// }

// 更多的查询方法，可参考 dz的源码

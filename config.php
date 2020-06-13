<?php
// 不允许直接访问， 必须是在定义了常量 IN_MYDB的页面中访问
!defined('IN_MYDB') && exit('Access Denied');

/**
 * 数据库配置文件
 * @Author: tekin
 * @Date:   2020-06-12 12:13:29
 * @Last Modified 2020-06-13
 * @Last Modified time: 2020-06-13 14:02:45
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

// -------  分库分表映射配置 详细参考 setConfigh函数  ----------- //
// $_config['map']['forum_thread'] = '1'; // 可指定某些表所在的数据库连接id,既这里1代表的是 $_config['db']['1']中的1
// $_config['map']['forum_post'] = '1';


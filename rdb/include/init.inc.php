<?php 
/**
 * rdb 模块初始化
 * 2011-10-14
 *
 * @version 20120501
 * @package default
 * @filesource
 */
if (defined('RDB_PATH'))
	{
	return ;
	}
define('RDB_PATH', dirname(dirname(__FILE__)) . '/');
require_once RDB_PATH . "include/config.inc.php";
// require_once RDB_PATH . "include/global.func.php";
// require_once RDB_PATH . "include/paging.func.php";
require_once RDB_PATH . "api/rdb.class.php";
require_once RDB_PATH . "drive/drive.class.php";

//实例化数据库访问类
// global $db;
// $dbclass = 'db_'.RDB_DATABASE;
// require_once RDB_PATH . "include/{$dbclass}.class.php";
// $db = new $dbclass;
// $tmp = $db->connect(RDB_HOST, RDB_USER, RDB_PW, RDB_NAME, RDB_PCONNECT, RDB_CHARSET);
// $tmp = $tmp ? true : false;
// define('IS_DB', $tmp);	//标识数据库是否链接成功
// unset($tmp);

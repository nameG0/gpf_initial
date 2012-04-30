<?php 
/**
 * db 模块初始化
 * 
 * 2011-10-14
 * @package default
 * @filesource
 */
if (defined('DB_ROOT'))
	{
	return ;
	}
define('DB_ROOT', dirname(dirname(__FILE__)) . '/');
require_once DB_ROOT . "include/config.inc.php";
require_once DB_ROOT . "include/global.func.php";
require_once DB_ROOT . "include/paging.func.php";

//实例化数据库访问类
global $db;
$dbclass = 'db_'.DB_DATABASE;
require_once DB_ROOT . "include/{$dbclass}.class.php";
$db = new $dbclass;
$tmp = $db->connect(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_PCONNECT, DB_CHARSET);
$tmp = $tmp ? true : false;
define('IS_DB', $tmp);	//标识数据库是否链接成功
unset($tmp);
?>

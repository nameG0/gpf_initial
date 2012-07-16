<?php
/**
 * 模块初始化文件
 * 
 * @package default
 * @filesource
 */
define('GM_ADMIN_PATH', dirname(dirname(__FILE__)) . '/');
define('GM_ADMIN_PATH_PASSWORD', G_PATH_DATA . "admin/password"); //密码文件路径
define('GM_ADMIN_SESSION_KEY', 'is_admin'); //SESSION 记录 KEY

session_start();
require GM_ADMIN_PATH . "api/admin.func.php";

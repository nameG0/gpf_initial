<?php
/**
 * 模块初始化文件
 * 
 * @package default
 * @filesource
 */
//加载 PHPCMS 的公共函数
gpf::inc(GPF_PATH_LIB . "phpcms2008/include/global.func.php");
gpf::inc(GPF_PATH_LIB . "phpcms2008/include/form.class.php");

gmod::inc('admin', 'include/global.func.php');
// define('GM_ADMIN_PATH', dirname(dirname(__FILE__)) . '/');
// define('GM_ADMIN_PATH_PASSWORD', G_PATH_DATA . "admin/password"); //密码文件路径
// define('GM_ADMIN_SESSION_KEY', 'is_admin'); //SESSION 记录 KEY

// session_start();
// require GM_ADMIN_PATH . "api/admin.func.php";

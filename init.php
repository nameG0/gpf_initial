<?php
/**
 * GPF 框架入口文件。
 * 
 * @version 2012-04-04
 * @package default
 * @filesource
 */

// 入口文件必须定义常量：
// GPF_PATH_MODULE 模块目录，以 / 结尾。

define('GPF_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR); //gpf 目录
require GPF_PATH . "gpf.func.php";
// require GPF_PATH . "gpf.cls.php";
// require GPF_PATH . "gmod.cls.php";

register_shutdown_function('_gpf_shutdown_function');

// define('DS', DIRECTORY_SEPARATOR);
// //路径常量用 G_PATH_ 前序。
// define('GPF_PATH_CORE', GPF_PATH . 'core' . DS); //gpf core 目录
// define('GPF_PATH_LIB', GPF_PATH . 'lib' . DS); //gpf lib 目录

// //加载配置文件
// require GPF_PATH . "config.inc.php";

// //load core
// require GPF_PATH_CORE . "module.func.php";
// require GPF_PATH_CORE . "log.class.php";
// require GPF_PATH_CORE . "gpf.class.php";

// //load lib
// include GPF_PATH_LIB . "common.func.php";

// define('SCRIPT_NAME', isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : preg_replace("/(.*)\.php(.*)/i", "\\1.php", $_SERVER['PHP_SELF']));
// define('QUERY_STRING', $_SERVER['QUERY_STRING']);
// log::add("TIME[" . time() . "] FILE[".SCRIPT_NAME."?".QUERY_STRING."]", log::INFO);

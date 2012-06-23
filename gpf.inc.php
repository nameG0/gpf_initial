<?php
/**
 * GPF 框架入口文件。
 * 
 * @version 2012-04-04
 * @package default
 * @filesource
 */
error_reporting(E_ALL ^ E_NOTICE);

// 入口文件必须定义常量：
// *因为 gencms 并不知道项目的名称，因此如数据目录需要在入口文件中定义。
// $ds = DIRECTORY_SEPARATOR;$dir = dirname(__FILE__);
// define('GPF_PATH_SOUR', "{$dir}{$ds}sour{$ds}"); //源目录(系统模块)
// define('GPF_PATH_INST', "{$dir}{$ds}inst{$ds}"); //副本目录(项目模块)。
// define('G_PATH_DATA', "{$dir}{$ds}data{$ds}"); //数据目录。
// define('G_PATH_UPLOADFILE', "{$dir}{$ds}uploadfile{$ds}"); //上传文件保存目录。
// define('G_PATH_CACHE', "{$dir}{$ds}cache{$ds}"); //缓存文件目录。
// include "{$dir}{$ds}gpf{$ds}gpf.inc.php";
// unset($ds, $dir);

define('DS', DIRECTORY_SEPARATOR);
//路径常量用 G_PATH_ 前序。
define('GPF_PATH', dirname(__FILE__) . DS); //gpf 目录
define('GPF_PATH_CORE', GPF_PATH . 'core' . DS); //gpf core 目录
define('GPF_PATH_LIB', GPF_PATH . 'lib' . DS); //gpf lib 目录

//加载配置文件
require GPF_PATH . "config.inc.php";

//load core
require GPF_PATH_CORE . "module.func.php";
require GPF_PATH_CORE . "log.class.php";
require GPF_PATH_CORE . "gpf.class.php";

//load lib
include GPF_PATH_LIB . "common.func.php";

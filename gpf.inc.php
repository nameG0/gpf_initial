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
// $ds = DIRECTORY_SEPARATOR;$dir = dirname(__FILE__);$pdir = dirname($dir);$name = 'project_name';
// define('G_PATH_INST', $dir . $ds); //项目目录。
// define('G_PATH_DATA', "{$pdir}{$ds}{$name}_data{$ds}"); //数据目录。
// define('G_PATH_UPLOADFILE', "{$pdir}{$ds}{$name}_uploadfile{$ds}"); //上传文件保存目录。
// define('G_PATH_CACHE', "{$pdir}{$ds}{$name}_cache{$ds}"); //缓存文件目录。
// unset($ds, $dir, $pdir, $name);

define('DS', DIRECTORY_SEPARATOR);
//路径常量用 G_PATH_ 前序。
define('G_PATH_GPF', dirname(__FILE__) . DS); //gpf 目录
define('G_PATH_GPF_CORE', G_PATH_GPF . 'core' . DS); //gpf core 目录
define('G_PATH_GPF_LIB', G_PATH_GPF . 'lib' . DS); //gpf lib 目录
define('G_PATH_MOD_SOUR', dirname(G_PATH_GPF) . DS); //系统模块目录
define('G_PATH_MOD_INST', G_PATH_INST); //项目模块目录

//加载配置文件
require G_PATH_GPF . "config.inc.php";

//load core
require G_PATH_GPF_CORE . "module.func.php";
require G_PATH_GPF_CORE . "log.class.php";
require G_PATH_GPF_CORE . "gpf.class.php";

//load lib
include G_PATH_GPF_LIB . "common.func.php";

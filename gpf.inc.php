<?php
/**
 * GPF 框架入口文件。
 * 
 * @version 2012-04-04
 * @package default
 * @filesource
 */
error_reporting(E_ALL ^ E_NOTICE);

define('DS', DIRECTORY_SEPARATOR);
//路径常量用 G_PATH_ 前序。
define('G_PATH_GPF', dirname(__FILE__) . DS); //gpf 目录
define('G_PATH_GPF_CORE', G_PATH_GPF . 'core' . DS); //gpf core 目录
define('G_PATH_GPF_LIB', G_PATH_GPF . 'lib' . DS); //gpf core 目录
define('G_PATH_MOD', G_PATH_GPF . '../'); //系统模块目录
define('G_PATH_MOD_RUN', G_PATH_RUN); //项目模块目录

//入口文件必须定义常量
//define('G_PATH_RUN'); //项目目录。

//load core
require G_PATH_GPF_CORE . "module.func.php";
require G_PATH_GPF_CORE . "log.class.php";
require G_PATH_GPF_CORE . "gpf.class.php";

//load lib
include G_PATH_GPF_LIB . "common.func.php";

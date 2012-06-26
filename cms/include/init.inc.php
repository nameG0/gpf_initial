<?php
/**
 * 模块初始化
 * 
 * @package default
 * @filesource
 */
define('CMS_PATH', dirname(dirname(__FILE__)) . DS);
define('CMS_PATH_DATA', G_PATH_DATA . 'cms' . DS);
// $mod = 'category';
// require_once dirname(__FILE__) . "/../../include/common.inc.php";
// require_once PHPCMS_ROOT . "{$mod}/include/init.inc.php";
require CMS_PATH . "include/global.func.php";

global $CATEGORY;
$CATEGORY = category_get();

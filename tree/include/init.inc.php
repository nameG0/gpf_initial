<?php 
/**
 * category 模块初始化
 * 
 * 2011-10-14
 * @package default
 * @filesource
 */
if (defined('CATEGORY_ROOT'))
	{
	return ;
	}
define('CATEGORY_ROOT', dirname(dirname(__FILE__)) . '/');
include CATEGORY_ROOT . 'include/config.inc.php';
include CATEGORY_ROOT . 'lang/zh.inc.php';
// require_once CATEGORY_ROOT . "include/cache.func.php";
// require_once CATEGORY_ROOT . "include/global.func.php";

global $CATEGORY;
// $CATEGORY = category_get();

// require_once PHPCMS_ROOT . "content/include/init.inc.php";
mod_init('conm');

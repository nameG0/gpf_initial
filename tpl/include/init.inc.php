<?php 
/**
 * 初始化
 * 20120505
 * 
 * @version 20120505
 * @package default
 * @filesource
 */
if (defined('GM_PATH_TPL'))
	{
	return ;
	}
define('GM_PATH_TPL', dirname(dirname(__FILE__)) . DS);

include GM_PATH_TPL . 'include/config.inc.php';
require_once GM_PATH_TPL . "api/tpl.func.php";

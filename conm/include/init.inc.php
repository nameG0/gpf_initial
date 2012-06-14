<?php
if (defined('GM_PATH_CONM'))
	{
	return ;
	}
define('GM_PATH_CONM', dirname(dirname(__FILE__)) . '/');
require GM_PATH_CONM . "include/model.func.php";
require GM_PATH_CONM . "api/model.func.php";
// require GM_PATH_CONM . "include/field.func.php";
// include CONTENT_ROOT . 'include/global.func.php';
// include CONTENT_ROOT . 'include/config.inc.php';
// include CONTENT_ROOT . 'include/cache.func.php';

// global $MODEL;
// $MODEL = content_model();

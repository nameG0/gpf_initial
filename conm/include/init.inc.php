<?php
if (defined('GM_CONM_PATH'))
	{
	return ;
	}
define('GM_CONM_PATH', dirname(dirname(__FILE__)) . '/');
require GM_CONM_PATH . "include/model.func.php";
require GM_CONM_PATH . "api/model.func.php";
require GM_CONM_PATH . "include/model_plug.func.php";
// require GM_CONM_PATH . "include/field.func.php";
// include CONTENT_ROOT . 'include/global.func.php';
// include CONTENT_ROOT . 'include/config.inc.php';
// include CONTENT_ROOT . 'include/cache.func.php';

// global $MODEL;
// $MODEL = content_model();

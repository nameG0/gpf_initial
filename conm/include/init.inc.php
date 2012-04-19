<?php
defined('IN_PHPCMS') or exit('Access Denied');
define('CONTENT_ROOT', dirname(dirname(__FILE__)) . '/');
include CONTENT_ROOT . 'include/global.func.php';
include CONTENT_ROOT . 'include/config.inc.php';
include CONTENT_ROOT . 'include/cache.func.php';

global $MODEL;
$MODEL = content_model();
?>

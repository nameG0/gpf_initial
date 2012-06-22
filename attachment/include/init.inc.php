<?php 
/*
2011-10-15
附件模块
*/
if (defined('M_ATTA_ROOT'))
	{
	return ;
	}

// defined('IN_PHPCMS') or exit('Access Denied');
define('M_ATTA_ROOT', dirname(dirname(__FILE__)) . '/');
include M_ATTA_ROOT . 'include/config.inc.php';
include M_ATTA_ROOT . 'api/upload.func.php';

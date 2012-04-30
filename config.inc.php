<?php 
/**
 * GPF 默认配置
 * 20120430
 * 
 * @version 20120430
 * @package default
 * @filesource
 */
//加载项目默认配置
$_tmp = G_PATH_MOD_RUN . "include/config.inc.php";
if (is_file($_tmp))
	{
	include $_tmp;
	}
$_tmp = array(
	);
foreach ($_tmp as $k => $v)
	{
	if (!defined($k))
		{
		define($k, $v);
		}
	}
unset($_tmp);

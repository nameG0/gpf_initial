<?php
/**
 * 显示 PHP 类型模板的 API
 * ggzhu@2012-05-05
 * 
 * @version 2012-05-05
 * @package default
 * @filesource
 */

function tpl($name, $mod = CURRENT_MOD)
{//{{{
	$path = GM_PATH_TPL_INST . "{$mod}/{$name}.tpl.php";
	if (is_file($path))
		{
		return $path;
		}
	return G_PATH_MOD_SOUR . "{$mod}/template/{$name}.tpl.php";
}//}}}

function tpl_admin($name, $mod = CURRENT_MOD)
{//{{{
	$path = GM_PATH_TPL_INST . "{$mod}/admin/{$name}.tpl.php";
	if (is_file($path))
		{
		return $path;
		}
	return G_PATH_MOD_SOUR . "{$mod}/template/admin/{$name}.tpl.php";
}//}}}

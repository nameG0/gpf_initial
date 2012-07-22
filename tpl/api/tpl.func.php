<?php
/**
 * 显示 PHP 类型模板的 API
 * ggzhu@2012-05-05
 * 
 * @version 2012-05-05
 * @package default
 * @filesource
 */

function tpl($name, $mod = CTRL_MOD)
{//{{{
	$path = GM_PATH_TPL_INST . "{$mod}/{$name}.tpl.php";
	// return $path;
	if (is_file($path))
		{
		return $path;
		}
	return GPF_PATH_SOUR . "{$mod}/template/{$name}.tpl.php";
}//}}}

function tpl_admin($name, $mod = CTRL_MOD)
{//{{{
	$path = GM_PATH_TPL_INST . "{$mod}/admin/{$name}.tpl.php";
	if (!is_file($path))
		{
		$path = GPF_PATH_SOUR . "{$mod}/template/admin/{$name}.tpl.php";
		}
	log::add("{$mod}/{$name}::{$path}", log::INFO, __FILE__, __LINE__, __FUNCTION__);
	return $path;
}//}}}

/**
 * 临时定义一个提示用函数
 */
function showmessage($msg, $url = NULL)
{//{{{
	echo "showmessage:{$msg}";
	if ($url)
		{
		?>
		<a href="<?=$url?>">GO</a>
		<?php
		}
	exit;
}//}}}

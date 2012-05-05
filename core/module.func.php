<?php
/**
 * 模块间通讯函数
 * 
 * @version 2012-04-04
 * @package default
 * @filesource
 */

/**
 * 初始化指定模块
 * 加载模块 include/init.inc.php 文件。
 * 此处不检查模块是否重复初始化，需要模块 init 文件自行检查。因为本函数可以被跳过而直接 include init 文件。
 */
function mod_init($mod)
{//{{{
	$mod_path = mod_info($mod, 'path_full');
	$path = "{$mod_path}/include/init.inc.php";
	if (!is_file($path))
		{
		log::add("无法初始化模块 {$mod}, init 文件不存在", log::NOTEXI, __FILE__, __LINE__, __FUNCTION__);
		return false;
		}
	log::add($mod, log::INFO, __FILE__, __LINE__, __FUNCTION__);
	//init 文件可通过返回 true/false 标记模块初始化是否成功
	$ret = include $path;
	if (is_bool($ret))
		{
		return $ret;
		}
	return true;
}//}}}

/**
 * 读取指定模块信息。
 * @param string $mod 模块名。
 * @param NULL|string $key 信息名，NULL 表示返回所有信息，此时会返回数组。
 * @return mixed|false
 */
function mod_info($mod, $key = NULL)
{//{{{
	if ('main' == $mod && 'path_full' == $key)
		{
		return G_PATH_MOD_INST . 'main' . DS;
		}
	if ('conm' == $mod && 'path_full' == $key)
		{
		return G_PATH_MOD_SOUR . 'conm' . DS;
		}
	if ('rdb' == $mod && 'path_full' == $key)
		{
		return G_PATH_MOD_SOUR . 'rdb' . DS;
		}
	if ('tpl' == $mod && 'path_full' == $key)
		{
		return G_PATH_MOD_SOUR . 'tpl' . DS;
		}
}//}}}

/**
 * 进行模块的 callback 操作。包括，注册，提取。
 */
//mod_callback

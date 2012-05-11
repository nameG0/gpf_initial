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
 * 进行模块的 callback 操作。
 * <b>查询</b>
 * <code>
 * mod_callback('conm');
 * </code>
 * <pre>
 * 返回所有在 conm 模块注册过的模块的 callback 目录绝对路径，一个模块可能会有两个 callback 目录，一个是源目录中，一个在副本目录中。
 * array[] = callback 目录绝对路径
 * </pre>
 * <b>注册</b>
 * <code>
 * mod_callback('conm', 'add', 'category');
 * </code>
 * <pre>
 * 把 category 模块注册到 conm 模块的 callback 列表中。
 * </pre>
 * <b>删除</b>
 * <code>
 * mod_callback('conm', 'del', 'category');
 * </code>
 * <pre>
 * 把 category 模块从 conm 模块的 callback 列表中删除。
 * </pre>
 * @param string $target 目标模块。
 * @param string|NULL $action 操作{add:注册, del:删除, NULL:查询}
 * @param string|NULL $register 注册模块。
 * @return array|bool 查询模块的 callback 时返回数组，其它操作返回 t/f 。
 */
function mod_callback($target, $action = NULL, $register = NULL)
{//{{{
	static $cache = array(); //把已读取过的数据缓存在内存变量中。
	static $callback = array(); //缓存查询操作的返回结果。

	//------ 主要思路 ------
	//所有操作都先把目标模块的 callback 数据缓存在 $cache 变量中，修改及删除操作先修改变量中的数据，再持久化到文件中保存。
	//文件保存在 project_name_data/gpf/mod_callback/ 下，每个目标模块一个文件，文件内容为注册到此目标模块下的模块列表。
	//注册模块列表使用数组保存，保存到文件时用 serialize 序列化。
	//------------

	if (!isset($cache[$target]))
		{
		$path = GPF_PATH_DATA . "mod_callback/{$target}";
		if (is_file($path))
			{
			$data = file_get_contents($path);
			$cache[$target] = unserialize($data);
			unset($data);
			}
		else
			{
			$cache[$target] = array();
			}
		unset($path);
		}

	switch ($action)
		{
		default:
			if (is_null($action))
				{
				if (!isset($callback[$target]))
					{
					
					}
				return $callback[$target];
				}
			break;
		}
}//}}}

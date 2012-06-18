<?php
/**
 * 模块间通讯函数
 *
 * <pre>
 * <b>info:模块信息</b>
 * path_sour 源绝对路径，不存在则为空字符串。
 * path_inst 副本绝对路径，不存在则为空字符串。
 * <b>数据结构</b>
 * $ModInfo array mod_info() 的返回值，包含模块信息的数组。
 * </pre>
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
	$ModInfo = mod_info($mod);
	if (!$ModInfo)
		{
		return false;
		}
	//优先加载副本中的 init 文件。
	$path = "{$ModInfo['path_inst']}include/init.inc.php";
	if (!is_file($path))
		{
		$path = "{$ModInfo['path_sour']}include/init.inc.php";
		if (!is_file($path))
			{
			log::add("无法初始化模块 {$mod}, 找不到 init 文件", log::NOTEXI, __FILE__, __LINE__, __FUNCTION__);
			return false;
			}
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
 *
 * @param string $mod 模块名。
 * @param NULL|string $key 信息名，NULL 表示返回所有信息，此时会返回数组。
 * @return mixed|false 若模块可用返回模块信息，否则返回 false
 */
function mod_info($mod, $key = NULL)
{//{{{
	//已读取的模块信息缓存在变量中。
	static $cache = array();

	//模块信息的命名规则为 {module_name}.info
	//模块信息保存在 {project_name_data}/module/ 下。

	if (!isset($cache[$mod]))
		{
		
		$path = GPF_PATH_DATA . "module/{$mod}.info";
		if (!is_file($path))
			{
			log::add("模块 {$mod} 未启用", log::ERROR, __FILE__, __LINE__, __FUNCTION__);
			$cache[$mod] = false;
			}
		else
			{
			$mod_info = unserialize(file_get_contents($path));

			//生成 path_sour, path_inst 。
			$path_sour = G_PATH_MOD_SOUR . $mod . DS;
			$mod_info['path_sour'] = is_dir($path_sour) ? $path_sour : '';
			$path_inst = G_PATH_MOD_INST . $mod . DS;
			$mod_info['path_inst'] = is_dir($path_inst) ? $path_inst : '';

			$cache[$mod] = $mod_info;
			}
		}

	if (!is_null($key) && is_array($cache[$mod]))
		{
		return $cache[$mod][$key];
		}
	return $cache[$mod];
}//}}}

/**
 * 进行模块的 callback 操作。
 *
 * <code>
 * mod_callback('module'); //$action 默认使用 rp ,即与下面一至.
 * mod_callback('module', 'rp'); //rp(register path):返回注册在 {module} 模块下所有 callback 目录绝对路径(包括源与副本 callback 目录)
 * mod_callback('module', 'rm'); //rm(register module):返回注册在 {module} 模块下所有 callback 模块名.
 * mod_callback('module', 'p'); //p(path):返回 {module} 模块自己的 callback 目录绝对路径(包括源与副本的 callback 目录)
 * mod_callback('module', 'add', 'module_2'); //add:把 {module_2} 模块加入 {module} 模块的 callback 注册列表中.
 * mod_callback('module', 'del', 'module_2'); //del:把 {module_2} 模块从 {module} 模块的 callback 注册列表中删除.
 * </code>
 * <pre>
 * <b>返回值</b>
 * rp:array('{module}/sour' => {module}模块源目录 callback 绝对路径, '{module}/inst' => {module}模块副本目录 callback 绝对路径, ...);
 * p:同 rp 的返回值.
 * rm:array('{module_1}', '{module_2}', ...)
 * <b>说明</b>
 * 一个模块可能会有两个 callback 目录，一个是源目录中，一个在副本目录中。
 * </pre>
 * @param string $target 目标模块。
 * @param string|NULL $action 操作{add:注册, del:删除, NULL:查询}
 * @param string|NULL $register 注册模块。
 * @return array|bool 查询模块的 callback 时返回数组，其它操作返回 t/f 。
 */
function mod_callback($target, $action = 'rp', $register = NULL)
{//{{{
	//缓存模块的 callback 注册列表。[mod] => array(call_1, call_2, ...)
	static $cache = array();
	//缓存模块的 callback 目录绝对路径. [mod] => array("sour" => path, "inst" => path,)
	static $callback = array();

	//因为函数本身会使用 $action=p 读取模块的 callback 目录，所以优先处理。
	if ('p' == $action)
		{
		if (!isset($callback[$target]))
			{
			//使用 mod_info() 取得模块的源路径与副本路径。分别检查是否带有 callback 目录。
			$ModInfo = mod_info($target);
			if (!$ModInfo)
				{
				$callback[$target] = false;
				}
			else
				{
				$path = "{$ModInfo['path_sour']}callback" . DS;
				if ($ModInfo['path_sour'] && is_dir($path))
					{
					$callback[$target]["sour"] = $path;
					}
				$path = "{$ModInfo['path_inst']}callback" . DS;
				if ($ModInfo['path_inst'] && is_dir($path))
					{
					$callback[$target]["inst"] = $path;
					}
				}
			}
		return $callback[$target];
		}

	//------ 主要思路 ------
	//所有操作都先把目标模块的 callback 数据缓存在 $cache 变量中，修改及删除操作先修改变量中的数据，再持久化到文件中保存。
	//文件保存在 project_name_data/gpf/module/ 下，每个目标模块一个文件，文件内容为注册到此目标模块下的模块列表。
	//文件的命名规则为 {module_name}.callback
	//注册模块列表使用数组保存，保存到文件时用 serialize 序列化。
	//------------

	$callback_file_path = GPF_PATH_DATA . "module/{$target}.callback";
	if (!isset($cache[$target]))
		{
		if (is_file($callback_file_path))
			{
			$cache[$target] = unserialize(file_get_contents($callback_file_path));
			}
		else
			{
			$cache[$target] = array();
			}
		}

	switch ($action)
		{
		//callback注册。
		case "add":
			//避免重复注册
			if (is_string($register) && !in_array($register, $cache[$target]))
				{
				$cache[$target][] = $register;
				return file_put_contents($callback_file_path, serialize($cache[$target]));
				}
			return true;
			break;
		//删除callback注册
		case "delete":
			$seek = array_search($register, $cache[$target]);
			if (false !== $seek)
				{
				unset($cache[$target][$seek]);
				return file_put_contents($callback_file_path, serialize($cache[$target]));
				}
			return true;
			break;
		case "rm":
			//模块本身总是存在于 callback 注册列表中。
			$ret = $cache[$target];
			$ret[] = $target;
			return $ret;
			break;
		case "rp":
		default:
			$list = array();
			$c_list = $cache[$target];
			$c_list[] = $target;
			foreach ($c_list as $m)
				{
				if (!isset($callback[$m]))
					{
					$callback[$m] = mod_callback($m, 'p');
					}
				if (is_array($callback[$m]))
					{
					foreach ($callback[$m] as $k => $v)
						{
						$list["{$m}/{$k}"] = $v;
						}
					}
				}
			return $list;
			break;
		}
	log::add("参数超出预设范围", log::WARN, __FILE__, __LINE__, __FUNCTION__);
	return false;
}//}}}

/**
 * 注册或删除模块信息
 *
 * <pre>
 * <b>注册</b>
 * mod_setting('name', array('setting' => array(), ...)
 * 注册 name 模块。 $info 参数为模块的信息。
 * <b>删除</b>
 * mod_setting('name', NULL);
 * </pre>
 * @param string $mod 模块名。
 * @param array|NULL 模块信息，若为 NULL 表示删除模块信息。
 */
function mod_setting($mod, $info)
{//{{{
	$path = GPF_PATH_DATA . "module/{$mod}.info";
	if (is_null($info))
		{
		if (is_file($path))
			{
			unlink($path);
			}
		return ;
		}

	$info['name'] = $mod;
	$info_str = serialize($info);
	file_put_contents($path, $info_str);
}//}}}

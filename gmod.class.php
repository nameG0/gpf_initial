<?php
/**
 * 模块间通讯类
 * 
 * @version 2012-12-12
 * @package default
 * @filesource
 */
class gmod
{
	/**
	 * 构造方法声明为private，防止直接创建对象
	 */
	private function __construct() {
	}
	/**
	 * 初始化模块
	 * 即加载模块的 include/init.inc.php
	 */
	static public function init($mod_name)
	{//{{{
		$path = GPF_PATH_MODULE . "{$mod_name}/include/init.inc.php";
		if (gpf::is_inc($path))
			{
			return true;
			}
		if (!is_file($path))
			{
			gpf::log("模块初始化文件不存在[{$path}]", gpf::WARN, '', 0, __CLASS__.'->'.__FUNCTION__);
			return false;
			}
		gpf::inc($path);
		gpf::log($mod_name, gpf::INFO, '', 0, __CLASS__.'->'.__FUNCTION__);
		return true;
	}//}}}
	/**
	 * 计算模块下文件的绝对路径
	 * @param string $path 模块下文件路径。eg. include/common.inc.php
	 * @return string 对应文件的绝对路径。
	 */
	static public function path($mod_name, $path)
	{//{{{
		return GPF_PATH_MODULE . "{$mod_name}/{$path}";
	}//}}}
	/**
	 * 单次包含模块内文件
	 * @param string $path 模块文件路径, eg. abc.class.php
	 */
	static public function inc($mod_name, $path)
	{//{{{
		gpf::inc(GPF_PATH_MODULE . "{$mod_name}/{$path}");
	}//}}}
	/**
	 * 加载模块 API 目录文件。
	 * api 目录中的类使用 {mod_name}Api_{class_name} 为前序, 对应文件名为 {class_name}.class.php。
	 * @param string $mod_name 模块名。
	 * @param string $file_name 文件名，不含 .php 后序。eg. api.func, api.class
	 */
	static public function api($mod_name, $file_name)
	{//{{{
		$path = GPF_PATH_MODULE . "{$mod_name}/api/{$file_name}.php";
		gpf::inc($path);
		return self::_api_class($mod_name, $file_name);
	}//}}}
	/**
	 * 若加载的 API 目录文件为类定义文件，则实例化。
	 */
	static private function _api_class($mod_name, $file_name)
	{//{{{
		if ('.class' !== substr($file_name, -6, 6))
			{
			return ;
			}
		$class_name = substr($file_name, 0, -6);
		$class_full = "{$mod_name}Api_{$class_name}";
		if (!gpf::is_obj($class_full))
			{
			gpf::obj_set($class_full, new $class_full());
			}
		return gpf::obj_get($class_full);
	}//}}}
	/**
	 * 加载读类(r_)Model
	 * @param string $mod_name 模块名。eg. cms
	 * @param string $class_name 类名，eg. content > r_content.class.php > r_cms_content
	 */
	static public function rm($mod_name, $class_name)
	{//{{{
		$class_full = "r_{$mod_name}_{$class_name}";
		if (gpf::is_obj($class_full))
			{
			return gpf::obj_get($class_full);
			}
		$path = GPF_PATH_MODULE . "{$mod_name}/model/r_{$class_name}.class.php";
		gpf::inc($path);
		gpf::obj_set($class_full, new $class_full());
		return gpf::obj_get($class_full);
	}//}}}
	/**
	 * 加载写类(r_)Model
	 * @param string $mod_name 模块名。eg. cms
	 * @param string $class_name 类名，eg. content > w_content.class.php > w_cms_content
	 */
	static public function wm($mod_name, $class_name)
	{//{{{
		$class_full = "w_{$mod_name}_{$class_name}";
		if (gpf::is_obj($class_full))
			{
			return gpf::obj_get($class_full);
			}
		$path = GPF_PATH_MODULE . "{$mod_name}/model/w_{$class_name}.class.php";
		gpf::inc($path);
		gpf::obj_set($class_full, new $class_full());
		return gpf::obj_get($class_full);
	}//}}}
}

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
			$path_sour = GPF_PATH_SOUR . $mod . DS;
			$mod_info['path_sour'] = is_dir($path_sour) ? $path_sour : '';
			$path_inst = GPF_PATH_INST . $mod . DS;
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

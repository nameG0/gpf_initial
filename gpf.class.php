<?php
/**
 * GPF 主类。
 * 
 * @version 2012-05-05
 * @package default
 * @filesource
 */
class gpf
{
	static private $inc = array(); //保存已加载过的文件标记。
	static private $obj = array(); //保存对象实例。

	/**
	 * 构造方法声明为private，防止直接创建对象
	 */
	private function __construct() {
	}
	/**
	 * 检测一个文件路径是否已加载
	 * @param string $path 文件绝对路径。
	 */
	static private function _is_inc($path)
	{//{{{
		if (isset($path[33]))
			{
			//长度超过 32 位转为 md5
			$path = md5($path);
			}
		return isset(self::$inc[$path]);
	}//}}}
	/**
	 * 把一个文件路径设为已加载。
	 * @param string $path 文件绝对路径。
	 */
	static private function _set_inc($path)
	{//{{{
		if (isset($path[33]))
			{
			//长度超过 32 位转为 md5
			$path = md5($path);
			}
		self::$inc[$path] = true;
	}//}}}

	/**
	 * 加载模块 API 目录文件。
	 * api 目录中的类使用 {mod_name}Api_ 为前序。
	 * @param string $mod_name 模块名。
	 * @param string $file_name 文件名，不含 .php 后序。eg. api.func, api.class
	 */
	static public function api($mod_name, $file_name)
	{//{{{
		$path = GPF_PATH_MODULE . "{$mod_name}/api/{$file_name}.php";
		if (self::_is_inc($path))
			{
			return self::_api_class($mod_name, $file_name);
			}
		require $path;
		self::_set_inc($path);
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
		if (!isset(self::$obj[$class_full]))
			{
			self::$obj[$class_full] = new $class_full();
			}
		return self::$obj[$class_full];
	}//}}}

	/**
	 * 加载读类(r_)Model
	 * @param string $mod_name 模块名。eg. cms
	 * @param string $class_name 类名，eg. content > r_content.class.php > r_cms_content
	 */
	static public function rm($mod_name, $class_name)
	{//{{{
		$class_full = "r_{$mod_name}_{$class_name}";
		if (isset(self::$obj[$class_full]))
			{
			return self::$obj[$class_full];
			}
		$path = GPF_PATH_MODULE . "{$mod_name}/model/r_{$class_name}.class.php";
		require $path;
		self::$obj[$class_full] = new $class_full();
		return self::$obj[$class_full];
	}//}}}
	/**
	 * 加载写类(r_)Model
	 * @param string $mod_name 模块名。eg. cms
	 * @param string $class_name 类名，eg. content > w_content.class.php > w_cms_content
	 */
	static public function wm($mod_name, $class_name)
	{//{{{
		$class_full = "w_{$mod_name}_{$class_name}";
		if (isset(self::$obj[$class_full]))
			{
			return self::$obj[$class_full];
			}
		$path = GPF_PATH_MODULE . "{$mod_name}/model/w_{$class_name}.class.php";
		require $path;
		self::$obj[$class_full] = new $class_full();
		return self::$obj[$class_full];
	}//}}}
	/**
	 * 单次加载(require_once)
	 * @param string $path 文件绝对路径
	 */
	static public function inc($path)
	{//{{{
		if (!self::_is_inc($path))
			{
			require $path;
			self::_set_inc($path);
			}
	}//}}}
}

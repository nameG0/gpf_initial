<?php
/**
 * 查询器入口。
 * <pre>
 * 查询器使用 callback 目录供其它模块扩展。
 * 都使用 ing() 方法表示执行查询。
 * 都使用连贯操作。
 * </pre>
 * 
 * @package api
 * @filesource
 */

class find
{
	private static $obj = NULL;

	// 构造方法声明为private，防止直接创建对象
	private function __construct() {}
	/**
	 * 开始
	 * @param string $point 指示需要哪个模块的查询器，格式：mod/name eg. cms/list
	 */
	static function init($point)
	{//{{{
		//总是初始化默认的空查询器，在找不到指向的查询器时返回，避免 PHP 报致命错误。
		if (!isset(self::$obj['siud/example']))
			{
			require SIUD_PATH . "callback/siud_find/example.class.php";
			self::$obj['siud/example'] = new siudFind_siud__example();
			}
		if (is_object(self::$obj[$point]))
			{
			self::$obj[$point]->init();
			return self::$obj[$point];
			}
		if (isset($obj) && false === $obj)
			{
			return self::$obj['siud/example'];
			}
		list($mod, $name) = explode("/", $point);
		$callback = mod_callback($mod, 'p');
		$path = '';
		foreach ($callback as $k => $v)
			{
			$_path = "{$v}siud_find/{$name}.class.php";
			if (is_file($_path))
				{
				$path = $_path;
				break;
				}
			}
		if (!$path)
			{
			log::add("找不到查询器定义文件 {$point}", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			self::$obj[$point] = false;
			return self::$obj['siud/example'];
			}
		include $path;
		$class_name = "siudFind_{$mod}__{$name}";
		if (!class_exists($class_name))
			{
			log::add("未定义查询器类 {$class_name}", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			self::$obj[$point] = false;
			return self::$obj['siud/example'];
			}
		self::$obj[$point] = new $class_name();
		self::$obj[$point]->init();
		return self::$obj[$point];
	}//}}}
}

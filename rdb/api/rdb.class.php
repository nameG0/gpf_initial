<?php
/**
 * rdb API 类
 * 20120501
 * 
 * @version 20120501
 * @package default
 * @filesource
 */
class rdb
{
	/**
	 * 保存数据库驱动对象
	 */
	static public $rdb_obj = array();
	/**
	 * 保存已加载的数据库驱动类定义文件.
	 */
	static public $drive = array();

	/**
	 * 返回指定的数据库驱动对像
	 * <pre>
	 * 可以使用连贯操作:
	 * rdb::obj()->query();
	 * </pre>
	 * @param string $name 数据库配置的组名,默认的组为 default ,即用常量定义的数据库链接信息
	 * @param bool $is_auto_create 若数据库驱动对象未实例化,是否自动实例化.
	 * @return obj|bool 数据库驱动对象,失败返回 false
	 */
	static function obj($name = 'default', $is_auto_create = true)
	{//{{{
		if (isset(self::$rdb_obj[$name]))
			{
			return self::$rdb_obj[$name];
			}
		if ($is_auto_create)
			{
			$cfg = self::_cfg_by_name($name);
			if (!$cfg['database'])
				{
				self::$rdb_obj[$name] = false;
				return false;
				}
			$db = self::create($cfg['database'], $cfg['host'], $cfg['user'], $cfg['pw'], $cfg['pconnect'], $cfg['charset']);
			self::$rdb_obj[$name] = $db;
			return $db;
			}
		return false;
	}//}}}

	/**
	 * 提取指定数据库配置组的配置
	 * @param string $name 数据库配置组名
	 * @return array 配置数据
	 */
	static function _cfg_by_name($name)
	{//{{{
		if ('default' == $name)
			{
			return array(
				"host" => RDB_HOST,
				"user" => RDB_USER,
				"pw" => RDB_PW,
				"name" => RDB_NAME,
				"charset" => RDB_CHARSET,
				"pconnect" => RDB_PCONNECT,
				"database" => RDB_DATABASE,
				);
			}
		//todo 未完成,需要从 gpf::cfg() 中读取其它数据库配置.
		return array();
	}//}}}

	/**
	 * 实例化数据库驱动类
	 * @param string $database 数据库类型,即数据库驱动类的类名. eg. mysql
	 * @param mixed ... 若传入不止一个参数,除第一个参数之外的其它参数都会传给驱动类的 connect() 方法.
	 * @return obj|bool 成功返回数据库驱动对象(可能是一个连接不成功的驱动对象),失败返回 false ,如驱动类未定义.
	 */
	static function create($database)
	{//{{{
		$class_name = "rdb_{$database}";
		if (!isset(self::$drive[$database]))
			{
			$path = RDB_PATH . "drive/{$database}.class.php";
			if (!is_file($path))
				{
				self::$drive[$database] = false;
				return false;
				}
			include_once $path;
			if (!class_exists($class_name))
				{
				self::$drive[$database] = false;
				return false;
				}
			self::$drive[$database] = true;
			}
		else if (!self::$drive[$database])
			{
			return false;
			}
		$db = new $class_name();
		if (func_num_args() > 1)
			{
			$args = func_get_args();
			unset($args[0]);
			call_user_func_array(array($db, 'connect'), $args);
			}
		return $db;
	}//}}}
}

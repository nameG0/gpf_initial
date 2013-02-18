<?php
/**
 * SIUD 入口类，助手性质，加强易用性。
 * 
 * @package api
 * @filesource
 */

class siud
{
	private static $obj = NULL;
	private $obj_select = NULL; //保存 select 类实例
	private $obj_save = NULL;
	private $obj_delete = NULL;

	// 构造方法声明为private，防止直接创建对象
	private function __construct() {}
	/**
	 * 实例化
	 */
	static function _obj()
	{//{{{
		if (is_null(self::$obj))
			{
			self::$obj = new siud();
			}
		return self::$obj;
	}//}}}
	/**
	 * siud_select 类，limit=1
	 */
	static function find($table = NULL)
	{//{{{
		$siud = self::_obj();
		if (is_null($siud->obj_select))
			{
			include_once SIUD_PATH . 'include/select.class.php';
			include_once SIUD_PATH . 'include/table.class.php';
			include_once SIUD_PATH . 'include/where.class.php';
			$siud->obj_select = new siud_select();
			}
		$siud->obj_select->init($table);
		$siud->obj_select->limit(1);
		return $siud->obj_select;
	}//}}}
	static function select($table = NULL)
	{//{{{
		$siud = self::_obj();
		if (is_null($siud->obj_select))
			{
			include_once SIUD_PATH . 'include/select.class.php';
			include_once SIUD_PATH . 'include/table.class.php';
			include_once SIUD_PATH . 'include/where.class.php';
			$siud->obj_select = new siud_select();
			}
		$siud->obj_select->init($table);
		return $siud->obj_select;
	}//}}}
	static function save($table = NULL)
	{//{{{
		$siud = self::_obj();
		if (is_null($siud->obj_save))
			{
			include_once SIUD_PATH . 'include/table.class.php';
			include_once SIUD_PATH . 'include/save.class.php';
			$siud->obj_save = new siud_save();
			}
		$siud->obj_save->init($table);
		return $siud->obj_save;
	}//}}}
	static function insert()
	{//{{{
		$obj = self::_obj();
		$obj->type = 'insert';
		return $obj;
	}//}}}
	static function delete($table = NULL)
	{//{{{
		$siud = self::_obj();
		if (is_null($siud->obj_delete))
			{
			include_once SIUD_PATH . 'include/table.class.php';
			include_once SIUD_PATH . 'include/where.class.php';
			include_once SIUD_PATH . 'include/delete.class.php';
			$siud->obj_delete = new siud_delete();
			}
		$siud->obj_delete->init($table);
		return $siud->obj_delete;
	}//}}}
}

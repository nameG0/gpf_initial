<?php
/**
 * 对 $_POST, $_GET 这类 $HTTP_XXX_VAR 变量的包装。
 * <pre>
 * 2012-05-07
 * <b>使用的配置参数</b>
 * gpf_is_form_adds
 * <b>返回值</b>
 * 按传入参数顺序返回一个数组，使用 list() 赋值。
 * eg. list($a, $b) = _g('a', 'b');
 * </pre>
 * 
 * @version 2012-06-23
 * @package default
 * @filesource
 */
define('GPF_SQL_SAVE', 10000); //进行引号转换，以安全用于SQL语句
define('GPF_NO_ADDS', 10001); //默认会把返回值进行引号转换，以安全用于SQL语句，在最后一个参数使用此常量表示不转换引号。
define('GPF_INT', 90000); //表示INT类型，用在某个键之后表示对此键值进行intval过滤。
define('GPF_STRING', 90001); //表示STRING类型

class i
{
	static private $obj = NULL; //保存本类实例化对像
	private $type = GPF_INT; //返回值数组键类型{GPF_INT:数组键, GPF_STRING:字符串键}
	private $mode = GPF_SQL_SAVE; //保存返回值是否过滤引号
	private $from_first = array(); //优先使用来源
	private $from_second = array(); //备选使用来源
	private $data = array(); //保存提取出的数据。

	/**
	 * 负责本类实例化
	 */
	private static function _obj()
	{//{{{
		if (is_null($obj))
			{
			self::$obj = new i();
			}
		return self::$obj;
	}//}}}

	/**
	 * 提取 $_GET 的数据
	 */
	static function g($type = GPF_INT, $mode = GPF_SQL_SAVE)
	{//{{{
		$obj = self::_obj();
		$obj->type = $type;
		$obj->mode = $mode;
		$obj->from_first = $_GET;
		$obj->from_second = array();
		$obj->data = array();
		return $obj;
	}//}}}
	/**
	 * 从 GET, POST 中提取数据， GET 优先。
	 */
	static function gp($type = GPF_INT, $mode = GPF_SQL_SAVE)
	{//{{{
		$obj = self::_obj();
		$obj->type = $type;
		$obj->mode = $mode;
		$obj->from_first = $_GET;
		$obj->from_second = $_POST;
		$obj->data = array();
		return $obj;
	}//}}}
	/**
	 * 提取 $_POST 数据。
	 */
	static function p($type = GPF_INT, $mode = GPF_SQL_SAVE)
	{//{{{
		$obj = self::_obj();
		$obj->type = $type;
		$obj->mode = $mode;
		$obj->from_first = $_POST;
		$obj->from_second = array();
		$obj->data = array();
		return $obj;
	}//}}}
	/**
	 * 从 POST, GET 中提取数据， POST 优先。
	 */
	static function pg($type = GPF_INT, $mode = GPF_SQL_SAVE)
	{//{{{
		$obj = self::_obj();
		$obj->type = $type;
		$obj->mode = $mode;
		$obj->from_first = $_POST;
		$obj->from_second = $_GET;
		$obj->data = array();
		return $obj;
	}//}}}

	/**
	 * 从 $from_first, $from_second 数组中取值。
	 */
	private function _get($key)
	{//{{{
		return isset($from_first[$key]) ? $from_first[$key] : $from_second[$key];
	}//}}}
	/**
	 * 增加一个提取数据
	 */
	private function _set($key, $value)
	{//{{{
		if (GPF_INT === $this->type)
			{
			$this->data[] = $value;
			}
		else
			{
			$this->data[$key] = $value;
			}
	}//}}}

	/**
	 * 作为INT类型提取的键,以参数名分隔多个键。 eg. int('a', 'b', ...)
	 */
	function int()
	{//{{{
		$arg = func_get_args();
		foreach ($arg as $k)
			{
			$value = $this->_get($k);
			$this->_set($k, intval($value));
			}
		return $this;
	}//}}}

	/**
	 * 在最后调用，结束操作，返回提取结果
	 * @return array
	 */
	function end()
	{//{{{
		if (0 == count($this->data))
			{
			//直接调用 end() 时返回整个数组。 eg. i::g()->end();
			$this->data = $this->from_first;
			}

		//对传入数据进行引号转换
		$is_form_adds = gpf::cfg('gpf_is_form_adds');
		if (GPF_SQL_SAVE === $this->mode && !$is_form_adds)
			{
			$this->data = gaddslashes($this->data);
			}
		else if (GPF_NO_ADDS === $this->mode && $is_form_adds)
			{
			$this->data = gstripslashes($this->data);
			}
		if (1 === count($this->data))
			{
			return current($this->data);
			}
		return $this->data;
	}//}}}
}

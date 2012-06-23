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

/**
 * 进行处理的函数
 * @param array $arg 操作链参数
 * @param array $from_first 优先使用来源
 * @param array $from_second 备选使用来源
 */
function _http__var($arg, $from_first, $from_second)
{//{{{
	$mode = GPF_SQL_SAVE;
	if (count($arg) <= 0)
		{
		$data = $from_first;
		}
	else
		{
		$end_value = end($arg);
		if (GPF_NO_ADDS === $end_value)
			{
			$mode = GPF_NO_ADDS;
			array_pop($arg);
			}
		$data = array();
		//第一个参数一定是第一个要取的键名。在循环之前取出，在循环内便不需要考虑所处理的键是否第一个键的问题。
		$k = array_shift($arg);
		$value = isset($from_first[$k]) ? $from_first[$k] : $from_second[$k];
		foreach ($arg as $k)
			{
			if (is_string($k))
				{
				$data[] = $value;
				$value = isset($from_first[$k]) ? $from_first[$k] : $from_second[$k];
				continue;
				}
			switch ($k)
				{
				case GPF_INT:
					$value = intval($value);
					break;
				}
			}
		//放入最后一个值。
		$data[] = $value;
		}
	//对传入数据进行引号转换
	$is_form_adds = gpf::cfg('gpf_is_form_adds');
	if (GPF_SQL_SAVE === $mode && !$is_form_adds)
		{
		$data = gaddslashes($data);
		}
	else if (GPF_NO_ADDS === $mode && $is_form_adds)
		{
		$data = gstripslashes($data);
		}
	if (1 === count($data))
		{
		return $data[0];
		}
	return $data;
}//}}}

/**
 * 提取 $_GET 的数据
 * <pre>
 * 无参数调用时表示返回整个 $_GET 数组，否则返回指定的值。
 * 最后一个参数可以是 GPF_SQL_SAVE, GPF_NO_ADDS 其中一个控制变量。
 * </pre>
 * @return array 使用 list() 赋值。
 */
function _g()
{//{{{
	$arg = func_get_args();
	return _http__var($arg, $_GET, array());
}//}}}

/**
 * 提取 $_POST 数据。
 */
function _p()
{//{{{
	$arg = func_get_args();
	return _http__var($arg, $_POST, array());
}//}}}

/**
 * 从 GET, POST 中提取数据， GET 优先。
 */
function _gp()
{//{{{
	$arg = func_get_args();
	return _http__var($arg, $_GET, $_POST);
}//}}}

/**
 * 从 POST, GET 中提取数据， POST 优先。
 */
function _pg()
{//{{{
	$arg = func_get_args();
	return _http__var($arg, $_POST, $_GET);
}//}}}

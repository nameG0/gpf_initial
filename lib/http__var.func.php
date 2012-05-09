<?php
/**
 * 对 $_POST, $_GET 这类 $HTTP_XXX_VAR 变量的包装。
 * 2012-05-07
 * 
 * @version 2012-05-07
 * @package default
 * @filesource
 */

/**
 * 提取 $_GET 的数据
 * @param NULL|string 若为 NULL 表示返回整个 $_GET 数组，否则返回指定的值。
 * @todo 原型应为 key, func_name(过滤函数且支持多个), default_value(默认值), default_type(使用默认值条件：empty, !isset)
 */
function _g($key = NULL)
{//{{{
	if (is_null($key))
		{
		return $_GET;
		}
	return $_GET[$key];
}//}}}

/**
 * 提取 $_POST 数据。
 * @param NULL|string 若为 NULL 表示返回整个 $_POST 数组，否则返回指定的值。
 */
function _p($key = NULL)
{//{{{
	if (is_null($key))
		{
		return $_POST;
		}
	return $_POST[$key];
}//}}}

/**
 * 从 GET, POST 中提取数据， GET 优先。
 */
function _gp($key)
{//{{{
	if (isset($_GET[$key]))
		{
		return $_GET[$key];
		}
	return $_POST[$key];
}//}}}

/**
 * 从 POST, GET 中提取数据， POST 优先。
 */
function _pg($key)
{//{{{
	if (isset($_POST[$key]))
		{
		return $_POST[$key];
		}
	return $_GET[$key];
}//}}}

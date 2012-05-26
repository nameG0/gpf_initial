<?php
/**
 * 常用函数
 * 
 * @version 2012-05-05
 * @package default
 * @filesource
 */

/**
 * 计算运行时间
 * @param NULL|int $time {NULL:返回当前时间, int:计算当前时间与转入时间的间隔}
 * <code>
 * $t1 = run_time(); //存当前时间
 * sleep(1);
 * echo run_time($t1); //计算运行时间
 * </code>
 */
function run_time($time = NULL)
{//{{{
	list($usec, $sec) = explode(" ", microtime());
	$mt = ((float)$usec + (float)$sec);
	if (is_null($time))
		{
		return $mt;
		}
	return $mt - $time;
}//}}}
/**
 * 支持数组的 stripslashes
 * @param string|array $data
 */
function gstripslashes($data)
{//{{{
	return is_array($data) ? array_map(__FUNCTION__, $data) : stripslashes($data);
}//}}}
/**
 * 支持数组的 addslashes
 * @param string|array $data
 */
function gaddslashes($data)
{//{{{
	return is_array($data) ? array_map(__FUNCTION__, $data) : addslashes($data);
}//}}}

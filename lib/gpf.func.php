<?php
/**
 * GPF 助手函数
 * 
 * @package default
 * @filesource
 */

/**
 * 返回后台相关操作url
 * $arg mod.[file.action.other.get]
 * 可以这样 ..action,则 mod 及 file 会用当前页面中的值
 * get 部份只需提供键值，会自动从get中取出对应的值
 */
function ctrl_url($arg = '...')
{
	list($_mod, $_file, $_action, $other, $get) = explode(".", $arg);
	$id = array();
	//list($mod, $file, $action) = explode(",", $id);
	$id[] = $_mod ? $_mod : CTRL_MOD;
	$id[] = $_file ? $_file : CTRL_FILE;
	$id[] = $_action ? $_action : CTRL_ACTION;
	$ret = "?a=" . join(",", $id);
	$ret .= $other;
	if ($get)
		{
		$gets = explode(",", $get);
		foreach ($gets as $k => $v)
			{
			$ret .= isset($_GET[$v]) ? "&{$v}=".$_GET[$v] : '';
			}
		}
	return $ret;
}

<?php
/**
 * 自动删除数据页面
 *
 * 2011-09-05
 * input:
 * 	$siud_delete
 * 		[table]
 * 		[w]
 * 		[w_value]
 * output:
 * 	$siud_error
 *
 * @package default
 * @filesource
 */
$siud_error = '';
if (!$siud_delete['table'])
	{
	$siud_error = 'Require [table]';
	return ;
	}

if (!$siud_delete['w_value'] || !is_array($siud_delete['w_value']))
	{
	$siud_error = "Require [w_value]";
	return ;
	}

$t_w = siud_where_init($siud_delete);
$siud_error = siud_where_check($t_w);
if ($siud_error)
	{
	return ;
	}
$t_where = siud_where_make($t_w);
$sql = "DELETE FROM {$siud_delete['table']} {$t_where}";

//delete_before_func
$func_name = "_before_delete_";
if (function_exists($func_name))
	{
	$is_ok = $func_name($siud_delete['w_value']);
	if (!$is_ok)
		{
		$siud_error = "删除被中断";
		return ;
		}
	}
//删除数据
log::add($sql, log::INFO, __FILE__, __LINE__, 'delete.inc.php');
$is_ok = $db->query($sql);

//after_func
$func_name = "_after_delete_";
if (function_exists($func_name))
	{
	$func_name($siud_delete['w_value']);
	}
unset($siud_delete);
?>

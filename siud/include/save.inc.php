<?php
/**
 * 保存(添加或修改)一行数据（整条记录）
 *
 * 2011-09-27<br/>
 * save 与 update 的不同之处就在于，save 最小操作单位是一整条记录， update 最小操作单位是一个字段。<br/>
 * 在添加及修改整条记录的情况下， save 明显比 insert + update 来得方便。<br/>
 * <b>input:</b>
 * - 	$siud_save array 保存器参数
 * 
 * <b>output:</b>
 * - 	$insert_id array 新插入数据的 insert_id
 * - 	$siud_error string 出错信息，无错则为空字符串
 *
 * <b>设置参数</b><br/>
 * table string 操作的表的完整表名，必须
 *
 * pk string 表的主键，必须。通过判断主键是否为空分别进行更新或添建。
 *
 * is_bat bool 是否允许批量操作，即一次添加或更新多条记录。
 * <code>
 * //设 $data 为表单提交的数据，批量操作的格式如下：
 * $data[0]['userid'] = 1;
 * //第一维的键是数字，非批量操作格式如下：
 * $data['userid'] = 1;
 * //第一维的键非数字。
 * </code>
 *
 * <b>其它参数参考：</b>
 * @see siud_data_init
 * @package default
 * @filesource
 */
$siud_error = '';
if (!$siud_save['table'])
	{
	$siud_error = 'Require [table]';
	return ;
	}
if (!$siud_save['pk'])
	{
	$siud_error = 'Require [pk]';
	return ;
	}
if (!$siud_save['d_value'] || !is_array($siud_save['d_value']))
	{
	$siud_error = 'Require d_value';
	return ;
	}

$siud_data = array();
//为了可以统一处理，强制格式化为批量操作
if (!is_numeric(key($siud_save['d_value'])))
	{
	$siud_data = array($siud_save['d_value']);
	}
else
	{
	if (isset($siud_save['is_bat']) && !$siud_save['is_bat'])
		{
		$siud_error = 'Cannot Bat';
		return ;
		}
	$siud_data = $siud_save['d_value'];
	}
unset($siud_save['d_value']);

$t_d = siud_data_init($siud_save);

//插入前检查
foreach ($siud_data as $k => $v)
	{
	$siud_action = $data[$siud_save['pk']] ? 'update' : 'insert';
	$siud_error = siud_data_check($t_d, $v, $siud_action);
	if ($siud_error)
		{
		return ;
		}
	}

//插入数据库
$insert_id = array();
foreach ($siud_data as $k => $v)
	{
	if (!$v[$siud_save['pk']])
		{
		//插入数据
		$v = siud_data_make($t_d, $v, 'insert');
		$is_ok = $db->insert($siud_save['table'], $v);
		$insert_id[] = $db->insert_id();
		}
	else
		{
		//更新数据
		$v = siud_data_make($t_d, $v, 'update');
		$is_ok = $db->update($siud_save['table'], $v, "`{$siud_save['pk']}` = '{$v[$siud_save['pk']]}'");
		$func_name = '_after_update_';
		if (function_exists($func_name))
			{
			$func_name(array($siud_save['pk'] => $v[$siud_save['pk']]), $v, $db->affected_rows());
			}
		}
	if (!$is_ok)
		{
		break;
		}
	}
unset($t_d, $siud_save, $siud_data);
?>

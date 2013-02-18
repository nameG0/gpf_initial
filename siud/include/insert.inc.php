<?php
/**
 * ��������
 *
 * 2011-09-27
 * input:
 * 	$siud_insert
 * 		[table]
 * 		[d]
 * 		[d_value]
 * 		[is_bat]
 * output:
 * 	$insert_id(array)
 * 	$siud_error(string)
 *
 * @package default
 * @filesource
 */
$siud_error = '';
if (!$siud_insert['table'])
	{
	$siud_error = 'Require [table]';
	return ;
	}
if (!$siud_insert['d_value'] || !is_array($siud_insert['d_value']))
	{
	$siud_error = 'Require [d_value]';
	return ;
	}

$siud_data = array();
//Ϊ�˿���ͳһ����ǿ�Ƹ�ʽ��Ϊ��������
if (!is_numeric(key($siud_insert['d_value'])))
	{
	$siud_data = array($siud_insert['d_value']);
	}
else
	{
	if (isset($siud_insert['is_bat']) && !$siud_insert['is_bat'])
		{
		$siud_error = 'Cannot Bat';
		return ;
		}
	$siud_data = $siud_insert['d_value'];
	}
unset($siud_insert['d_value']);

$t_d = siud_data_init($siud_insert);

//����ǰ���
foreach ($siud_data as $k => $v)
	{
	$siud_error = siud_data_check($t_d, $v, 'insert');
	if ($siud_error)
		{
		return ;
		}
	}

//�������ݿ�
$insert_id = array();
foreach ($siud_data as $k => $v)
	{
	$v = siud_data_make($t_d, $v, 'insert');
	//��������
	$is_ok = $db->insert($siud_insert['table'], $v);
	$insert_id[] = $db->insert_id();
	if (!$is_ok)
		{
		break;
		}
	}
unset($t_d, $siud_insert, $siud_data);
?>

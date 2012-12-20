<?php
/**
 * �Զ���������ҳ��
 * 
 * 2011-09-05
 * - input:
 * - 	$siud_update
 * - 		[table]
 * - 		[w]
 * - 		*[w_value](��������ʱΪ��ά����)
 * - 		[d]
 * - 			[require]
 * - 			[in]
 * - 			[allow]
 * - 			[deny]
 * - 		*[d_value](��������ʱΪ��ά����)
 * - 		[is_bat]
 *
 * - output:
 * - 	$siud_error(string)	��Ϊ���ַ�����Ϊ������Ϣ
 * 
 * @package default
 * @filesource
 */
$siud_error = '';
if (!$siud_update['table'])
	{
	$siud_error = 'Require [table]';
	return ;
	}
if (!$siud_update['w_value'] || !is_array($siud_update['w_value']))
	{
	$siud_error = "Require w_value";
	return ;
	}
if (!$siud_update['d_value'] || !is_array($siud_update['d_value']))
	{
	$siud_error = 'Require d_value';
	return ;
	}
//$where = $siud_update['w_value'];
//$data = $d;
//unset($d);

//list($t_d, $t_allow, $t_deny) = _siud_d_format($siud_update['d']);

//����Ƿ����(����)����ģʽ, �� w[����][�����ֶ�] �������һάΪ���֣���Ϊ���飬���� w[�����ֶ�] �������һά��Ϊ���֣����Ƿ��顣
$siud_where = array();
$siud_data = array();
//����ģʽ
if (is_numeric(key($siud_update['w_value'])))
	{
	if (isset($siud_update['is_bat']) && !$siud_update['is_bat'])
		{
		$siud_error = 'Cannot Bat';
		return ;
		}
	//��һ�Ա� $where �� $data �������������һ��ȱ
	foreach ($siud_update['w_value'] as $k => $v)
		{
		if (!$siud_update['d_value'][$k])
			{
			$siud_error = "���� {$k} ���ݲ�����";
			return ;
			}
		}
	$siud_where = $siud_update['w_value'];
	$siud_data = $siud_update['d_value'];
	}
else
	{
	//Ϊ�˿���ͳһ����ǿ�ưѷǷ���ĸ��¸�ʽ��Ϊ�������
	$siud_where = array($siud_update['w_value']);
	$siud_data = array($siud_update['d_value']);
	}
unset($siud_update['w_value'], $siud_update['d_value']);

$t_d = siud_data_init($siud_update);

//����ǰ���
foreach ($siud_where as $k => $v)
	{
	$siud_error = siud_data_check($t_d, $siud_data[$k], 'update');
	if ($siud_error)
		{
		return ;
		}

	$t_w = $siud_update;
	$t_w['w_value'] = $v;
	$t_w = siud_where_init($t_w);
	$siud_error = siud_where_check($t_w);
	unset($t_w);
	if ($siud_error)
		{
		return ;
		}
	}

//�������ݿ�
foreach ($siud_where as $k => $v)
	{
	//update_before_func
	$func_name = "_before_update_";
	if (function_exists($func_name))
		{
		$is_ok = $func_name($v, $siud_data[$k]);
		if (!$is_ok)
			{
			$siud_error = "�����ж�";
			return ;
			}
		}
	//��װ WHERE
	$t_w = $siud_update;
	$t_w['w_value'] = $v;
	$t_w = siud_where_init($t_w);
	$t_where = siud_where_make($t_w);
	$t_where = substr($t_where, 5);
	unset($t_w);
	log::add($t_where, log::INFO, __FILE__, __LINE__, 'update.inc.php');
	//��������
	$is_ok = $db->update($siud_update['table'], $siud_data[$k], $t_where);

	//after_func
	$func_name = "_after_update_";
	if (function_exists($func_name))
		{
		$affect = $db->affected_rows();
		$func_name($v, $data, $affect);
		}

	if (!$is_ok)
		{
		break;
		}
	}
unset($siud_update, $siud_where, $siud_data, $t_d);
?>

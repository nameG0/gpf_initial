<?php
/**
 * ����(��ӻ��޸�)һ�����ݣ�������¼��
 *
 * 2011-09-27<br/>
 * save �� update �Ĳ�֮ͬ�������ڣ�save ��С������λ��һ������¼�� update ��С������λ��һ���ֶΡ�<br/>
 * ����Ӽ��޸�������¼������£� save ���Ա� insert + update ���÷��㡣<br/>
 * <b>input:</b>
 * - 	$siud_save array ����������
 * 
 * <b>output:</b>
 * - 	$insert_id array �²������ݵ� insert_id
 * - 	$siud_error string ������Ϣ���޴���Ϊ���ַ���
 *
 * <b>���ò���</b><br/>
 * table string �����ı����������������
 *
 * pk string ������������롣ͨ���ж������Ƿ�Ϊ�շֱ���и��»�����
 *
 * is_bat bool �Ƿ�����������������һ����ӻ���¶�����¼��
 * <code>
 * //�� $data Ϊ���ύ�����ݣ����������ĸ�ʽ���£�
 * $data[0]['userid'] = 1;
 * //��һά�ļ������֣�������������ʽ���£�
 * $data['userid'] = 1;
 * //��һά�ļ������֡�
 * </code>
 *
 * <b>���������ο���</b>
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
//Ϊ�˿���ͳһ����ǿ�Ƹ�ʽ��Ϊ��������
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

//����ǰ���
foreach ($siud_data as $k => $v)
	{
	$siud_action = $data[$siud_save['pk']] ? 'update' : 'insert';
	$siud_error = siud_data_check($t_d, $v, $siud_action);
	if ($siud_error)
		{
		return ;
		}
	}

//�������ݿ�
$insert_id = array();
foreach ($siud_data as $k => $v)
	{
	if (!$v[$siud_save['pk']])
		{
		//��������
		$v = siud_data_make($t_d, $v, 'insert');
		$is_ok = $db->insert($siud_save['table'], $v);
		$insert_id[] = $db->insert_id();
		}
	else
		{
		//��������
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

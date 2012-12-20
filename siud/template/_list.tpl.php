<?php
/**
 * ��ʾ block_list.tpl.php ģ��
 * 
 * 2011-09-20
 * @package template
 * @filesource
 */
require_once SIUD_PATH . "include/siud.func.php";

//���ع������˵�
$block_header = array(
	"title" => $list['caption'],
	"submenu" => $list_submenu,
	);
include admin_tpl('block_header', 'phpcms');

//������
if ($search)
	{
	$block_search = $search;
	include a_siud_tpl('block_search');
	}

//���ǰģ��|before_table_tpl
if ($list_tpl['before_table_tpl'])
	{
	include admin_tpl($list['before_table_tpl'][1], $list['before_table_tpl'][0]);
	}

//��ʾ���
//�Զ����� manage �ֶ�
if (is_array($list_tpl['display']) && in_array('manage', $list_tpl['display']) && !$list_tpl['name']['manage'])
	{
	$list_tpl['name']['manage'] = '�������';
	}
$block_list = $list_tpl;
$block_list['is_form'] = true;
$block_list['result'] = $RESULT;
$block_list['pages'] = $RESULT_pages;
//�ֶ���������
if ($RESULT_order)
	{
	foreach ($RESULT_order as $k => $v)
		{
		$block_list['name'][$k] = "<a href=\"{$v}\" title=\"�������\">{$block_list['name'][$k]}</a>|";
		}
	}

//�� _print_field ����
foreach ($list_tpl['display'] as $v)
	{
	$func_name = "_list_{$v}";
	if (function_exists($func_name))
		{
		$block_list['field_print_func'][$v] = $func_name;
		}
	}
//�� _print_bottom_ ����
$func_name = "_list_bottom_";
if (function_exists($func_name))
	{
	$block_list['form_end_func'] = $func_name;
	}
unset($func_name);

include SIUD_PATH . 'template/block_list.tpl.php';
echo $block_header;
?>

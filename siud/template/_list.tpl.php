<?php
/**
 * 显示 block_list.tpl.php 模板
 * 
 * 2011-09-20
 * @package template
 * @filesource
 */
require_once SIUD_PATH . "include/siud.func.php";

//加载工具栏菜单
$block_header = array(
	"title" => $list['caption'],
	"submenu" => $list_submenu,
	);
include admin_tpl('block_header', 'phpcms');

//搜索表单
if ($search)
	{
	$block_search = $search;
	include a_siud_tpl('block_search');
	}

//表格前模板|before_table_tpl
if ($list_tpl['before_table_tpl'])
	{
	include admin_tpl($list['before_table_tpl'][1], $list['before_table_tpl'][0]);
	}

//显示表格
//自动设置 manage 字段
if (is_array($list_tpl['display']) && in_array('manage', $list_tpl['display']) && !$list_tpl['name']['manage'])
	{
	$list_tpl['name']['manage'] = '管理操作';
	}
$block_list = $list_tpl;
$block_list['is_form'] = true;
$block_list['result'] = $RESULT;
$block_list['pages'] = $RESULT_pages;
//字段排序链接
if ($RESULT_order)
	{
	foreach ($RESULT_order as $k => $v)
		{
		$block_list['name'][$k] = "<a href=\"{$v}\" title=\"点击排序\">{$block_list['name'][$k]}</a>|";
		}
	}

//绑定 _print_field 函数
foreach ($list_tpl['display'] as $v)
	{
	$func_name = "_list_{$v}";
	if (function_exists($func_name))
		{
		$block_list['field_print_func'][$v] = $func_name;
		}
	}
//绑定 _print_bottom_ 函数
$func_name = "_list_bottom_";
if (function_exists($func_name))
	{
	$block_list['form_end_func'] = $func_name;
	}
unset($func_name);

include SIUD_PATH . 'template/block_list.tpl.php';
echo $block_header;
?>

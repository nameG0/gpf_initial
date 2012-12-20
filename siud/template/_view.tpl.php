<?php
/**
 * 显示 block_info.tpl.php 模板
 * 
 * 2011-09-20
 * @package template
 * @filesource
 */
require_once SIUD_PATH . "include/form.func.php";
//加载工具栏菜单
$block_header = array(
	"title" => $view['caption'],
	);
if (${$siud_action . '_submenu'})
	{
	$block_header['submenu'] = ${$siud_action . '_submenu'};
	}
include admin_tpl('block_header', 'phpcms');

//表格前模板|before_table_tpl
if ($view['before_table_tpl'])
	{
	include admin_tpl($view['before_table_tpl'][1], $view['before_table_tpl'][0]);
	}
//header_func
$func_name = "view_header_{$table}";
if (function_exists($func_name))
	{
	$func_name();
	}

//读取表单类型设置
$tpl = ${$siud_action . '_tpl'};
$block_info = array(
	"value" => $DATA,
	"form_name_pre" => 'd',
	"display" => $tpl['display'],
	"form" => $tpl["form"],
	"zh" => $tpl['zh'],
	"require" => $tpl['require'],
	"comment" => $tpl['comment'],
	);
$title = array(
	"new" => '添加',
	"edit" => '修改',
	"show" => '查看',
	);
$block_info['caption'] = $tpl["caption"] ? $tpl["caption"] : $title[$siud_action];
unset($title);
//field_print_func
$block_info['func'] = array();
foreach ($tpl['display'] as $k => $v)
	{
	$func_action = "_{$siud_action}_{$v}";
	if (function_exists($func_action))
		{
		$block_info['func'][$v] = $func_action;
		}
	}
include a_siud_tpl('block_info');
//after_function
$func_action = "_after_{$siud_action}_";
if (function_exists($func_action))
	{
	$func_action();
	}

echo $block_header;
?>

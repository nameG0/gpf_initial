<?php 
/**
 * 栏目ID 字段类型
 * 2011-10-17
 * 
 * @package default
 * @filesource
 */
function cm_ft_cms__catid_sql($set, $rdb_type = NULL)
{//{{{
	//todo 目前 $rdb_type 参数无效。
	return "INT(11) NOT NULL DEFAULT 0";
}//}}}

function cm_ft_cms__catid_FString($set)
{//{{{
	return "int(11)|NO|0|";
}//}}}

function cm_ft_cms__catid_form($field, $value, $fieldinfo)
{//{{{
	return hd("cms.select_category|module=cms|parentid=0|name=data[{$field}]|id=parentid|alt=无（作为一级栏目）|catid={$value}|type=2");
}//}}}

function cm_ft_cms__catid_input()
{//{{{
	
}//}}}

function cm_ft_cms__catid_update()
{//{{{
	
}//}}}

function cm_ft_cms__catid_search_form($field, $value, $fieldinfo)
{//{{{
	return form::text($field, $field, $value, 'text', 20);
}//}}}

function cm_ft_cms__catid_search($field, $value)
{//{{{
	return $value === '' ? '' : " `$field` LIKE '%$value%' ";
}//}}}

function cm_ft_cms__catid_output($field, $value)
{//{{{
	return $value;
	// $value = htmlspecialchars($value);
	// return output::style($value, $content['style']);
}//}}}

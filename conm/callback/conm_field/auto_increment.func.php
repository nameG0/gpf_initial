<?php 
/**
 * 自动编号 字段类型
 * 2011-10-17
 * 
 * @package default
 * @filesource
 */
function cm_ft_conm__auto_increment_sql($set, $rdb_type = NULL)
{//{{{
	$key_type = 'pri' == $set['key_type'] ? 'PRIMARY KEY' : 'UNIQUE';
	return "INT(10) UNSIGNED NOT NULL {$key_type} AUTO_INCREMENT";
}//}}}
function cm_ft_conm__auto_increment_FString($set)
{//{{{
	return "int(10) unsigned|NO||auto_increment";
}//}}}
function cm_ft_conm__auto_increment_setting($set)
{//{{{
	?>
<div >
	<label><input name="setting[key_type]" type="radio" value="pri" checked />主键</label>
	<label><input name="setting[key_type]" type="radio" value="uni" />唯一<label>
	(只有新建字段时有效，已存在的字段需在模型设置中修改索引信息)
</div>
	<?php
}//}}}
function cm_ft_conm__auto_increment_form($field, $value, $CMFr)
{//{{{
	return intval($value);
}//}}}

function cm_ft_conm__auto_increment_input()
{//{{{
	
}//}}}

function cm_ft_conm__auto_increment_update()
{//{{{
	
}//}}}

function cm_ft_conm__auto_increment_search_form($field, $value, $fieldinfo)
{//{{{
	return form::text($field, $field, $value, 'text', 20);
}//}}}

function cm_ft_conm__auto_increment_search($field, $value)
{//{{{
	return $value === '' ? '' : " `$field` LIKE '%$value%' ";
}//}}}

function cm_ft_conm__auto_increment_output($field, $value)
{//{{{
	$value = htmlspecialchars($value);
	return output::style($value, $content['style']);
}//}}}

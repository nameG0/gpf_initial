<?php 
/**
 * 联动菜单 字段类型
 * 2011-10-17
 * 
 * @package default
 * @filesource
 */
function cm_ft_tree__linkage_sql($set, $rdb_type = NULL)
{//{{{
	//todo 目前 $rdb_type 参数无效。
	$minnumber = intval($set['minnumber']);
	$defaultvalue = $set['decimaldigits'] == 0 ? intval($set['defaultvalue']) : floatval($set['defaultvalue']);
	$field_type = $set['decimaldigits'] == 0 ? 'INT' : 'FLOAT';
	$unsigned = $set['minnumber'] >= 0 ? 'UNSIGNED' : '';
	return "{$field_type}(10) {$unsigned} NOT NULL DEFAULT '{$defaultvalue}'";
}//}}}
function cm_ft_tree__linkage_FString($set)
{//{{{
	$minnumber = intval($set['minnumber']);
	$defaultvalue = $set['decimaldigits'] == 0 ? intval($set['defaultvalue']) : floatval($set['defaultvalue']);
	$field_type = $set['decimaldigits'] == 0 ? 'int' : 'float';
	$unsigned = $set['minnumber'] >= 0 ? ' unsigned' : '';
	return "int(10){$unsigned}|NO|{$defaultvalue}|";
}//}}}

function cm_ft_tree__linkage_setting($set)
{//{{{
	echo 
		'字段类型',
		hd("radio|name=setting[field_type]|value={$set['field_type']}|_default=int", array("_data" => array("int" => 'int',),)),
		'<br/>',
		hd("text|label=首层PID|name=setting[default_pid]|value={$set['default_pid']}|br"),
		hd("textarea|label=查询SQL语句|name=setting[sql]", array("value" => $set['sql'],)),
		"(返回 id, name 两个字段，用 {DB_PRE} 表示表前序，用 {pid} 表示父ID)"
		;
}//}}}

function cm_ft_tree__linkage_form($field, $value, $set)
{//{{{
	return <<<EOT
<input type="text" name="info[{$field}]" value="{$value}" />
EOT;

	global $catid;
	extract($fieldinfo);
	if(!$value) $value = $defaultvalue;
	global $catid;
	extract($fieldinfo);
	if(!$value) $value = $defaultvalue;
	$data = '';
	if(defined('IN_ADMIN'))
	{
		//$data = "<input type=\"button\" value=\"检测标题是否已存在\" onclick=\"$.post('?mod=phpcms&file=content&catid=".$catid."', { action : 'check_title', c_title:$('#title').val()}, function(data){ $('#t_msg').html(data); })\">&nbsp;<span style=\"color:'#ff0000'\" id='t_msg'></span>";
	}
	$formattribute .= 'onBlur="$.post(\'api/get_keywords.php?number=3&sid=\'+Math.random()*5, {data:$(\'#title\').val()}, function(data){if(data) $(\'#keywords\').val(data); })"';
	return form::text('info['.$field.']', $field, $value, 'text', $size, $css, $formattribute, $minlength, $maxlength).$data;
}//}}}

function cm_ft_tree__linkage_input()
{//{{{
	
}//}}}

function cm_ft_tree__linkage_update()
{//{{{
	
}//}}}

function cm_ft_tree__linkage_search_form($field, $value, $fieldinfo)
{//{{{
	return form::text($field, $field, $value, 'text', 20);
}//}}}

function cm_ft_tree__linkage_search($field, $value)
{//{{{
	return $value === '' ? '' : " `$field` LIKE '%$value%' ";
}//}}}

function cm_ft_tree__linkage_output($field, $value)
{//{{{
	$value = htmlspecialchars($value);
	return output::style($value, $content['style']);
}//}}}

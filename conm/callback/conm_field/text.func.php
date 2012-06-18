<?php 
/**
 * 单行文本 字段类型
 * 2011-10-17
 * 
 * @package default
 * @filesource
 */
/**
 * 根据字段配置返回MySQL数据字段相关属性
 */
function _cm_ft_conm__text_mysql($set)
{//{{{
	if(!$set['maxlength']) $set['maxlength'] = 255;
	$maxlength = min($set['maxlength'], 255);
	$ret = array(
		"type" => $set['field_type'],
		"maxlength" => $maxlength,
		);
	return $ret;
}//}}}

function cm_ft_conm__text_sql($set, $rdb_type = NULL)
{//{{{
	//todo 目前 $rdb_type 参数无效。
	$field = _cm_ft_conm__text_mysql($set);
	return "{$field['type']}( {$field['maxlength']} ) NOT NULL DEFAULT '{$set['defaultvalue']}'";
}//}}}

function cm_ft_conm__text_FString($set)
{//{{{
	$field = _cm_ft_conm__text_mysql($set);
	return "{$field['type']}({$field['maxlength']})|NO|{$set['defaultvalue']}|";
}//}}}

function cm_ft_conm__text_setting($setting)
{//{{{
	?>
<div >
字段类型：
<?=hd("radio|name=setting[field_type]|value={$setting['field_type']}|_default=char",
array("_data" => array("char" => 'char', "varchar" => 'varchar',),))?>
</div>
<div >
<?=hd("text|label=文本框长度|name=setting[size]|value={$setting['size']}|size=10")?>
</div>
	<?php
}//}}}

function cm_ft_conm__text_form($field, $value, $fieldinfo)
{//{{{
	return <<<EOT
<input type="text" name="data[{$field}]" value="{$value}" />
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

function cm_ft_conm__text_input()
{//{{{
	
}//}}}

function cm_ft_conm__text_update()
{//{{{
	
}//}}}

function cm_ft_conm__text_search_form($field, $value, $fieldinfo)
{//{{{
	return form::text($field, $field, $value, 'text', 20);
}//}}}

function cm_ft_conm__text_search($field, $value)
{//{{{
	return $value === '' ? '' : " `$field` LIKE '%$value%' ";
}//}}}

function cm_ft_conm__text_output($field, $value)
{//{{{
	$value = htmlspecialchars($value);
	return output::style($value, $content['style']);
}//}}}

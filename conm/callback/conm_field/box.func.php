<?php 
/**
 * box 字段类型
 * 2011-10-17
 * 
 * @package default
 * @filesource
 */
function cm_ft_conm__box_sql($set, $rdb_type = NULL)
{//{{{
	//todo 目前 $rdb_type 参数无效。
	$maxlength = $set['maxlength'];
	$fieldtype = $set['fieldtype'];
	$defaultvalue = $set['defaultvalue'];

	if (!$fieldtype)
		{
		$fieldtype = 'TINYINT';
		}
	if(!$maxlength) $maxlength = 10;
	if($fieldtype == 'INT')
		{
		$defaultvalue = intval($defaultvalue);
		if($defaultvalue > 2147483647) $defaultvalue = 0;
		if($maxlength > 10) $maxlength = 11;
		}
	else if($fieldtype == 'MEDIUMINT')
		{
		$defaultvalue = intval($defaultvalue);
		if($defaultvalue > 8388607) $defaultvalue = 0;
		if($maxlength > 6) $maxlength = 7;
		}
	else if($fieldtype == 'SMALLINT')
		{
		$defaultvalue = intval($defaultvalue);
		if($defaultvalue > 32767) $defaultvalue = 0;
		if($maxlength > 4) $maxlength = 5;
		}
	else if($fieldtype == 'TINYINT')
		{
		$defaultvalue = intval($defaultvalue);
		if($defaultvalue > 127) $defaultvalue = 0;
		if($maxlength > 2) $maxlength = 3;
		}
	return "{$fieldtype}( {$maxlength} ) NOT NULL DEFAULT '{$defaultvalue}'";
}//}}}
function cm_ft_conm__box_FString($set)
{//{{{
	$maxlength = $set['maxlength'];
	$fieldtype = $set['fieldtype'];
	$defaultvalue = $set['defaultvalue'];

	if (!$fieldtype)
		{
		$fieldtype = 'TINYINT';
		}
	if(!$maxlength) $maxlength = 10;
	if($fieldtype == 'INT')
		{
		$defaultvalue = intval($defaultvalue);
		if($defaultvalue > 2147483647) $defaultvalue = 0;
		if($maxlength > 10) $maxlength = 11;
		}
	else if($fieldtype == 'MEDIUMINT')
		{
		$defaultvalue = intval($defaultvalue);
		if($defaultvalue > 8388607) $defaultvalue = 0;
		if($maxlength > 6) $maxlength = 7;
		}
	else if($fieldtype == 'SMALLINT')
		{
		$defaultvalue = intval($defaultvalue);
		if($defaultvalue > 32767) $defaultvalue = 0;
		if($maxlength > 4) $maxlength = 5;
		}
	else if($fieldtype == 'TINYINT')
		{
		$defaultvalue = intval($defaultvalue);
		if($defaultvalue > 127) $defaultvalue = 0;
		if($maxlength > 2) $maxlength = 3;
		}
	$fieldtype = strtolower($fieldtype);
	return "{$fieldtype}({$maxlength})|NO||{$defaultvalue}|";
}//}}}

function cm_ft_conm__box_setting($setting)
{//{{{
	?>
<table cellpadding="2" cellspacing="1">
	<tr> 
      <td>文本框长度</td>
      <td><input type="text" name="setting[size]" value="<?=$size?>" size="10"></td>
    </tr>
	<tr> 
      <td>默认值</td>
      <td><input type="text" name="setting[defaultvalue]" value="<?=$defaultvalue?>" size="40"></td>
    </tr>
</table>
	<?php
}//}}}

function cm_ft_conm__box_form($field, $value, $fieldinfo)
{//{{{
	return 'box';

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

function cm_ft_conm__box_input()
{//{{{
	
}//}}}

function cm_ft_conm__box_update()
{//{{{
	
}//}}}

function cm_ft_conm__box_search_form($field, $value, $fieldinfo)
{//{{{
	return form::text($field, $field, $value, 'text', 20);
}//}}}

function cm_ft_conm__box_search($field, $value)
{//{{{
	return $value === '' ? '' : " `$field` LIKE '%$value%' ";
}//}}}

function cm_ft_conm__box_output($field, $value)
{//{{{
	$value = htmlspecialchars($value);
	return output::style($value, $content['style']);
}//}}}

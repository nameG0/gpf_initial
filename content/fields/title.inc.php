<?php 
/*
2011-10-17
标题 字段类型
*/
function content_field_title_add($tablename, $info, $setting)
{//{{{
	global $db;
	if(!$maxlength) $maxlength = 255;
	$maxlength = min($maxlength, 255);
	$sql = "ALTER TABLE `$tablename` ADD `$field` CHAR( $maxlength ) NOT NULL DEFAULT '$defaultvalue'";
	if($isstyle) $sql .= ", ADD `style` VARCHAR( 30 ) NOT NULL";
	$db->query($sql);
}//}}}

function content_field_title_drop($tablename, $info, $setting)
{//{{{
	global $db;
	$db->query("ALTER TABLE `$tablename` DROP `$field`");
}//}}}

function content_field_title_change($tablename, $info, $setting)
{//{{{
	global $db;
	extract($setting);
	extract($info);
	if(!$maxlength) $maxlength = 255;
	$maxlength = min($maxlength, 255);
	$fieldtype = $issystem ? 'CHAR' : 'VARCHAR';
	$sql = "ALTER TABLE `$tablename` CHANGE `$field` `$field` $fieldtype( $maxlength ) NOT NULL DEFAULT '$defaultvalue'";
	$db->query($sql);
}//}}}

function content_field_title_setting($info, $setting)
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

function content_field_title_form($field, $value, $fieldinfo)
{//{{{
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

function content_field_title_input()
{//{{{
	
}//}}}

function content_field_title_update()
{//{{{
	
}//}}}

function content_field_title_search_form($field, $value, $fieldinfo)
{
	return form::text($field, $field, $value, 'text', 20);
}

function content_field_title_search($field, $value)
{
	return $value === '' ? '' : " `$field` LIKE '%$value%' ";
}

function content_field_title_output($field, $value)
{
	$value = htmlspecialchars($value);
	return output::style($value, $content['style']);
}
?>

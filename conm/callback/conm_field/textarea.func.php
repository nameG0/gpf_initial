<?php 
function cm_ft_conm__textarea_sql($set, $rdb_type = NULL)
{//{{{
	//todo 目前 $rdb_type 参数无效。
	if(!$set['maxlength']) $set['maxlength'] = 255;
	$maxlength = min($set['maxlength'], 255);
	if ($maxlength && $maxlength <= 255)
		{
		$sql = "VARCHAR( {$maxlength} ) NOT NULL DEFAULT '{$set['defaultvalue']}'";
		}
	else
		{
		$sql = "MEDIUMTEXT NOT NULL";
		}
	return $sql;
}//}}}

function cm_ft_conm__textarea_FString($set)
{//{{{
	if(!$set['maxlength']) $set['maxlength'] = 255;
	$maxlength = min($set['maxlength'], 255);
	if ($maxlength && $maxlength <= 255)
		{
		$field_type = 'varchar';
		}
	else
		{
		$field_type = 'mediumtext';
		}
	return "varchar({$maxlength})|NO||{$set['defaultvalue']}|";
}//}}}

function content_field_textarea_change($tablename, $info, $setting)
{//{{{
	global $db;
	$maxlength = max(intval($maxlength), 0);
	$fieldtype = $issystem ? 'CHAR' : 'VARCHAR';
	$sql = "ALTER TABLE `$tablename` CHANGE `$field` `$field` ";
	$sql .= ($maxlength && $maxlength <= 255) ? "$fieldtype( $maxlength ) NOT NULL DEFAULT '$defaultvalue'" : "MEDIUMTEXT NOT NULL";
	$db->query($sql);
}//}}}

function content_field_textarea_drop($tablename, $info)
{//{{{
	global $db;
	$db->query("ALTER TABLE `$tablename` DROP `$field`");
}//}}}

function content_field_textarea_setting($info, $setting)
{//{{{
	?>
<table cellpadding="2" cellspacing="1">
	<tr> 
      <td>文本域行数</td>
      <td><input type="text" name="setting[rows]" value="<?=$rows?>" size="10"></td>
    </tr>
	<tr> 
      <td>文本域列数</td>
      <td><input type="text" name="setting[cols]" value="<?=$cols?>" size="10"></td>
    </tr>
	<tr> 
      <td>默认值</td>
      <td><textarea name="setting[defaultvalue]" rows="2" cols="20" id="defaultvalue" style="height:60px;width:250px;"><?=$defaultvalue?></textarea></td>
    </tr>
	<tr> 
      <td>是否启用关联链接：</td>
      <td><input type="radio" name="setting[enablekeylink]" value="1" <?=($enablekeylink == 1 ? 'checked' : '')?> /> 是 <input type="radio" name="setting[enablekeylink]" value="0" <?=($enablekeylink == 0 ? 'checked' : '')?> /> 否  <input type="text" name="setting[replacenum]" value="<?=$replacenum?>" size="4"> 替换次数 （留空则为替换全部）</td>
    </tr>
	<tr> 
      <td>是否启用剩余字符提示：</td>
      <td><input type="radio" name="setting[checkcharacter]" value="1" <?=($checkcharacter ? 'checked' : '')?> /> 是 <input type="radio" name="setting[checkcharacter]" value="0" <?=($checkcharacter ? '' : 'checked')?> /> 否 <font color='#f00;'>启用此项，必填字符长度最大值</font></td>
    </tr>
</table>
	<?php
}//}}}

function content_field_textarea_form($field, $value, $fieldinfo)
{//{{{
	extract($fieldinfo);
	if(!$value) $value = $defaultvalue;
	if($checkcharacter && $maxlength)
	{
		$formattribute .= ' onkeyup="checkLength(this, \''.$field.'\', \''.$maxlength.'\');"';
	}
	$html = '';
	if($value && $checkcharacter && $maxlength)
	{
		$html = '<script type="text/javascript">checkLength(document.getElementById(\''.$field.'\'), \''.$field.'\', \''.$maxlength.'\');</script>';
	}
	return form::textarea('info['.$field.']', $field, $value, $rows, $cols, $css, $formattribute, $checkcharacter, $maxlength).$html;
}//}}}

function content_field_textarea_save($field, $value)
{//{{{
	//phpcms 的旧代码
	//if(!fields[$field]['enablehtml']) $value = strip_tags($value);
	//return $value;
}//}}}

function content_field_textarea_output($field, $value)
{//{{{
	//phpcms 的旧代码
	// if($this->fields[$field]['enablekeylink'])
	// {
		// $replacenum = $this->fields[$field]['replacenum'];
		// $data = keylinks($data, $replacenum);
	// }
	//return format_textarea($value);

	return $value;
}//}}}

<?php 
/**
 * 编辑器字段类型
 * 
 * @package default
 * @filesource
 */
function cm_ft_editor__editor_sql($set)
{//{{{
	$type = strtoupper($set['field_type']);
	return "{$type} NOT NULL";
}//}}}
function cm_ft_editor__editor_FString($set)
{//{{{
	return "{$set['field_type']}|NO||";
}//}}}

function cm_ft_editor__editor_setting($setting)
{//{{{
	a::i($setting)->int(array('width', 'height'))->d('width', '500', a::F)->d('height', 300, a::F);
echo 
'字段类型：',
hd("radio|name=setting[field_type]|value={$setting['field_type']}|_default=text",
array("_data" => array("text" => 'text', "mediumtext" => 'mediumtext',),)),
'<br/>',

'编辑器样式：',
hd("radio|name=setting[toolbar]|value={$setting['toolbar']}|_default=basic",
array("_data" => array("basic" => '简洁型', "standard" => '标准型', "full" => '全功能',),)),

'<br/>编辑器大小：',
hd("text|label=宽|name=setting[width]|value={$setting['width']}|size=4"), 'px ',
hd("text|label=高|name=setting[height]|value={$setting['height']}|size=4"), 'px'
;
}//}}}

function cm_ft_editor__editor_form($field, $value, $set)
{//{{{
	if(!$value) $value = $set['defaultvalue'];

	$data = "<textarea name=\"data[{$field}]\" id=\"{$field}\" style=\"display:none\">{$value}</textarea>\n";
	return $data . hd("editor.fck|textareaid={$field}|toolbar={$set['toolbar']}|width={$set['width']}|height={$set['height']}");
}//}}}

function cm_ft_editor__editor_output($field, $value, $setting)
{//{{{
	return $value;
	// $data = $setting['storage'] == 'database' ? $value : content_get($contentid, $field);
	// if($setting['enablekeylink'])
	// {
		// $replacenum = $setting['replacenum'];
		// $data = keylinks($data, $replacenum);
	// }
	// return $data;
}//}}}

function cm_ft_editor__editor_search($field, $value)
{//{{{
	return $value ? " `$field` LIKE '%$value%' " : '';
}//}}}

function cm_ft_editor__editor_search_form($field, $value, $fieldinfo)
{//{{{
	return "<input type=\"text\" name=\"$field\" value=\"$value\" size=\"20\">";
}//}}}

function cm_ft_editor__editor_update($field, $value)
{//{{{
	global $aids,$attachment;
	if(!$value) return false;
	if($this->fields[$field]['storage'] == 'file')
	{
		content_set($this->contentid, $field, stripslashes($value));
	}
	$attachment->update($this->contentid, $field, $value);
	if($GLOBALS['add_introduce'] && $value)
	{
		$attachment->update_intr($this->contentid, $value, $GLOBALS['introcude_length']);
	}
	if($GLOBALS['auto_thumb'])
	{
		$attachment->update_thumb($this->contentid, $GLOBALS['auto_thumb_no']);
	}
	return 1;
}//}}}

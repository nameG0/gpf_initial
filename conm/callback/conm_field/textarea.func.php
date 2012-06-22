<?php 
/**
 * 多行文本字段类型
 * 
 * @package default
 * @filesource
 */
function _cm_ft_conm__textarea_mysql($set)
{//{{{
	if(!$set['maxlength']) $set['maxlength'] = 255;
	$maxlength = min($set['maxlength'], 255);
	$is_text = false;
	if ('text' == $set['field_type'] || 'mediumtext' == $set['field_type'])
		{
		$is_text = true;
		$set['defaultvalue'] = '';
		}
	$ret = array(
		"type" => $set['field_type'],
		"maxlength" => $maxlength,
		"defaultvalue" => $set['defaultvalue'],
		"is_text" => $is_text, //指示是否无长度无默认值的TEXT类型
		);
	return $ret;
}//}}}

function cm_ft_conm__textarea_sql($set, $rdb_type = NULL)
{//{{{
	//todo 目前 $rdb_type 参数无效。
	$field = _cm_ft_conm__textarea_mysql($set);
	if ($field['is_text'])
		{
		$len_str = $default_str = '';
		}
	else
		{
		$len_str = "({$field['maxlength']})";
		$default_str = "DEFAULT '{$field['defaultvalue']}'";
		}
	$field['type'] = strtoupper($field['type']);
	return "{$field['type']}{$len_str} NOT NULL {$default_str}";
}//}}}
function cm_ft_conm__textarea_FString($set)
{//{{{
	$field = _cm_ft_conm__textarea_mysql($set);
	if ($field['is_text'])
		{
		$len_str = '';
		}
	else
		{
		$len_str = "({$field['maxlength']})";
		}
	return "{$field['type']}{$len_str}|NO|{$field['defaultvalue']}|";
}//}}}
function cm_ft_conm__textarea_setting($setting)
{//{{{
	?>
<div >
字段类型：
<?=hd("radio|name=setting[field_type]|value={$setting['field_type']}|_default=text",
array("_data" => array("char" => 'char', "varchar" => 'varchar', "text" => 'text', "mediumtext" => 'mediumtext',),))?>
</div>
<?php
echo 
hd("text|label=文本域行数|name=setting[rows]|value={$setting['rows']}|size=10|br"),
hd("text|label=文本域列数|name=setting[cols]|value={$setting['cols']}|size=10|br")
;
}//}}}

function cm_ft_conm__textarea_form($field, $value, $set)
{//{{{
	$value = hd('html', $value);
	return hd("textarea|name=data[{$field}]|value={$value}|cols={$set['cols']}|rows={$set['rows']}");
}//}}}

function cm_ft_conm__textarea_save($field, $data)
{//{{{
	return $data[$field];
	//phpcms 的旧代码
	//if(!fields[$field]['enablehtml']) $value = strip_tags($value);
	//return $value;
}//}}}

function cm_ft_conm__textarea_output($field, $value)
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

<?php 
/**
 * 单文件上传
 * 
 * @package default
 * @filesource
 */
function _cm_ft_attachment__file_mysql($set)
{//{{{
	$maxlength = $set['maxlength'];
	if(!$maxlength) $maxlength = 255;
	$maxlength = min($maxlength, 255);
	return array(
		"maxlength" => $maxlength,
		);
}//}}}

function cm_ft_attachment__file_sql($set)
{//{{{
	$field = _cm_ft_attachment__file_mysql($set);
	return "VARCHAR( {$field['maxlength']} ) NOT NULL DEFAULT '{$set['defaultvalue']}'";
}//}}}
function cm_ft_attachment__file_FString($set)
{//{{{
	$field = _cm_ft_attachment__file_mysql($setting);
	return "varchar({$field['maxlength']})|NO|{$setting['defaultvalue']}|";
}//}}}

function cm_ft_attachment__file_setting($set)
{//{{{
	a::i($set)->d('size', 30)->d('upload_maxsize', 1024)->d('upload_allowext', 'zip|rar|doc|docx|xls|ppt|txt|gif|jpg|jpeg|png|bmp')->d('downloadtype', '0');
	echo 
		hd("text|label=文本框长度|name=setting[size]|value={$set['size']}|size=5|br"),
			hd("text|label=允许上传的文件大小|name=setting[upload_maxsize]|value={$set['upload_maxsize']}|size=5"),
			'KB 提示：1 KB = 1024 Byte，1 MB = 1024 KB<br/>',
			hd("text|label=允许上传的文件类型|name=setting[upload_allowext]|size=50|br", array("value" => $set['upload_allowext'],)),
			'是否保存文件大小',
			hd("radio|name=setting[issavefilesize]|value={$set['issavefilesize']}|_default=0", array("_data" => array("1" => '是', "0" => '否',),)),
			'<br/>文件下载方式:',
			hd("radio|name=setting[downloadtype]|value={$set['downloadtype']}", array("_data" => array("0" => '链接文件地址', "1" => '通过PHP读取',),))
		;
}//}}}

function cm_ft_attachment__file_form($set)
{//{{{
	if(!$value) $value = $defaultvalue;
	$data = '<input type="hidden" name="info['.$field.']" value="'.$value.'"/>';
	$data .= form::file($field, $field, $size, $css, $formattribute);
	if($value) $data .= " <a href='$value' title='$value'>查看文件</a>";
	return $data;
}//}}}

function cm_ft_attachment__file_input($set)
{//{{{
	global $PHPCMS,$aids,$mod,$catid,$attachment,$contentid;
	$upload_maxsize = 1024*$this->fields[$field]['upload_maxsize'];
	if($contentid) $result = $attachment->listinfo("contentid=$contentid AND field='$field'", 'aid');
	$aids = $attachment->upload($field, $this->fields[$field]['upload_allowext'], $upload_maxsize, 1);
	if(!$aids) return $result ? 1 : 0;
	return UPLOAD_URL.$attachment->attachments[$field][$aids[0]];
}//}}}

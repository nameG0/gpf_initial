<?php 
/**
 * 单图片上传字段，一般用于文章缩图
 * 
 * @package default
 * @filesource
 */
function _cm_ft_attachment__image_mysql($set)
{//{{{
	$maxlength = $set['maxlength'];
	if(!$maxlength) $maxlength = 255;
	$maxlength = min($maxlength, 255);
	return array(
		"maxlength" => $maxlength,
		);
}//}}}

function cm_ft_attachment__image_sql($setting)
{//{{{
	$field = _cm_ft_attachment__image_mysql($setting);
	return "VARCHAR( {$field['maxlength']} ) NOT NULL DEFAULT '{$setting['defaultvalue']}'";
}//}}}

function cm_ft_attachment__image_FString($setting)
{//{{{
	$field = _cm_ft_attachment__image_mysql($setting);
	return "varchar({$field['maxlength']})|NO|{$setting['defaultvalue']}|";
}//}}}

function cm_ft_attachment__image_setting($setting)
{//{{{
	a::i($setting)->d('upload_maxsize', 1024)->d('upload_allowext', 'gif|jpg|jpeg|png|bmp')->d('isselectimage', 0);
	echo 
		hd("text|label=录入框长度|name=setting[size]|value={$setting['size']}|size=10|br"),
			hd("text|label=允许上传的图片大小|name=setting[upload_maxsize]|value={$setting['upload_maxsize']}|size=5"),
			"KB 提示：1KB=1024Byte，1MB=1024KB<br/>",
			hd("text|label=允许上传的图片类型|name=setting[upload_allowext]|size=40|br", array("value" => $setting['upload_allowext'],)),
			'是否从已上传中选择',
			hd("radio|name=setting[isselectimage]|value={$setting['isselectimage']}", array("_data" => array("1" => '是', "0" => '否',),)),
			"<br/>是否产生缩略图",
			hd("radio|name=setting[isthumb]|value={$setting['isthumb']}|_default=0", array("_data" => array("1" => '是',"0" => '否',),)),
			'缩略图大小',
			hd("text|label=宽|name=setting[thumb_width]|value={$setting['thumb_width']}|size=3"),
			'px',
			hd("text|label=高|name=setting[thumb_height]|value={$setting['thumb_height']}|size=3"),
			'px<br/>是否加图片水印',
			hd("radio|name=setting[iswatermark]|value={$setting['iswatermark']}|_default=0", array("_data" => array("1" => '是',"0" => '否', ),)),
			hd("text|label=水印图片路径|name=setting[watermark_img]|value={$setting['watermark_img']}|size=40")
			;
}//}}}

function cm_ft_attachment__image_form($field, $value, $fieldinfo)
{//{{{
	return <<<EOT
输入 
<input type="text" name="data[{$field}]" value="{$value}" />
<input type="hidden" name="data[{$field}_old]" value="{$value}" />
或上传：<input type="file" name="{$field}" size="20" />
EOT;
	//下面是旧的代码
	if(defined('IN_ADMIN'))
	{
		return "<input type=\"text\" name=\"info[$field]\" id=\"$field\" value=\"$value\" size=\"$size\" class=\"$css\" $formattribute/> <input type=\"hidden\" name=\"{$field}_aid\" id=\"{$field}_aid\" value=\"0\"> <input type=\"button\" name=\"{$field}_upimage\" id=\"{$field}_upimage\" value=\"上传图片\" style=\"width:60px\" onclick=\"javascript:openwinx('?mod=phpcms&file=upload_field&uploadtext={$field}&modelid={$modelid}&catid={$catid}&fieldid={$fieldid}','upload','450','350')\"/> $data <input name=\"cutpic\" type=\"button\" id=\"cutpic\" value=\"裁剪图片\" onclick=\"CutPic('$field','$PHPCMS[siteurl]')\"/>{$getimg}";
	}
	else
	{
		return "<input type=\"text\" name=\"info[$field]\" id=\"$field\" value=\"$value\" size=\"$size\" class=\"$css\" $formattribute/> <input type=\"hidden\" name=\"{$field}_aid\" id=\"{$field}_aid\" value=\"0\"> <input type=\"button\" name=\"{$field}_upimage\" id=\"{$field}_upimage\" value=\"上传图片\" style=\"width:60px\" onclick=\"javascript:openwinx('".PHPCMS_PATH."upload_field.php?uploadtext={$field}&modelid={$modelid}&catid={$catid}&fieldid={$fieldid}','upload','450','350')\"/> <input name=\"cutpic\" type=\"button\" id=\"cutpic\" value=\"裁剪图片\" onclick=\"CutPic('$field','$PHPCMS[siteurl]')\"/>";
	}
}//}}}

function cm_ft_attachment__image_save($field, $data, $keep, $setting)
{//{{{
	global $db;
	mod_init('attachment');

	$is_del_old_image = false;	//开关，是否删除旧图片文件
	$new_image = $data[$field];	//手动输入的图片地址，若是修改文章，则为当前的图片地址
	$old_image = $data["{$field}_old"];	//表中的字段值，用隐藏域保存
	unset($data["{$field}_old"]);
	$upload = atta_upload_init($field);
	//若上传了新文件，则更新字段值,并删除旧文件
	if ($upload)
		{
		atta_upload_filter($upload);
		if (!$upload[0]['_is_error'])
			{
			$is_del_old_image = true;
			atta_upload_move($upload);
			//因为图片是保存到主表中的，所以加上附件目录的访问路径
			$new_image = $upload[0]['_url'];
			log::add("上传新图 {$new_image}", log::INFO, __FILE__, __LINE__, __FUNCTION__);
			atta_upload_close($upload);
			}
		}
	else if ($old_image != $new_image)
		{
		log::add("录入新图 {$new_image}", log::INFO, __FILE__, __LINE__, __FUNCTION__);
		$is_del_old_image = true;
		}
	//删除旧文件,如果是插入文章，则 old_image 是空的
	if ($is_del_old_image && $old_image)
		{
		//UPLOAD_ROOT 与 UPLOAD_URL 最后一个目录名是重复的。
		$path = str_replace(UPLOAD_URL, '', UPLOAD_ROOT) . $old_image;
		if (is_file($path))
			{
			log::add("删除旧图 {$path}", log::INFO, __FILE__, __LINE__, __FUNCTION__);
			unlink($path);
			}
		}
	//更新字段值
	$data[$field] = $new_image;
}//}}}

function cm_ft_attachment__image_output($field, $value)
{//{{{
	if($value !='')
	{
		$value = '<img src="'.$value.'" border="0">';
	}
	else
	{
		$value = '<img src="images/nopic.gif" border="0">';
	}
	return $value;
}//}}}

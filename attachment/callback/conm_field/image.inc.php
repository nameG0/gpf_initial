<?php 
/**
 * 单图片上传字段，一般用于文章缩图
 * 
 * @package default
 * @filesource
 */

function content_field_image_add($tablename, $info, $setting)
{//{{{
	global $db;
	$maxlength = $info['maxlength'];
	$field = $info['field'];
	$defaultvalue = $setting['defaultvalue'];

	if(!$maxlength) $maxlength = 255;
	$maxlength = min($maxlength, 255);

	$sql = "ALTER TABLE `$tablename` ADD `$field` VARCHAR( $maxlength ) NOT NULL DEFAULT '$defaultvalue'";
	echo $sql;exit;
	$db->query($sql);
}//}}}

function content_field_image_change($tablename, $info, $setting)
{//{{{
	global $db;
	if(!$maxlength) $maxlength = 255;
	$maxlength = min($maxlength, 255);
	$db->query("ALTER TABLE `$tablename` CHANGE `$field` `$field` CHAR( $maxlength ) NOT NULL DEFAULT '$defaultvalue'");
}//}}}

function content_field_image_drop($tablename, $info, $setting)
{//{{{
	global $db;
	$sql = "ALTER TABLE `$tablename` DROP `$field` ";
	$db->query($sql);
}//}}}

function content_field_image_setting($info, $setting)
{//{{{
	?>
<table cellpadding="2" cellspacing="1" onclick="javascript:$('#minlength').val(0);$('#maxlength').val(255);">
	<tr> 
      <td>文本框长度</td>
      <td><input type="text" name="setting[size]" value="<?=$size?>" size="10"></td>
    </tr>
	<tr> 
      <td>默认值</td>
      <td><input type="text" name="setting[defaultvalue]" value="<?=$defaultvalue?>" size="40"></td>
    </tr>
	<tr> 
      <td>允许上传的图片大小</td>
      <td><input type="text" name="setting[upload_maxsize]" value="1024" size="5">KB 提示：1KB=1024Byte，1MB=1024KB *</td>
    </tr>
	<tr> 
      <td>允许上传的图片类型</td>
      <td><input type="text" name="setting[upload_allowext]" value="gif|jpg|jpeg|png|bmp" size="40"></td>
    </tr>
    <!--
	<tr> 
      <td>是否从已上传中选择</td>
      <td><input type="radio" name="setting[isselectimage]" value="1"> 是 <input type="radio" name="setting[isselectimage]" value="0" checked> 否</td>
    </tr>
	<tr> 
      <td>是否产生缩略图</td>
      <td><input type="radio" name="setting[isthumb]" value="1" <?=($PHPCMS['thumb_enable'] ? 'checked' : '')?> onclick="$('#thumb_size').show()"/> 是 <input type="radio" name="setting[isthumb]" value="0"  <?=($PHPCMS['thumb_enable'] ? '' : 'checked')?> onclick="$('#thumb_size').hide()"/> 否</td>
    </tr>
	<tr id="thumb_size" style="display:<?=($PHPCMS['thumb_enable'] ? 'block' : 'none')?>"> 
      <td>缩略图大小</td>
      <td>宽 <input type="text" name="setting[thumb_width]" value="<?=$PHPCMS['thumb_width']?>" size="3">px 高 <input type="text" name="setting[thumb_height]" value="<?=$PHPCMS['thumb_height']?>" size="3">px</td>
    </tr>
	<tr> 
      <td>是否加图片水印</td>
      <td><input type="radio" name="setting[iswatermark]" value="1" <?=($PHPCMS['watermark_enable'] ? 'checked' : '')?> onclick="$('#watermark_img').show()"/> 是 <input type="radio" name="setting[iswatermark]" value="0"  <?=($PHPCMS['watermark_enable'] ? '' : 'checked')?> onclick="$('#watermark_img').hide()"/> 否</td>
    </tr>
	<tr id="watermark_img" style="display:<?=($PHPCMS['watermark_enable'] ? 'block' : 'none')?>"> 
      <td>水印图片路径</td>
      <td><input type="text" name="setting[watermark_img]" value="<?=$PHPCMS['watermark_img']?>" size="40"></td>
    </tr>
    -->
</table>
	<?php
}//}}}

function content_field_image_form($field, $value, $fieldinfo)
{//{{{
	global $catid,$PHPCMS;
	extract($fieldinfo);
	if(!$value) $value = $defaultvalue;
	$data = $isselectimage ? " <input type='button' value='浏览...' style='cursor:pointer;' onclick=\"file_select('$field', $catid, 1)\">" : '';
	$getimg = $get_img ? '<input type="checkbox" name="info[getpictothumb]" value="1" checked /> 保存文章第一张图片为缩略图' : '';
	?>
	<?php
	return <<<EOT
输入 
<input type="text" name="info[{$field}]" value="{$value}" />
<input type="hidden" name="info[{$field}_old]" value="{$value}" />
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

function content_field_image_save($field, & $data, $setting)
{//{{{
	global $db;
	$is_del_old_image = false;	//开关，是否删除旧图片文件
	$new_image = $data[$field];	//手动输入的图片地址，若是修改文章，则为当前的图片地址
	$old_image = $data["{$field}_old"];	//表中的字段值，用隐藏域保存
	unset($data["{$field}_old"]);
	module_init('attachment');
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

function content_field_image_output($field, $value)
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

?>

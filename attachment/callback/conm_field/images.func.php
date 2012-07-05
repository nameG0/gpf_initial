<?php 
/**
 * 多图片上传
 * 
 * @package default
 * @filesource
 */
function _cm_ft_attachment__images_mysql($set)
{//{{{
	$maxlength = $set['maxlength'];
	if(!$maxlength) $maxlength = 255;
	$maxlength = min($maxlength, 255);
	return array(
		"maxlength" => $maxlength,
		);
}//}}}

function cm_ft_attachment__images_sql($setting)
{//{{{
	$field = _cm_ft_attachment__images_mysql($setting);
	return "VARCHAR( {$field['maxlength']} ) NOT NULL DEFAULT '{$setting['defaultvalue']}'";
}//}}}

function cm_ft_attachment__images_FString($setting)
{//{{{
	$field = _cm_ft_attachment__images_mysql($setting);
	return "varchar({$field['maxlength']})|NO|{$setting['defaultvalue']}|";
}//}}}

function cm_ft_attachment__images_setting($setting)
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

function cm_ft_attachment__images_form($field, $value, $fieldinfo)
{//{{{
	return <<<EOT
输入 
<input type="text" name="data[{$field}]" value="{$value}" title="{$value}" size="50" />
<input type="hidden" name="keep[{$field}]" value="{$value}" /><br />
或上传：<input type="file" name="{$field}" size="20" />
EOT;
	//下面是旧的代码
	    global $attachment;
		extract($fieldinfo);
		$data = '';
		$data .= "<div id='FilePreview' style='Z-INDEX: 1000; LEFT: 0px; WIDTH: 10px; POSITION: absolute; TOP: 0px; HEIGHT: 10px; display: none;'></div>\n";
		if(!$value)
		{
		    $value = $defaultvalue;
		}
		else
		{
            $data .= "<div id='file_uploaded'>\n";
			$attachments = $attachment->listinfo("`contentid`=$this->contentid AND `field`='$field'", '`aid`,`filename`,`filepath`,`description`,`listorder`,`isthumb`');
			foreach($attachments as $k=>$v)
			{
			    $aid = $v['aid'];
			    $url = $v['isthumb'] ? $attachment->get_thumb($v['filepath']) : $v['filepath'];
			    $data .= "<div id='file_uploaded_$aid'><span style='width:30px'><input type='checkbox' name='{$field}_delete[]' value='$aid' title='删除'></span><span style='width:40px'><input type='text' name='{$field}_listorder[$aid]' value='$v[listorder]' size='3' title='排序'></span><span style='width:60px'><input type='text' name='{$field}_description[$aid]' value='$v[description]' size='20' title='修改图片说明'></span> <a href='###' onMouseOut='javascript:FilePreview(\"$url\", 0);' onMouseOver='javascript:FilePreview(\"$url\", 1);'>$v[filename] ".($v['description'] ? '('.$v['description'].')' : '')."</a></div>\n";
			}
		    $data .= "</div>\n";
		}
		$addmorepic = '';
		if(defined('IN_ADMIN')) $addmorepic = '<input type="button" onclick="AddMorePic(\'addmore_'.$field.'\');" value="批量添加">';
		$data .= "<div id='addmore_$field'></div>";
		$data .= '<input type="hidden" name="info['.$field.']" value="'.$value.'"/>';
        $data .= '<div id="file_div">';
		$data .= '<div id="file_1"><input type="file" name="'.$field.'[1]" size="20" onchange="javascript:AddInputFile(\''.$field.'\')"> <input type="text" name="'.$field.'_description[1]" size="20" title="名称"> <input type="button" value="删除" name="Del" onClick="DelInputFile(1);"> 
		'.$addmorepic.'</div>';
		$data .= '</div>';
		$_SESSION['field_images'] = 1;
		return $data;
}//}}}

function cm_ft_attachment__images_save($field, $data, $keep, $setting)
{//{{{
	global $db;

	$is_del_old_image = false;	//开关，是否删除旧图片文件
	$new_image = $data[$field];	//手动输入的图片地址，若是修改文章，则为当前的图片地址
	$old_image = $keep["{$field}"];	//表中的字段值，用隐藏域保存
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
	return $new_image;
}//}}}

function cm_ft_attachment__images_output($field, $value)
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

<?php
/**
 * 多文件上传
 * 2011-10-15
 * 
 * @package default
 * @filesource
 */

function content_field_downfiles_add($tablename, $info, $setting)
{//{{{
	global $db;
	$sql = "ALTER TABLE `{$tablename}` ADD `{$info['field']}` TEXT NOT NULL";
	$db->query($sql);
}//}}}

function content_field_downfiles_drop($info, $setting)
{//{{{
	global $db;
	$sql = "ALTER TABLE `{$tablename}` DROP `{$field}` ";
	$db->query($sql);
}//}}}

function content_field_downfiles_change($tablename, $info, $setting)
{//{{{
	global $db;
	$sql = "ALTER TABLE `{$tablename}` CHANGE `{$info['field']}` `{$info['field']}` TEXT NOT NULL";
	//实际上没有改变字段
	//$db->query($sql);
}//}}}

function content_field_downfiles_setting($info, $setting)
{//{{{
	$setting['size'] = $setting['size'] ? $setting['size'] : 50;
	?>
	<table cellpadding="2" cellspacing="1">
		<tr> 
	      <td>字段长度</td>
	      <td><input type="text" name="setting[size]" value="<?=$setting['size']?>" size="5"></td>
	    </tr>
		<tr> 
	      <td>文件下载方式</td>
	      <td>
	      <label>
	      <input type="radio" name="setting[downloadtype]" value="0" <?=($setting['downloadtype'] == 0 ? 'checked' : '')?> /> 链接文件地址
	      </label>
	      <label>
	      <input type="radio" name="setting[downloadtype]" value="1" <?=($setting['downloadtype'] == 1 ? 'checked' : '')?> /> 通过PHP读取
	      </label>
	      </td>
	    </tr>
	    <tr>
	    	<td>是否记录到附件表</td>
		<td>
		<label>
		<input name="setting[is_insert_attachment]" type="radio" value="1" <?=($setting['is_insert_attachment'] ? 'checked' : '')?> />是
		</label>
		<label>
		<input name="setting[is_insert_attachment]" type="radio" value="0" <?=($setting['is_insert_attachment'] ? '' : 'checked')?> />否
		</label>
		</td>
	    </tr>
	</table>
	<?php
}//}}}

function content_field_downfiles_form($field, $value, $setting)
{//{{{
	global $catid;
	$atta = array();
	if($value)
		{
		foreach (array_filter(explode("||", $value)) as $v)
			{
			$atta[] = explode("|", $v);
			}
		}
	else
		{
		$value = $setting['defaultvalue'];
		}
	ob_start();
	$content_bak = ob_get_clean();
	?>
<div id="file_uploaded">
<input type="hidden" name="info[<?=$field?>]" value="<?=$value?>" />
<input type="hidden" name="info[<?=$field?>_del]" id="<?=$field?>_del" value="" />
<div id="file_div">
<?php
foreach ($atta as $k => $v)
	{
	?>
<div >
	<?=$v[1]?>
	<input type="text" name="info[<?=$field?>_desc_<?=$k?>]" value="<?=$v[2]?>" />
	<input type="button" value="删除" onClick="$('#<?=$field?>_del').val($('#<?=$field?>_del').val() + '<?=$v[0] . '|' . $v[1]?>||');$(this).parent().remove();">
	<a href="<?=UPLOAD_URL, $v[1]?>">下载</a>
</div>
	<?php
	}
?>
上传新文件：
<div>
<input type="file" name="<?=$field?>[]" size="20" onchange="javascript:_AddInputFile('<?=$field?>');">
<input type="text" name="info[<?=$field?>_description][]" size="20" title="名称">
<input type="button" value="删除" onClick="$(this).parent().remove();">
</div>
</div>
</div>
<script type="text/javascript">
function _AddInputFile(Field)
{
	var fileTag = "<div><input type='file' name='" + Field + "[]' size='20' onchange='javascript:_AddInputFile(\""+Field+"\")'> <input type='text' name='info[" + Field + "_description][]' size='20' title='名称'> <input type='button' value='删除' onClick='$(this).parent().remove();'></div>";
	var fileObj = document.createElement("div");
	fileObj.innerHTML = fileTag;
	document.getElementById("file_div").appendChild(fileObj);
}
</script>
	<?php
	$string = ob_get_clean();
	echo $content_bak;
	return $string;
}//}}}

function content_field_downfiles_save($field, & $data, $setting)
{//{{{
	$del = $data["{$field}_del"];
	$desc = $data["{$field}_description"];
	unset($data["{$field}_del"], $data["{$field}_description"]);
	module_init('attachment');
	// require_once PHPCMS_ROOT . "attachment/include/attachment.class.php";
	// $o_atta = new attachment();
	// $upload = $o_atta->upload_format($field);
	$upload = atta_upload_init($field);
	//检查上传过程中是否有错，有错则不做修改，直接返回
	if ($upload)
		{
		$is_error = false;
		foreach ($upload as $k => $v)
			{
			if ($v['_is_waring'])
				{
				content_field_error("{$v['name']} - {$v['_error']}");
				$is_error  = true;
				}
			}
		if ($is_error)
			{
			return ;
			}
		}

	//格式化旧值
	$value = array();
	$tmp = array_filter(explode("||", $data[$field]));
	foreach ($tmp as $k => $v)
		{
		$v = explode("|", $v);
		$v[2] = $data["{$field}_desc_{$k}"];
		unset($data["{$field}_desc_{$k}"]);
		//用路径做键
		$value[$v[1]] = $v;
		}

	//不保存到附件表
	if (!$setting['is_insert_attachment'])
		{
		if ($del)
			{
			$tmp = array_filter(explode("||", $del));
			foreach ($tmp as $v)
				{
				$v = explode("|", $v);
				@unlink(UPLOAD_ROOT . $v[1]);
				unset($value[$v[1]]);
				}
			}
		foreach ($upload as $k => $v)
			{
			if ($v['_is_error'])
				{
				continue;
				}
			$description = $desc[$k] ? $desc[$k] : ($v['name'] ? $v['name'] : $v['_name']);
			dir_create($v['_fulldir']);
			move_uploaded_file($v['tmp_name'], $v['_fullpath']);
			$value[$v['_path']] = array(0, $v['_path'], $description);
			}
		}
	else
		{
		//保存到附件表，未完成
		}
	$str_value = array();
	foreach ($value as $v)
		{
		$str_value[] = join("|", $v);
		}
	$data[$field] = join("||", $str_value);
}//}}}

function content_field_downfiles_output($field, $value, $setting)
{//{{{
	//$contentid = $this->contentid;
	$downloadtype = $setting['downloadtype'];
	$values = explode("||", $value);
	$result = '';
	$middle = '';
	foreach($values as $k=>$v)
	{
		list($aid, $url, $desc) = explode("|", $v);
		$url = UPLOAD_URL . $url;
		$result .= "{$middle}<a href=\"{$url}\" target=\"_blank\">{$desc}</a>";
		$middle = '<br />';
		// $v = explode("|",$v);
		// $name = $v[0];
		// $downurl = $v[1];
		// $a_k = urlencode(phpcms_auth("i=$contentid&s=$serverurl&m=0&f=$downurl&d=$downloadtype", 'ENCODE', AUTH_KEY));
		// $result .= "<a href='down.php?a_k=$a_k' target='_blank'>$name</a>";
	}
	return $result;
}//}}}

function content_field_downfiles_search()
{//{{{
	
}//}}}
?>

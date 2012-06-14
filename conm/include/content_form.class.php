<?php
class content_form
{
	var $modelid;
	var $fields;
	var $contentid;

	function __construct($modelid)
	{
		global $db;
		$this->db = &$db;
		$this->modelid = $modelid;
		$this->fields = cache_read($this->modelid.'_fields.inc.php', CACHE_MODEL_PATH);
	}

	function content_form($modelid)
	{
		$this->__construct($modelid);
	}

	function areaid($field, $value, $fieldinfo)
	{
		global $AREA;
		extract($fieldinfo);
		$js = "<script type=\"text/javascript\">
			function area_load(id)
			{
				$.get('load.php', { field: 'areaid', id: id, value: '".$field."' },
						function(data){
						$('#load_$field').append(data);
						});
			}
		function area_reload()
		{
			$('#load_$field').html('');
			area_load(0);
		}
		area_load(0);
		</script>";
		if($value)
		{
			$areaname = $AREA[$value]['name'];
			return "<input type=\"hidden\" name=\"info[$field]\" id=\"$field\" value=\"$value\">
				<span onclick=\"this.style.display='none';\$('#reselect_$field').show();\" style=\"cursor:pointer;\">$areaname <font color=\"red\">点击重选</font></span>
				<span id=\"reselect_$field\" style=\"display:none;\">
				<span id=\"load_$field\"></span> 
				<a href=\"javascript:area_reload();\">重选</a>
				</span>$js";
		}
		else
		{
			return "<input type=\"hidden\" name=\"info[$field]\" id=\"$field\" value=\"$value\">
				<span id=\"load_$field\"></span>
				<a href=\"javascript:area_reload();\">重选</a>$js";
		}
	}
	function author($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		$infos = cache_read('author.php');
		$data = '<select name="" onchange="$(\'#'.$field.'\').val(this.value)" style="width:75px"><option>常用作者</option>';
		foreach((array)$infos as $v)
		{
			$data .= "<option value='{$v}'>{$v}</option>\n";
		}
		$data .= '</select>';
		if(defined('IN_ADMIN')) $data .= ' <a href="###" onclick="SelectAuthor();">更多&gt;&gt;</a>';
		return form::text('info['.$field.']', $field, $value, 'text', $size, $css, $formattribute).$data;
	}
	function box($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		if($boxtype == 'radio')
		{
			return form::radio($options, 'info['.$field.']', $field, $value, $cols, $css, $formattribute, $width);
		}
		elseif($boxtype == 'checkbox')
		{
			return form::checkbox($options, 'info['.$field.']', $field, $value, $cols, $css, $formattribute, $width);
		}
		elseif($boxtype == 'select')
		{
			return form::select($options, 'info['.$field.']', $field, $value, $size, $css, $formattribute);
		}
		elseif($boxtype == 'multiple')
		{
			return form::multiple($options, 'info['.$field.']', $field, $value, $size, $css, $formattribute);
		}
	}
	function copyfrom($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		$infos = cache_read('copyfrom.php');
		$data = '<select name="select_copyfrom" onchange="$(\'#'.$field.'\').val(this.value)" style="width:75px"><option>常用来源</option>';
		foreach((array)$infos as $info)
		{
			$data .= "<option value='{$info[name]}|{$info[url]}'>{$info[name]}</option>\n";
		}
		$data .= '</select>';
		if(defined('IN_ADMIN')) $data .= ' <a href="###" onclick="SelectCopyfrom();">更多&gt;&gt;</a>';
		return form::text('info['.$field.']', $field, $value, $type, $size, $css, $formattribute).$data;
	}

	function downfile($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		return form::downfile('info['.$field.']', $field, $value, $size, $mode, $css, $formattribute);
	}
	function file($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		$data = '<input type="hidden" name="info['.$field.']" value="'.$value.'"/>';
		$data .= form::file($field, $field, $size, $css, $formattribute);
		if($value) $data .= " <a href='$value' title='$value'>查看文件</a>";
		return $data;
	}
	function flashupload($field, $value, $fieldinfo)
	{
		global $attachment, $player;
		if(!is_a($player, 'player'))
		{
			$player = load('player.class.php');
		}
		$arr_player = $player->listinfo('disabled=0');
		$session_id = session_id();
		$cookie_auth = get_cookie('auth');
		$cookie_cookietime = get_cookie('cookietime');
		@extract($fieldinfo);
		if($upload_allowext)
		{
			$org_upload_allowext = $upload_allowext;
			$arr_allowext = explode('|', $upload_allowext);
			foreach($arr_allowext as $k=>$v)
			{
				$v = '*.'.$v;
				$array[$k] = $v;
			}
			$upload_allowext = implode(';', $array);
		}
		$firstid = $field.'1';
		$data = '';
		if(!$value)
		{
			$value = $defaultvalue;
		}
		else
		{
			$value = str_replace(array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;'),array('&', '"', "'", '<', '>') ,$value);
			eval("\$value = $value;");
			$playid = $value['player'];
			@extract($value);
			$value['str_video'] = str_replace(';', "\n", $value['str_video']);
		}

		$script = "
			<textarea name=\"info[$field][videourl]\" id=\"videoshow\" cols=\"80\" rows=\"5\" />".$value['str_video']."</textarea>
			<link href=\"admin/skin/default.css\" rel=\"stylesheet\" type=\"text/css\">
			<script language=\"JavaScript\" src=\"images/js/swfupload/swfupload.js\"></script>
			<script language=\"JavaScript\" src=\"images/js/swfupload/fileprogress.js\"></script>
			<script language=\"JavaScript\" src=\"images/js/swfupload/hanlders.js\"></script>
			<script language=\"javascript\">
			var swfu;
		window.onload = function() {
			var settings = {
upload_url : \"flash_upload.php?dosubmit=1\",
	     flash_url : \"images/js/swfupload/swfupload.swf\",
	     post_params: 
	     {\"PHPSESSID\" : \"$session_id\",
		     \"auth\" : \"$cookie_auth\",
		     \"cookietime\" : \"$cookie_cookietime\",
		     \"modelid\" : \"$this->modelid\",
		     \"fieldid\" : \"$fieldid\"},
		     file_size_limit : \"$upload_maxsize KB\",
		     file_types : \"$upload_allowext\",
		     file_types_description : \"All Files\",
		     file_upload_limit : 100,
		     file_queue_limit : \"$upload_items\",
		     custom_settings : {
progressTarget : \"fsUploadProgress\",
		 cancelButtonId : \"btnCancel\"
		     },
debug: false,

       button_image_url: \"images/flash_button.gif\",	// Relative to the Flash file
	       button_placeholder_id: \"spanButtonPlaceHolder\",
       button_width: 80,
       button_height: 22,

       file_dialog_start_handler : fileDialogStart,
       file_queued_handler : fileQueued,
       file_queue_error_handler : fileQueueError,
       file_dialog_complete_handler : fileDialogComplete,
       upload_progress_handler : uploadProgress,
       upload_error_handler : uploadError,
       upload_success_handler : uploadSuccess,
       upload_complete_handler : uploadComplete
			};
			swfu = new SWFUpload(settings);
		};
		var n = 1;
		function uploadSuccess(file, serverData) {
			try {
				if(serverData==1)
				{
					alert('上传的文件超过了 php.ini 中 upload_max_filesize=".ini_get('upload_max_filesize')." 选项限制的值');
					return false;
				}
				else if(serverData==99)
				{
					alert('上传的文件超过了 php.ini 中 post_max_size=".ini_get('post_max_size')."选项限制的值');
					return false;
				}
				var progress = new FileProgress(file, this.customSettings.progressTarget);
				progress.setComplete();
				progress.setStatus(\"上传完成\");
				progress.toggleCancel(false);
				if($('#videoshow').html())
				{
					document.getElementById('videoshow').value += serverData + \"\\n\";
				}
				else
				{
					document.getElementById('videoshow').value = serverData + \"\\n\";
				}	
			} catch (ex) {
				this.debug(ex);
			}
		}

		function setvideo()
		{
			var i = 0;
			var data = '';
			var startnum = parseInt($('#startvideo').val());
			var endnum = parseInt($('#endvideo').val());
			var videourl = $('#playurl').val();
			var videoext = $('#vext').val();
			for(i=startnum; i<=endnum; i++)
			{
				data = i + '|' +videourl + i +videoext;
				document.getElementById('videoshow').value += data + \"\\n\";
			}
		}
		</script>
			<fieldset class=\"flash\" id=\"fsUploadProgress\">
			<legend>上传列表</legend>
			</fieldset>
			<span id=\"spanButtonPlaceHolder\"></span>&nbsp;
		<input type=\"button\" id=\"btupload\" value=\"开始上传\" onClick=\"swfu.startUpload();\" />
			<input id=\"btnCancel\" type=\"button\" value=\"取消上传\" onClick=\"cancelQueue(upload1);\" disabled=\"disabled\" style=\"margin-left: 2px; height: 22px; font-size: 8pt;\" />";
		foreach($arr_player as $play)
		{
			$arr_p[$play['playerid']] = $play['subject']; 
		}
		if($arr_p)
		{
			$arr_p[''] = '自动选择播放器';
			ksort($arr_p);
			$sele_player = form::select($arr_p, 'info['.$field.'][player]', 'player', $player);
		}
		if($servers)
		{
			$sele_server = form::select($servers, 'info['.$field.'][server]', 'player', $server);
		}
		$op_server = $sele_player.'&nbsp;'.$sele_server.'<br />'.'播放地址：<input type="text" name="playurl" id="playurl" >开始集数：<input type="text" name="startvideo" id="startvideo" value="1">结束：<input type="text" name="endvideo" id="endvideo" value="1">视频格式：<input type="text" name="vext" id="vext" value=".rm">&nbsp;<input type="button" value="设定" onclick="setvideo()">';
		$data = $op_server.'<br />'.$script;
		return  $data;
	}	function groupid($field, $value, $fieldinfo)
	{
		global $priv_group;
		extract($fieldinfo);
		$groupids = '';
		if($value && $this->contentid) 
		{
			$groupids = $priv_group->get_groupid('contentid', $this->contentid, $priv);
			$groupids = implode(',', $groupids);
		}
		return form::select_group('info['.$field.']', $field, $groupids, $cols, $width);
	}
	function image($field, $value, $fieldinfo)
	{
		global $catid,$PHPCMS;
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		$data = $isselectimage ? " <input type='button' value='浏览...' style='cursor:pointer;' onclick=\"file_select('$field', $catid, 1)\">" : '';
		$getimg = $get_img ? '<input type="checkbox" name="info[getpictothumb]" value="1" checked /> 保存文章第一张图片为缩略图' : '';
		if(defined('IN_ADMIN'))
		{
			return "<input type=\"text\" name=\"info[$field]\" id=\"$field\" value=\"$value\" size=\"$size\" class=\"$css\" $formattribute/> <input type=\"hidden\" name=\"{$field}_aid\" id=\"{$field}_aid\" value=\"0\"> <input type=\"button\" name=\"{$field}_upimage\" id=\"{$field}_upimage\" value=\"上传图片\" style=\"width:60px\" onclick=\"javascript:openwinx('?mod=phpcms&file=upload_field&uploadtext={$field}&modelid={$modelid}&catid={$catid}&fieldid={$fieldid}','upload','450','350')\"/> $data <input name=\"cutpic\" type=\"button\" id=\"cutpic\" value=\"裁剪图片\" onclick=\"CutPic('$field','$PHPCMS[siteurl]')\"/>{$getimg}";
		}
		else
		{
			return "<input type=\"text\" name=\"info[$field]\" id=\"$field\" value=\"$value\" size=\"$size\" class=\"$css\" $formattribute/> <input type=\"hidden\" name=\"{$field}_aid\" id=\"{$field}_aid\" value=\"0\"> <input type=\"button\" name=\"{$field}_upimage\" id=\"{$field}_upimage\" value=\"上传图片\" style=\"width:60px\" onclick=\"javascript:openwinx('".PHPCMS_PATH."upload_field.php?uploadtext={$field}&modelid={$modelid}&catid={$catid}&fieldid={$fieldid}','upload','450','350')\"/> <input name=\"cutpic\" type=\"button\" id=\"cutpic\" value=\"裁剪图片\" onclick=\"CutPic('$field','$PHPCMS[siteurl]')\"/>";
		}
	}
	function images($field, $value, $fieldinfo)
	{
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
	}
	function islink($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if($value)
		{
			$linkurl = $this->content_url;
			$disabled = '';
			$checked = 'checked';
		}
		else
		{
			$value = $defaultvalue;
			$disabled = 'disabled';	
			$checked = '';
		}
		$strings = '<input type="hidden" name="info['.$field.']" value="99"><input type="text" name="info[linkurl]" id="linkurl" value="'.$linkurl.'" size="50" maxlength="255" '.$disabled.'> <font color="#FF0000"><label><input name="info['.$field.']" type="checkbox" id="islink" value="1" onclick="ruselinkurl();" '.$checked.'> 转向链接</label></font><br/><font color="#FF0000">如果使用转向链接则点击标题就直接跳转而内容设置无效</font>';
		return $strings;
	}
	function keyword($field, $value, $fieldinfo)
	{
		log::add("未完成", log::DEBUG, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
		return ;
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		$infos = cache_read('keyword.php');
		$data = "<select name='select_keyword' onchange=\"if($('#{$field}').val()==''){ $('#{$field}').val(this.value);}else if($('#{$field}').val().indexOf(this.value)==-1){ $('#{$field}').val($('#{$field}').val()+' '+this.value);}\" style='width:85px'><option>常用关键词</option>";
		foreach($infos as $info)
		{
			$data .= "<option value='{$info}'>{$info}</option>\n";
		}
		$data .= "</select>";
		if(defined('IN_ADMIN')) $data .= " <a href=\"###\" onclick=\"SelectKeyword();\">更多&gt;&gt;</a>";
		return form::text('info['.$field.']', $field, $value, $type, $size, $css, $formattribute).$data;
	}

	function linkage($field, $value, $fieldinfo)
	{
		$linageid = $fieldinfo['linageid'];
		return menu_linkage($linageid,$field,$value);
	}
	function number($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		return form::text('info['.$field.']', $field, $value, 'text', 10, $css, $formattribute, $minlength, $maxlength, $pattern, $errortips);
	}

	function pages($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if($value)
		{
			$v = explode('|', $value);
			$data = "<select name=\"info[paginationtype]\" id=\"paginationtype\" onchange=\"if(this.value==1)\$('#paginationtype1').css('display','');else\$('#paginationtype1').css('display','none');\">";
			$type = array('不分页', '自动分页', '手动分页');
			if($v[0]==1) $con = 'style="display:"';
			else $con = 'style="display:none"';
			foreach($type as $i => $val)
			{
				if($i==$v[0]) $tag = 'selected';
				else $tag = '';
				$data .= "<option value=\"$i\" $tag>$val</option>";
			}
			$data .= "</select> &nbsp;&nbsp;&nbsp;&nbsp;<strong><font color=\"#0000FF\">注：</font></strong><font color=\"#0000FF\">手动分页时，将光标放在需要分页处，点编辑器下面的“</font> 分页 <font color=\"#0000FF\">”即可。点击“</font> 子标题 <font color=\"#0000FF\">”可以设置每篇分页的标题。</font><div id=\"paginationtype1\" $con>自动分页时的每页大约字符数（包含HTML标记）<strong> <input name=\"info[maxcharperpage]\" type=\"text\" id=\"maxcharperpage\" value=\"$v[1]\" size=\"8\" maxlength=\"8\"></strong></div>";
			return $data;
		}
		else
		{
			return "<select name=\"info[paginationtype]\" id=\"paginationtype\" onchange=\"if(this.value==1)\$('#paginationtype1').css('display','');else \$('#paginationtype1').css('display','none');\">
				<option value=\"0\">不分页</option>
				<option value=\"1\">自动分页</option>
				<option value=\"2\">手动分页</option>
				</select> &nbsp;&nbsp;&nbsp;&nbsp;<strong><font color=\"#0000FF\">注：</font></strong><font color=\"#0000FF\">手动分页时，将光标放在需要分页处，点编辑器下面的“</font> 分页 <font color=\"#0000FF\">”即可。点击“</font> 子标题 <font color=\"#0000FF\">”可以设置每篇分页的标题。</font>
				<div id=\"paginationtype1\" style=\"display:none\">自动分页时的每页大约字符数（包含HTML标记）<strong> <input name=\"info[maxcharperpage]\" type=\"text\" id=\"maxcharperpage\" value=\"10000\" size=\"8\" maxlength=\"8\"></strong></div>";
		}
	}

	function posid($field, $value, $fieldinfo)
	{
		log::add("未完成", log::DEBUG, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
		return ;
		extract($fieldinfo);
		$result = $this->db->select("SELECT `posid` FROM `".DB_PRE."content_position` WHERE `contentid`='$this->contentid'", 'posid');
		$posids = implode(',', array_keys($result));
		return form::select_pos('info['.$field.']', $field, $posids, $cols, $width);
	}
	function style($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		return form::style("info[$field]", $value);
	}
	function template($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		return form::select_template('phpcms','info['.$field.']', $field, $value, '', 'show');
	}
	function text($field, $value, $fieldinfo)
	{
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		$type = $ispassword ? 'password' : 'text';
		return form::text('info['.$field.']', $field, $value, $type, $size, $css, $formattribute, $minlength, $maxlength, $pattern, $errortips);
	}
	function textarea($field, $value, $fieldinfo)
	{
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
	}
	function typeid($field, $value, $fieldinfo)
	{
		log::add("未完成", log::DEBUG, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
		return ;
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		return form::select_type('phpcms', 'info['.$field.']', $field, '请选择', $value, '', $this->modelid);
	}
}
?>

<?php
require_once CONTENT_ROOT . "include/content.func.php";

class content_input
{
	var $modelid;
	var $fields;
	var $data;
	var $isimport;

	function __construct($modelid)
	{
		global $db;
		$this->db = &$db;
		$this->modelid = $modelid;
		$this->fields = cache_read($this->modelid.'_fields.inc.php', CACHE_MODEL_PATH);

		content_field_load($this->fields);
	}

	function content_input($modelid)
	{
		$this->__construct($modelid);
	}

	function get($data, $isimport = 0)
	{
		global $_roleid, $MODEL, $_groupid,$action,$G;
		$this->isimport = $isimport;
		//if(!$G['allowpost']) showmessage('你所在的用户组没有发表权限');
		$this->data = $data;
		$info = array();
		$field_list = include CONTENT_ROOT . 'fields/fields.inc.php';
		//issystem 保存在缓存中，不容易调整，手动强制建立 issystem 字段的列表
		$system_field = array('thumb', 'description');
		foreach ($this->fields as $field => $setting)
			{
			//加工字段值
			$formtype = $setting['formtype'];
			$func_name = "content_field_{$formtype}_save";
			if (function_exists($func_name))
				{
				//调用其它模块提供的字段类型
				$func_name($field, $data, $setting);
				}
			else if(method_exists($this, $formtype))
				{
				$data[$field] = $this->$formtype($field, $data[$field]);
				}

			$issystem = in_array($field, $system_field) ? true : $setting['issystem'];
			if ($issystem)
				{
				$info['system'][$field] = $data[$field];
				}
			else
				{
				$info['model'][$field] = $data[$field];
				}
			}
		//链接类型的文章需要跳过的字段名
		// $debar_filed = array('catid','title','style','thumb','status','islink','description');
		// foreach($data as $field => $value)
		// {
			// if($data['islink']==1 && !in_array($field,$debar_filed)) continue;
			// if(!isset($this->fields[$field]) || check_in($_roleid, $this->fields[$field]['unsetroleids']) || check_in($_groupid, $this->fields[$field]['unsetgroupids'])) continue;
			// //字段值验证
			// $name = $this->fields[$field]['name'];
			// $minlength = $this->fields[$field]['minlength'];
			// $maxlength = $this->fields[$field]['maxlength'];
			// $pattern = $this->fields[$field]['pattern'];
			// $errortips = $this->fields[$field]['errortips'];
			// if(empty($errortips)) $errortips = "$name 不符合要求！";
			// $length = strlen($value);
			// if($minlength && $length < $minlength && !$isimport) showmessage("$name 不得少于 $minlength 个字符！");
			// if($maxlength && $length > $maxlength && !$isimport)
			// {
				// showmessage("$name 不得超过 $maxlength 个字符！");
			// }
			// else
			// {
				// str_cut($value, $maxlength);
			// }
			// if($pattern && $length && !preg_match($pattern, $value) && !$isimport) showmessage($errortips);
			// $checkunique_table = $this->fields[$field]['issystem'] ? DB_PRE.'content' : DB_PRE.'c_'.$MODEL[$this->modelid]['tablename'];
			// if($this->fields[$field]['isunique'] && $this->db->get_one("SELECT $field FROM $checkunique_table WHERE `$field`='$value' LIMIT 1") && $action != 'edit') showmessage("$name 的值不得重复！");

		// }
		if($isimport) $info['system']['username'] = $data['username'];
		//todo: 错误处理不应放在此处。
		$error = content_field_error();
		if ($error)
			{
			showmessage(join("<br/>\n", $error));
			}
		return $info;
	}

	function areaid($field, $value)
	{
		global $AREA;
		if($value && !isset($AREA[$value])) showmessage("所选地区不存在！");
		return $value;
	}
	function author($field, $value)
	{
		if(empty($value)) return null;
		if(!$this->db->get_one("SELECT `authorid` FROM `".DB_PRE."author` WHERE `name`='$value'"))
		{
			$this->db->query("INSERT INTO ".DB_PRE."author (`name`,`updatetime`) VALUES('$value','".TIME."')");
		}
		return $value;
	}
	function box($field, $value)
	{
		if($this->fields[$field]['boxtype'] == 'checkbox') 
		{
			if(!is_array($value) || empty($value)) return false;
			$value = implode(',', $value);
		}
		return $value;
	}
	function copyfrom($field, $value)
	{
		if(!$value) return '';
		if(strpos($value, '|'))
		{
			$copyfrom = explode('|', $value);
			$name = $copyfrom[0];
			$url = $copyfrom[1];
		}
		else
		{
			$name = $value;
			$url = '';
		}
		if($this->db->get_one("SELECT `name` FROM `".DB_PRE."copyfrom` WHERE `name`='$name'"))
		{
			$this->db->query("UPDATE `".DB_PRE."copyfrom` SET `url`='$url',`usetimes`=`usetimes`+1,`updatetime`='".TIME."' WHERE `name`='$name'");
		}
		else
		{
			$this->db->query("INSERT INTO `".DB_PRE."copyfrom` (`name`,`url`,`usetimes`,`updatetime`) VALUES('$name','$url','1','".TIME."')");
		}
		return $value;
	}

	function datetime($field, $value)
	{
		if($this->fields[$field]['dateformat'] == 'int')
		{
			return strtotime($value);
		}
		else
		{
			return $value;
		}
	}
	function editor($field, $value)
	{
		global $attachment;
		//if($this->fields[$field]['enablesaveimage'] && !$this->isimport) $value = $attachment->download($field, $value);
		return $value;
	}
	function file($field, $value)
	{
		global $PHPCMS,$aids,$mod,$catid,$attachment,$contentid;
		$upload_maxsize = 1024*$this->fields[$field]['upload_maxsize'];
		if($contentid) $result = $attachment->listinfo("contentid=$contentid AND field='$field'", 'aid');
		$aids = $attachment->upload($field, $this->fields[$field]['upload_allowext'], $upload_maxsize, 1);
		if(!$aids) return $result ? 1 : 0;
		return UPLOAD_URL.$attachment->attachments[$field][$aids[0]];
	}

	function flashupload($field, $value)
	{		
		$serverurl = $value['server'] ? $value['server'] : SITE_URL;		
		$values = explode("\n",$value['videourl']);
		foreach($values AS $k=>$v)
		{
			$v = explode("|",$v);
			if(!$v[0]) continue;
			$name = $v[0];
			$videourl = $v[1];
			$str_video .= $name.'|'.$videourl.'\n';
		}
		$str_video = str_replace('\n', ';', $str_video);
		$array['str_video'] = $str_video;
		$array['player'] = $value['player'];
		$array['server'] = $serverurl;
		$str_video = array2string($array);
		return $str_video;
	}	function groupid($field, $value)
	{
		return $value ? 1 : 0;
	}
	function images($field, $value)
	{
		global $PHPCMS,$aids,$mod,$catid,$attachment,$contentid,$MODULE;
		$upload_maxsize = 1024*$this->fields[$field]['upload_maxsize'];
		$addmorepic = $GLOBALS['addmore_'.$field];
		if(!empty($addmorepic) && is_array($addmorepic))
		{
			$attachment->field = $field;
			foreach($addmorepic AS $i => $v)
			{
				if(in_array($v,$GLOBALS['addmore_'.$field.'_delete'])) continue;
				$v = str_replace(UPLOAD_URL,'',$v);
				$filename = basename($v);
				$this->imageexts = $fileext = fileext($filename);
				if(!preg_match("/^(jpg|jpeg|gif|bmp|png)$/", $fileext)) continue;
				$uploadedfile = array('filename'=>$filename, 'filepath'=>$v, 'filetype'=>'', 'filesize'=>'', 'fileext'=>$fileext, 'description'=>$GLOBALS['addmore_'.$field.'_description'][$i]);
				$attachment->add($uploadedfile);
			}
			$is_addmorepic = TRUE;
		}
		if(isset($GLOBALS[$field.'_listorder']))
		{
			foreach($GLOBALS[$field.'_listorder'] as $aid=>$listorder)
			{
				$attachment->listorder($aid, $listorder);
			}
		}
		if(isset($GLOBALS[$field.'_delete']))
		{
			$del_aids = implode(',', $GLOBALS[$field.'_delete']);
			$attachment->delete("`aid` IN($del_aids)");
		}
		if(isset($GLOBALS[$field.'_description']))
		{
			foreach($GLOBALS[$field.'_description'] as $aid=>$description)
			{
				$attachment->description($aid, $description);
			}
		}
		if($contentid) $result = $attachment->listinfo("contentid=$contentid AND field='$field'", 'aid');
		$aids = $attachment->upload($field, $this->fields[$field]['upload_allowext'], $upload_maxsize, 1);
		if(!$aids) return ($result || $is_addmorepic) ? 1 : 0;
		require_once 'image.class.php';
		$image = new image();
		foreach($attachment->attachments[$field] as $aid=>$f)
		{
			$img = UPLOAD_URL.$f;
			if($this->fields[$field]['isthumb'])
			{
				$thumb = $attachment->get_thumb($img);
				$image->thumb($img, $thumb, $this->fields[$field]['thumb_width'], $this->fields[$field]['thumb_height']);
				$attachment->set_thumb($aid);
			}
			if($this->fields[$field]['iswatermark']) $image->watermark($img, '', $PHPCMS['watermark_pos'], $this->fields[$field]['watermark_img'], '', 5, '#ff0000', $PHPCMS['watermark_jpgquality']);
		}

		return 1;
	}
	function islink($field, $value)
	{
		if($value == '') $value = 99;
		return $value ==99 ? 0 : 1;
	}
	function keyword($field, $value)
	{
		if(!$value)
		{
			if(extension_loaded('scws'))
			{
				$data = $this->data['title'].$this->data['description'];
				require_once PHPCMS_ROOT.'api/keyword.func.php';
				$value = get_keywords($data, 2);
			}
			if(!$value) return '';
		}
		if(strpos($value, ','))
		{
			$s = ',';
		}
		else
		{
			$s = ' ';
		}
		$keywords = isset($s) ? array_unique(array_filter(explode($s, $value))) : array($value);
		foreach($keywords as $tag)
		{
			$tag = trim($tag);
			if($this->db->get_one("SELECT `tagid` FROM `".DB_PRE."keyword` WHERE `tag`='$tag'"))
			{
				$this->db->query("UPDATE `".DB_PRE."keyword` SET `usetimes`=`usetimes`+1,`lastusetime`=".TIME." WHERE `tag`='$tag'");
			}
			else
			{
				$this->db->query("REPLACE INTO `".DB_PRE."keyword` (`tag`,`usetimes`,`lastusetime`) VALUES('$tag','1','".TIME."')");
			}
		}
		return implode($s, $keywords);
	}
	function linkage($field, $value)
	{
		global $$field;
		$value = $$field;
		return $value;
	}
	function posid($field, $value)
	{
		return $value && $value != -99 ? 1 : 0;
	}
	function textarea($field, $value)
	{
		if(!$this->fields[$field]['enablehtml']) $value = strip_tags($value);
		return $value;
	}
}
?>

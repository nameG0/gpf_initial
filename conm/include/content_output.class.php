<?php
/**
 * 应该已被 global.func.php 中的 content_field_output 代替
 * 
 * @package default
 * @filesource
 */
class content_output
{
	var $fields;
	var $data;

	function __construct()
	{
		global $db, $CATEGORY;
		$this->db = &$db;
		$this->CATEGORY = $CATEGORY;
	}

	function content_output()
	{
		$this->__construct();
	}

	function set_catid($catid)
	{
		$modelid = $this->CATEGORY[$catid]['modelid'];
		$this->modelid = $modelid;
		$this->fields = cache_read($modelid.'_fields.inc.php', CACHE_MODEL_PATH);
	}

	function get($data)
	{

		$this->data = $data;
		$this->contentid = $data['contentid'];
		$this->set_catid($data['catid']);

		//加载其它模块的字段类型
		$field_list = require CONTENT_ROOT . "fields/fields.inc.php";
		foreach ($this->fields as $field => $setting)
		{
			$formtype = $setting['formtype'];
			if (function_exists("content_field_{$formtype}_form"))
			{
				continue;
			}
			if (is_array($field_list[$setting['formtype']]) && $field_list[$setting['formtype']][1])
			{
				require_once PHPCMS_ROOT . "{$field_list[$formtype][1]}/fields/{$formtype}.inc.php";
			}
			else
			{
				$path = CONTENT_ROOT . "fields/{$formtype}.inc.php";
				if (is_file($path))
				{
					require_once $path;
				}
			}
		}
		
		//格式化输出
		$info = array();
		foreach($this->fields as $field => $v)
		{
			if(!isset($data[$field]))
				{
				continue;
				}
			$func = $formtype = $v['formtype'];
			$value = $data[$field];
			//调用函数式字段类型
			$func_name = "content_field_{$formtype}_output";
			if (function_exists($func_name))
			{
				$result = call_user_func($func_name, $field, $value, $v);
			}
			else
			{
				$result = method_exists($this, $func) ? $this->$func($field, $data[$field]) : $data[$field];
			}
			if($result !== false)
				{
				$info[$field] = $result;
				}
		}
		return $info;
	}
	function areaid($field, $value)
	{
		global $AREA;
		return $AREA[$value]['name'];
	}
	function author($field, $value)
	{
		return $value;
	}
	function box($field, $value)
	{
		$s1 = "\n";
		$s2 = '|';
		$options = explode($s1, $this->fields[$field]['options']);
		foreach($options as $option)
		{
			if(strpos($option, $s2))
			{
				list($name, $id) = explode($s2, trim($option));
			}
			else
			{
				$name = $id = trim($option);
			}
			$os[$id] = $name;
		}
		if(strpos($value, ','))
		{
			$ids = explode(',', $value);
			$value = '';
			foreach($ids as $id)
			{
				$value .= $os[$id].' ';
			}
		}
		else
		{
			$value = $os[$value];
		}
		return $value;
	}
	function catid($field, $value)
	{
		return $value;
	}
	function copyfrom($field, $value)
	{
		if(strpos($value, '|'))
		{
			$copyfrom = explode('|', $value);
			$value = '<a href="'.$copyfrom[1].'" target="_blank" class="copyfrom">'.$copyfrom[0].'</a>';
		}
		return $value;
	}

	function datetime($field, $value)
	{
		return $this->fields[$field]['dateformat'] == 'int' ? date($this->fields[$field]['format'], $value) : (substr($value, 0, 4) =='0000' ? '' : $value);
	}function downfile($field, $value)
	{
		$contentid = $this->contentid;
		$mode = $this->fields[$field]['mode'];
		$result = '';
		if($mode)
		{
			$servers = $this->fields[$field]['servers'];
			$downloadtype = $this->fields[$field]['downloadtype'];
			$servers = explode("\n",$servers);
			foreach($servers AS $k=>$server)
			{
				$server = explode("|",$server);
				$serverurl = $server[1];
				$a_k = urlencode(phpcms_auth("i=$contentid&s=$serverurl&m=1&f=$value&d=$downloadtype", 'ENCODE', AUTH_KEY));
				$result .= "<a href='down.php?a_k=$a_k' target='_blank'>$server[0]</a>";
			}
		}
		else
		{
			$a_k = urlencode(phpcms_auth("i=$contentid&m=0&f=$value", 'ENCODE', AUTH_KEY));
			$result = "<a href='down.php?a_k=$a_k' target='_blank'>点击下载</a>";
		}
		return $result;
	}
	function downfiles($field, $value)
	{
		$contentid = $this->contentid;
		$result = '';
		$downloadtype = $this->fields[$field]['downloadtype'];
		$values = explode("\n",$value);
		foreach($values AS $k=>$v)
		{
			$v = explode("|",$v);
			$name = $v[0];
			$downurl = $v[1];
			$a_k = urlencode(phpcms_auth("i=$contentid&s=$serverurl&m=0&f=$downurl&d=$downloadtype", 'ENCODE', AUTH_KEY));
			$result .= "<a href='down.php?a_k=$a_k' target='_blank'>$name</a>";
		}
		return $result;
	}
	function editor($field, $value)
	{
		$data = $this->fields[$field]['storage'] == 'database' ? $value : content_get($this->contentid, $field);
		if($this->fields[$field]['enablekeylink'])
		{
			$replacenum = $this->fields[$field]['replacenum'];
			//$data = keylinks($data, $replacenum);
		}
		return $data;
	}

	function flashupload($field, $value)
	{
		global $attachment;
		$contentid = $this->contentid;
		$result = '';
		$value = str_replace(array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;'),array('&', '"', "'", '<', '>') ,$value);
		eval("\$value = $value;");
		$values = explode(";",$value['str_video']);
		$t = $p = $value['player'];
		$server = $value['server'];
		foreach($values AS $k=>$v)
		{
			if(!$v) continue;
			$v = explode("|",$v);
			$name = $v[0];
			if(preg_match('/:\/\//i',$v[1]))
			{
				$videourl = trim($v[1]);
			}
			else
			{
				$videourl = $server.trim($v[1]);
			}
			if(!$t)
			{
				$fileext = fileext($videourl);
				if(preg_match("/rm|rmvb|avi|smi/",$fileext)){
					$p = 3;// playerid =3 采用 精美real播放器
				}
				elseif(preg_match("/flv/",$fileext)){
					$p = 4;//  playerid =4 采用 FLV 播放器
				}
				elseif(preg_match("/swf/",$fileext)){
					$p = 5;//  playerid =5 采用 FLASH 播放器
				}
				else{
					$p = 2;//playerid =2 采用window media player
				}
			}
			$number = $k+1;
			$a_k = urlencode(phpcms_auth("i=$contentid&m=0&p=$p&f=$videourl", 'ENCODE', AUTH_KEY));
			$result .= "<a href='play.php?a_k=$a_k' target='_blank'>$name</a>";
		}
		return $result;
	}
	function groupid($field, $value)
	{
		global $priv_group, $GROUP;
		if(!isset($GROUP)) $GROUP = cache_read('member_group.php');
		$value = '';
		$priv = $this->fields[$field]['priv'];
		//$groupids = $priv_group->get_groupid('contentid', $this->contentid, $priv);
		// foreach($groupids as $groupid)
		// {
		// $value .= $GROUP[$groupid].' ';
		// }
		return $value;
	}
	function image($field, $value)
	{
		if($value !='')
		{
			$value = '<img src="'.$value.'" border="0">';
		}
		else
		{
			$value = '<img src="images/nopic.gif" border="0">';
		}
		return $value;
	}
	function images($field, $value)
	{
		global $attachment, $contentid, $show_url_path, $mod, $C, $PHPCMS, $MODULE;
		if(!$this->contentid) return false;
		$r = $this->data;
		$value = '';
		if(is_array($C))
		{
			$modelid = $C['modelid'];
		}
		else
		{
			$modelid = $this->modelid;
		}
		$modelcache = cache_read('model_'.$modelid.'.php');
		if(!is_object($attachment))
		{
			include 'attachment.class.php';
			$attachment = new attachment();
		}
		$images = $attachment->listinfo("contentid=$this->contentid AND field='$field'", 'aid,filepath,description','listorder,aid');
		$images_number = count($images);

		if($modelcache['ishtml'] && $this->fields[$field]['ishtml'] && $show_url_path)
		{
			$ishtml = 1;
			$array_images = $images;
			@extract($this->data);
			$updatetime = date('Y-m-d');
			$userid = $this->userid('userid', $userid);
			$copyfrom = $this->copyfrom('copyfrom',$copyfrom);
			$head['title'] = $title.' - '.$PHPCMS['sitename'];
			$head['keywords'] = $keywords;
			$head['description'] = $this->data['title'];
			ob_start();
			foreach($images AS $page=>$v)
			{
				$filename = PHPCMS_ROOT.$show_url_path.'_'.$page.'.'.$PHPCMS['fileext'];
				include template('phpcms', $GLOBALS['template_show_images']);
				$data = ob_get_contents();
				ob_clean();
				dir_create(dirname($filename));
				file_put_contents($filename, $data);
				@chmod($filename, 0777);
			}
		}
		else
		{
			foreach($images as $a)
			{
				if(strstr($a['filepath'], 'http://')) {
					$thumb = $attachment->get_thumb($a['filepath']);
			} else {
				$thumb = UPLOAD_URL.$attachment->get_thumb(UPLOAD_PATH.$a['filepath']);
			}
			$url = 'images.php?aid='.$a['aid'];
			$value .= "<a href='$url' target='_blank'><img src='$thumb' border='0' alt='$a[description]'></a><br/>$a[description]<br/>";
			}
		}
		$GLOBALS['array_images'] = $images;
		$GLOBALS['images_number'] = $images_number;
		return $images;
	}
	function islink($field, $value)
	{
		return $value;
	}
	function keyword($field, $value)
	{
		if($value == '') return '';
		$v = '';
		if(strpos($value, ',')===false)
		{
			$tags = explode(' ', $value);
		}
		else
		{
			$tags = explode(',', $value);
		}
		foreach($tags as $tag)
		{
			$v .= '<a href="tag.php?tag='.urlencode($tag).'" class="keyword">'.$tag.'</a>';
		}
		return $v;
	}
	function linkage($field, $value)
	{
		return $value;
	}
	function posid($field, $value)
	{
		global $priv_group, $POS;
		if(!isset($POS)) $POS = cache_read('position.php');
		$result = $this->db->select("SELECT `posid` FROM `".DB_PRE."content_position` WHERE `contentid`='$this->contentid'", 'posid');
		$posids = array_keys($result);
		$value = '';
		foreach($posids as $posid)
		{
			$value .= $POS[$posid].' ';
		}
		return $value;
	}
	function textarea($field, $value)
	{
		if($this->fields[$field]['enablekeylink'])
		{
			$replacenum = $this->fields[$field]['replacenum'];
			$data = keylinks($data, $replacenum);
		}
		return format_textarea($value);
	}
	function title($field, $value)
	{
		$value = htmlspecialchars($value);
		return output::style($value, $this->content['style']);
	}
	function typeid($field, $value)
	{
		global $TYPE;
		return $TYPE[$value]['name'];
	}
	function userid($field, $value)
	{
		//return '<a href="'.space_url($value).'" target="_blank" class="username">'.$this->data['username'].'</a>';
	}
}
?>

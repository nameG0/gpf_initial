<?php 
/**
 * 默认支持的HTML DOM
 * 
 * @package default
 * @filesource
 */

/**
 * htmlspecialchars 助手函数
 * @param array|string $data 需处理的内容。
 */
function hd_html($data)
{//{{{
	return is_array($data) ? array_map('hd_html', $data) : htmlspecialchars($data);
}//}}}

/**
 * 根据输入的数组生成表单的属性 html 代码: 属性名 => 属性值
 */
function _hd_make_attr($attr)
{//{{{
	$html = '';
	foreach ($attr as $k => $v)
		{
		$html .= $k .'="' . htmlspecialchars($v) . '" ';
		}
	return $html;
}//}}}

/**
 * 单个文件域
 */
function hd_file($attr = array())
{//{{{
	$attr['type'] = 'file';
	$attr_html = _hd_make_attr($attr);
	return "<input {$attr_html} {$ext['_attr']} />{$ext['_html']}";
}//}}}
/**
 * 文件域组（多个文件域）
 *
 * 	_num	数量
 * 	_is_br	文本域之间是否插入 <br/> 换行
 * 	_is_add_button	是否显示追加文件域按钮，需要 jquery 支持
 * 	_is_del_button	是否显示删除文件域按钮，需要 jquery 支持
 */
function hd_files($attr = array(), $ext = array())
{//{{{
	$file_html = file($attr, $ext);
	$num = max(1, intval($ext['_num']));
	$br_html = $ext['_is_br'] ? '<br />' : '';
	$del_html = $ext['_is_del_button'] ? "<input type=\"button\" value=\"删除\" onclick=\"$(this).parent().remove()\" />" : '';
	$full_file_html = "<span>{$file_html}{$del_html}{$br_html}</span>";
	$html = str_repeat($full_file_html . "\n", $num);

	if ($ext['_is_add_button'])
		{
		$tmp_file_html = addslashes(str_replace('"', "'", $file_html . $del_html . $br_html));
		$html .= "<input type=\"button\" value=\"添加\" onclick=\"$(this).before('{$tmp_file_html}')\" />{$br_html}";
		unset($tmp_file_html);
		}
	return $html;
}//}}}
/**
 * 可浏览图片文件的文件域
 */
function hd_img_file($attr = array())
{//{{{
	$html = '';
	if ($attr['value'])
		{
		$html .= "<img src=\"{$attr['value']}\" width=\"100\" /><br/>\n";
		unset($attr['value']);
		}
	$html .= hd_file($attr);
	return $html;
}//}}}
/**
 * 单选框
 */
function _form_radio($attr = array())
{//{{{
	$attr['type'] = 'radio';
	$attr_html = _hd_make_attr($attr);
	return "<input {$attr_html} />";
}//}}}
/**
 * 单选框组
 *
 * <code>
 * _data:单选框数据
 * "_data" => array('单选框值' => '显示的名称'),
 *
 * _title:各单选项的 title 属性值
 * '_title' => array('单选框值' => 'title 值', ...)
 *
 * 当没有单选框项被选中时默认选中的项
 * _default = 'XX'
 * </code>
 */
function hd_radio($attr = array())
{//{{{
	$data = $attr['_data'];
	$value = $attr['value'];
	$title = $attr['_title'];
	unset($attr['_data'], $attr['_title']);
	if (!is_array($data))
		{
		return '';
		}
	//_default
	if (isset($attr['_default']))
		{
		//循历所有选项值，检查是否需要 _default
		$is_need_default = true;
		foreach ($data as $k => $v)
			{
			if ($k == $value)
				{
				$is_need_default = false;
				break;
				}
			}
		if ($is_need_default)
			{
			$value = $attr['_default'];
			}
		unset($attr['_default']);
		}

	$html = '';
	foreach ($data as $k => $v)
		{
		$attr['value'] = $k;
		$attr['title'] = $title[$k];
		unset($attr['checked']);
		if ($k == $value)
			{
			$attr['checked'] = 'checked';
			}
		$html .= "<label>" . _form_radio($attr) . "{$v}</label>\n";
		}
	return $html;
}//}}}
/**
 * 一个多选框
 * @param mixed $value 多选框的值
 * @param mixed $checked 若与 $value 相等表示多选框被选中。
 */
function hd_checkbox($attr)
{//{{{
	$_ = array('zh', 'checked', 'title', 'br');
	foreach ($_ as $v)
		{
		$$v = $attr[$v];
		unset($attr[$v]);
		}
	$attr['type'] = 'checkbox';
	$attr_html = _hd_make_attr($attr);
	$checked_str = '';
	if ($checked == $attr['value'])
		{
		$checked_str = 'checked="checked" ';
		}
	if ($title)
		{
		$title = "title=\"{$title}\" ";
		}
	$br_str = '';
	if ($br)
		{
		$br_str = "<br />\n";
		}
	return "<label {$title}><input {$attr_html} {$checked_str} />{$zh}</label>{$br_str}";
}//}}}
/**
 * 多选框
 */
function _form_checkbox($attr = array(), $ext = array())
{//{{{
	$attr['type'] = 'checkbox';
	$attr_html = _hd_make_attr($attr);
	return "<input {$attr_html} {$ext['_attr']} />{$ext['_html']}";
}//}}}
/**
 * 多选框组
 *
 * @param array $value 选项。[value] => name.
 * @param array $checked 选中项。 eg. array(1, 2, 3)
 */
function hd_checkbox_group($attr = array())
{//{{{
	$html = '';
	$_ = array('zh', 'checked', 'br', 'value');
	foreach ($_ as $v)
		{
		$$v = $attr[$v];
		unset($attr[$v]);
		}
	if ($zh)
		{
		$html .= "{$zh}:";
		}
	foreach ($value as $k => $v)
		{
		$tmp = $attr;
		$tmp['value'] = $k;
		if (in_array($k, $checked) || (isset($attr['value']) && $k == $attr['value']))
			{
			$tmp['checked'] = 'checked';
			}
		$html .= "<label title=\"{$k}\">" . _form_checkbox($tmp) . "{$v}</label>\n";
		}
	if ($br)
		{
		$html .= "<br />\n";
		}
	return $html;
}//}}}

/**
 * 下接框
 *
 * option(array)	下拉框数据，下拉框值 => 显示的名称
 */
function hd_select($attr)
{//{{{
	//强制类型转换，因为有可能需要生成一个空的下拉框
	$option = (array)$attr['option'];
	$value = $attr['value'];
	unset($attr['option'], $attr['value']);
	$attr_html = _hd_make_attr($attr);
	$html = "<select {$attr_html} >\n";
	foreach ($option as $k => $v)
		{
		$selected = $k == $value['value'] ? 'selected="selected"' : '';
		$html .= "<option value=\"{$k}\" {$selected}>{$v}</option>\n";
		}
	$html .= "</select>";
	return $html;
}//}}}
/**
 * 输入框
 */
function hd_text($attr = array())
{//{{{
	$list = array('label', 'br');
	$help = array();
	foreach ($attr as $k => $v)
		{
		if (in_array($k, $list))
			{
			$help[$k] = $v;
			unset($attr[$k]);
			}
		}
	unset($list);
	$attr['type'] = 'text';
	$attr_html = _hd_make_attr($attr);
	$str = "<input {$attr_html} />";
	if ($help['label'])
		{
		$str = "<label>{$help['label']}{$str}</label>";
		}
	if ($help['br'])
		{
		$str .= "<br />\n";
		}
	return $str;
}//}}}
/**
 * 密码框
 */
function hd_password($attr = array())
{//{{{
	$attr['type'] = 'password';
	$attr_html = _hd_make_attr($attr);
	return "<input {$attr_html} />";
}//}}}
/**
 * 文本框
 */
function hd_textarea($attr)
{//{{{
	$list = array('value', 'label', 'br');
	$help = array();
	foreach ($attr as $k => $v)
		{
		if (in_array($k, $list))
			{
			$help[$k] = $v;
			unset($attr[$k]);
			}
		}
	unset($list);
	$map = array("h" => 'rows', "w" => 'cols',);
	foreach ($map as $k => $v)
		{
		if (isset($attr[$k]))
			{
			$attr[$v] = $attr[$k];
			unset($attr[$k]);
			}
		}

	$attr_html = _hd_make_attr($attr);
	$str = "<textarea {$attr_html} >{$help['value']}</textarea>";
	if ($help['label'])
		{
		$str = "<label>{$help['label']}{$str}</label>";
		}
	if ($help['br'])
		{
		$str .= "<br />\n";
		}
	return $str;
}//}}}
/**
 * 隐藏域
 */
function hd_hide($attr = array())
{//{{{
	$attr['type'] = 'hidden';
	$attr_html = _hd_make_attr($attr);
	return "<input {$attr_html} />";
}//}}}
/**
 * 隐藏域组
 *
 * _data(array)	隐藏域组数据,隐藏域名 => 隐藏域值
 * _from_get(string)	从get中取的键名,多个值以","(逗号)分隔，如 mod,action,file
 */
function hd_hides($attr = array(), $ext = array())
{//{{{
	$data = array();
	if (is_array($ext['_data']))
		{
		$data = $ext['_data'];
		}
	if ($ext['_from_get'])
		{
		$from_get = explode(",", $ext['_from_get']);
		foreach ($from_get as $k)
			{
			$data[$k] = $_GET[$k];
			}
		}
	$html = '';
	foreach ($data as $k => $v)
		{
		$html .= "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" />\n";
		}
	return $html;
}//}}}
/**
 * 显示 phpcms 原来的日期选择器
 */
function hd_date_phpcms($attr)
{//{{{
	require_once PHPCMS_ROOT . "include/form.class.php";
	return hform::date($attr['name'], $attr['value']);
}//}}}
function hd_button($attr)
{//{{{
	$attr['type'] = 'button';
	$attr_html = _hd_make_attr($attr);
	$str = "<input {$attr_html} />";
	return $str;
}//}}}


/*
2011-09-09 GGzhu 已移动到 /siud/include/ 下
%%
2011-05-25
表单项输出类

所有表单项输出方法都按以下规则编写：
	方法名：跟 _ui 属性一致
	参数：
		$attr	表单项直接输出的属性及值
		$ext	各方法接收的特殊属性值（以下划线“_”开头为特殊属性）

通用特殊属性：
	_ui	表单项方法名
	_attr	在表单项标签内输出的 html 代码，如 <input 在这个位置输出>
	_html	在表单项标签外输出的 html 代码，如 <input />在这个位置输出
*/
class formi
{
	//根据输入的数组生成表单的属性 html 代码: 属性名 => 属性值
	function _make_attr($attr)
	{
		$html = '';
		foreach ($attr as $k => $v)
			{
			$html .= $k .'="' . htmlspecialchars($v) . '" ';
			}
		return $html;
	}

	//单个文件域
	function file($attr = array(), $ext = array())
	{
		$attr['type'] = 'file';
		$attr_html = self::_make_attr($attr);
		return "<input {$attr_html} {$ext['_attr']} />{$ext['_html']}";
	}
	//文件域组（多个文件域）
		//_num	数量
		//_is_br	文本域之间是否插入 <br/> 换行
		//_is_add_button	是否显示追加文件域按钮，需要 jquery 支持
		//_is_del_button	是否显示删除文件域按钮，需要 jquery 支持
	function files($attr = array(), $ext = array())
	{
		$file_html = self::file($attr, $ext);
		$num = max(1, intval($ext['_num']));
		$br_html = $ext['_is_br'] ? '<br />' : '';
		$del_html = $ext['_is_del_button'] ? "<input type=\"button\" value=\"删除\" onclick=\"$(this).parent().remove()\" />" : '';
		$full_file_html = "<span>{$file_html}{$del_html}{$br_html}</span>";
		$html = str_repeat($full_file_html . "\n", $num);

		if ($ext['_is_add_button'])
			{
			$tmp_file_html = addslashes(str_replace('"', "'", $file_html . $del_html . $br_html));
			$html .= "<input type=\"button\" value=\"添加\" onclick=\"$(this).before('{$tmp_file_html}')\" />{$br_html}";
			unset($tmp_file_html);
			}
		return $html;
	}
	//可浏览图片文件的文件域
		//_img	图片
	function img_file($attr = array(), $ext = array())
	{
		$html = '';
		$html .= $ext['_img'] ? "<img src=\"{$ext['_img']}\" width=\"100\" /><br/>\n" : '';
		$html .= self::file($attr);
		return $html;
	}

	//单选框
	function radio($attr = array(), $ext = array())
	{
		$attr['type'] = 'radio';
		$attr_html = self::_make_attr($attr);
		return "<input {$attr_html} {$ext['_attr']} />{$ext['_html']}";
	}
	//单选框组
		//_data	单选框数据，单选框值 => 显示的名称
	function radios($attr = array(), $ext = array())
	{
		$html = '';
		if (!is_array($ext['_data']))
			{
			return '';
			}
		foreach ($ext['_data'] as $k => $v)
			{
			$tmp = $attr;
			$tmp['value'] = $k;
			if ($k == $attr['value'])
				{
				$tmp['checked'] = 'checked';
				}
			$html .= "<label>" . self::radio($tmp, $ext) . "{$v}</label>\n";
			}
		return $html;
	}

	//多选框
	function checkbox($attr = array(), $ext = array())
	{
		$attr['type'] = 'checkbox';
		$attr_html = self::_make_attr($attr);
		return "<input {$attr_html} {$ext['_attr']} />{$ext['_html']}";
	}
	//多选框组
		//_data(array)	多选框数据，多选框值 => 显示的名称
		//_checked(array)	已选中项
	function checkboxs($attr = array(), $ext = array())
	{
		$html = '';
		if (!is_array($ext['_data']))
			{
			return '';
			}
		$ext['_checked'] = (array)$ext['_checked'];
		foreach ($ext['_data'] as $k => $v)
			{
			$tmp = $attr;
			$tmp['value'] = $k;
			if (in_array($k, $ext['_checked']) || (isset($attr['value']) && $k == $attr['value']))
				{
				$tmp['checked'] = 'checked';
				}
			$html .= "<label>" . self::checkbox($tmp, $ext) . "{$v}</label>\n";
			}
		return $html;
	}

	//下接框
		//_data(array)	下拉框数据，下拉框值 => 显示的名称
	function select($attr = array(), $ext = array())
	{
		$attr_html = self::_make_attr($attr);
		$html = "<select {$attr_html} {$ext['_attr']} >\n";
		//强制类型转换，因为有可能需要生成一个空的下拉框
		$ext['_data'] = (array)$ext['_data'];
		foreach ($ext['_data'] as $k => $v)
			{
			$selected = $k == $attr['value'] ? 'selected="selected"' : '';
			$html .= "<option value=\"{$k}\" {$selected}>{$v}</option>\n";
			}
		$html .= "</select>{$ext['_html']}";
		return $html;
	}

	//输入框
	function text($attr = array(), $ext = array())
	{
		$attr['type'] = 'text';
		$attr_html = self::_make_attr($attr);
		return "<input {$attr_html} {$ext['_attr']} />{$ext['_html']}";
	}
	//密码框
	function password($attr = array(), $ext = array())
	{
		$attr['type'] = 'password';
		$attr_html = self::_make_attr($attr);
		return "<input {$attr_html} {$ext['_attr']} />{$ext['_html']}";
	}
	//文本框
	function textarea($attr = array(), $ext = array())
	{
		$value = $attr['value'];
		unset($attr['value']);

		$attr_html = self::_make_attr($attr);
		return "<textarea {$attr_html} {$ext['_attr']} >{$value}</textarea>{$ext['_html']}";
	}

	//隐藏域
	function hide($attr = array(), $ext = array())
	{
		$attr['type'] = 'hidden';
		$attr_html = self::_make_attr($attr);
		return "<input {$attr_html} {$ext['_attr']} />{$ext['_html']}";
	}
	//隐藏域组
		//_data(array)	隐藏域组数据,隐藏域名 => 隐藏域值
		//_from_get(string)	从get中取的键名,多个值以","(逗号)分隔，如 mod,action,file
	function hides($attr = array(), $ext = array())
	{
		$data = array();
		if (is_array($ext['_data']))
			{
			$data = $ext['_data'];
			}
		if ($ext['_from_get'])
			{
			$from_get = explode(",", $ext['_from_get']);
			foreach ($from_get as $k)
				{
				$data[$k] = $_GET[$k];
				}
			}
		$html = '';
		foreach ($data as $k => $v)
			{
			$html .= "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" />\n";
			}
		return $html;
	}
}


class form
{
	//处理 name[.id] 格式的 $name 参数
	function _name($name)
	{
		$ret = explode(".", $name);
		$ret[1] = $ret[1] ? $ret[1] : $ret[0];
		return $ret;
	}
	//处理 $str 参数
	function _str($str)
	{
		$ret = array();
		$str = explode(",", $str);
		foreach ($str as $k => $v)
			{
			list($a, $b) = explode("=", $v);
			$b = is_null($b) ? true : $b;
			$ret[$a] = $b;
			}
		return $ret;
	}

	//random 的缩写，用于产生随机字符串,可以用于指定表单元素的id
	function r($len = 10, $chars = '0123456789')
	{
		return random($len, $chars);
	}

	//隐藏域
	//调用方式:
		//$name(array)	键值对应的数据数组
		//$name(string), $value(string)	一般形式，名称与值相对应
		//$name(string), $value(array), $str(string)"get"	$value 是键值对应的待输出数据,$name 中为从 GET 或 POST 中保持原值输出的键名,用","号分隔, $str 表示从 GET 还是 POST 中取,可以用 all,get 表示优先从 GET 中取,没有再到 POST 中查找, all 等于 all.post
		//$name(string), 同 $name(string), $str(string)"all"
	function hide($name, $value = '', $str = '')
	{
		$str = !$value && !$str && !is_array($name) ? 'all' : $str;
		$str = $str ? self::_str($str) : '';
		$hide = is_array($name) ? $name : (is_array($value) ? $value : array());
		if ($str)
			{
			$keys = explode(",", $name);
			foreach ($keys as $k => $v)
				{
				$from = $str['get'] ? $_GET : $_POST;
				$from = !isset($from[$v]) && $str['all'] ? ($str['get'] ? $_POST : $_GET) : $from;
				if (isset($from[$v]))
					{
					$hide[$v] = $from[$v];
					}
				}
			}
		else if (!is_array($value))
			{
			$hide[$name] = $value;
			}
		$data = '';
		foreach ($hide as $k => $v)
			{
			$data .= "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" />\n";
			}
		return $data;
	}
	
	function editor($textareaid = 'content', $toolbar = 'standard', $width = '100%', $height = 400, $isshowext = 1)
	{
		//ggzhu 2010-10-20 添加 contentid 变量
		global $PHPCMS, $mod, $file, $catid, $_userid, $contentid;
		$contentid = intval($contentid);
		$str = "<script type=\"text/javascript\" src=\"fckeditor/fckeditor.js\"></script>\n<script language=\"JavaScript\" type=\"text/JavaScript\">var SiteUrl = \"".SITE_URL."\"; var Module = \"".$mod."\"; var Contentid = \"{$contentid}\"; var sBasePath = \"".SITE_URL."\" + 'fckeditor/'; var oFCKeditor = new FCKeditor( '".$textareaid."' ) ; oFCKeditor.BasePath = sBasePath ; oFCKeditor.Height = '".$height."'; oFCKeditor.Width	= '".$width."' ; oFCKeditor.ToolbarSet	= '".$toolbar."' ;oFCKeditor.ReplaceTextarea();";
		if($_userid && $isshowext)
		{
			$str .= "editor_data_id += '".$textareaid."|';if(typeof(MM_time)=='undefined'){MM_time = setInterval(update_editor_data,".($PHPCMS['editor_interval_data']*1000).");}";
		}
		$str .= "</script>";
		if($isshowext)
		{
			$str .= "<div style='width:$width;text-align:left'>";
			if($_userid)
			{
				$str .= "<span style='float:right;height:22px'>";
				if(defined('IN_ADMIN') && $mod == 'phpcms' && $file == 'content')
				{
					$str .= "<span style='padding:1px;margin-right:10px;background-color: #fefe;border:#006699 solid 1px;'><a href='javascript:insert_page(\"$textareaid\")' title='在光标处插入分页标记'>分页</a></span>";
					$str .= "<span style='padding:1px;margin-right:10px;background-color: #fefe;border:#006699 solid 1px;'><a href='javascript:insert_page_title(\"$textareaid\")' title='在光标处插入带子标题的分页标记'>子标题</a></span>";
				}
				$str .= "<span style='padding:1px;margin-right:10px;background-color: #fefe;border:#006699 solid 1px;'><div id='page_title_div' style='background-color: #fff;border:#006699 solid 1px;position:absolute;z-index:10;padding:1px;display:none;right:80px;'>
				<table cellpadding='0' cellspacing='1' border='0'><tr><td >请输入子标题名称：<span id='msg_page_title_value'></span></td><td><span style='cursor:pointer;float:right;' onclick='javascript:$(\"#page_title_div\").hide()'>×</span></td>
				<tr><td colspan='2'><input name='page_title_value' id='page_title_value' value='' size='40'>&nbsp;<input type='button' value=' 确定 ' onclick=insert_page_title(\"$textareaid\",1)></td></tr>
				</table></span></div>";

				$str .= "<span style='padding:1px;margin-right:10px;background-color: #fefe;border:#006699 solid 1px;'><div id='".$textareaid."_div' style='background-color: #fff;border:#006699 solid 1px;position:absolute;z-index:10;padding:5px;display:none;right:60px;'>
						<table cellpadding='0' cellspacing='1' border='0'><tr><td>		
						<div>";
				for($i=1; $i<=$PHPCMS['editor_max_data_hour']; $i++)
				{
					$bold = $i==1 ? "font-weight: bold;" : '';
					$str .= "<a href='javascript:get_editor_data_list(\"".$textareaid."\",$i)' class='hour' style='border:#cccccc solid 1px;margin:2px;padding-left:4px;padding-right:4px;$bold' title='$i 小时'>$i</a>";	
				}
				$str .= "</div></td><td><span style='cursor:pointer;' onclick='javascript:$(\"#".$textareaid."_div\").hide()'>×</span></td></tr></table><ul id='".$textareaid."_lists' style='height:200px;width:140px;overflow:auto;'></ul></div><a href='javascript:get_editor_data_list(\"".$textareaid."\",1)' title='点击恢复数据'>恢复数据</a></span></span>";
			}
			$str .= "<img src=\"".SITE_URL."images/editor_add.jpg\" title='增加编辑器高度' tag='1' fck=\"".$textareaid."\"/>&nbsp;  <img src=\"".SITE_URL."images/editor_diff.jpg\" title='减少编辑器高度' tag='0' fck=\"".$textareaid."\"/></div>";
		}
		$str .= "<div id=\"MM_file_list_".$textareaid."\" style=\"text-align:left\"></div><div id='FilePreview' style='Z-INDEX: 1000; LEFT: 0px; WIDTH: 10px; POSITION: absolute; TOP: 0px; HEIGHT: 10px; display: none;'></div><div id='".$textareaid."_save'></div>";
		return $str;
	}
	//2010-9-21 gl:加多了一个参数$i，用于循环时间数组时用的ID
	function date($name, $value = '', $isdatetime = 0, $readonly=1 , $ext = '', $i=NULL)
	{
		if($value == '0000-00-00 00:00:00') $value = '';
		if(is_null($i))
			{
			$id = preg_match("/\[(.*)\]/", $name, $m) ? $m[1] : $name;
			}
		else
			{
			$id = $i;
			}
		if($isdatetime)
		{
			$size = 21;
			$format = '%Y-%m-%d %H:%M:%S';
			$showsTime = 'true';
		}
		else
		{
			$size = 10;
			$format = '%Y-%m-%d';
			$showsTime = 'false';
		}
		if($readonly) $readonly = "readonly";
		else $readonly = "";
		$str = '';
		if(!defined('CALENDAR_INIT'))
		{
			define('CALENDAR_INIT', 1);
			$str .= '<link rel="stylesheet" type="text/css" href="images/js/calendar/calendar-blue.css"/>
			        <script type="text/javascript" src="images/js/calendar/calendar.js"></script>';
		}
		$str .= '<input type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" size="'.$size.'" '.$readonly.' '.$ext.' >&nbsp;';
		$str .= '<script language="javascript" type="text/javascript">
					date = new Date();document.getElementById ("'.$id.'").value="'.$value.'";
					Calendar.setup({
						inputField     :    "'.$id.'",
						ifFormat       :    "'.$format.'",
						showsTime      :    '.$showsTime.',
						timeFormat     :    "24"
					});
				 </script>';
		return $str;
	}

	function checkcode($name = 'checkcode', $size = 4, $extra = '',$id='checkcode')
	{
		return '<input name="'.$name.'" id="'.$id.'" type="text" size="'.$size.'" '.$extra.'> <img src="checkcode.php?'.rand(1000,10000).'" id="'.$id.'img" onclick="this.src=\'checkcode.php?id=\'+Math.random()*5;" style="cursor:pointer;" alt="验证码,看不清楚?请点击刷新验证码" align="absmiddle"/>';
	}

	function style($name = 'style', $style = '')
	{
		global $styleid, $LANG;
		if(!$styleid) $styleid = 1; else $styleid++;
		$color = $strong = '';
		if($style)
		{
			list($color, $b) = explode(' ', $style);
		}
		$styleform = "<option value=\"\">".$LANG['color']."</option>\n";
		for($i=1; $i<=15; $i++)
		{
			$styleform .= "<option value=\"c".$i."\" ".($color == 'c'.$i ? "selected=\"selected\"" : "")." class=\"bg".$i."\"></option>\n";
		}
		$styleform = "<select name=\"style_color$styleid\" id=\"style_color$styleid\" onchange=\"document.all.style_id$styleid.value=document.all.style_color$styleid.value;if(document.all.style_strong$styleid.checked)document.all.style_id$styleid.value += ' '+document.all.style_strong$styleid.value;\">\n".$styleform."</select>\n";
		$styleform .= " <input type=\"checkbox\" name=\"style_strong$styleid\" id=\"style_strong$styleid\" value=\"b\" ".($b == 'b' ? "checked=\"checked\"" : "")." onclick=\"document.all.style_id$styleid.value=document.all.style_color$styleid.value;if(document.all.style_strong$styleid.checked)document.all.style_id$styleid.value += ' '+document.all.style_strong$styleid.value;\"> ".$LANG['bold'];
		$styleform .= "<input type=\"hidden\" name=\"".$name."\" id=\"style_id$styleid\" value=\"".$style."\">";
		return $styleform;
	}

	function text($name, $id = '', $value = '', $type = 'text', $size = 50, $class = '', $ext = '', $minlength = '', $maxlength = '', $pattern = '', $errortips = '')
	{
		if(!$id) $id = $name;
		$checkthis = '';
		$showerrortips = "字符长度必须为".$minlength."到".$maxlength."位";
		if($pattern)
		{
			$pattern = 'regexp="'.substr($pattern,1,-1).'"';
		}
		$require = $minlength ? 'true' : 'false';
		if($pattern && ($minlength || $maxlength))
		{
			$string_datatype = substr($string_datatype, 1);
			$checkthis = "require=\"$require\" $pattern datatype=\"limit|custom\" min=\"$minlength\" max=\"$maxlength\" msg='$showerrortips|$errortips'";
		}
		elseif($pattern)
		{
			$checkthis = "require=\"$require\" $pattern datatype=\"custom\" msg='$errortips'";
		}
		elseif($minlength || $maxlength)
		{
			$checkthis = "require=\"$require\" datatype=\"limit\" min=\"$minlength\" max=\"$maxlength\" msg='$showerrortips'";
		}
		return "<input type=\"$type\" name=\"$name\" id=\"$id\" value=\"$value\" size=\"$size\" class=\"$class\" $checkthis $ext/> ";
	}

	function textarea($name, $id = '', $value = '', $rows = 10, $cols = 50, $class = '', $ext = '')
	{
		if(!$id) $id = $name;
		return "<textarea name=\"$name\" id=\"$id\" rows=\"$rows\" cols=\"$cols\" class=\"$class\" $ext>$value</textarea>";
	}

	function select($options, $name, $id = '', $value = '', $size = 1, $class = '', $ext = '')
	{
		if(!$id) $id = $name;
		if(!is_array($options)) $options = form::_option($options);			
		if($size >= 1) $size = " size=\"$size\"";
		if($class) $class = " class=\"$class\"";
		$data = "<select name=\"$name\" id=\"$id\" $size $class $ext>";
		foreach($options as $k=>$v)
		{
			$selected = $k == $value ? 'selected' : '';
			$data .= "<option value=\"$k\" $selected>$v</option>\n";
		}
		$data .= '</select>';
		return $data;
	}

	function multiple($options, $name, $id = '', $value = '', $size = 3, $class = '', $ext = '')
	{
		if(!$id) $id = $name;
		if(!is_array($options)) $options = form::_option($options);
		$size = max(intval($size), 3);
		if($class) $class = " class=\"$class\"";
		$value = strpos($value, ',') ? explode(',', $value) : array($value);
		$data .= "<select name=\"$name\" id=\"$id\" multiple=\"multiple\" size=\"$size\" $class $ext>";
		foreach($options as $k=>$v)
		{
			$selected = in_array($k, $value) ? 'selected' : '';
			$data .= "<option value=\"$k\" $selected>$v</option>\n";
		}
		$data .= '</select>';
		return $data;
	}

	function checkbox($options, $name, $id = '', $value = '', $cols = 5, $class = '', $ext = '', $width = 100)
	{
		if(!$options) return '';
		if(!$id) $id = $name;
		if(!is_array($options)) $options = form::_option($options);
		$i = 1;
		$data = '<input type="hidden" name="'.$name.'" value="-99">';
		if($class) $class = " class=\"$class\"";
		if($value != '' && !is_array($value)) $value = strpos($value, ',') ? explode(',', $value) : array($value);
		foreach($options as $k=>$v)
		{
			$checked = ($value && in_array($k, $value)) ? 'checked' : '';
			$data .= "<span style=\"width:{$width}px\"><label><input type=\"checkbox\" boxid=\"{$id}\" name=\"{$name}[]\" id=\"{$id}\" value=\"{$k}\" style=\"border:0px\" $class {$ext} {$checked}/> {$v}</label></span>\n ";
			if($i%$cols == 0) $data .= "<br />\n";
			$i++;
		}
		return $data;
	}

	function radio($options, $name, $id = '', $value = '', $cols = 5, $class = '', $ext = '', $width = 100)
	{
		if(!$id) $id = $name;
		if(!is_array($options)) $options = form::_option($options);
		$i = 1;
		$data = '';
		if($class) $class = " class=\"$class\"";
		foreach($options as $k=>$v)
		{
			$checked = $k == $value ? 'checked' : '';
			$data .= "<span style=\"width:{$width}px\"><label><input type=\"radio\" name=\"{$name}\" id=\"{$id}\" value=\"{$k}\" style=\"border:0px\" $class {$ext} {$checked}/> {$v}</label></span> ";
			if($i%$cols == 0) $data .= "<br />\n";
			$i++;
		}
		return $data;
	}

	function _option($options, $s1 = "\n", $s2 = '|')
	{
		$options = explode($s1, $options);
		foreach($options as $option)
		{
			if(strpos($option, $s2))
			{
				list($name, $value) = explode($s2, trim($option));
			}
			else
			{
				$name = $value = trim($option);
			}
			$os[$value] = $name;
		}
		return $os;
	}

	function image($name, $id = '', $value = '', $size = 50, $class = '', $ext = '', $modelid = 0, $fieldid = 0)
	{
		if(!$id) $id = $name;
		//return "<input type=\"text\" name=\"$name\" id=\"$id\" value=\"$value\" size=\"$size\" class=\"$class\" $ext/> <input type=\"hidden\" name=\"{$id}_aid\" value=\"0\"> <input type=\"button\" name=\"{$name}_upimage\" id=\"{$id}_upimage\" value=\"上传图片\" style=\"width:60px\" onclick=\"javascript:openwinx('?mod=phpcms&file=upload_field&uploadtext={$id}&modelid={$modelid}&fieldid={$fieldid}','upload','350','350')\"/>";
		return "<input type=\"text\" name=\"$name\" id=\"$id\" value=\"$value\" size=\"$size\" class=\"$class\" $ext/> <input type=\"hidden\" name=\"{$id}_aid\" value=\"0\"> <input type=\"button\" name=\"{$name}_upimage\" id=\"{$id}_upimage\" value=\"上传图片\" style=\"width:60px\" onclick=\"javascript:openwinx('upload_field.php?uploadtext={$id}&modelid={$modelid}&fieldid={$fieldid}','upload','350','350')\"/>";
	}

	function file($name, $id = '', $size = 50, $class = '', $ext = '')
	{
		if(!$id) $id = $name;
		return "<input type=\"file\" name=\"$name\" id=\"$id\" size=\"$size\" class=\"$class\" $ext/> ";
	}

	function downfile($name, $id = '', $value = '', $size = 50, $mode, $class = '', $ext = '')
	{
		if(!$id) $id = $name;
		if($mode) $mode = "&mode=1";
		if(defined('IN_ADMIN'))
		{
			return "<input type=\"text\" name=\"$name\" id=\"$id\" value=\"$value\" size=\"$size\" class=\"$class\" $ext/> <input type=\"hidden\" name=\"{$id}_aid\" value=\"0\"> <input type=\"button\" name=\"{$name}_upfile\" id=\"{$id}_upfile\" value=\"上传文件\" style=\"width:60px\" onclick=\"javascript:openwinx('?mod=phpcms&file=upload&uploadtext={$id}{$mode}','upload','390','180')\"/>";
		}
		else
		{
			return true;
		}
	}

	function upload_image($name, $id = '', $value = '', $size = 50, $class = '', $property = '')
	{
		if(!$id) $id = $name;
		return "<input type=\"text\" name=\"$name\" id=\"$id\" value=\"$value\" size=\"$size\" class=\"$class\" $property/> <input type=\"button\" name=\"{$name}_upimage\" id=\"{$id}_upimage\" value=\"上传图片\" style=\"width:60px\" onclick=\"javascript:openwinx('?mod=phpcms&file=upload&uploadtext={$id}','upload','350','350')\"/>";
	}

	function select_template($module, $name, $id = '', $value = '', $property = '', $pre = '')
	{
		if(!$id) $id = $name;
		$templatedir = TPL_ROOT.TPL_NAME.'/'.$module.'/';
		$files = array_map('basename', glob($templatedir.$pre.'*.html'));
		$names = cache_read('name.inc.php', $templatedir);
		$templates = array(''=>'请选择');
		foreach($files as $file)
		{
			$key = substr($file, 0, -5);
			$templates[$key] = isset($names[$file]) ? $names[$file].'('.$file.')' : $file;
		}
		ksort($templates);
		return form::select($templates, $name, $id, $value, $property);
	}

	function select_file($name, $id = '', $value = '', $size = 30, $catid = 0, $isimage = 0)
	{
		if(!$id) $id = $name;
		return "<input type='text' name='$name' id='$id' value='$value' size='$size' /> <input type='button' value='浏览...' style='cursor:pointer;' onclick=\"file_select('$id', $catid, $isimage)\">";
	}

	function select_module($name = 'module', $id ='', $alt = '', $value = '', $property = '')
	{
		global $MODULE;
		if($alt) $arrmodule = array('0'=>$alt);
		foreach($MODULE as $k=>$v)
		{
			$arrmodule[$k] = $v['name'];
		}
		if(!$id) $id = $name;
		return form::select($arrmodule, $name, $id, $value, 1, '', $property);
	}

	function select_model($name = 'modelid', $id ='', $alt = '', $modelid = '', $property = '')
	{
		global $MODEL;
		if($alt) $arrmodel = array('0'=>$alt);
		foreach($MODEL as $k=>$v)
		{
			if($v['modeltype'] > 0) continue;
			$arrmodel[$k] = $v['name'];
		}
		if(!$id) $id = $name;
		return form::select($arrmodel, $name, $id, $modelid, 1, '', $property);
	}

	function select_member_model($name = 'modelid', $id = '', $alt = '', $modelid = '', $property = '')
	{
		global $MODEL;
		if($alt) $arrmodel = array('0'=>$alt);
		foreach($MODEL as $k=>$v)
		{
			if($v['modeltype'] == '2')
			{
				$arrmodel[$k] = $v['name'];
			}
		}
		if(!$id) $id = $name;
		return form::select($arrmodel, $name, $id, $modelid, 1, '', $property);
	}
	
	//$name	表单项 name
	//$id	表单项id
	function select_category($module = 'phpcms', $parentid = 0, $name = 'catid', $id ='', $alt = '', $catid = 0, $property = '', $type = 0, $optgroup = 0)
	{
		global $tree, $CATEGORY;
		if(!is_object($tree))
		{
			require_once 'tree.class.php';
			$tree = new tree;
		}
		if(!$id) $id = $name;
		if($optgroup) $optgroup_str = "<optgroup label='\$name'></optgroup>";
		$data = "<select name='$name' id='$id' $property>\n<option value='0'>$alt</option>\n";
		if(is_array($CATEGORY))
		{
			$categorys = array();
			foreach($CATEGORY as $id=>$cat)
			{
				if(($type == 2 && $cat['type'] ==2) || ($type == 1 && $cat['type'])) continue;
				if($cat['module'] == $module) $categorys[$id] = array('id'=>$id, 'parentid'=>$cat['parentid'], 'name'=>$cat['catname']);
			}
			$tree->tree($categorys);
			$data .= $tree->get_tree($parentid, "<option value='\$id' \$selected>\$spacer\$name</option>\n", $catid, '' , $optgroup_str);
		}
		$data .= '</select>';
		return $data;
	}

	//级联式栏目下拉框
	//$name	$id 表单项属性
	//$catid	默认选择的栏目
	function select_categoryi($catid = 0, $name = 'catid', $id = '')
	{
		$id = $id ? $id : str_replace(array('[', ']'), array('', ''), $name);
		return "<input type=\"hidden\" name=\"{$name}\" id=\"{$id}\" value=\"{$catid}\"><span id=\"load_{$id}\"></span><script type=\"text/javascript\">category_load_simple('{$catid}','{$id}');</script>";
	}

/*
	function select_special_category($name = 'scatid', $id ='', $alt = '', $scatid = 0, $property = '')
	{
		global $SPECIAL_CATEGORY;
		if(!isset($SPECIAL_CATEGORY)) $SPECIAL_CATEGORY = cache_read('special_category.php');
		if(!$id) $id = $name;
		$data = "<select name='$name' id='$id' $property>\n<option value='0'>$alt</option>\n";
		foreach($SPECIAL_CATEGORY as $id=>$scatname)
		{
			$selected = $id == $scatid ? 'selected' : '';
			$data .= "<option value='$id' $selected>$scatname</option>\n";
		}
		$data .= '</select>';
		return $data;
	}

	function select_special($name = 'specialid', $id ='', $alt = '', $specialids = '', $property = '')
	{
		global $db;
		if(!$id) $id = $name;
		$db->query("SELECT  FROM WHERE ");
		$data = "<select name='$name' id='$id' $property>\n<option value='0'>$alt</option>\n";
		foreach($TYPE as $id=>$t)
		{
			$selected = $id == $typeid ? 'selected' : '';
			$data .= "<option value='$id' $selected>$t[name]</option>\n";
		}
		$data .= '</select>';
		return $data;
	}
*/
	function select_pos($name = 'posid', $id ='', $posids = '', $cols = 1, $width = 100)
	{
		global $db,$priv_role, $POS;
		if(!$id) $id = $name;
		$pos = array();
		foreach($POS as $posid=>$posname)
		{
			if($priv_role->check('posid', $posid)) $pos[$posid] = str_cut($posname, 16, '');
		}
		return form::checkbox($pos, $name, $id, $posids, $cols, '', '', $width);
	}

	function select_group($name = 'groupid', $id ='', $groupids = '', $cols = 1, $width = 100)
	{
		global $db, $GROUP;
		if(!$id) $id = $name;
		return form::checkbox($GROUP, $name, $id, $groupids, $cols, '', '', $width);
	}

	function select_type($module = 'phpcms', $name = 'typeid', $id ='', $alt = '', $typeid = 0, $property = '')
	{
		$types = subtype($module);
		if(!$id) $id = $name;
		$data = "<select name='$name' id='$id' $property>\n<option value='0'>$alt</option>\n";
		foreach($types as $id=>$t)
		{
			$selected = $id == $typeid ? 'selected' : '';
			$data .= "<option value='$id' $selected>$t[name]</option>\n";
		}
		$data .= '</select>';
		return $data;
	}

	function select_area($name = 'areaid', $id ='', $alt = '', $parentid = 0, $areaid = 0, $property = '')
	{
		global $tree, $AREA;
		if(!is_object($tree))
		{
			require_once 'tree.class.php';
			$tree = new tree;
		}
		if(!$id) $id = $name;
		$data = "<select name='$name' id='$id' $property>\n<option value='0'>$alt</option>\n";
		if(is_array($AREA))
		{
			$areas = array();
			foreach($AREA as $id=>$a)
			{
				$areas[$id] = array('id'=>$id, 'parentid'=>$a['parentid'], 'name'=>$a['name']);
			}
			$tree->tree($areas);
			$data .= $tree->get_tree($parentid, "<option value='\$id' \$selected>\$spacer\$name</option>\n", $areaid);
		}
		$data .= '</select>';
		return $data;
	}

	function select_urlrule($module = 'phpcms', $file = 'category', $ishtml = 1, $name = 'urlruleid', $id ='', $urlruleid = 0, $property = '')
	{
		global $db;
		$urlrules = array();
		$result = $db->select("SELECT `urlruleid`,`example` FROM `".DB_PRE."urlrule` WHERE `module`='$module' AND `file`='$file' AND `ishtml`='$ishtml' ORDER BY `urlruleid`");
		foreach ($result as $k => $r)
			{
			$urlrules[$r['urlruleid']] = $r["urlruleid"] . '|' . $r['example'];
			}
		if(!$id) $id = $name;
		return form::select($urlrules, $name, $id, $urlruleid, 1, '', $property);
	}
}

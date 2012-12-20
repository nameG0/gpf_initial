<?php 
/**
 * 表单项输出类
 *
 * 2011-05-25
 * 
 * 通用特殊属性：
 * 	_attr	在表单项标签内输出的 html 代码，如 <input 在这个位置输出>
 * 	_html	在表单项标签外输出的 html 代码，如 <input />在这个位置输出
 *
 * @package form
 * @filesource
 */

/**
 * 根据输入的数组生成表单的属性 html 代码: 属性名 => 属性值
 */
function _form_make_attr($attr)
{
	$html = '';
	foreach ($attr as $k => $v)
		{
		$html .= $k .'="' . htmlspecialchars($v) . '" ';
		}
	return $html;
}

/**
 * 单个文件域
 */
function form_file($attr = array())
{
	$attr['type'] = 'file';
	$attr_html = _form_make_attr($attr);
	return "<input {$attr_html} {$ext['_attr']} />{$ext['_html']}";
}
/**
 * 文件域组（多个文件域）
 *
 * 	_num	数量
 * 	_is_br	文本域之间是否插入 <br/> 换行
 * 	_is_add_button	是否显示追加文件域按钮，需要 jquery 支持
 * 	_is_del_button	是否显示删除文件域按钮，需要 jquery 支持
 */
function form_files($attr = array(), $ext = array())
{
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
}
/**
 * 可浏览图片文件的文件域
 */
function form_img_file($attr = array())
{
	$html = '';
	if ($attr['value'])
		{
		$html .= "<img src=\"{$attr['value']}\" width=\"100\" /><br/>\n";
		unset($attr['value']);
		}
	$html .= form_file($attr);
	return $html;
}
/**
 * 单选框
 */
function _form_radio($attr = array())
{
	$attr['type'] = 'radio';
	$attr_html = _form_make_attr($attr);
	return "<input {$attr_html} />";
}
/**
 * 单选框组
 *
 * _data	单选框数据，单选框值 => 显示的名称
 */
function form_radio($attr = array())
{
	$data = $attr['_data'];
	$value = $attr['value'];
	unset($attr['_data']);
	if (!is_array($data))
		{
		return '';
		}
	$html = '';
	foreach ($data as $k => $v)
		{
		$attr['value'] = $k;
		unset($attr['checked']);
		if ($k == $value)
			{
			$attr['checked'] = 'checked';
			}
		$html .= "<label>" . _form_radio($attr) . "{$v}</label>\n";
		}
	return $html;
}
/**
 * 多选框
 */
function _form_checkbox($attr = array(), $ext = array())
{
	$attr['type'] = 'checkbox';
	$attr_html = _form_make_attr($attr);
	return "<input {$attr_html} {$ext['_attr']} />{$ext['_html']}";
}
/**
 * 多选框组
 *
 * _data(array)	多选框数据，多选框值 => 显示的名称
 * _checked(array)	已选中项
 */
function form_checkbox($attr = array())
{
	$html = '';
	if (!is_array($attr['_data']))
		{
		return '';
		}
	$attr['_checked'] = (array)$attr['_checked'];
	foreach ($attr['_data'] as $k => $v)
		{
		$tmp = $attr;
		$tmp['value'] = $k;
		if (in_array($k, $attr['_checked']) || (isset($attr['value']) && $k == $attr['value']))
			{
			$tmp['checked'] = 'checked';
			}
		$html .= "<label>" . _form_checkbox($tmp) . "{$v}</label>\n";
		}
	return $html;
}

/**
 * 下接框
 *
 * option(array)	下拉框数据，下拉框值 => 显示的名称
 */
function form_select($attr)
{
	//强制类型转换，因为有可能需要生成一个空的下拉框
	$option = (array)$attr['option'];
	$value = $attr['value'];
	unset($attr['option'], $attr['value']);
	$attr_html = _form_make_attr($attr);
	$html = "<select {$attr_html} >\n";
	foreach ($option as $k => $v)
		{
		$selected = $k == $value['value'] ? 'selected="selected"' : '';
		$html .= "<option value=\"{$k}\" {$selected}>{$v}</option>\n";
		}
	$html .= "</select>";
	return $html;
}
/**
 * 输入框
 */
function form_text($attr = array())
{
	$attr['type'] = 'text';
	$attr_html = _form_make_attr($attr);
	return "<input {$attr_html} />";
}
/**
 * 密码框
 */
function form_password($attr = array())
{
	$attr['type'] = 'password';
	$attr_html = _form_make_attr($attr);
	return "<input {$attr_html} />";
}
/**
 * 文本框
 */
function form_textarea($attr)
{
	$value = $attr['value'];
	unset($attr['value']);

	$attr_html = _form_make_attr($attr);
	return "<textarea {$attr_html} >{$value}</textarea>";
}
/**
 * 隐藏域
 */
function form_hide($attr = array())
{
	$attr['type'] = 'hidden';
	$attr_html = _form_make_attr($attr);
	return "<input {$attr_html} />";
}
/**
 * 隐藏域组
 *
 * _data(array)	隐藏域组数据,隐藏域名 => 隐藏域值
 * _from_get(string)	从get中取的键名,多个值以","(逗号)分隔，如 mod,action,file
 */
function form_hides($attr = array(), $ext = array())
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
/**
 * 显示 phpcms 原来的日期选择器
 */
function form_date_phpcms($attr)
{
	require_once PHPCMS_ROOT . "include/form.class.php";
	return form::date($attr['name'], $attr['value']);
}
?>

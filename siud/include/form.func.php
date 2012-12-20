<?php 
/**
 * ���������
 *
 * 2011-05-25
 * 
 * ͨ���������ԣ�
 * 	_attr	�ڱ����ǩ������� html ���룬�� <input �����λ�����>
 * 	_html	�ڱ����ǩ������� html ���룬�� <input />�����λ�����
 *
 * @package form
 * @filesource
 */

/**
 * ����������������ɱ������� html ����: ������ => ����ֵ
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
 * �����ļ���
 */
function form_file($attr = array())
{
	$attr['type'] = 'file';
	$attr_html = _form_make_attr($attr);
	return "<input {$attr_html} {$ext['_attr']} />{$ext['_html']}";
}
/**
 * �ļ����飨����ļ���
 *
 * 	_num	����
 * 	_is_br	�ı���֮���Ƿ���� <br/> ����
 * 	_is_add_button	�Ƿ���ʾ׷���ļ���ť����Ҫ jquery ֧��
 * 	_is_del_button	�Ƿ���ʾɾ���ļ���ť����Ҫ jquery ֧��
 */
function form_files($attr = array(), $ext = array())
{
	$file_html = file($attr, $ext);
	$num = max(1, intval($ext['_num']));
	$br_html = $ext['_is_br'] ? '<br />' : '';
	$del_html = $ext['_is_del_button'] ? "<input type=\"button\" value=\"ɾ��\" onclick=\"$(this).parent().remove()\" />" : '';
	$full_file_html = "<span>{$file_html}{$del_html}{$br_html}</span>";
	$html = str_repeat($full_file_html . "\n", $num);

	if ($ext['_is_add_button'])
		{
		$tmp_file_html = addslashes(str_replace('"', "'", $file_html . $del_html . $br_html));
		$html .= "<input type=\"button\" value=\"���\" onclick=\"$(this).before('{$tmp_file_html}')\" />{$br_html}";
		unset($tmp_file_html);
		}
	return $html;
}
/**
 * �����ͼƬ�ļ����ļ���
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
 * ��ѡ��
 */
function _form_radio($attr = array())
{
	$attr['type'] = 'radio';
	$attr_html = _form_make_attr($attr);
	return "<input {$attr_html} />";
}
/**
 * ��ѡ����
 *
 * _data	��ѡ�����ݣ���ѡ��ֵ => ��ʾ������
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
 * ��ѡ��
 */
function _form_checkbox($attr = array(), $ext = array())
{
	$attr['type'] = 'checkbox';
	$attr_html = _form_make_attr($attr);
	return "<input {$attr_html} {$ext['_attr']} />{$ext['_html']}";
}
/**
 * ��ѡ����
 *
 * _data(array)	��ѡ�����ݣ���ѡ��ֵ => ��ʾ������
 * _checked(array)	��ѡ����
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
 * �½ӿ�
 *
 * option(array)	���������ݣ�������ֵ => ��ʾ������
 */
function form_select($attr)
{
	//ǿ������ת������Ϊ�п�����Ҫ����һ���յ�������
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
 * �����
 */
function form_text($attr = array())
{
	$attr['type'] = 'text';
	$attr_html = _form_make_attr($attr);
	return "<input {$attr_html} />";
}
/**
 * �����
 */
function form_password($attr = array())
{
	$attr['type'] = 'password';
	$attr_html = _form_make_attr($attr);
	return "<input {$attr_html} />";
}
/**
 * �ı���
 */
function form_textarea($attr)
{
	$value = $attr['value'];
	unset($attr['value']);

	$attr_html = _form_make_attr($attr);
	return "<textarea {$attr_html} >{$value}</textarea>";
}
/**
 * ������
 */
function form_hide($attr = array())
{
	$attr['type'] = 'hidden';
	$attr_html = _form_make_attr($attr);
	return "<input {$attr_html} />";
}
/**
 * ��������
 *
 * _data(array)	������������,�������� => ������ֵ
 * _from_get(string)	��get��ȡ�ļ���,���ֵ��","(����)�ָ����� mod,action,file
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
 * ��ʾ phpcms ԭ��������ѡ����
 */
function form_date_phpcms($attr)
{
	require_once PHPCMS_ROOT . "include/form.class.php";
	return form::date($attr['name'], $attr['value']);
}
?>

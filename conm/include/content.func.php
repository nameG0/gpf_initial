<?php
/**
 * 文章模型相关函数
 * 
 * @package default
 * @filesource
 */

/**
 * 加载函数式封装的字段
 *
 * 在为模型添加，修改字段等都经常需要加载对应的字段，所以定义个函数。
 * @param string $formtype 字段类型
 */
function content_field_inc($formtype)
{//{{{
	//加载其它模块的字段类型
	$field_list = require CONTENT_ROOT . "fields/fields.inc.php";
	if (function_exists("content_field_{$formtype}_form"))
		{
		continue;
		}
	if (is_array($field_list[$formtype]) && $field_list[$formtype][1])
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
}//}}}

/**
 * 加载字段钩
 * @param array $field 文章模型的字段数据，加载对应的字段。
 * @return void
 */
function content_field_load($field)
{//{{{
	//加载其它模块的字段类型
	$field_list = require CONTENT_ROOT . "fields/fields.inc.php";
	foreach ($field as $k => $setting)
		{
		$formtype = $setting['formtype'];
		if (function_exists("content_field_{$formtype}_form"))
			{
			continue;
			}
		if (is_array($field_list[$formtype]) && $field_list[$formtype][1])
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
}//}}}

function content_field_output($data)
{//{{{

	$this->data = $data;
	$this->contentid = $data['contentid'];
	$this->set_catid($data['catid']);
	
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
}//}}}

/**
 * 供字段类型进行报错
 *
 * 若字段类型处理过程中发生错误，直接调用即可：
 * <code>
 * content_field_error('错误信息');
 * </code>
 * @param NULL|string NULL 时表示清空已保存的错误信息
 * @return array 无参数调用时返回所有错误信息
 */
function content_field_error($msg = '')
{//{{{
	static $error = array();
	if (is_null($msg))
		{
		$error = array();
		return ;
		}
	if ($msg)
		{
		$error[] = $msg;
		return ;
		}
	return $error;
}//}}}

<?php 
/**
 * 内容模型相关API
 * 
 * @package default
 * @filesource
 */

/**
 * 取指定模型数据
 * @param int $modelid 模型ID
 * @return CMMr
 */
function conm_model_get($modelid)
{//{{{
	$CMMr = siud::find('model')->wis('modelid', $modelid)->ing();
	if (!$CMMr)
		{
		return array();
		}
	a::i($CMMr)->unsers('setting');
	return $CMMr;
}//}}}

/**
 * 返回指定模型的编辑表单HTML代码
 * @param array $data 表单项值，对模型内容修改时使用。
 * @return array [field] => HTML代码
 */
function conm_content_form($modelid, $data = array())
{//{{{
	$info = array();
	$this->content_url = $data['url'];
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
	//生成表单
	foreach($this->fields as $field=>$v)
	{
		if(defined('IN_ADMIN'))
		{
			if($v['iscore'] || check_in($_roleid, $v['unsetroleids']) || check_in($_groupid, $v['unsetgroupids'])) continue;
		}
		else
		{
			if($v['iscore'] || !$v['isadd'] || check_in($_roleid, $v['unsetroleids']) || check_in($_groupid, $v['unsetgroupids'])) continue;
		}
		$formtype = $v['formtype'];
		$value = isset($data[$field]) ? htmlspecialchars($data[$field], ENT_QUOTES) : '';
		if($func=='pages' && isset($data['maxcharperpage']))
		{
			$value = $data['paginationtype'].'|'.$data['maxcharperpage'];
		}

		//调用函数式字段类型
		$func_name = "content_field_{$formtype}_form";
		if (function_exists($func_name))
		{
			$form = call_user_func($func_name, $field, $value, $v);
		}
		else
		{
			$form = call_user_func(array($this, $formtype), $field, $value, $v);
		}

		if($form !== false)
		{
			if(defined('IN_ADMIN'))
			{
				if($v['isbase'])
				{
					$star = $v['minlength'] || $v['pattern'] ? 1 : 0;
					$info['base'][$field] = array('name'=>$v['name'], 'tips'=>$v['tips'], 'form'=>$form, 'star'=>$star);
				}
				else
				{
					$star = $v['minlength'] || $v['pattern'] ? 1 : 0;
					$info['senior'][$field] = array('name'=>$v['name'], 'tips'=>$v['tips'], 'form'=>$form, 'star'=>$star);
				}
			}
			else
			{
				$star = $v['minlength'] || $v['pattern'] ? 1 : 0;
				$info[$field] = array('name'=>$v['name'], 'tips'=>$v['tips'], 'form'=>$form, 'star'=>$star);
			}
		}
	}
	return $info;
}//}}}

//------ 旧函数，待改进 ------

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

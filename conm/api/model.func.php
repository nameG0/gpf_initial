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
 * @return array [field] => {form:HTML代码, name:字段中文名, tips:字段提示信息, }
 */
function conm_content_form($modelid, $data = array())
{//{{{
	$CMFs = siud::select('model_field')->wis('modelid', $modelid)->ing();
	$CMFl = array();
	$CMFTid_list = array();
	foreach ($CMFs as $k => $r)
		{
		$CMFl[$r['field']] = $r;
		$CMFTid_list[] = $r['formtype'];
		}
	unset($CMFs);

	cm_f_field_load($CMFTid_list);

	$form = cm_m_content_form($CMFl, $data);
	$ret = array();
	foreach ($CMFl as $f => $set)
		{
		$ret[$f] = array(
			"form" => $form[$f],
			"name" => $set['name'],
			"tips" => $set['tips'],
			);
		}

	return $ret;
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

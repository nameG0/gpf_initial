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
 * @return array $CMMr
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
 * 取指定模型的字段数据
 * @return array $CMFl
 */
function conm_field_get($modelid)
{//{{{
	$CMFs = siud::select('model_field')->wis('modelid', $modelid)->ing();
	if (!$CMFs)
		{
		return array();
		}
	$CMFl = array();
	foreach ($CMFs as $k => $r)
		{
		a::i($r)->unsers('setting');
		$CMFl[$r['field']] = $r;
		}
	return $CMFl;
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
		a::i($r)->unsers('setting');
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
function content_model($modelid = 0)
{//{{{
	if (!$modelid)
		{
		$cache = cache_read('model.php', CONTENT_DATA_DIR, 0, true);
		if (!$cache)
			{
			$cache = _content_model_cache_all();
			}
		}
	else
		{
		$cache = cache_read("model_{$catid}.php", CONTENT_DATA_DIR, 0, true);
		if (!$cache)
			{
			$cache = _content_model_cache_modelid($modelid);
			}
		}
	return $cache;
}//}}}

function _content_model_cache_all()
{//{{{
	global $db;
	$data = array();
	$result = $db->select("SELECT * FROM `".DB_PRE."model` WHERE `disabled`=0");
	foreach ($result as $k => $r)
		{
		$data[$r['modelid']] = $r;
		}
	cache_write('model.php', $data, CONTENT_DATA_DIR, true);
	return $data;
}//}}}
function _content_model_cache_modelid($modelid)
{//{{{
	global $db;
	$sql = "SELECT * FROM " . DB_PRE . "model WHERE modelid={$catid}";
	$r = $db->get_one($sql);
	if (!empty($r['setting']))
		{
		$setting = $r['setting'];
		eval("\$setting = $setting;"); 
		unset($r['setting']);
		if (is_array($setting))
			{
			foreach ($setting as $k => $v)
				{
				$r[$k] = $v;
				}
			}
		}
	cache_write("modelid_{$modelid}.php", $r, CONTENT_DATA_DIR, true);
	return $r;
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

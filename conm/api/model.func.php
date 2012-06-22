<?php 
/**
 * 内容模型相关API
 * 
 * @package default
 * @filesource
 */
define('CONM_MODEL_FIELD', 0);
define('CONM_ONLY_MODEL', 1);
define('CONM_ONLY_FIELD', 2);

/**
 * 提取模型及其字段信息 CMMR
 * @param int $modelid 模型ID
 * @param int $mode {CONM_MODEL_FIELD:取模型与字段, CONM_ONLY_MODEL:只取模型, CONM_ONLY_FIELD:只取字段}
 * @return array $CMMR, 提取数据失败时返回空数组。
 */
function conm_CMMR($modelid, $mode = CONM_MODEL_FIELD)
{//{{{
	//------ 取模型数据 ------
	$CMMr = siud::find('model')->wis('modelid', $modelid)->ing();
	if (!$CMMr)
		{
		return array();
		}
	a::i($CMMr)->unsers('setting');

	//------ 取字段数据 ------
	$CMFs = siud::select('model_field')->wis('modelid', $modelid)->ing();
	if (!$CMFs)
		{
		return $CMMr;
		}
	$CMFl = array();
	foreach ($CMFs as $k => $r)
		{
		a::i($r)->unsers('setting');
		$CMFl[$r['field']] = $r;
		}

	//------ 为 CMFl 增加模型信息 ------
	$CMFl['_info'] = array(
		"modelid" => $modelid,
		"tablename" => $CMMr['tablename'],
		);
	$CMMTid = cm_m_CMMTid($CMMr['modeltype']);
	cm_m_load($CMMTid);
	list($mod, $name) = explode("/", $CMMTid);
	$func_name = "cm_mt_{$mod}__{$name}_fill_info";
	if (function_exists($func_name))
		{
		$func_name($CMFl['_info'], $CMMr['setting']);
		}

	$CMMr['CMFL'] = $CMFl;
	return $CMMr;
}//}}}

/**
 * 返回指定模型的编辑表单HTML代码
 * @param array $data 表单项值，对模型内容修改时使用。
 * @return array [field] => {form:HTML代码, name:字段中文名, tips:字段提示信息, }
 */
function conm_form($CMFL, $data = array())
{//{{{
	unset($CMFL['_info']);
	$CMFTid_list = array();
	foreach ($CMFL as $k => $r)
		{
		// a::i($r)->unsers('setting');
		// $CMFl[$r['field']] = $r;
		$CMFTid_list[] = $r['formtype'];
		}

	cm_f_load($CMFTid_list);

	$form = cm_c_form($CMFL, $data);
	$ret = array();
	foreach ($CMFL as $f => $set)
		{
		$ret[$f] = array(
			"form" => $form[$f],
			"name" => $set['name'],
			"tips" => $set['tips'],
			);
		}

	return $ret;
}//}}}
/**
 * 填充字段数据。
 * @param array $CMFL
 * @param array $data 表单提交，待录入数据。
 * @param array $keep 助手性质，比如保存字段的旧值。
 */
function conm_fill($CMFL, $data, $keep = array())
{//{{{
	$_info = $CMFL['_info'];
	unset($CMFL['_info']);
	//加载字段类型文件
	$CMFTid_list = array();
	foreach ($CMFL as $f => $r)
		{
		$CMFTid_list[] = $r['formtype'];
		}
	cm_f_load($CMFTid_list);

	$id = $data[$_info['pk']];
	//按字段类型进行填充，在表单输入值为空的情况下也可以填入默认值。
	foreach ($CMFL as $f => $r)
		{
		list($mod, $name) = explode("/", $r['formtype']);
		$func_name = "cm_ft_{$mod}__{$name}_save";
		if (!function_exists($func_name))
			{
			continue;
			}
		$data[$f] = $func_name($f, $data, $keep, $r['setting'], $id);
		}
	return $data;
}//}}}
/**
 * 返回传入字段列表中属于关联字段类型的字段名,若没有则返回空数组
 * @param array $CMFL
 * @return array [] => field
 */
function conm_use_id($CMFL)
{//{{{
	unset($CMFL['_info']);
	$CMMid_list = array();
	foreach ($CMFL as $f => $r)
		{
		$CMMid_list[] = $r['formtype'];
		}
	cm_f_load($CMMid_list);
	$ret = array();
	foreach ($CMFL as $f => $r)
		{
		list($mod, $name) = explode("/", $r['formtype']);
		$func_name = "cm_ft_{$mod}__{$name}_use_id";
		if (function_exists($func_name) && $func_name($r['setting']))
			{
			$ret[] = $f;
			continue;
			}
		else if (defined(strtoupper("CM_FT_{$mod}__{$name}_USE_ID")))
			{
			$ret[] = $f;
			}
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

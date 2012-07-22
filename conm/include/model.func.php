<?php
/**
 * 内容模型相关函数
 * 
 * @package include
 * @filesource
 */

/**
 * 返回一个表所有字段的指纹值
 * <pre>
 * SHOW COLUMNS 语句返回的字段有：Field, Type, Null, Key, Default, Extra
 * 不使用 Field,Key 两列，因为字段类型SQL语句不需包含字段名。
 * Key列为索引情况，由模型类型设置，也与单个字段无关。
 * </pre>
 * @param string $table 表名,无需包含表前序
 * @return array [field] => FString
 */
function cm_m_get_FString($table)
{//{{{
	$db = rdb::obj();

	$table = RDB_PRE . $table;
	$result = $db->select("SHOW COLUMNS FROM {$table}");
	$FString_list = array();
	foreach ($result as $k => $r)
		{
		$FString_list[$r['Field']] = "{$r['Type']}|{$r['Null']}|{$r['Default']}|{$r['Extra']}";
		}
	return $FString_list;
}//}}}
/**
 * 显示模型类型设置表单
 * @param array $setting 模型类型配置参数
 */
function cm_m_setting_form($CMMTid, $setting = array())
{//{{{
	list($mod, $name) = explode("/", $CMMTid);
	$callback = mod_callback($mod, 'p');
	foreach ($callback as $k => $v)
		{
		$path = "{$v}conm_model/{$name}/setting.inc.php";
		if (is_file($path))
			{
			include $path;
			return ;
			}
		}
}//}}}

/**
 * 取所有字段类型
 * @param string $mod 字段类型所属模块，为空则返回所有模块的字段类型。
 * @return array $CMFTl
 */
function cm_f_field_list($mod = '')
{//{{{
	static $CMFTl_cache = array(); //缓存每个模块的字段类型列表, array[mod] = $CMFTl
	static $callback_mod = NULL; //保存需要 callback 注册的模块,当 $mod='' 时使用此数据加载并清空此数组,这样便避免多次 $mod='' 时重复加载. 

	if (is_null($callback_mod))
		{
		//第一次调用时,取出所有 callback 模块列表
		$callback_mod = mod_callback('conm', 'rm');
		}

	//先判断字段类型是否需要加载
	if (!$mod || !isset($CMFTl_cache[$mod]))
		{
		$callback = array(); //存放需要载入模块名。若此数组不为空则表示需要进行加载。
		if ($mod)
			{
			//传入参数指定模块名时，直接加载即可。
			//也就是提供一个加载未注册 callback 的模块字段类型的可能性。
			$callback[] = $mod;
			}
		else
			{
			$callback = $callback_mod;
			$callback_mod = array();
			}
		//去除已加载过的模块.
		foreach ($callback as $k => $v)
			{
			if (isset($CMFTl_cache[$v]))
				{
				unset($callback[$k]);
				}
			}
		//加载指定模块字段类型。
		foreach ($callback as $m)
			{
			//因为已加载过的模块已被 unset 掉,所以把需加载模块的值初始化为空数组.
			$CMFTl_cache[$m] = array();
			$p = mod_callback($m, 'p');
			if (is_array($p))
				{
				foreach ($p as $v)
					{
					$path = "{$v}conm_field/fields.inc.php";
					if (!is_file($path))
						{
						continue;
						}
					$field = include $path;
					foreach ($field as $f => $n)
						{
						$CMFTl_cache[$m]["{$m}/{$f}"] = array(
							"m" => $m,
							"n" => $f,
							"nn" => $n,
							"p" => "{$v}conm_field/{$f}.func.php",
							);
						}
					unset($field);
					}
				}
			unset($p);
			}
		}

	if ($mod)
		{
		return $CMFTl_cache[$mod];
		}
	$ret = array();
	foreach ($CMFTl_cache as $l)
		{
		foreach ($l as $k => $v)
			{
			$ret[$k] = $v;
			}
		}
	return $ret;
}//}}}
/**
 * 加载一个或一组字段类型
 * @param string|array 字段类型ID , 数组则由字段类型ID组成. eg. conm/title eg. array[] => 'conm/title'
 */
function cm_f_load($CMFTid)
{//{{{
	static $cache = array(); //记录已加载的字段类型,避免重复加载.

	if (is_string($CMFTid))
		{
		$CMFTid = array($CMFTid);
		}
	if (!is_array($CMFTid))
		{
		return ;
		}

	foreach ($CMFTid as $k => $v)
		{
		if (isset($cache[$v]))
			{
			unset($CMFTid[$k]);
			}
		}

	//若在模块源与副本目录都定义有同名的字段类型,优先加载副本目录的字段类型.
	foreach ($CMFTid as $k => $v)
		{
		$cache[$v] = true;
		list($mod, $name) = explode("/", $v);
		//自动加载字段类型所在模块。
		mod_init($mod);
		
		$callback = mod_callback($mod, 'p');
		$path_inst = $callback["inst"] . "conm_field/{$name}.func.php";
		$path_sour = $callback["sour"] . "conm_field/{$name}.func.php";
		if ($callback['inst'] && is_file($path_inst))
			{
			include_once $path_inst;
			}
		else if ($callback['sour'] && is_file($path_sour))
			{
			include_once $path_sour;
			}
		}
}//}}}
/**
 * 加载模型类型处理函数
 * @param string $CMMTid
 * @return void
 */
function cm_m_load($CMMTid)
{//{{{
	list($mod, $name) = explode("/", $CMMTid);
	$callback = mod_callback($mod, 'p');
	foreach ($callback as $k => $v)
		{
		$p = "{$v}conm_model/{$name}/function.func.php";
		if (is_file($p))
			{
			include_once $p;
			break;
			}
		}
}//}}}
/**
 * @param int $modeltype 模型类型注册ID
 * @return string $CMMTid
 */
function cm_m_CMMTid($modeltype)
{//{{{
	switch ($modeltype)
		{
		case "0":
			return 'conm/table';
			break;
		case "1":
			return 'cms/content';
			break;
		case "2":
			return 'tree/tree';
			break;
		case "10":
			return 'member/member';
			break;
		default:
			//todo
			exit('未完成其它内容模型的同步功能(' . __FILE__ . ':' . __LINE__ . ')');
			break;
		}
}//}}}
/**
 * CMMTid 反查 modeltype 值
 * @return int
 */
function cm_m_modeltype($CMMTid)
{//{{{
	switch ($CMMTid)
		{
		case "cms/content":
			return 1;
			break;
		}
}//}}}

/**
 * 返回模型编辑表单（调用者需要使用 cm_f_load() 加载字段类型）
 * @param array $data 表单项值，对模型内容修改时使用。
 * @return array [field] => HTML代码
 */
function cm_c_form($CMFl, $data = array())
{//{{{
	$ret = array();
	foreach ($CMFl as $f => $set)
		{
		list($_mod, $_name) = explode("/", $set['formtype']);
		$func_name = "cm_ft_{$_mod}__{$_name}_form";
		if (!function_exists($func_name))
			{
			continue;
			}
		$ret[$f] = $func_name($f, $data[$f], $set['setting']);
		}
	return $ret;
}//}}}


//------ 旧函数，待改进 ------

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

<?php
/**
 * 内容模型相关函数
 * <pre>
 * 各模块字段类型存放规则：
 * 存放目录： {module_name}/callback/conm_field/
 * 字段类型索引文件： fields.inc.php
 * 字段类型定义文件： {field_name}.func.php
 * 索引文件定义格式： return array('字段名' => '字段昵称', ...);
 * 其中字段名可由英文和数字组可，昵称可使用中文。若需要禁用某个字段类型，只需不包含在索引文件中即或。
 * <b>数据结构</b>
 * FString(field string) string 一个字段的指纹值,用于比较内容模型字段设置是否与实际表字段一致.
 * MySQL的指纹值为SHOW COLUMNS语句返回的字段值组合,但不包含Field字段的值.
 *
 * $CMFTid(content module field type ID): 字段类型ID
 * 字段类型ID命名规则：{module_name}/{field_name} eg. conm/title
 *
 * $CMFTr(content module field type row): 一个字段类型.
 * array[m]=模块(module), [n]=字段类型名称(name), [nn]=中文昵称(nickname), [p]=绝对路径(path)
 *
 * $CMFTl(content module field type list): 字段类型列表
 * array[字段类型ID] = $CMFTr
 * <b>字段类型接口</b>
 * 前序:cm_ft_ (content model field type)
 * 命名规则:前序_{mod}_{name}_接口名. eg. cm_ft_conm__example_setting
 * void setting($setting): 显示字段类型设置表单
 * string FString($set) 返回字段类型指纹值.
 * </pre>
 * 
 * @package default
 * @filesource
 */

/**
 * 返回一个表所有字段的指纹值
 * @param string $table 表名,无需包含表前序
 * @return array [field] => FString
 */
function cm_m_get_FString($table)
{//{{{
	$db = rdb::obj();

	$table = RDB_PRE . $table;
	$result = $db->select("SHOW COLUMNS FROM {$table}");
	//Field, Type, Null, Key, Default, Extra
	$FString_list = array();
	foreach ($result as $k => $r)
		{
		$FString_list[$r['Field']] = "{$r['Type']}|{$r['Null']}|{$r['Key']}|{$r['Default']}|{$r['Extra']}";
		}
	return $FString_list;
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
 * 加载一个字段类型或加载一组字段类型
 * @param string|array 字段类型ID , 数组则由字段类型ID组成. eg. conm/title
 */
function cm_f_field_load($CMFTid)
{//{{{
	static $cache = array(); //记录已加载的字段类型,避免重复加载.

	if (!is_array($CMFTid))
		{
		$CMFTid = array($CMFTid);
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

<?php 
/**
 * 字段类型相关函数
 * 2012-05-11
 * <pre>
 * 字段类型ID命名规则：{module_name}/{field_name} eg. conm/title
 *
 * 各模块字段类型存放规则：
 * 存放目录： {module_name}/callback/conm_field/
 * 字段类型索引文件： fields.inc.php
 * 字段类型定义文件： {field_name}.func.php
 * 索引文件定义格式： return array('字段名' => '字段昵称', ...);
 * 其中字段名可由英文和数字组可，昵称可使用中文。若需要禁用某个字段类型，只需不包含在索引文件中即或。
 * </pre>
 * 
 * @package default
 * @filesource
 */

/**
 * 取所有字段类型
 * @param string $mod 字段类型所属模块，为空则返回所有模块的字段类型。
 * @return array [字段类型ID] => 定义文件绝对路径。
 */
function cm_f_field_list($mod = '')
{//{{{
	static $cache = array();

	//先判断字段类型是否需要加载
	$callback = array(); //存放需要载入的 callback 绝对路径。若此数组不为空则表示需要加载。
	if (!$mod || !isset($cache[$mod]))
		{
		if ($mod)
			{
			//传入参数指定模块名时，不需要使用 callback 列表检查，直接加载即可。
			//也就是提供一个加载未注册 callback 的模块字段类型的可能性。
			$callback = mod_callback($mod, 'r');
			}
		else
			{
			//传入参数不指定具体模块时，取出所有 callback 模块，去除已加载的模块后加载。
			//此时所有模块的字段类型都将被加载。
			list($tmp, $callback) = mod_callback('conm');
			unset($tmp);
			foreach ($callback as $k => $v)
				{
				list($m) = explode("/", $k);
				if (isset($cache[$m]))
					{
					unset($callback[$k]);
					}
				}
			}
		}

	if ($callback)
		{
		//加载指定模块字段类型。
		foreach ($callback as $k => $v)
			{
			list($m) = explode("/", $k);
			$path = "{$v}conm_field/fields.inc.php";
			if (!is_file($path))
				{
				continue;
				}
			$field = include $path;
			foreach ($field as $f => $n)
				{
				$cache[$m][$f] = $n;
				}
			}
		}

	//经过

	if ($mod && isset($cache[$mod]))
		{
		return $cache[$mod];
		}



	var_dump($cache);exit;
}//}}}

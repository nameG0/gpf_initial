<?php
/**
 * GPF 助手函数
 * 
 * @package default
 * @filesource
 */
/**
 * 支持数组的 addslashes
 * @param string|array $data
 */
function gpf_adds($data)
{//{{{
	return is_array($data) ? array_map(__FUNCTION__, $data) : addslashes($data);
}//}}}
/**
 * 支持数组的 stripslashes
 * @param string|array $data
 */
function gpf_unadds($data)
{//{{{
	return is_array($data) ? array_map(__FUNCTION__, $data) : stripslashes($data);
}//}}}
/**
 * 支持数组的 htmlspecialchars
 */
function gpf_html($data)
{//{{{
	return is_array($data) ? array_map(__FUNCTION__, $data) : htmlspecialchars($data);
}//}}}
/**
 * 把参数强制转换为数组返回
 */
function gpf_arr($data)
{//{{{
	return (array)$data;
}//}}}

/**
 * 更新 static 目录文件。
 * <pre>
 * 需定义常量：
 * GPF_STATIC_DIR :/public/static/ 目录路径。
 * </pre>
 * @param string $mod_name 模块名,为空表示全部复制（一般用于初始化）。
 */
function gpf_static($mod_name = '')
{//{{{
	gpf::log($mod_name, gpf::INFO, __FILE__, __LINE__, __FUNCTION__);
	if ($mod_name)
		{
		$to = GPF_STATIC_DIR . "{$mod_name}/";
		_gpf_static_copy(GPF_PATH_MODULE . "{$mod_name}/static/", $to);
		}
	else
		{
		$handle = dir(GPF_PATH_MODULE);
		while ($entry = $handle->read())
			{
			if (($entry == ".") || ($entry == ".."))
				{
				continue;
				}
			_gpf_static_copy(GPF_PATH_MODULE . $entry . "/static/", GPF_STATIC_DIR . $entry . '/');
			}
		$handle->close();
		}
}//}}}
/**
 * 只复制更新过的文件，因为“复制”操作很耗时。
 */
function _gpf_static_copy($sour, $to)
{//{{{
	if (is_dir($sour))
		{
		$handle = dir($sour);
		while ($entry = $handle->read())
			{
			if (($entry == ".") || ($entry == ".."))
				{
				continue;
				}
			if (is_dir($sour . $entry))
				{
				$entry .= '/';
				}
			_gpf_static_copy($sour . $entry, $to . $entry);
			}
		$handle->close();
		return ;
		}
	if (!is_file($sour))
		{
		return ;
		}
	if (is_file($to) && filemtime($to) >= filemtime($sour))
		{
		return ;
		}
	gpf_mkdir(dirname($to));
	copy($sour, $to);
}//}}}

/**
 * 返回模板路径
 * @return string 模板路径
 */
function gpf_tpl($mod, $file)
{//{{{
	gpf::log("{$mod} : {$file}", gpf::INFO, __FILE__, __LINE__, __FUNCTION__);
	$path = gmod::path($mod, "template/{$file}.tpl.php");
	if (!is_file($path))
		{
		gpf::log("模板不存在[{$path}]", gpf::ERROR, __FILE__, __LINE__, __FUNCTION__);
		gpf::err("template not exists", __FILE__, __LINE__, __FUNCTION__);
		return false;
		}
	return $path;
}//}}}

<?php
/**
 * 常用函数
 * 
 * @version 2012-05-05
 * @package default
 * @filesource
 */

/**
 * 计算运行时间
 * @param NULL|int $time {NULL:返回当前时间, int:计算当前时间与转入时间的间隔}
 * <code>
 * $t1 = run_time(); //存当前时间
 * sleep(1);
 * echo run_time($t1); //计算运行时间
 * </code>
 */
function run_time($time = NULL)
{//{{{
	list($usec, $sec) = explode(" ", microtime());
	$mt = ((float)$usec + (float)$sec);
	if (is_null($time))
		{
		return $mt;
		}
	return $mt - $time;
}//}}}
/**
 * 支持数组的 stripslashes
 * @param string|array $data
 */
function gstripslashes($data)
{//{{{
	return is_array($data) ? array_map(__FUNCTION__, $data) : stripslashes($data);
}//}}}
/**
 * 支持数组的 addslashes
 * @param string|array $data
 */
function gaddslashes($data)
{//{{{
	return is_array($data) ? array_map(__FUNCTION__, $data) : addslashes($data);
}//}}}

/**
 * 创建一个目录(树)
 * @param int $i 循环最大次数，以防止死循环 -- 1000(1000 层的目录)
 */
function gpf_mkdir($dir, $mode = 0777, $i = 1000)
{//{{{
	if (is_dir($dir))
		{
		return true;
		}
	$dirs = array();
	while (!is_dir($dir) && $i > 0)
		{
		$dirs[] = $dir;
		$dir = dirname($dir);
		$i--;
		}
	$dirs = array_reverse($dirs);
	foreach ($dirs as $k => $v)
		{
		mkdir($v, $mode);
		}
	return true;
}//}}}
/**
 * 复制目录
 * $f-是否复制文件夹下文件，$d是否复制搜索下级文件夹
 * 2010-6-26 from 08cms
 * require gpf_mkdir()
 * note 睇归
 */
function gpf_dir_copy($source,$destination,$f = 1,$d = 1)
{//{{{
	echo "{$source} --- {$destination}<br />";
	if(!is_dir($source)) return false;
	gpf_mkdir($destination);
	if($f || $d){
		$handle = dir($source);
		while($entry = $handle->read()){
			if(($entry != ".") && ($entry != "..")){
				if(is_dir($source."/".$entry)){
					$d && gpf_dir_copy($source."/".$entry,$destination."/".$entry,$f,$d);
				}else{
					$f && copy($source."/".$entry, $destination."/".$entry);
				}
			}
		}
	}
	return true;
}//}}}

/**
 * 更新 static 目录文件。
 * <pre>
 * 需定义常量：
 * GPF_STATIC_DIR :/public/static/ 目录路径。
 * GPF_STATIC_PRIOR :/public/_static/ 目录路径。
 * </pre>
 * @param string $mod_name 模块名,为空表示全部复制（一般用于初始化）。
 */
function gpf_static($mod_name = '')
{//{{{
	$time = run_time();
	if ($mod_name)
		{
		$to = GPF_STATIC_DIR . "{$mod_name}/";
		_gpf_static_copy(GPF_PATH_MODULE . "{$mod_name}/static/", $to);
		_gpf_static_copy(GPF_STATIC_PRIOR . "{$mod_name}/", $to);
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

		_gpf_static_copy(GPF_STATIC_PRIOR, GPF_STATIC_DIR);
		}

	echo run_time($time);
	exit;
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

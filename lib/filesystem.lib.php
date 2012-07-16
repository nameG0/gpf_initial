<?php
/*
create:2010-1-12
last_update:
2011-07-08

对 PHP filesystem 类函数的增强
*/
//==============================

function fileext($filename)
{//{{{
	return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
}//}}}

//强制解释 ini 文件，以第一个 = 号分隔键与值, 其它什么都不管,如果一组中出现多个同名键，则以数组形式返回
function parse_ini_filei($path)
{//{{{
	$ini = array();
	$current_tag = '_';
	$fp = fopen($path, 'r');
	while (!feof($fp))
		{
		$l = trim(fgets($fp));
		if (!$l)
			{
			continue;
			}
		if (';' == $l[0])
			{
			continue;
			}
		if ('[' == $l[0])
			{
			$current_tag = substr($l, 1, -1);
			continue;
			}
		list($key, $value) = parse_ini_str($l);
		if (isset($ini[$current_tag][$key]))
			{
			if (is_array($ini[$current_tag][$key]))
				{
				$ini[$current][$key][] = $value;
				}
			else
				{
				$ini[$current_tag][$key] = array($ini[$current_tag][$key], $value);
				}
			}
		else
			{
			$ini[$current_tag][$key] = $value;
			}
		unset($key, $value);
		}
	fclose($fp);
	return $ini;
}//}}}
//解释一行 ini 字符串, 不包括 [xx] 这样的行, 只解释 xx=xxxx 这样的行
//return array(key, value)
function parse_ini_str($ini)
{//{{{
	$seek = strpos($ini, '=');
	$key = trim(substr($ini, 0, $seek));
	$value = trim(substr($ini, $seek + 1));
	return array($key, $value);
}//}}}

//解释一行 dsv 风格字符串
//$col	为最多解释多少列，0 为不限
function parse_dsv_str($dsv, $col = 0, $section = ':')
{//{{{
	$ret = array();
	$count = strlen($dsv);
	$tmp = '';
	for ($i = 0; $i < $count; $i++)
		{
		if ('\\' == $dsv[$i])
			{
			$tmp .= $dsv[$i + 1];
			$i++;
			continue;
			}
		if ($section == $dsv[$i])
			{
			if ($col && (count($ret) + 1) >= $col)
				{
				$tmp .= substr($dsv, $i);
				$ret[] = $tmp;
				$tmp = '';
				break;
				}
			$ret[] = $tmp;
			$tmp = '';
			continue;
			}
		$tmp .= $dsv[$i];
		}
	if ($tmp)
		{
		$ret[] = $tmp;
		}
	return $ret;
}//}}}
//解释dsv格式文件,支持续行符（/）
function parse_dsv_file($file, $col = 0)
{//{{{
	//echo $file, __FILE__, __LINE__;exit;
	if (!is_file($file))
		{
		return false;
		}
	$fp = fopen($file, 'rb');
	if (!$fp)
		{
		return false;
		}
	$list = array();
	$is_goon = false;	//上一行是否需要续行
	$line = '';	//当前行
	while (!feof($fp))
		{
		$l = trim(fgets($fp));
		if ($is_goon)
			{
			if ('\\' == substr($l, -1))
				{
				$l = substr($l, 0, -1);
				}
			else
				{
				$is_goon = false;
				}
			$line .= $l;
			}
		else if ('\\' == substr($l, -1))
			{
			$is_goon = true;
			$line .= substr($l, 0, -1);
			}
		else
			{
			$line = $l;
			}
		unset($l);
		if ($is_goon && !feof($fp))
			{
			continue;
			}

		if (empty($line))
			{
			continue;
			}
		$list[] = parse_dsv_str($line, $col);
		$line = '';
		}
	fclose($fp);
	//print_r($list);echo __FILE__, __LINE__;exit;
	return $list;
}//}}}
//解释以“%%”分隔记录，每行为dsv风格的键值文件：
/*
name:dennis
age:21
%%
name:catty
age:22
%%
name:green
age:10
*/
function parse_record_jar_file($file)
{//{{{
	$dsv = parse_dsv_file($file, 2);
	if (!$dsv)
		{
		return $dsv;
		}
	$ret = array();
	$r = array();
	foreach ($dsv as $v)
		{
		if ('%' == $v[0][0] && '%' == $v[0][1])
			{
			if (!empty($r))
				{
				$ret[] = $r;
				$r = array();
				}
			continue;
			}
		$r[$v[0]] = $v[1];
		}
	if (!empty($r))
		{
		$ret[] = $r;
		$r = array();
		}
	return $ret;
}//}}}
//$ret = parse_dsv_str("%%\n");var_dump($ret);
//$ret = parse_dsv_file('./dsv.txt'); print_r($ret);
//$ret = parse_record_jar_file('./dsv.txt'); print_r($ret);

/**
 * 创建一个目录(树)
 * @param int $i 循环最大次数，以防止死循环 -- 1000(1000 层的目录)
 */
function mkdiri($dir, $mode = 0777, $i = 1000)
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
 * 删除指定目录及其下的所有文件和子目录
 *
 * 用法：
 * <code>
 * // 删除 my_dir 目录及其下的所有文件和子目录
 * rmdirs('/path/to/my_dir');
 * </code>
 *
 * 注意：使用该函数要非常非常小心，避免意外删除重要文件。
 *
 * @param string $dir
 */
//note 睇归
function rmdiri($dir)
{//{{{
	$me = __FUNCTION__;
	$dir = realpath($dir);
	// 禁止删除根目录
	if ($dir == '' || $dir == '/' || (strlen($dir) == 3 && substr($dir, 1) == ':\\'))
	{
		return false;
	}

	// 遍历目录，删除所有文件和子目录
	if(false !== ($dh = opendir($dir)))
	{
		while (false !== ($file = readdir($dh)))
		{
			if ($file == '.' || $file == '..')
			{
				continue;
			}
			$path = $dir . '/' . $file;
			if (is_dir($path))
			{
				if (!$me($path))
				{
					return false;
				}
			}
			else
			{
				unlink($path);
			}
		}
		closedir($dh);
		rmdir($dir);
		return true;
	}
	else
	{
		return false;
	}
}//}}}

//2010-6-26 from 08cms
//$f-是否复制文件夹下文件，$d是否复制搜索下级文件夹
//require dir_make()
//note 睇归
function dir_copy($source,$destination,$f = 1,$d = 1)
{//{{{
	if(!is_dir($source)) return false;
	dir_make($destination,0);
	if($f || $d){
		$handle = dir($source);
		while($entry = $handle->read()){
			if(($entry != ".") && ($entry != "..")){
				if(is_dir($source."/".$entry)){
					$d && dir_copy($source."/".$entry,$destination."/".$entry,$f,$d);
				}else{
					$f && copy($source."/".$entry, $destination."/".$entry);
				}
			}
		}
	}
	return true;
}//}}}

/*
//ggzhu 2010-6-26
//require not
//取得指定目录下的所有路径(包括文件夹及文件)
//note	不使用glob是因为其对中文路径支持有点问题
//	中文路径需转为gb2312编码
function path_list($dir)
{
	$dir = realpath($dir);
	if (!$dir)
		{
		return false;
		}
	$ret = array();
	$handle = opendir($dir);
	while ($file = readdir($handle))
		{
		if ('..' != $file && '.' != $file)
			{
			$ret[] = $dir . '/' . $file;
			}
		}
	closedir($handle);
	return $ret;
}

//ggzhu 2010-6-25
//require path_list()
//取得指定目录下的所有子目录
//$basename	是否只返回目录名，否为返回路径
//neededit:目录匹配符
function dir_list($dir, $basename = 1)
{
	$ret = array();
	$dirs = path_list($dir);
	if (!$dirs)
		{
		return false;
		}
	foreach ($dirs as $k => $v)
		{
		if (is_dir($v))
			{
			$ret[] = $v;
			}
		}
	if ($basename)
		{
		foreach ($ret as $k => $v)
			{
			$ret[$k] = basename($v);
			}
		}
	return $ret;
}

//ggzhu 2010-6-25
//require path_list()
//取得指定目录下的所有文件
function file_list($dir, $basename = 1)
{
	$ret = array();
	$dirs = path_list($dir);
	if (!$dirs)
		{
		return false;
		}
	foreach ($dirs as $k => $v)
		{
		if (is_file($v))
			{
			$ret[] = $v;
			}
		}
	if ($basename)
		{
		foreach ($ret as $k => $v)
			{
			$ret[$k] = basename($v);
			}
		}
	return $ret;
}

//ggzhu 2010-6-26
//require path_list()
//取得指定目录下的所有路径(包括所有子文件夹及所有文件)
//$i	循环最大次数，以防止死循环 -- 10000
function path_all($dir, $i = 10000)
{
	$dir = realpath($dir);
	if (!$dir)
		{
		return false;
		}
	$ret = array();
	$dirs = path_list($dir);
	while (!empty($dirs) && $i > 0)
		{
		$tmp = array_pop($dirs);
		if (is_dir($tmp))
			{
			$tmp_dirs = path_list($tmp);
			foreach ($tmp_dirs as $k => $v)
				{
				$dirs[] = $v;
				}
			}
		$ret[] = $tmp;
		$i--;
		}
	natsort($ret);
	return $ret;
}

//ggzhu 2010-6-26
//require path_all()
//取得指定目录下的所有目录(包括所有子目录)
function dir_all($dir, $i = 10000)
{
	$ret = array();
	$dirs = path_all($dir, $i);
	foreach ($dirs as $k => $v)
		{
		if (is_dir($v))
			{
			$ret[] = $v;
			}
		}
	return $ret;
}

//ggzhu 2010-6-26
//require path_all()
//取得指定目录下的所有文件(包括所有子文件夹中的文件)
function file_all($dir, $i = 10000)
{
	$ret = array();
	$dirs = path_all($dir, $i);
	foreach ($dirs as $k => $v)
		{
		if (is_file($v))
			{
			$ret[] = $v;
			}
		}
	return $ret;
}
*/

//2010-1-12 from thinkphp/common/extend.php:143
//计算形像化的文件大小，则自动选择单位为MB或KB
//$dec 文件大小精确到的位数
function file_size($size = 0, $dec = 2)
{//{{{
	$a = array("B", "KB", "MB", "GB", "TB", "PB");
	$pos = 0;
	while ($size >= 1024)
	{
		$size /= 1024;
		$pos++;
	}
	return round($size, $dec) . ' ' . $a[$pos];
}//}}}

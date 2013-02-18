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

<?php
/**
 * 文件缓存函数
 * 
 * @package default
 * @filesource
 */
define('GPF_SER', 1); //表示使用 serialize 序列化
define('GPF_VAR', 2); //表示使用 var_export 序列化

/**
 * 读文件缓存内容
 * @param string $path 缓存文件绝对路径
 * @param int $mode 序列化方式{GPF_SER, GPF_VAR}
 */
function cache_file_read($path, $mode = GPF_SER)
{//{{{
	if (!is_file($path))
		{
		return false;
		}
	if (GPF_SER === $mode)
		{
		return unserialize(file_get_contents($path));
		}
	else
		{
		return include $path;
		}
}//}}}

/**
 * 写入文件缓存内容
 * @param string $path
 * @param mixed $data 缓存数据
 * @param int $mode
 */
function cache_file_write($path, $data, $mode = GPF_SER)
{//{{{
	if (GPF_SER === $mode)
		{
		$content = serialize($data);
		}
	else
		{
		$content = "<?php\nreturn " . var_export($data, true) . ";\n?>";
		}
	if (function_exists('mkdiri'))
		{
		mkdiri(dirname($path));
		}
	$strlen = file_put_contents($path, $content);
	@chmod($path, 0777);
	return $strlen;
}//}}}

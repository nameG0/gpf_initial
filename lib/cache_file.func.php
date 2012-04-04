<?php
/*
ggzhu
2010-1-28
文件缓存函数

*/
//==============================

//以 serialize 的方式
//$data = null	读取
//$data != null	写入
function cache_file($file, $data = NULL)
{
	if (is_null($data))
		{
		return _cache_file_read($file, true);
		}
	else
		{
		return _cache_file_write($file, $data, true);
		}
}

//以 var_export 的方式缓存数据
function cache_var($file, $data = null)
{
	if (is_null($data))
		{
		return _cache_file_read($file, false);
		}
	else
		{
		return _cache_file_write($file, $data, false);
		}
}

function _cache_file_write($file, $data, $is_serialize = false)
{
	if ($is_serialize)
		{
		$content = '<?php die(); ?>' . serialize($data);
		}
	else
		{
		$content = "<?php\nreturn " . var_export($data, true) . ";\n?>";
		}
	$strlen = file_put_contents($file, $content);
	@chmod($file, 0777);
	return $strlen;
}

function _cache_file_read($file, $is_serialize = false)
{
	if (!is_file($file))
		{
		return false;
		}
	if ($is_serialize)
		{
		return unserialize(substr(file_get_contents($file), 15));
		}
	else
		{
		return include $file;
		}
}
<?php
/*
ggzhu
2010-1-28
缓存函数

函数前序：cache
*/
//==============================

//缓存入口类
class Cache
{
	public $cache;
	
	//构造函数
	//$cache 缓存驱动对象
	function __construct($cache)
	{
		$this->cache = $cache;
	}
	
	//ggzhu 2010-5-15
	//写入缓存
	//如果缓存类型为文件，则 $name 为缓存文件存放路径
	function write($name, $data)
	{
		return $this->cache->write($name, $data);
	}
	
	//ggzhu 2010-5-15
	//读取缓存
	function read($name)
	{
		return $this->cache->read($name);
	}
	
	//ggzhu 2010-5-15
	//删除缓存入口
	function delete($name)
	{
		return $this->cache->delete($name);
	}
}

//文件缓存驱动类
class CacheFile
{
	//ggzhu 2010-5-15
	//序列化数据
	function serialize($data)
	{
		return '<?php die(); ?>' . serialize($data);
	}
	
	//ggzhu 2010-5-15
	//反序列化数据
	function unserialize($data)
	{
		return unserialize(substr($data, 15));
	}
	
	//ggzhu 2010-5-15：from phpcms2008
	//缓存数据到文件
	//返回缓存文件的大小
	function write($file, $data)
	{
		$data = $this->serialize($data);
		$strlen = file_put_contents($file, $data);
		@chmod($file, 0777);
		return $strlen;
	}
	
	//ggzhu 2010-5-15：from phpcms2008
	//读取由 write 生成的缓存
	function read($file)
	{
		return $this->unserialize(file_get_contents($file));
	}
	
	//ggzhu 2010-5-15：from phpcms2008
	//删除缓存文件
	function delete($file)
	{
		return @unlink($file);
	}
}
?>
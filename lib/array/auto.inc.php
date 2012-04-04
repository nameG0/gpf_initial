<?php
/*
ggzhu 2010-6-18
数组操作自动化辅助函数
*/

//ggzhu 2010-6-7
//提取数组数据
//$field 为 需要的键列表字符串，多个键之间以","分隔,支持sql中select语句中字段的as语法。$data(array[])为数据数组
//如$field='a,b as theb,c',而$data中存在a b c d 4个键值,则会提取a b c三个键值的值，而其中b键值会被替换为"theb"
//注意，“,”左右不能有多余的空格，因为数组键值支持空格，写入多余的空格可能会使结果出错。
function array_sub($data, $field)
{
	$ret = array();
	$fields = explode(',', $field);
	foreach ($fields as $value)
		{
		//搜索字符串中是否含有“as”字符
		$seek = stripos($value, ' as ');
		if (false !== $seek)
			{
			$key = substr($value, 0, $seek);
			$map = substr($value, $seek + 4);
			}
		else
			{
			$key = $map = $value;
			}
		//进行数据的提取
		if (isset($data[$key]))
			{
			$ret[$map] = $data[$key];
			}
		}
	return $ret;
}

//填充数组默认值
//$data(array[]) 待填充数组
//$script 填充描述
//key=value	当key不存在(!isset)时，填充为value
//key==value 当key为空时(empty)，填充为value
//key===value 无论如何都把key填充为value
//多个键值以分号";"分隔：key1=value1;key2===value2
function array_default($data, $script)
{
	assert(is_array($data) && is_string($script));
	
	$defaults = explode(';', $script);
	foreach ($defaults as $default)
		{
		//$type 填充类型：1 is "="(默认),2 is "==",3 is "==="
		$type = 1;
		$action = explode('=', $default);
		$len = count($action);
		//元素小于2将会出错
		if ($len < 2)
			{
			continue;
			}
		//通过分隔出的数组长度判断填充类型
		switch ($len)
			{
			case 4:
				$type = 3;
				break;
			case 3:
				$type = 2;
				break;
			}
		$value = array_pop($action);
		$key = $action[0];
		//是否进行填充
		$isDefault = 0;
		switch ($type)
			{
			case 3:
				$isDefault = 1;
				break;
			case 2:
				$isDefault = empty($data[$key]);
				break;
			default:
			case 1:
				$isDefault = !isset($data[$key]);
				break;
			}
		//进行填充操作
		if ($isDefault)
			{
			$data[$key] = $value;
			}
		}
	return $data;
}

//ggzhu 2010-6-18
//对指定的数组元素进行函数调用处理其值
//函数调用通过字符串描述
//$script 描述字符串,多个键之间以","号分隔
//	键=函数名,....
//	如：pw=md5,name=trim
function array_func($data, $script)
{
	$scripts = explode(',', $script);
	foreach ($scripts as $v)
		{
		if (empty($v))
			{
			continue;
			}
		$args = explode('=', $v);
		if (count($args) < 2)
			{
			continue;
			}
		$field = $args[0];
		$func = $args[1];
		if (!function_exists($func))
			{
			continue;
			}
		if (!isset($data[$field]))
			{
			continue;
			}
		$data[$field] = $func($data[$field]);
		}
	return $data;
}
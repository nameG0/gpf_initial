<?php
/*
ggzhu 2010-6-18
对数组进行检查，检查规则用字符串描述
*/

//arg
//$script 描述字符串，格式：
//	检查类型:字段,...:错误提示
//	如：not_empty:userid,username:数据不完整
//	一行一单位
//	对于一些需要附加参数的检查类型，如正则，需要附加正则表达式，一般以"-"为分隔符把附加参数附于字段后
//	如: regexp:number-^\d+$:number只能为数字
//	附加参数分隔符一般为"-",但可能个别检查函数会自定义不同的分隔符，需参考具体检查函数的说明

//return(false/string)
//返回值比较特别，当所有检查都通过时，返回false，当某个检查不通过时，返回其错误提示，如没有错误显示，则返回true
//这样，就可以直接用 if() 来处理错误了，个人认为这种形式较方便使用

//note
//检查函数命名规则为：_check_关键字($data, $field),如 _check_not_empty($data, $field)
//本函数调用检查函数时会传入两个参数：$data 待检查的数组，$field 需检查字段,检查函数如检查通过，则返回true，否则返回false
//如检查函数需附加参数，附加参数的分隔符不能为":"及","
//如需要，用户可自己义自己的检查函数
function array_check($data, $script)
{
	$scripts = cut_line($script, 1, 1);
	foreach ($scripts as $v)
		{
		$line = explode(':', $v);
		if (count($line) > 1)
			{
			$func = '_check_' . $line[0];
			if (!function_exists($func))
				{
				continue;
				}
			$fields = explode(',', $line[1]);
			foreach ($fields as $f)
				{
				//检查不通过时进行处理
				if (!$func($data, $f))
					{
					if (!empty($line[2]))
						{
						return $line[2];
						}
					return true;
					}
				}
			}
		}
	return false;
}

////////////////////////////////////////
////		下面以_check_起头的函数为默认支持的检查函数
////////////////////////////////////////
function _check_not_empty($data, $field)
{
	return !empty($data[$field]);
}

function _check_isset($data, $field)
{
	return isset($data[$field]);
}

//输入是否为数字(字符串数字也被认为是数字)
function _check_is_int($data, $field)
{
	if (!isset($data[$field]))
		{
		return false;
		}
	return preg_match('/^\d+$/', $data[$field]) === 1;
}

//正则检查
//$arg field-正则表达式
function _check_regexp($data, $arg)
{
	$args = explode('-', $arg);
	if (count($args) < 2)
		{
		return false;
		}
	$field = $args[0];
	$regexp = '/' . $args[1] . '/';
	if (!isset($data[$field]))
		{
		return false;
		}
	return preg_match($regexp, $data[$field]) === 1;
}

//是否唯一
//$arg field-table[-field]
//	table表名，[-field]表对应字段，如不设此值，则默认同field
//note 需要数据库支持
function _check_unique($data, $arg)
{
	if (!$db = obj('db'))
		{
		return false;
		}
	$args = explode('-', $arg);
	if (count($args) < 2)
		{
		return false;
		}
	$field = $args[0];
	$table = $args[1];
	$table_field = $field;
	if (!empty($args[2]))
		{
		$table_field = $args[2];
		}
	if (!isset($data[$field]))
		{
		return false;
		}
	$sql = "SELECT count(*) AS `sum` FROM `{$table}` WHERE `{$table_field}` = '{$data[$field]}'";
	$r = $db->find($sql);
	return $r['sum'] == 0;
}

//外键验证
//$arg 同_check_unique()
//note 需数据库支持
function _check_from($data, $arg)
{
	if (!$db = obj('db'))
		{
		return false;
		}
	$args = explode('-', $arg);
	if (count($args) < 2)
		{
		return false;
		}
	$field = $args[0];
	$table = $args[1];
	$table_field = $field;
	if (!empty($args[2]))
		{
		$table_field = $args[2];
		}
	if (!isset($data[$field]))
		{
		return false;
		}
	$sql = "SELECT count(*) AS `sum` FROM `{$table}` WHERE `{$table_field}` = '{$data[$field]}'";
	$r = $db->find($sql);
	return $r['sum'] > 0;
}

//是否在某个数组范围之内
//$arg field-ok1-ok2...
function _check_in($data, $arg)
{
	$args = explode('-', $arg);
	if (count($args) < 2)
		{
		return false;
		}
	$field = $args[0];
	unset($args[0]);
	if (!isset($data[$field]))
		{
		return false;
		}
	return in_array($data[$field], $args);
}

//不能等于指定的值
//$arg 同_check_in()
function _check_not_in($data, $arg)
{
	$args = explode('-', $arg);
	if (count($args) < 2)
		{
		return false;
		}
	$field = $args[0];
	unset($args[0]);
	if (!isset($data[$field]))
		{
		return false;
		}
	return !in_array($data[$field], $args);
}
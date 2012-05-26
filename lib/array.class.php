<?php
/*
ggzhu 2010-6-18
version:2010-11-28 14:58:42
对数组进行指定操作，操作规则用字符串描述

arrayo 中的 o 表示 operate[操作]
*/

//arg:
//$data	所需操作的数组
//$script 描述字符串
//$error	检查不通过的错误信息
//return(bool) 检查通过=true,反之=false
//$script格式：
	//字段:key=val,...
	//各种检查以","号分隔,每字段一行,error对应字段通用错误提示,key_error为各检查自身的错误提示
	//如:userid:empty=false,int=true,error=userid不合法,int_error=userid须为数字
	//意为:userid字段不通为空且为数字,int_error为int检查不通过时的错误提示
	//val格式参考具体检查函数的说明
//note
//处理函数接口:func($data, $field, $arg, &$msg)
	//$data 需检查的数组,如函数需改变数组值,如填充函数,可用 &$data
	//$field	当前字段
	//$arg	处理参数,如 int=true
	//&$msg	处理函数默认错误信息
//操作函数命名规则为：__o_名称,如 __o_empty
//如需中断操作则返回 false,否则返回 true
//如需要，用户可自己义自己的检查函数
function arrayo(&$data, $script, &$error = '')
{
	$data = (array)$data;
	$field = array();
	//把描述字符格式化为 field => array(key=>val ...) 的格式
	$script = str_replace("\r\n", "\n", $script);
	$script = explode("\n", $script);
	$script = array_filter(array_map('trim', $script));
	foreach ($script as $k => $v)
		{
		list($name, $tmp) = explode(":", $v);
		$other = explode(",", $tmp);
		$arg = array();
		foreach ($other as $k => $v)
			{
			list($key, $val) = explode("=", $v);
			$arg[$key] = $val;
			}
		$field[$name] = $arg;
		}
	//检查数组
	foreach ($field as $name => $args)
		{
		foreach ($args as $key => $val)
			{
			$msg = "";
			$func = "__o_{$key}";
			if (!function_exists($func))
				{
				continue;
				}
			$ret = $func($data, $name, $args, $msg);
			if (!$ret)
				{
				$error = $args["{$key}_error"] ? $args["{$key}_error"] : ($args['error'] ? $args['error'] : $msg);
				return false;
				}
			}
		}
	return true;
}

////////////////////////////////////////
////		下面以__o_起头的函数为默认支持的检查函数
////////////////////////////////////////
function __o_empty($data, $field, $arg, &$msg)
{
	$is_empty = empty($data[$field]);
	$msg = $field . ('true' == $arg['empty'] ? "必须为空" : "不能为空");
	return 'true' == $arg['empty'] ? $is_empty : !$is_empty;
}
function __o_isset($data, $field, $arg, &$msg)
{
	$is_set = isset($data[$field]);
	$msg = ('true' == $arg['isset'] ? "" : '不') . "要求{$field}";
	return 'true' == $arg['isset'] ? $is_set : !$is_set;
}
//输入是否为数字(字符串数字也被认为是数字)
function __o_int($data, $field, $arg, &$msg)
{
	$is_int = preg_match('/^\d+$/', $data[$field]) === 1;
	$msg = $field . ('true' == $arg["int"] ? '必须' : '不能') . "为数字";
	return 'true' == $arg['int'] ? $is_int : !$is_int;
}
//正则检查
function __o_reg($data, $field, $arg, &$msg)
{
	$msg = "{$field}检查不通过";
	return preg_match("/{$arg['reg']}/", $data[$field]) === 1;
}
//值唯一
//unique=table[.field]
//	table表名，[.field]表对应字段，如不设此值，则默认同field,如 user.userid
//note 需要数据库支持
function __o_unique($data, $field, $arg, &$msg)
{
	$msg = "{$field}已存在";
	if (!$db = obj('db'))
		{
		i('无法读取db类', ierror);
		return false;
		}
	$tmp = explode('.', $arg['unique']);
	$table = $tmp[0];
	$table_field = $tmp[1] ? $tmp[1] : $field;
	if (!isset($data[$field]))
		{
		return false;
		}
	$sql = "SELECT count(*) AS `sum` FROM `{$table}` WHERE `{$table_field}` = '{$data[$field]}'";
	$r = $db->find($sql);
	return $r['sum'] == 0;
}

//外键验证
//$arg 同__o_unique()
//note 需数据库支持
function __o_from($data, $field, $arg, &$msg)
{
	$msg = "{$data[$field]}不存在";
	if (!$db = obj('db'))
		{
		i('无法读取db类', ierror);
		return false;
		}
	$tmp = explode('.', $arg['unique']);
	$table = $tmp[0];
	$table_field = $tmp[1] ? $tmp[1] : $field;
	$args = explode('-', $arg);
	if (!isset($data[$field]))
		{
		return false;
		}
	$sql = "SELECT count(*) AS `sum` FROM `{$table}` WHERE `{$table_field}` = '{$data[$field]}'";
	$r = $db->find($sql);
	return $r['sum'] > 0;
}

//是否在某个数组范围之内
//in=1 2 3 4 (以空格分隔)
function __o_in($data, $field, $arg, &$msg)
{
	$msg = "{$field}超出范围";
	$limit = explode(" ", $arg['in']);
	if (!isset($data[$field]))
		{
		return false;
		}
	return in_array($data[$field], $limit);
}

//不能等于指定的值
//not_in=同__o_in()
function __o_not_in($data, $field, $arg, &$msg)
{
	$msg = "{$field}含有不允许的值";
	$limit = explode(" ", $arg['in']);
	if (!isset($data[$field]))
		{
		return false;
		}
	return !in_array($data[$field], $limit);
}

//填充操作
//md5加密
//md5
function __o_md5(&$data, $field)
{
	if ($data[$field])
		{
		$data[$field] = md5($data[$field]);
		}
	return true;
}
//调用函数
//func=函数名
function __o_func(&$data, $field, $arg)
{
	$func = $arg['func'];
	if ($data[$field] && function_exists($func))
		{
		$data[$field] = $func($data[$field]);
		}
	return true;
}
//not isset 默认值
//ifnset=值
function __o_ifnset(&$data, $field, $arg)
{
	if (!isset($data[$field]))
		{
		$data[$field] = $arg['ifnset'];
		}
	return true;
}
//empty 默认值
//ifempty=值
function __o_ifempty(&$data, $field, $arg)
{
	if (empty($data[$field]))
		{
		$data[$field] = $arg['ifempty'];
		}
	return true;
}
//__o_ifempty 的别名
//default=值
function __o_default(&$data, $field, $arg)
{
	$arg['ifempty'] = $arg['default'];
	return __o_ifempty($data, $field, $arg);
}
//字符串长度
//len=长度
function __o_len($data, $field, $arg)
{
	$len = strlen($data[$field]);
	return $len == $arg['len'];
}
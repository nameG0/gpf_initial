<?php
/**
 * GPF 函数
 * 
 * @package default
 * @filesource
 */
//============================== inc
$GLOBALS['gpf_inc'] = array(); //保存已加载过的文件标记。
/**
 * 把一个文件路径设为已加载。
 * @param string $path 文件绝对路径。
 */
function _gpf_set_inc($path)
{//{{{
	if (isset($path[33]))
		{
		//长度超过 32 位转为 md5
		$path = md5($path);
		}
	$GLOBALS['gpf_inc'][$path] = true;
}//}}}
/**
 * 检测一个文件路径是否已加载
 * @param string $path 文件绝对路径。
 * @return bool
 */
function gpf_is_inc($path)
{//{{{
	if (isset($path[33]))
		{
		//长度超过 32 位转为 md5
		$path = md5($path);
		}
	return isset($GLOBALS['gpf_inc'][$path]);
}//}}}
/**
 * 单次加载(require_once)
 * @param string $path 文件绝对路径
 */
function gpf_inc($path)
{//{{{
	if (!gpf_is_inc($path))
		{
		require $path;
		_gpf_set_inc($path);
		}
}//}}}
//============================== obj
$GLOBALS['gpf_obj'] = array(); //保存对象实例。
/**
 * 对应索引是否已存在
 */
function gpf_is_obj($name)
{//{{{
	return isset($GLOBALS['gpf_obj'][$name]);
}//}}}
/**
 * 对象实例保存区（单例）
 * @param null|obj NULL表示读，否则表示写
 */
function gpf_obj($name, $obj = NULL)
{//{{{
	if (is_null($obj))
		{
		return $GLOBALS['gpf_obj'][$name];
		}
	else
		{
		$GLOBALS['gpf_obj'][$name] = $obj;
		}
}//}}}
//============================== shutdown
/**
 * GPF 注册了 shutdown_function，因此需要注册此函数使用此函数注册。
 * 增加页面结束前调用的函数,用于追加需要在页面结束时才能写入的数据，比如页面运行时间。
 * @param mixed $callback {NULL: $name 就是对应 callback, false:删除 $name 的注册, callback:以 $name 为索引注册}
 */
function gpf_shutdown($name, $callback = NULL)
{//{{{
	$gk = 'gpf_shutdown_hook';
	if (is_null($callback))
		{
		$GLOBALS[$gk][] = $name;
		return ;
		}
	if (false === $callback)
		{
		unset($GLOBALS[$gk][$name]);
		return ;
		}
	$GLOBALS[$gk][$name] = $callback;
}//}}}
$GLOBALS['gpf_shutdown_hook'] = array();
/**
 * 注册到 register_shutdown_function 的处理函数
 */
function _gpf_shutdown_function()
{//{{{
	$gk = 'gpf_shutdown_hook';
	//ggzhu@2012-01-30 若脚本被 Fatal error 中断， php 不会调用 set_error_handler 注册的函数处理。
	$error_last = error_get_last();
	//若没有 Fatal 中断 $error_last = null.
	if ($error_last)
		{
		gpf_log($error_last['message'], $error_last['type'], $error_last['file'], $error_last['line']);
		}

	//调用持载的函数
	foreach ($GLOBALS[$gk] as $v)
		{
		call_user_func($v);
		}

	//处理错误日志
	_gpf_log_flush();
}//}}}
//============================== log
//错误等级
define('GPF_LOG_SYSTEM', 'SYSTEM'); 	//系统崩聩，如数据库无法链接
define('GPF_LOG_ERROR', 'ERROR');	//系统错误，如参数非法
define('GPF_LOG_WARN', 'WARN');		//警告信息，如调用废弃函数
define('GPF_LOG_FLOW', 'FLOW');		//流程信息，如是否需要更新html文件
define('GPF_LOG_SQL', 'SQL');		//数据库查错出错
define('GPF_LOG_INFO', 'INFO');	//普通信息
define('GPF_LOG_DEBUG', 'DEBUG');	//临时调试信息
define('GPF_LOG_DUE', 'DUE');		//调用过期（已废弃）的函数或代码
define('GPF_LOG_INPUT', 'INPUT');	//输入数据非法
define('GPF_LOG_NOTEXI', 'NOTEXI');	//数据不存在错误
//用户自处理函数接口
$GLOBALS['gpf_log_user_func_callback'] = NULL;
//从浏览器输出的错误等级及接口
$GLOBALS['gpf_log_output_level'] = array(
	GPF_LOG_SYSTEM => true,
	GPF_LOG_ERROR => true,
	GPF_LOG_WARN => true,
	GPF_LOG_FLOW => true,
	GPF_LOG_SQL => true,
	GPF_LOG_INFO => true,
	GPF_LOG_DEBUG => true,
	GPF_LOG_INPUT => true,
	GPF_LOG_NOTEXI => true,
	GPF_LOG_DUE => true,
	E_ERROR           => true,
	E_WARNING         => true,
	E_PARSE           => true,
	E_NOTICE          => true,
	E_CORE_ERROR      => true,
	E_CORE_WARNING    => true,
	E_COMPILE_ERROR   => true,
	E_COMPILE_WARNING => true,
	E_USER_ERROR      => true,
	E_USER_WARNING    => true,
	E_USER_NOTICE     => true,
	E_STRICT          => true,
);
//各等级信息的输出样式
$GLOBALS['gpf_log_style'] = array(
	GPF_LOG_SYSTEM => "background-color:red;color:yellow",
	GPF_LOG_ERROR => "color:red",
	GPF_LOG_WARN => "background-color:yellow",
	GPF_LOG_FLOW => "border:green solid 1px",
	GPF_LOG_SQL => "color:red",
	GPF_LOG_INFO => "",
	GPF_LOG_DEBUG => "color:red",
	GPF_LOG_INPUT => "color:red",
	GPF_LOG_NOTEXI => "color:red",
	GPF_LOG_DUE => "background-color:yellow",
	E_WARNING => "color:red",
	E_ERROR => "color:red",
	E_COMPILE_ERROR => "color:red",
	E_CORE_ERROR => "color:red",
);
//php本身的错误等级
$GLOBALS['gpf_log_txt'] = array(
	GPF_LOG_SYSTEM => GPF_LOG_SYSTEM,
	GPF_LOG_ERROR => GPF_LOG_ERROR,
	GPF_LOG_WARN => GPF_LOG_WARN,
	GPF_LOG_FLOW => GPF_LOG_FLOW,
	GPF_LOG_SQL => GPF_LOG_SQL,
	GPF_LOG_INFO => GPF_LOG_INFO,
	GPF_LOG_DEBUG => GPF_LOG_DEBUG,
	GPF_LOG_INPUT => GPF_LOG_INPUT,
	GPF_LOG_NOTEXI => GPF_LOG_NOTEXI,
	GPF_LOG_DUE => GPF_LOG_DUE,
	E_ERROR           => 'Error',
	E_WARNING         => 'Warning',
	E_PARSE           => 'Parsing Error',
	E_NOTICE          => 'Notice',
	E_CORE_ERROR      => 'Core Error',
	E_CORE_WARNING    => 'Core Warning',
	E_COMPILE_ERROR   => 'Compile Error',
	E_COMPILE_WARNING => 'Compile Warning',
	E_USER_ERROR      => 'User Error',
	E_USER_WARNING    => 'User Warning',
	E_USER_NOTICE     => 'User Notice',
	E_STRICT          => 'Runtime Notice'
);
$GLOBALS['gpf_log_data'] = array(); //保存信息数据
$GLOBALS['gpf_log_is_print'] = true; //设置是否在页面尾部输出 debug 信息
/**
 * 添加日志信息
 * @param string $msg 信息内容
 * @param int $level 信息等级
 * @param string $file 所在文件(__FILE__)
 * @param int $line 所在行号(__LINE__)
 * @param string $func 所在函数(__FUNCTION__)
 */
function gpf_log($msg, $level = GPF_LOG_INFO, $file = '', $line = 0, $func = '')
{//{{{
	$gk = 'gpf_log_data';
	$GLOBALS[$gk][] = array(
		'msg' => $msg,
		'level' => $level,
		"file" => $file,
		"line" => $line,
		"func" => $func ? $func . '()' : '',
	);
}//}}}
/**
 * 设置页面结束时是否输出日志信息
 */
function gpf_log_is_print($is_print)
{//{{{
	$gk = 'gpf_log_is_print';
	$GLOBALS[$gk] = $is_print ? true : false;
}//}}}
/**
 * 接管或重置php的错误处理
 */
function gpf_log_php($is_register = true)
{//{{{
	$err_level = E_ERROR | E_WARNING | E_PARSE;
	return $is_register ? set_error_handler('_gpf_phperror', $err_level) : restore_error_handler();
}//}}}
/**
 * 用于接管php的错误处理
 */
function _gpf_phperror($errno, $errstr, $errfile, $errline)
{//{{{
	if (error_reporting())
		{
		gpf_log($errstr, $errno, $errfile, $errline);
		}
}//}}}
/**
 * 输出信息到浏览器
 */
function _gpf_log_flush()
{//{{{
	$gk_callback = 'gpf_log_user_func_callback';
	$gk_data = 'gpf_log_data';
	$gk_is_print = 'gpf_log_is_print';
	$gk_output_level = 'gpf_log_output_level';
	$gk_style = 'gpf_log_style';
	$gk_txt = 'gpf_log_txt';

	//用户自处理接口
	if (!empty($GLOBALS[$gk_callback]) && !empty($GLOBALS[$gk_data]))
		{
		call_user_func($GLOBALS[$gk_callback], $GLOBALS[$gk_data]);
		}

	if (empty($GLOBALS[$gk_output_level]) || !$GLOBALS[$gk_is_print])
		{
		return ;
		}
	$count = count($GLOBALS[$gk_data]);
	echo "<br/><font color=blue>Infomation:({$count})</font><hr/><div>\n";
	foreach ($GLOBALS[$gk_data] as $k => $v)
		{
		if (empty($GLOBALS[$gk_output_level][$v['level']]))
			{
			continue;
			}
		$br = isset($v['msg'][100]) ? '<br/><br/>' : '<br/>';	//长句加多个换行，更容易查看
		echo "<span style=\"", $GLOBALS[$gk_style][$v['level']], "\">{$v['func']} {$v['msg']} [", $GLOBALS[$gk_txt][$v['level']], "] {$v['file']}:{$v['line']}</span>{$br}\n";
		}
	echo "<br/><br/><br/></div>\n";
}//}}}

//============================== error ==============================
$GLOBALS['gpf_error_func'] = 'exit'; //callback|NULL 操作出错时自动进行提示的提示函数。
$GLOBALS['gpf_error'] = ''; //错误提示信息。
$GLOBALS['gpf_is_pass'] = false; //标记流程是否正常。
$GLOBALS['gpf_pass_num'] = 0; //流程计数器，每调用一次 start() 加1.
/**
 * 一个流程开始时调用
 */
function gpf_pnew()
{//{{{
	$gk_num = 'gpf_pass_num';
	$gk_error = 'gpf_error';
	$gk_is_pass = 'gpf_is_pass';

	$GLOBALS[$gk_num]++;
	if (1 === $GLOBALS[$gk_num])
		{
		$GLOBALS[$gk_is_pass] = true;
		$GLOBALS[$gk_error] = '';
		}
}//}}}
/**
 * 一个流程结束后调用
 */
function gpf_pend()
{//{{{
	$gk_num = 'gpf_pass_num';
	$gk_is_pass = 'gpf_is_pass';
	$GLOBALS[$gk_num]--;
	if ($GLOBALS[$gk_num] < 1)
		{
		$GLOBALS[$gk_num] = 0;
		$GLOBALS[$gk_is_pass] = false;
		}
}//}}}
/**
 * 检查流程是否正常，不正常直接提示并中断程序运行。
 */
function gpf_pcheck()
{//{{{
	$gk_is_pass = 'gpf_is_pass';
	if (!$GLOBALS[$gk_is_pass])
		{
		gpf_err("流程出错");
		}
}//}}}
/**
 * 进行错误提示
 * @param string $error 错误提示信息。
 * @param string 所有文件(__FILE__)
 * @param int 所有行号(__LINE__)
 * @param string 所在函数(__FUNCTION__)
 */
function gpf_err($error, $file = '', $line = 0, $func = '')
{//{{{
	$gk_error = 'gpf_error';
	$gk_is_pass = 'gpf_is_pass';
	$gk_error_func = 'gpf_error_func';

	$GLOBALS[$gk_error] = $error;
	$GLOBALS[$gk_is_pass] = false;
	gpf_log("(GERR){$error}", GPF_LOG_FLOW, $file, $line, $func);

	$error_func = $GLOBALS[$gk_error_func];
	if ($error_func)
		{
		if (!is_callable($error_func))
			{
			exit($error);
			}
		call_user_func($error_func, $error);
		}
}//}}}
/**
 * 取错误提示内容。
 */
function gpf_err_get()
{//{{{
	$gk = 'gpf_error';
	return $GLOBALS[$gk_error];
}//}}}
/**
 * 设置自动提示函数
 * @param NULL|callback $func_name 错误处理函数，NULL 表示自动提示函数。
 */
function gpf_err_func($func_name)
{//{{{
	$gk_error_func = 'gpf_error_func';
	if (is_null($func_name))
		{
		$func_name = 'exit';
		}
	$GLOBALS[$gk_error_func] = $func_name;
}//}}}

//============================== hook ==============================
$GLOBALS['gpf_obj_hook '] = array();
$GLOBALS['gpf_obj_callback '] = array();
/**
 * 实例化并返回 hook 对象。
 * @param string $mod_name 模块名。eg. member
 * @param string $class_name hook类名。eg. base -> h_base.class.php -> h_member_base
 */
function gpf_hook($mod_name, $class_name)
{//{{{
	$gk_hook = 'gpf_obj_hook';

	$class_name_full = "h_{$mod_name}_{$class_name}";
	if (isset($GLOBALS[$gk_hook][$class_name_full]))
		{
		return $GLOBALS[$gk_hook][$class_name_full];
		}
	if (!class_exists($class_name_full))
		{
		$_path = PHPCMS_ROOT . "{$mod_name}/hook/h_{$class_name}.class.php";
		if (!is_file($_path))
			{
			gpf_log("hook 类不存在[{$_path}]", GPF_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__);
			$GLOBALS[$gk_hook][$class_name_full] = false;
			return false;
			}
		require $_path;
		if (!class_exists($class_name_full))
			{
			gpf_log("hook 类未定义[{$class_name_full}]", GPF_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__);
			$GLOBALS[$gk_hook][$class_name_full] = false;
			return false;
			}
		}
	$obj = new $class_name_full();
	$GLOBALS[$gk_hook][$class_name_full] = $obj;
	return $obj;
}//}}}
/**
 * 加载并返回 callback 对象数组。
 * <pre>
 * 提供给 hook 类使用，调用格式：$list = ghook::load(mod_name, __CLASS__, __FUNCTION__);
 * </pre>
 * @param string $class_name hook 类完整类名，一般使用 __CLASS__。eg. h_member_base
 */
function gpf_hook_load($mod_name, $class_name, $func_name)
{//{{{
	$gk_callback = 'gpf_obj_callback';

	if (!isset($GLOBALS[$gk_callback]["{$mod_name}/{$class_name}"]))
		{
		$GLOBALS[$gk_callback]["{$mod_name}/{$class_name}"] = _gpf_load_callback($mod_name, $class_name);
		}
	$obj_list = array();
	foreach ($GLOBALS[$gk_callback]["{$mod_name}/{$class_name}"] as $_obj)
		{
		if (method_exists($_obj, $func_name))
			{
			$obj_list[] = $_obj;
			}
		}
	return $obj_list;
}//}}}
/**
 * 加载挂钩模块的 callback 对象
 * @param string hook 模块名
 * @param string $class_name self::load() 同名参数
 * @param array 对象列表
 */
function _gpf_load_callback($mod_name, $class_name)
{//{{{
	$mod_callback = _gpf_hook_mod_file($mod_name);
	if (!$mod_callback)
		{
		return array();
		}
	$class_name_short = substr($class_name, 2);
	$obj_callback = array();
	foreach ($mod_callback as $_mod)
		{
		$class_name_full = "hc_{$_mod}_{$class_name_short}";
		if (!class_exists($class_name_full))
			{
			$_path = PHPCMS_ROOT . "{$_mod}/hook/hc_{$class_name_short}.class.php";
			if (!is_file($_path))
				{
				gpf_log("callback 文件不存在[{$_path}]", GPF_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__);
				continue;
				}
			require $_path;
			if (!class_exists($class_name_full))
				{
				gpf_log("callback 类未定义[{$class_name_full}]", GPF_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__);
				continue;
				}
			}
		$obj_callback[] = new $class_name_full();
		}
	return $obj_callback;
}//}}}
/**
 * 加载模块 hook 目录 mod 文件
 * @param array 挂钩的模块列表。
 */
function _gpf_hook_mod_file($mod_name)
{//{{{
	$_path = PHPCMS_ROOT . "{$mod_name}/hook/mod";
	if (!is_file($_path))
		{
		return array();
		}
	$mod_callback = file($_path);
	$mod_callback = array_filter(array_map('trim', $mod_callback));
	return $mod_callback;
}//}}}

//============================== url ==============================
$GLOBALS['gpf_url_count'] = 0; //替换词计数器
$GLOBALS['gpf_url_name'] = array(); //保存URL替换词
$GLOBALS['gpf_url_search'] = array(); //方便URL替换词
$GLOBALS['gpf_url_replace'] = array(); //方便URL替换词
$GLOBALS['gpf_url_func'] = array(); //保存URL回调函数。
/**
 * 用于输出完整的URL
 * @param string $url 用 @ 表示替换词，用 {func_name:args} 表示执行函数
 */
function gpf_url($url)
{//{{{
	$gk_search = 'gpf_url_search';
	$gk_replace = 'gpf_url_replace';

	return 'http://' . str_replace($GLOBALS[$gk_search], $GLOBALS[$gk_replace], $url);
}//}}}
/**
 * 设置URL替换词
 */
function gpf_url_name($name, $value)
{//{{{
	$gk_name = 'gpf_url_name';
	$gk_search = 'gpf_url_search';
	$gk_replace = 'gpf_url_replace';

	$name = '@' . $name;
	if (isset($GLOBALS[$gk_name][$name]))
		{
		$k = $GLOBALS[$gk_name][$name];
		$GLOBALS[$gk_search][$k] = $name;
		$GLOBALS[$gk_replace][$k] = $value;
		return ;
		}
	$k = $GLOBALS[$gk_count]++;
	$GLOBALS[$gk_name][$name] = $k;
	$GLOBALS[$gk_search][$k] = $name;
	$GLOBALS[$gk_replace][$k] = $value;
}//}}}
function gpf_url_func($name, $value)
{//{{{
	//todo 未实现
	//开始和结束标记使用类静态变量定义（即 { 和 }）
	//因为模板引擎常用占用 { 和 }，所以可考虑改用 [ 和 ]
}//}}}

//============================== get/post ==============================
$GLOBALS['gpf_unadds'] = false; //不进行引号过滤 todo 未实现
$GLOBALS['gpf_unadds_once'] = false; //当次不进行引号过滤 todo 未实现
//如果取不到数据，直接返回默认值，无需过滤。
//也有一种可能性：过滤函数是 MD5 这类，而默认值又希望使用明文。所以默认值也有需要过滤的。
//所以取值和过滤引号和过滤函数作为一个函数。返回默认值为另一个函数。
//应该先用过滤函数过滤再过滤引号，因为不排除过滤函数会加入引号。
/**
 * @param null|mixed $name 若为 NULL 则返回整个 $data
 * @param string $def_if {!isset, empty}
 */
function _gpf_input_get($data, $name, $def_val, $def_if)
{//{{{
	if (is_null($name))
		{
		return $data;
		}
	if (!isset($data[$name]))
		{
		return $def_val;
		}
	$value = $data[$name];
	if ('empty' === $def_if && empty($value))
		{
		$value = $def_val;
		}
	return $value;
}//}}}
//调用过滤函数
//强制类型转换可以这样写：(int), (array),注意要用小写字母。
//array_filter(array_map('intval', (array)$arr)) 可以这样写：(array),intval,@array_filter
function _gpf_input_filter($value, $filter)
{//{{{
	if ($filter)
		{
		$list = explode(",", $filter);
		foreach ($list as $v)
			{
			//函数名前加@表示函数参数要求为数组。比如 array_filter
			if ('@' === $v[0])
				{
				$v = substr($v, 1);
				$value = $v($value);
				}
			else if ('(' === $v[0])
				{
				//强制类型转换使用 () 表示，比如 (array)
				if ('(array)' === $v)
					{
					$value = (array)$value;
					}
				else if ('(int)' === $v)
					{
					$value = (int)$value;
					}
				else if ('(string)' === $v)
					{
					$value = (string)$value;
					}
				}
			else
				{
				$value = _gpf_input_call($value, $v);
				}
			}
		}
	return $value;
}//}}}
function _gpf_input_call($value, $func_name)
{//{{{
	if (!is_array($value))
		{
		return $func_name($value);
		}
	foreach ($value as $k => $v)
		{
		$value[$k] = _gpf_input_call($v, $func_name);
		}
	return $value;
}//}}}
//过滤引号
function _gpf_input_adds($value)
{//{{{
	if (!get_magic_quotes_gpc())
		{
		$value = gpf_adds($value);
		}
	return $value;
}//}}}
/**
 * 取 $_GET 数据
 * @param null|mixed 所取索引，为 NULL 表示取整个数组。
 */
function gpf_get($name = NULL, $filter = 'gpf_html', $def_val = NULL, $def_if = '!isset')
{//{{{
	//取数据（包括处理默认值）
	$value = _gpf_input_get($_GET, $name, $def_val, $def_if);
	if (is_null($value))
		{
		return $value;
		}
	//过滤函数
	$value = _gpf_input_filter($value, $filter);
	//过滤引号
	$value = _gpf_input_adds($value);
	return $value;
}//}}}
//取 $_POST 数据
function gpf_post($name = NULL, $filter = 'gpf_html', $def_val = NULL, $def_if = '!isset')
{//{{{
	//取数据（包括处理默认值）
	$value = _gpf_input_get($_POST, $name, $def_val, $def_if);
	if (is_null($value))
		{
		return $value;
		}
	//过滤函数
	$value = _gpf_input_filter($value, $filter);
	//过滤引号
	$value = _gpf_input_adds($value);
	return $value;
}//}}}
//取 COOKIE 数据
function gpf_cookie($name = NULL, $filter = 'gpf_html', $def_val = NULL, $def_if = '!isset')
{//{{{
	//取数据（包括处理默认值）
	$value = _gpf_input_get($_COOKIE, $name, $def_val, $def_if);
	if (is_null($value))
		{
		return $value;
		}
	//过滤函数
	$value = _gpf_input_filter($value, $filter);
	//过滤引号
	$value = _gpf_input_adds($value);
	return $value;
}//}}}

//============================== module ==============================
/**
 * 初始化模块
 * 即加载模块的 include/init.inc.php
 */
function gpf_mod_init($mod_name)
{//{{{
	$path = GPF_PATH_MODULE . "{$mod_name}/include/init.inc.php";
	if (gpf_is_inc($path))
		{
		return true;
		}
	if (!is_file($path))
		{
		gpf_log("模块初始化文件不存在[{$path}]", GPF_LOG_WARN, '', 0, __FUNCTION__);
		return false;
		}
	gpf_inc($path);
	gpf_log($mod_name, GPF_LOG_INFO, '', 0, __FUNCTION__);
	return true;
}//}}}
/**
 * 计算模块下文件的绝对路径
 * @param string $path 模块下文件路径。eg. include/common.inc.php
 * @return string 对应文件的绝对路径。
 */
function gpf_mod_path($mod_name, $path)
{//{{{
	return GPF_PATH_MODULE . "{$mod_name}/{$path}";
}//}}}
/**
 * 单次包含模块内文件
 * @param string $path 模块文件路径, eg. abc.class.php
 */
function gpf_mod_inc($mod_name, $path)
{//{{{
	gpf_inc(GPF_PATH_MODULE . "{$mod_name}/{$path}");
}//}}}
/**
 * 加载模块 API 目录文件。
 * api 目录中的类使用 {mod_name}Api_{class_name} 为前序, 对应文件名为 {class_name}.class.php。
 * @param string $mod_name 模块名。
 * @param string $file_name 文件名，不含 .php 后序。eg. api.func, api.class
 */
function gpf_mod_api($mod_name, $file_name)
{//{{{
	$path = GPF_PATH_MODULE . "{$mod_name}/api/{$file_name}.php";
	gpf_inc($path);
	return _gpf_api_class($mod_name, $file_name);
}//}}}
/**
 * 若加载的 API 目录文件为类定义文件，则实例化。
 */
function _gpf_mod_api_class($mod_name, $file_name)
{//{{{
	if ('.class' !== substr($file_name, -6, 6))
		{
		return ;
		}
	$class_name = substr($file_name, 0, -6);
	$class_full = "{$mod_name}Api_{$class_name}";
	if (!gpf_is_obj($class_full))
		{
		gpf_obj_set($class_full, new $class_full());
		}
	return gpf_obj_get($class_full);
}//}}}

//============================== module#old ==============================
/**
 * 读取指定模块信息。
 *
 * @param string $mod 模块名。
 * @param NULL|string $key 信息名，NULL 表示返回所有信息，此时会返回数组。
 * @return mixed|false 若模块可用返回模块信息，否则返回 false
 */
function mod_info($mod, $key = NULL)
{//{{{
	//已读取的模块信息缓存在变量中。
	static $cache = array();

	//模块信息的命名规则为 {module_name}.info
	//模块信息保存在 {project_name_data}/module/ 下。

	if (!isset($cache[$mod]))
		{
		
		$path = GPF_PATH_DATA . "module/{$mod}.info";
		if (!is_file($path))
			{
			gpf_log("模块 {$mod} 未启用", GPF_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__);
			$cache[$mod] = false;
			}
		else
			{
			$mod_info = unserialize(file_get_contents($path));

			//生成 path_sour, path_inst 。
			$path_sour = GPF_PATH_SOUR . $mod . DS;
			$mod_info['path_sour'] = is_dir($path_sour) ? $path_sour : '';
			$path_inst = GPF_PATH_INST . $mod . DS;
			$mod_info['path_inst'] = is_dir($path_inst) ? $path_inst : '';

			$cache[$mod] = $mod_info;
			}
		}

	if (!is_null($key) && is_array($cache[$mod]))
		{
		return $cache[$mod][$key];
		}
	return $cache[$mod];
}//}}}

/**
 * 进行模块的 callback 操作。
 *
 * <code>
 * mod_callback('module'); //$action 默认使用 rp ,即与下面一至.
 * mod_callback('module', 'rp'); //rp(register path):返回注册在 {module} 模块下所有 callback 目录绝对路径(包括源与副本 callback 目录)
 * mod_callback('module', 'rm'); //rm(register module):返回注册在 {module} 模块下所有 callback 模块名.
 * mod_callback('module', 'p'); //p(path):返回 {module} 模块自己的 callback 目录绝对路径(包括源与副本的 callback 目录)
 * mod_callback('module', 'add', 'module_2'); //add:把 {module_2} 模块加入 {module} 模块的 callback 注册列表中.
 * mod_callback('module', 'del', 'module_2'); //del:把 {module_2} 模块从 {module} 模块的 callback 注册列表中删除.
 * </code>
 * <pre>
 * <b>返回值</b>
 * rp:array('{module}/sour' => {module}模块源目录 callback 绝对路径, '{module}/inst' => {module}模块副本目录 callback 绝对路径, ...);
 * p:同 rp 的返回值.
 * rm:array('{module_1}', '{module_2}', ...)
 * <b>说明</b>
 * 一个模块可能会有两个 callback 目录，一个是源目录中，一个在副本目录中。
 * </pre>
 * @param string $target 目标模块。
 * @param string|NULL $action 操作{add:注册, del:删除, NULL:查询}
 * @param string|NULL $register 注册模块。
 * @return array|bool 查询模块的 callback 时返回数组，其它操作返回 t/f 。
 */
function mod_callback($target, $action = 'rp', $register = NULL)
{//{{{
	//缓存模块的 callback 注册列表。[mod] => array(call_1, call_2, ...)
	static $cache = array();
	//缓存模块的 callback 目录绝对路径. [mod] => array("sour" => path, "inst" => path,)
	static $callback = array();

	//因为函数本身会使用 $action=p 读取模块的 callback 目录，所以优先处理。
	if ('p' == $action)
		{
		if (!isset($callback[$target]))
			{
			//使用 mod_info() 取得模块的源路径与副本路径。分别检查是否带有 callback 目录。
			$ModInfo = mod_info($target);
			if (!$ModInfo)
				{
				$callback[$target] = false;
				}
			else
				{
				$path = "{$ModInfo['path_sour']}callback" . DS;
				if ($ModInfo['path_sour'] && is_dir($path))
					{
					$callback[$target]["sour"] = $path;
					}
				$path = "{$ModInfo['path_inst']}callback" . DS;
				if ($ModInfo['path_inst'] && is_dir($path))
					{
					$callback[$target]["inst"] = $path;
					}
				}
			}
		return $callback[$target];
		}

	//------ 主要思路 ------
	//所有操作都先把目标模块的 callback 数据缓存在 $cache 变量中，修改及删除操作先修改变量中的数据，再持久化到文件中保存。
	//文件保存在 project_name_data/gpf/module/ 下，每个目标模块一个文件，文件内容为注册到此目标模块下的模块列表。
	//文件的命名规则为 {module_name}.callback
	//注册模块列表使用数组保存，保存到文件时用 serialize 序列化。
	//------------

	$callback_file_path = GPF_PATH_DATA . "module/{$target}.callback";
	if (!isset($cache[$target]))
		{
		if (is_file($callback_file_path))
			{
			$cache[$target] = unserialize(file_get_contents($callback_file_path));
			}
		else
			{
			$cache[$target] = array();
			}
		}

	switch ($action)
		{
		//callback注册。
		case "add":
			//避免重复注册
			if (is_string($register) && !in_array($register, $cache[$target]))
				{
				$cache[$target][] = $register;
				return file_put_contents($callback_file_path, serialize($cache[$target]));
				}
			return true;
			break;
		//删除callback注册
		case "delete":
			$seek = array_search($register, $cache[$target]);
			if (false !== $seek)
				{
				unset($cache[$target][$seek]);
				return file_put_contents($callback_file_path, serialize($cache[$target]));
				}
			return true;
			break;
		case "rm":
			//模块本身总是存在于 callback 注册列表中。
			$ret = $cache[$target];
			$ret[] = $target;
			return $ret;
			break;
		case "rp":
		default:
			$list = array();
			$c_list = $cache[$target];
			$c_list[] = $target;
			foreach ($c_list as $m)
				{
				if (!isset($callback[$m]))
					{
					$callback[$m] = mod_callback($m, 'p');
					}
				if (is_array($callback[$m]))
					{
					foreach ($callback[$m] as $k => $v)
						{
						$list["{$m}/{$k}"] = $v;
						}
					}
				}
			return $list;
			break;
		}
	log::add("参数超出预设范围", log::WARN, __FILE__, __LINE__, __FUNCTION__);
	return false;
}//}}}

/**
 * 注册或删除模块信息
 *
 * <pre>
 * <b>注册</b>
 * mod_setting('name', array('setting' => array(), ...)
 * 注册 name 模块。 $info 参数为模块的信息。
 * <b>删除</b>
 * mod_setting('name', NULL);
 * </pre>
 * @param string $mod 模块名。
 * @param array|NULL 模块信息，若为 NULL 表示删除模块信息。
 */
function mod_setting($mod, $info)
{//{{{
	$path = GPF_PATH_DATA . "module/{$mod}.info";
	if (is_null($info))
		{
		if (is_file($path))
			{
			unlink($path);
			}
		return ;
		}

	$info['name'] = $mod;
	$info_str = serialize($info);
	file_put_contents($path, $info_str);
}//}}}

//============================== mvc ==============================
/**
 * 加载读类(r_)Model
 * @param string $mod_name 模块名。eg. cms
 * @param string $class_name 类名，eg. content > r_content.class.php > r_cms_content
 */
function gpf_mod_rm($mod_name, $class_name)
{//{{{
	$class_full = "r_{$mod_name}_{$class_name}";
	if (gpf_is_obj($class_full))
		{
		return gpf_obj_get($class_full);
		}
	$path = GPF_PATH_MODULE . "{$mod_name}/model/r_{$class_name}.class.php";
	gpf_inc($path);
	gpf_obj_set($class_full, new $class_full());
	return gpf_obj_get($class_full);
}//}}}
/**
 * 加载写类(r_)Model
 * @param string $mod_name 模块名。eg. cms
 * @param string $class_name 类名，eg. content > w_content.class.php > w_cms_content
 */
function gpf_mod_wm($mod_name, $class_name)
{//{{{
	$class_full = "w_{$mod_name}_{$class_name}";
	if (gpf_is_obj($class_full))
		{
		return gpf_obj_get($class_full);
		}
	$path = GPF_PATH_MODULE . "{$mod_name}/model/w_{$class_name}.class.php";
	gpf_inc($path);
	gpf_obj_set($class_full, new $class_full());
	return gpf_obj_get($class_full);
}//}}}

/**
 * 加载模块 view 目录下的界面组件。
 */
function gpf_mod_v($mod, $name)
{//{{{
	$path = GPF_PATH_MODULE . "{$mod}/view/{$name}.php";
	if (!gpf_is_inc($path))
		{
		if (!is_file($path))
			{
			gpf_log("文件不存在 {$path}", GPF_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__);
			gpf_err("相关文件不存在", __FILE__, __LINE__, __FUNCTION__);
			return false;
			}
		gpf_inc($path);
		}
	if ('.class' === substr($name, -6, 6))
		{
		if ('_' === $name[0])
			{
			$class_name = "v__{$mod}_" . substr($name, 1, -6);
			}
		else
			{
			$class_name = "v_{$mod}_" . substr($name, 0, -6);
			}
		if (gpf_is_obj($class_name))
			{
			return gpf_obj_get($class_name);
			}
		if (!class_exists($class_name))
			{
			gpf_log("类不存在 {$class_name}", GPF_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__);
			gpf_err("相关文件不存在", __FILE__, __LINE__, __FUNCTION__);
			return false;
			}
		$obj = new $class_name();
		gpf_obj_set($class_name, $obj);
		return $obj;
		}
}//}}}


//============================== other ==============================
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
 * 计算运行时间
 * @param NULL|int $time {NULL:返回当前时间, int:计算当前时间与转入时间的间隔}
 * <code>
 * $t1 = gpf_time(); //存当前时间
 * sleep(1);
 * echo gpf_time($t1); //计算运行时间
 * </code>
 */
function gpf_time($time = NULL)
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
 * 更新 static 目录文件。
 * <pre>
 * 需定义常量：
 * GPF_STATIC_DIR :/public/static/ 目录路径。
 * </pre>
 * @param string $mod_name 模块名,为空表示全部复制（一般用于初始化）。
 */
function gpf_static($mod_name = '')
{//{{{
	gpf_log($mod_name, GPF_LOG_INFO, __FILE__, __LINE__, __FUNCTION__);
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
	gpf_log("{$mod} : {$file}", GPF_LOG_INFO, __FILE__, __LINE__, __FUNCTION__);
	$path = gmod::path($mod, "template/{$file}.tpl.php");
	if (!is_file($path))
		{
		gpf_log("模板不存在[{$path}]", GPF_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__);
		gpf_err("template not exists", __FILE__, __LINE__, __FUNCTION__);
		return false;
		}
	return $path;
}//}}}

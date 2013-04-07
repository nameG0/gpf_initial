<?php
/**
 * GPF(G0 PHP FW)简单框架
 * 
 * @package default
 * @filesource
 */
//============================== define ===============================
//缩短 DIRECTORY_SEPARATOR
(defined('DS') OR define('DS', DIRECTORY_SEPARATOR));
//默认gpf以一个模块的形式出现，所以可以定义默认的目录常量。
(defined('GPF_MODULE') OR define('GPF_MODULE', dirname(dirname(__FILE__)) . '/'));
//建议CONFIG目录放到module目录外
(defined('GPF_CONFIG') OR define('GPF_CONFIG', dirname(GPF_MODULE) . '/config/'));
//建议LIB目录放到module目录外
(defined('GPF_LIB') OR define('GPF_LIB', dirname(GPF_MODULE) . '/0lib/'));
//建议放在config目录中。
defined('GPF_FACTORY') OR define('GPF_FACTORY', GPF_CONFIG . 'gpf_factory/');
//debug模式开关
//GPF_DEBUG
//debug模式时生成的php临时文件存放路径
//GPF_DEBUG_PHP
//debug模式输出信息文件存放路径
//GPF_DEBUG_OUTPUT
//debug.js请求路径
//GPF_DEBUG_JS_SCRIPT
//调用gpfd_js处理请求的PHP文件访问路径（必须带有?号）
//GPF_DEBUG_JS_PHP
//断言测试模式开关
//GPF_TEST

//============================== inc ===============================
$GLOBALS['gpf_inc'] = array(); //保存已加载过的文件标记。
/**
 * 把一个文件路径设为已加载。
 * @param string $path 文件绝对路径。
 */
function gpf_set_inc($path)
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
	if (gpf_is_inc($path))
		{
		return ;
		}
	gpf_set_inc($path);
	//DEBUG模式处理
	if (defined('GPF_DEBUG') && true === GPF_DEBUG)
		{
		$path = gpf_debug($path);
		}
	require $path;
}//}}}
/**
 * 若开启DEBUG模式，使用此函数生成不能使用gpf_inc加载的文件路径
 */
function gpf_path($path)
{//{{{
	//DEBUG模式处理
	if (!defined('GPF_DEBUG') || true !== GPF_DEBUG)
		{
		return $path;
		}
	return gpf_debug($path);
}//}}}
//============================== obj ===============================
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
//=============================== load
/**
 * 加载func,class等定义类文件，若为class定义文件，传入$class_name可以顺便实例化（只实例化一次）
 * @param string $pathfull 待加载文件的绝对路径,但不需要最后的“.php”
 * @param string $class_name 顺便实例化的类名
 */
function gpf_load($pathfull, $class_name = '')
{//{{{
	$gk_obj = 'gpf_obj';

	if ($class_name)
		{
		if (isset($GLOBALS[$gk_obj][$class_name]))
			{
			return $GLOBALS[$gk_obj][$class_name];
			}
		//避免通过其它语句已加载了文件
		//只能通过类名避免重复加载，若加载函数定义文件而文件又使用了其它方式加载，就等着php报fatal错吧！
		if (class_exists($class_name))
			{
			$GLOBALS[$gk_obj][$class_name] = new $class_name();
			return $GLOBALS[$gk_obj][$class_name];
			}
		}
	//加载文件
	$pathfull = $pathfull . '.php';
	gpf_inc($pathfull);
	if ($class_name)
		{
		//如果需要实例化的类未定义，就等着php报fatal错误吧！
		$GLOBALS[$gk_obj][$class_name] = new $class_name();
		return $GLOBALS[$gk_obj][$class_name];
		}
}//}}}
//=============================== factory ===============================
/**
 * 简单的工厂函数
 * 使用 GPF_FACTORY 常量定义工厂配置文件目录, eg. /config/gpf_factory/
 * @param string $name 配置文件名 eg. db
 */
function gpf_factory($name)
{//{{{
	$obj_key = "gpf_factory/{$name}";
	if (gpf_is_obj($obj_key))
		{
		return gpf_obj($obj_key);
		}

	$error = ''; //不为空表示发生错误
	do
		{
		$path = GPF_FACTORY . $name . '.inc.php';
		if (!is_file($path))
			{
			$error = "配置文件不存在 {$path}";
			break;
			}
		$config = include $path;
		if (!is_array($config))
			{
			$error = "配置文件未正确返回数据 {$path}";
			break;
			}
		$target = $config['0target'];
		$func_name = $config['0func'];
		$dir = $config['0dir'];
		if (!$target || !$func_name)
			{
			$error = "配置文件缺少 0target 或 0func 配置项 {$path}";
			break;
			}
		$func_name = "cb_gpf_factory_{$func_name}";
		if (function_exists($func_name))
			{
			break;
			}
		//加载cb函数定义文件
		if (!$dir)
			{
			$dir = MODULE_PATH;
			}
		$cb_path = "{$dir}{$target}/0cb_gpf_factory/func.php";
		if (!is_file($cb_path))
			{
			$error = "cb文件不存在 {$cb_path}";
			break;
			}
		gpf_inc($cb_path);
		if (!function_exists($func_name))
			{
			$error = "cb函数未定义 {$func_name}";
			break;
			}
		}
	while (false);
	if ($error)
		{
		gpf_log($error, GPF_LOG_SYSTEM, __FILE__, __LINE__, __FUNCTION__);
		return gpf_err('数据未定义');
		}
	//运行到此处表示对应的cb函数已加载
	$GLOBALS[$gk][$name] = $func_name($config);
	return $GLOBALS[$gk][$name];
}//}}}
//=============================== event ===============================
//保存格式：[$name] => array($callback, $arg)
$GLOBALS['gpf_event'] = array();
/**
 * 提供一个全局的，在底部输出内容（比如：JS）的挂钩点。
 * 注：回调函数需要自己输出JS标签
 * @param string $event 事件名，eg. foot
 * @param callback $callback 回调函数（或方法）
 * @param array $arg 调用回调函数的参数
 * @param bool|string $unique 唯一标识，true 表示使用 $callback 作为标识，false表示不作唯一标识，使用 string 表示在 $callback 标识后加上后序（这样可用于对同一个函数不同参数作唯一）。
 */
function gpf_event($event, $callback, $arg = array(), $unique = false)
{//{{{
	$gk = 'gpf_event';
	//取标记名称
	do
		{
		$name = '';
		if (!$unique)
			{
			break;
			}
		if (is_string($callback))
			{
			//用函数名
			$name = $callback;
			break;
			}
		if (is_array($callback))
			{
			if (is_string($callback[0]))
				{
				//假设这表示调用静态方法，eg. array('log', 'add')
				$name = $callback[0];
				break;
				}
			if (is_object($callback[0]))
				{
				$name = get_class($callback[0]);
				break;
				}
			}
		}
	while (false);
	if (is_string($unique) && $unique)
		{
		$name .= $unique;
		}

	if ($name)
		{
		$GLOBALS[$gk][$name] = array($callback, $arg);
		}
	else
		{
		$GLOBALS[$gk][] = array($callback, $arg);
		}
}//}}}
/**
 * 触发某一个事件中的回调函数
 */
function gpf_event_call($event)
{//{{{
	$gk = 'gpf_event';
	gpf_log($event, GPF_LOG_FLOW, __FILE__, __LINE__, __FUNCTION__);
	if (!is_array($GLOBALS[$gk]))
		{
		return ;
		}
	foreach ($GLOBALS[$gk] as $v)
		{
		call_user_func_array($v[0], $v[1]);
		}
	//zjq@2013-03-06 重置数组，避免重复被调用时出错
	$GLOBALS[$gk] = array();
}//}}}
//=============================== override
$GLOBALS['gpf_override'] = array();
/**
 * 有时一些部份允许由用户设置指定的函数接管处理（就像set_error_handler()这类）
 * 这里提供一个简单的接口托管这类功能。
 * @param string $name 命名
 * @param NULL|callback $callback 若为NULL表示删除override
 */
function gpf_override($name, $callback = NULL)
{//{{{
	$gk = 'gpf_override';
	if (is_null($callback))
		{
		unset($GLOBALS[$gk]);
		}
	else
		{
		$GLOBALS[$gk][$name] = $callback;
		}
}//}}}
/**
 * 取指定命名是否有设置接管函数。
 */
function gpf_override_get($name)
{//{{{
	$gk = 'gpf_override';
	if (isset($GLOBALS[$gk][$name]))
		{
		if (is_callable($GLOBALS[$gk][$name]))
			{
			return $GLOBALS[$gk][$name];
			}
		}
	return NULL;
}//}}}
//============================== shutdown
//GPF会注册系统的register_shutdown_function钩子，若其它函数也需要进行挂钩，可以使用 gpf_event：
//gpf_event('shutdown', ...)
/**
 * 注册到 register_shutdown_function 的处理函数
 */
function _gpf_shutdown_function()
{//{{{
	//zjq@2012-01-30 若脚本被 Fatal error 中断， php 不会调用 set_error_handler 注册的函数处理。
	$error_last = error_get_last();
	//若没有 Fatal 中断 $error_last = null.
	if ($error_last)
		{
		gpf_log($error_last['message'], $error_last['type'], $error_last['file'], $error_last['line']);
		}

	gpf_event_call('shutdown');

	//处理错误日志
	_gpf_log_flush();
}//}}}
register_shutdown_function('_gpf_shutdown_function');
//============================== log
//错误等级
define('GPF_LOG_SYSTEM', 'SYSTEM'); 	//系统崩聩，如数据库无法链接
define('GPF_LOG_ERROR', 'ERROR');	//系统错误，如参数非法
define('GPF_LOG_WARN', 'WARN');		//警告信息，如调用废弃函数
define('GPF_LOG_FLOW', 'FLOW');		//流程信息，如是否需要更新html文件
define('GPF_LOG_SQL', 'SQL');		//数据库查错出错
define('GPF_LOG_INFO', 'INFO');		//普通信息
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
	$output = '';
	$output .= "<br/><font color=blue>Infomation:({$count})</font><hr/><div>\n";
	foreach ($GLOBALS[$gk_data] as $k => $v)
		{
		if (empty($GLOBALS[$gk_output_level][$v['level']]))
			{
			continue;
			}
		$br = isset($v['msg'][100]) ? '<br/><br/>' : '<br/>';	//长句加多个换行，更容易查看
		$output .= "<span style=\"" . $GLOBALS[$gk_style][$v['level']] . "\">{$v['func']} {$v['msg']} [" . $GLOBALS[$gk_txt][$v['level']] . "] {$v['file']}:{$v['line']}</span>{$br}\n";
		}
	$output .= "<br/><br/><br/></div>\n";
	echo $output;
	if (function_exists('gpfd_output'))
		{
		gpfd_output($output);
		}
}//}}}

//============================== error ==============================
//可以用 gpf_override('gpf_err'); 设置错误处理接管函数
gpf_override('gpf_err', 'printf'); //默认默认为printf,实际使用exit（因为exit不是合法的callback，无法设置）
$GLOBALS['gpf_error'] = ''; //错误提示信息。
/**
 * 检查gpf_err信息是否不为空，不为空直接提示并中断程序运行。
 */
function gpf_err_check()
{//{{{
	$gk = 'gpf_error';
	if (!$GLOBALS[$gk])
		{
		return ;
		}
	//zjq@2013-03-21 因为是exit掉php，所以可以自动取trace信息
	$trace = debug_backtrace(false);
	// var_dump($trace);
	//处理逻辑根据debug_backtrace()的返回格式编写
	$t0 = $trace[0];
	$t1 = isset($trace[1]) ? $trace[1] : array();
	$file = $t0['file'];
	$line = $t0['line'];
	$func = isset($t1['function']) ? $t1['function'] : '';
	if (isset($t1['class']))
		{
		$func = "{$t1['class']}{$t1['type']}{$func}";
		}
	gpf_log("GERR_CHECK", GPF_LOG_ERROR, $file, $line, $func);
	exit("流程出错");
}//}}}
/**
 * 进行错误提示
 * @param string $error 错误提示信息。
 * @param false|array $arg 调用错误处理回调函数时传递的参数，若为false并且没有设置接管函数，则忽略此错误。
 * @return false
 */
function gpf_err($error, $arg = array())
{//{{{
	$gk = 'gpf_error';
	if (false === $arg)
		{
		if ('printf' === gpf_override_get('gpf_err'))
			{
			//忽略此次错误
			return false;
			}
		}

	//zjq@2013-03-21 因为gpf_err一调用基本就是exit掉php，所以可以自动取trace信息
	$trace = debug_backtrace(false);
	// var_dump($trace);
	//处理逻辑根据debug_backtrace()的返回格式编写
	$t0 = $trace[0];
	$t1 = isset($trace[1]) ? $trace[1] : array();
	$file = $t0['file'];
	$line = $t0['line'];
	$func = isset($t1['function']) ? $t1['function'] : '';
	if (isset($t1['class']))
		{
		$func = "{$t1['class']}{$t1['type']}{$func}";
		}

	$GLOBALS[$gk] = $error;
	gpf_log("(GERR){$error}", GPF_LOG_FLOW, $file, $line, $func);

	$callback = gpf_override_get('gpf_err');
	if ($callback)
		{
		if ('printf' === $callback)
			{
			exit($error);
			}
		//第一个参数是错误提示信息
		array_unshift($arg, $error);
		call_user_func_array($error_func, $arg);
		}
	return false;
}//}}}
/**
 * 取错误提示内容。
 */
function gpf_err_get()
{//{{{
	return $GLOBALS['gpf_error'];
}//}}}

//============================== hook ==============================
$GLOBALS['gpf_obj_callback'] = array();
/**
 * 加载并返回 callback 对象数组。
 * <pre>
 * 提供给 hook 类使用，调用格式：$list = gpf_hook(mod_name, __CLASS__, __FUNCTION__);
 * </pre>
 * @param string $class_name hook 类完整类名，一般使用 __CLASS__。eg. h_member_base
 */
function gpf_hook($mod_name, $class_name, $func_name)
{//{{{
	$gk_callback = 'gpf_obj_callback';

	if (!isset($GLOBALS[$gk_callback]["{$mod_name}/{$class_name}"]))
		{
		$GLOBALS[$gk_callback]["{$mod_name}/{$class_name}"] = _gpf_hook_callback($mod_name, $class_name);
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
 * 加载一个模块的一个 callback 对象
 * @param string hook 模块名
 * @param string $class_name eg. h_member_base
 * @param array 对象列表
 */
function _gpf_hook_callback($mod_name, $class_name)
{//{{{
	$mod_callback = _gpf_hook_mod_file($mod_name);
	if (!$mod_callback)
		{
		return array();
		}
	//eg. h_member_base > member_base
	$class_name_short = substr($class_name, 2);
	$obj_callback = array();
	foreach ($mod_callback as $_mod)
		{
		$class_name_full = "hc_{$_mod}_{$class_name_short}";
		if (!class_exists($class_name_full))
			{
			$_path = GPF_MODULE . "{$_mod}/0hook/hc_{$class_name_short}.class.php";
			if (!is_file($_path))
				{
				gpf_log("hook_callback 文件不存在[{$_path}]", GPF_LOG_WARN, __FILE__, __LINE__, __FUNCTION__);
				continue;
				}
			//debug/dump/$_path
			gpf_inc($_path);
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
$GLOBALS['gpf_hook_mod_file'] = array();
/**
 * 加载模块 hook 目录 mod 文件
 * @param array 挂钩的模块列表。
 */
function _gpf_hook_mod_file($mod_name)
{//{{{
	$gk = 'gpf_hook_mod_file';
	//debug/php/$GLOBALS['t_hmf_is_cache'] = false;
	if (isset($GLOBALS[$gk][$mod_name]))
		{
		//debug/php/$GLOBALS['t_hmf_is_cache'] = true;
		return $GLOBALS[$gk][$mod_name];
		}
	$GLOBALS[$gk][$mod_name] = array();

	$_path = GPF_MODULE . "{$mod_name}/0hook/mod";
	//debug/dump/$_path
	if (is_file($_path))
		{
		$list = file($_path);
		//debug/dump/$list
		$GLOBALS[$gk][$mod_name] = array_filter(array_map('trim', $list));
		}
	return $GLOBALS[$gk][$mod_name];
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

//============================== get,post ==============================
//可通过 gpf_override('gpf_xss', ...) 设置接管xss过滤的callback
/**
 * 兼容数组的 addslashes
 * @param string|array $data
 */
function gpf_addslashes($data)
{//{{{
	return is_array($data) ? array_map('gpf_addslashes', $data) : addslashes($data);
}//}}}
/**
 * 兼容数组的 stripslashes
 * @param string|array $data
 */
function gpf_stripslashes($data)
{//{{{
	return is_array($data) ? array_map('gpf_stripslashes', $data) : stripslashes($data);
}//}}}
/**
 * 兼容数组的 htmlspecialchars
 */
function gpf_htmlspecialchars($data)
{//{{{
	return is_array($data) ? array_map('gpf_htmlspecialchars', $data) : htmlspecialchars($data);
}//}}}
/**
 * 兼容数组的 strip_tags
 */
function gpf_strip_tags($data)
{//{{{
	return is_array($data) ? array_map('gpf_strip_tags', $data) : strip_tags($data);
}//}}}
/**
 * 兼容数组的 intval
 */
function gpf_intval($data)
{//{{{
	return is_array($data) ? array_map('gpf_intval', $data) : intval($data);
}//}}}
/**
 * 兼容数组的 floatval
 */
function gpf_floatval($data)
{//{{{
	return is_array($data) ? array_map('gpf_floatval', $data) : floatval($data);
}//}}}
/**
 * 处理一个标签属性部份的xss
 */
function _gpf_xss_preg_replace_callback($match)
{//{{{
	//普通字符串替换
	$str1 = $str2 = array();
	//避免通过换行绕过匹配
	$str1[] = "\t";
	$str2[] = '';
	$str1[] = "\n";
	$str2[] = '';
	$str1[] = "\r";
	$str2[] = '';
	//禁止关键词
	$str1[] = 'javascript:';
	$str2[] = 'gpfxss';
	$str1[] = 'expression';
	$str2[] = 'gpfxss';
	$str1[] = '/*';
	$str2[] = 'gpfxss';
	$str1[] = '*/';
	$str2[] = 'gpfxss';
	$str1[] = 'base64';
	$str2[] = 'gpfxss';
	$str1[] = 'vbscript';
	$str2[] = 'gpfxss';

	$xss = str_ireplace($str1, $str2, $match[0]);

	//禁用所有on*事件
	$xss = preg_replace('/\bon[^\b]+\b/i', ' gpfxss', $xss);
	return $xss;
}//}}}
/**
 * 简单的xss过滤功能(倾向执行速度)
 * 过滤逻辑基本假设：用户正常使用HTML代码不会带有可能导致XSS的代码，比如<script>标签.
 * 所以，对可能导致XSS的代码不是替换为空,而是无害化掉：
 * eg. <img onload="alert(1)" /> 替换为 <img xssload="alert(1)" /> 令 onload 失去作用即可。
 * 以尽可能避免因过滤逻辑漏洞而被xss代码绕过：
 * eg. <<script></script>script>alert(1);</script>
 */
function gpf_xss($xss)
{//{{{
	if (is_array($xss))
		{
		foreach ($xss as $k => $v)
			{
			$xss[$k] = gpf_xss($v);
			}
		return $xss;
		}

	//普通字符串替换
	$str1 = array();
	$str2 = array();

	//禁用所有&#97;这样的表示法,避免后面的过滤被绕过
	$str1[] = '&#';
	$str2[] = '&amp;#';
	//php标签
	$str1[] = '<?';
	$str2[] = '&lt;?';
	$str1[] = '?>';
	$str2[] = '?&gt;';

	$xss = str_ireplace($str1, $str2, $xss);

	//正则替换
	$preg1 = array();
	$preg2 = array();

	//转换属性中的">","<",后面的替换只在标签属性区内进行，避免被绕过
	//eg. <img title=">" onload="" />
	//在浏览器中测试过<img title="\">" 这种写法是不成立的，因此这个正则没有大问题。
	$preg1[] = "/[a-z]+=([\'\"]).*?\\1/ei";
	$preg2[] = 'str_replace(array(">", "<"), array("&gt;", "&lt;"), "\\0")';

	//去掉最明显的<script></script>标签,避免输出一些明显的xss代码面子上不好看。
	$preg1[] = '#<script[^>]*>(.*?)</script>#is';
	$preg2[] = '';
	//危险的标签
	$tag = array('meta', 'form', 'iframe', 'frame', 'frameset', 'style', 'script', 'link', 'object', 'applet', 'base', 'video', 'embed', 'head');
	$preg1[] = '/<('.join("|", $tag).')\b/i';
	$preg2[] = '<gpfxss';
	$preg1[] = '#</(' . join("|", $tag) . ')\b#';
	$preg2[] = '</gpfxss';

	$xss = preg_replace($preg1, $preg2, $xss);

	//开始过滤标签属性
	$xss = preg_replace_callback('/<[a-z]+[^>]*>/is', '_gpf_xss_preg_replace_callback', $xss);
	return $xss;
}//}}}

$GLOBALS['gpf_is_slashes'] = get_magic_quotes_gpc(); //标记PHP配置是否已自动处理引号
$GLOBALS['gpf_post'] = $_POST;
$GLOBALS['gpf_get'] = $_GET;
$GLOBALS['gpf_request'] = $_REQUEST;

/**
 * 简单填充默认值(btw. gpfif = gpf input function)
 * @param mixed $input 要填充的变量
 * @param NULL|mixed|array $def_val 填充参数，若不是数组，则默认使用 !isset 作为填充条件,参数值为填充默认值
 * 若为空数组，则理解为填充默认值为空数组，填充条件依然是 !isset
 * 若为非空数组，则按 array(默认值, 填充条件) 格式传入。
 * 填充条件可使用 isset, empty 以及其它接受一个参数的函数（eg. trim），都可以在名称前加“!”表示逻辑否。eg. !isset
 * 若$def_val=NULL 表示不填充。
 */
function gpfif_default_value(& $input, $def_val)
{//{{{
	if (is_null($def_val))
		{
		return ;
		}
	if (!is_array($def_val))
		{
		$def_val = array($def_val, '!isset');
		}
	else if (!$def_val)
		{
		//这个情况认为默认值为空数组
		$def_val = array(array(), '!isset');
		}
	list($def_value, $def_func) = $def_val;
	unset($def_val);

	$is_def_not = false; //默认值是否要经过!(not)运算
	if ('!' === $def_func[0])
		{
		$is_def_not = true;
		$def_func = substr($def_func, 1);
		}
	switch ($def_func)
		{
		//isset和empty是语言结构
		case "isset":
			$is_def = isset($input);
			break;
		case "empty":
			$is_def = empty($input);
			break;
		default:
			$is_def = $def_func($input);
			break;
		}
	if ($is_def_not)
		{
		$is_def = !$is_def;
		}
	if ($is_def)
		{
		$input = $def_value;
		}
}//}}}
/**
 * 强制传入参数是否转为数组（或转为非数组）或不处理
 * @param array $option {@@:必须是数组（强制类型转换）, !@:不允许是数组, @:自动} eg. array('@@')
 */
function gpfif_maybe_array(& $input, $option = array())
{//{{{
	//默认选项值
	$_option = array(
		"!@" => true, //不允许为数组(默认)
		"@@" => false, //必须为数组（强制类型转换为数组）
		"@" => false, //自动处理数组或非数组
	);
	foreach ($option as $k)
		{
		$_option[$k] = true;
		}
	//此处的判断逻辑是根据 $_option 设置的默认值以及调用者可能的输入编写的
	//eg. $option = array('@')
	if ($_option['@'])
		{
		return ;
		}
	if ($_option['@@'])
		{
		if (!is_array($input))
			{
			$input = (array)$input;
			}
		return ;
		}
	if ($_option['!@'])
		{
		if (is_array($input))
			{
			$input = (string)$input;
			}
		return ;
		}
}//}}}
/**
 * 根据get_magic_quotes_gpc()的设置值按要求返回是否转义引号的输入数据
 * @param array $option {'!\\':不转义引号} eg. $option = array('!\\')
 */
function gpfif_slashes($input, $option = array())
{//{{{
	$gk_is_slashes = 'gpf_is_slashes';
	//默认选项值
	$_option = array(
		'!\\' => false, //是否返回不转义引号的数据（自动处理已转换过的数据）
	);
	foreach ($option as $k)
		{
		$_option[$k] = true;
		}

	$is_addslashes = true;
	$is_stripslashes = false;
	if ($_option['!\\'])
		{
		//显式声明不转义引号
		$is_addslashes = false;
		$is_stripslashes = $GLOBALS[$gk_is_slashes];
		}
	else
		{
		//转义引号
		$is_stripslashes = false;
		$is_addslashes = !$GLOBALS[$gk_is_slashes];
		}
	if ($is_addslashes)
		{
		$input = gpf_addslashes($input);
		}
	else if ($is_stripslashes)
		{
		$input = gpf_stripslashes($input);
		}
	return $input;
}//}}}

/**
 * 集合一下共同的过滤流程
 * @param mixed 待处理变量
 * @param mixed $def_val 参见 gpfif_default_value 参数说明
 * @param array $option 参见 gpfif_maybe_array 和 gpfif_slashes 参数说明
 * @param string $proc_func 安全过滤函数，eg. gpf_htmlspecialchars
 * @param bool $is_slashes 是否处理引号（若已格式化为数组则可设为false，其余情况都应为true）
 */
function _gpfi($data, $def_val, $option, $proc_func, $is_slashes = true)
{//{{{
	//填充默认值
	gpfif_default_value($data, $def_val);
	//格式化数据类型
	gpfif_maybe_array($data, $option);
	//安全过滤
	do
		{
		if (!$proc_func)
			{
			break;
			}
		//特例处理,可以接管xss过滤函数
		if ('gpf_xss' === $proc_func)
			{
			$callback = gpf_override_get('gpf_xss');
			if ($callback)
				{
				$data = call_user_func($callback, $data);
				break;
				}
			}
		$data = $proc_func($data);
		}
	while (false);
	//处理引号
	if ($is_slashes)
		{
		$data = gpfif_slashes($data, $option);
		}
	return $data;
}//}}}

/**
 * gpfi 系列函数都从 $_REQUEST 取数据
 * 转换所有html实体字符(htmlspecialchars)
 */
function gpfi($name, $def_val = NULL, $option = array())
{//{{{
	$gk_array = 'gpfi_array';
	if (!is_null($GLOBALS[$gk_array]))
		{
		return _gpfi_array_set($name, $def_val, $option, 'gpf_htmlspecialchars');
		}
	return _gpfi($GLOBALS['gpf_request'][$name], $def_val, $option, 'gpf_htmlspecialchars');
}//}}}
/**
 * 强制格式化为数字(intval)
 */
function gpfi_int($name, $def_val = NULL, $option = array())
{//{{{
	$gk_array = 'gpfi_array';
	if (!is_null($GLOBALS[$gk_array]))
		{
		return _gpfi_array_set($name, $def_val, $option, 'gpf_intval', false);
		}
	//整数不需要处理引号
	return _gpfi($GLOBALS['gpf_request'][$name], $def_val, $option, 'gpf_intval', false);
}//}}}
/**
 * floatval
 */
function gpfi_float($name, $def_val = NULL, $option = array())
{//{{{
	$gk_array = 'gpfi_array';
	if (!is_null($GLOBALS[$gk_array]))
		{
		return _gpfi_array_set($name, $def_val, $option, 'gpf_floatval', false);
		}
	//小数不需要处理引号
	return _gpfi($GLOBALS['gpf_request'][$name], $def_val, $option, 'gpf_floatval', false);
}//}}}
/**
 * 过滤掉所有html标签（strip_tags）
 */
function gpfi_txt($name, $def_val = NULL, $option = array())
{//{{{
	$gk_array = 'gpfi_array';
	if (!is_null($GLOBALS[$gk_array]))
		{
		return _gpfi_array_set($name, $def_val, $option, 'gpf_strip_tags');
		}
	return _gpfi($GLOBALS['gpf_request'][$name], $def_val, $option, 'gpf_strip_tags');
}//}}}
/**
 * 允许html内容（做xss过滤）
 */
function gpfi_html($name, $def_val = NULL, $option = array())
{//{{{
	$gk_array = 'gpfi_array';
	if (!is_null($GLOBALS[$gk_array]))
		{
		return _gpfi_array_set($name, $def_val, $option, 'gpf_xss');
		}
	return _gpfi($GLOBALS['gpf_request'][$name], $def_val, $option, 'gpf_xss');
}//}}}
/**
 * 不做安全过滤（但引号还是会处理）
 */
function gpfi_in($name, $def_val = NULL, $option = array())
{//{{{
	$gk_array = 'gpfi_array';
	if (!is_null($GLOBALS[$gk_array]))
		{
		return _gpfi_array_set($name, $def_val, $option, '');
		}
	return _gpfi($GLOBALS['gpf_request'][$name], $def_val, $option, '');
}//}}}
/**
 * gpfig 系列函数都从 $_GET 取数据,其余与gpfi系列函数相同
 */
function gpfig($name, $def_val = NULL, $option = array())
{//{{{
	return _gpfi($GLOBALS['gpf_get'][$name], $def_val, $option, 'gpf_htmlspecialchars');
}//}}}
function gpfig_int($name, $def_val = NULL, $option = array())
{//{{{
	//整数不需要处理引号
	return _gpfi($GLOBALS['get_get'][$name], $def_val, $option, 'gpf_intval', false);
}//}}}
function gpfig_float($name, $def_val = NULL, $option = array())
{//{{{
	//小数不需要处理引号
	return _gpfi($GLOBALS['get_get'][$name], $def_val, $option, 'gpf_floatval', false);
}//}}}
function gpfig_txt($name, $def_val = NULL, $option = array())
{//{{{
	return _gpfi($GLOBALS['gpf_get'][$name], $def_val, $option, 'gpf_strip_tags');
}//}}}
function gpfig_html($name, $def_val = NULL, $option = array())
{//{{{
	return _gpfi($GLOBALS['gpf_get'][$name], $def_val, $option, 'gpf_xss');
}//}}}
function gpfig_in($name, $def_val = NULL, $option = array())
{//{{{
	return _gpfi($GLOBALS['gpf_get'][$name], $def_val, $option, '');
}//}}}
/**
 * gpfip 系列函数都从 $_POST 取数据,其余与gpfi系列函数相同
 */
function gpfip($name, $def_val = NULL, $option = array())
{//{{{
	return _gpfi($GLOBALS['gpf_post'][$name], $def_val, $option, 'gpf_htmlspecialchars');
}//}}}
function gpfip_int($name, $def_val = NULL, $option = array())
{//{{{
	//整数不需要处理引号
	return _gpfi($GLOBALS['gpf_post'][$name], $def_val, $option, 'gpf_intval', false);
}//}}}
function gpfip_float($name, $def_val = NULL, $option = array())
{//{{{
	//小数不需要处理引号
	return _gpfi($GLOBALS['gpf_post'][$name], $def_val, $option, 'gpf_floatval', false);
}//}}}
function gpfip_txt($name, $def_val = NULL, $option = array())
{//{{{
	return _gpfi($GLOBALS['gpf_post'][$name], $def_val, $option, 'gpf_strip_tags');
}//}}}
function gpfip_html($name, $def_val = NULL, $option = array())
{//{{{
	return _gpfi($GLOBALS['gpf_post'][$name], $def_val, $option, 'gpf_xss');
}//}}}
function gpfip_in($name, $def_val = NULL, $option = array())
{//{{{
	return _gpfi($GLOBALS['gpf_post'][$name], $def_val, $option, '');
}//}}}

$GLOBALS['gpfi_array'] = NULL; //临时保存处理一个数组时的数据
$GLOBALS['gpfi_get'] = array(); //保存经过处理后待返回的数据
/**
 * 集合一下共同的流程
 */
function _gpfi_array($name, $input_from)
{//{{{
	$gk_array = 'gpfi_array';
	if (!is_null($GLOBALS[$gk_array]))
		{
		return gpf_err("正在处理一组数据，不支持嵌套处理");
		}
	if (is_null($name))
		{
		$data = $GLOBALS[$input_from];
		}
	else if (is_array($name))
		{
		$data = $name;
		}
	else
		{
		//强制转换，避免后面的处理流程出错
		$data = (array)$GLOBALS[$input_from][$name];
		}
	$GLOBALS[$gk_array] = $data;
	return true;
}//}}}
/**
 * 处理一组数据中的具体键
 * @param string $name 具体键名，可以用“,”分隔多个。
 */
function _gpfi_array_set($name, $def_val, $option, $proc_func, $is_slashes = true)
{//{{{
	$gk_array = 'gpfi_array';
	$gk_get = 'gpfi_get';

	$name_list = array_map('trim', explode(",", $name));
	foreach ($name_list as $k)
		{
		//已处理的数据移到 gpfi_get 中。
		$GLOBALS[$gk_get][$k] = _gpfi($GLOBALS[$gk_array][$k], $def_val, $option, $proc_func, $is_slashes);
		// var_dump($GLOBALS[$gk_array][$k]);
		unset($GLOBALS[$gk_array][$k]);
		}
}//}}}
/**
 * 开始处理一个数据
 * 调用此函数后，继续使用 gpfi 系统函数，最后调用 gpfi_get 返回处理完的数组数据
 * @param mixed $name 若为字符串，从$_REQUEST中取数据，若为NULL，表示整个$_REQUEST，若为数组，表示处理传入数组（此时注意引号状态要与$_POST等输入数据状态一致）
 */
function gpfi_array($name = NULL)
{//{{{
	return _gpfi_array($name, 'gpf_request');
}//}}}
/**
 * 同 gpfi_array ，但只从$_GET取数据
 */
function gpfig_array($name = NULL)
{//{{{
	return _gpfi_array($name, 'gpf_get');
}//}}}
/**
 * 同 gpfi_array,但只从$_POST取数据
 */
function gpfip_array($name = NULL)
{//{{{
	return _gpfi_array($name, 'gpf_post');
}//}}}
/**
 * 调用 gpfi_array 系列后取最终数据
 * @param bool $is_auto_all 是否把未显式声名的键默认按 gpfi 处理，若为false则未显式声名的键全部丢弃
 */
function gpfi_get($is_auto_all = true)
{//{{{
	$gk_array = 'gpfi_array';
	$gk_get = 'gpfi_get';

	$ret = $GLOBALS[$gk_get];
	$GLOBALS[$gk_get] = array();
	if ($is_auto_all && $GLOBALS[$gk_array])
		{
		foreach ($GLOBALS[$gk_array] as $k => $v)
			{
			//已处理的数据移到 gpfi_get 中。
			$ret[$k] = _gpfi($v, $def_val, array(), 'gpf_htmlspecialchars');
			}
		}
	$GLOBALS[$gk_array] = NULL;
	return $ret;
}//}}}

/**
 * 手动写入数据（像框架路由这类功能会用到）
 * @param mixed $value 数据的引号转义请与环境初始的$_POST等数据的状态一致
 */
function gpfi_set($name, $value)
{//{{{
	$GLOBALS['gpf_request'][$name] = $value;
}//}}}
function gpfig_set($name, $value)
{//{{{
	$GLOBALS['gpf_get'][$name] = $value;
}//}}}
function gpfip_set($name, $value)
{//{{{
	$GLOBALS['gpf_post'][$name] = $value;
}//}}}

//zjq@2013-03-20 todo 等待重写为filter系列函数
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

//============================== module ==============================
/**
 * 加载模块内的定类文件(调用 gpf_load() 实现)
 * @param string $path 从GPF_MODULE目录开始的相对路径。eg. gpf/gpf.inc.php
 */
function gpf_mod_load($path, $class_name = '')
{//{{{
	$pathfull = GPF_MODULE . $path;
	return gpf_load($pathfull, $class_name);
}//}}}
/**
 * zjq@2013-03-21 todo +disable 被 gpf_mod_load 代替
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

//============================== module#old ==============================
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

//============================== other ==============================
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

//=============================== gpf_static ===============================
/**
 * 把$path+$dir所指向目录内的所有文件全复制到GPF_STATIC_DIR常量所指向的目录中。
 * <pre>
 * 需定义常量：
 * GPF_STATIC_DIR :/public/ 目录路径。
 * </pre>
 * @param string $path 起始目录。以/结尾,比如网站根目录,
 * @param string $dir 后续目录，eg. 0lib/gpf/ eg. 0module/main/
 */
function gpf_static($path, $dir)
{//{{{
	if (!defined('GPF_STATIC_DIR'))
		{
		//debug/testphp/$GLOBALS['t_static_not_dir'] = true;
		gpf_log('!defined GPF_STATIC_DIR', GPF_LOG_FLOW, __FILE__, __LINE__, __FUNCTION__);
		return false;
		}
	//debug/testphp/$GLOBALS['t_static_not_dir'] = false;
	$sour = $path . $dir;
	$to = GPF_STATIC_DIR . $dir;
	//debug/testphp/$GLOBALS['t_static_sour'] = $sour;
	//debug/testphp/$GLOBALS['t_static_to'] = $to;
	//debug/dump/$sour, $to
	if (!is_dir($sour))
		{
		//debug/testphp/$GLOBALS['t_static_not_sour'] = true;
		return false;
		}
	//debug/testphp/$GLOBALS['t_static_not_sour'] = false;
	$is_copy = false;
	//debug/testphp/$GLOBALS['t_static_not_to'] = false;
	//debug/testphp/$GLOBALS['t_static_switch'] = false;
	if (!is_dir($to))
		{
		//debug/testphp/$GLOBALS['t_static_not_to'] = true;
		$is_copy = true;
		}
	else if (defined('GPF_STATIC_SWITCH') && true === GPF_STATIC_SWITCH)
		{
		//debug/testphp/$GLOBALS['t_static_switch'] = true;
		$is_copy = true;
		}
	//debug/testphp/$GLOBALS['t_static_is_copy'] = $is_copy;

	if (!$is_copy)
		{
		gpf_log('!copy', GPF_LOG_FLOW, __FILE__, __LINE__, __FUNCTION__);
		return false;
		}
	gpf_log($dir, GPF_LOG_FLOW, __FILE__, __LINE__, __FUNCTION__);
	_gpf_static_copy($sour, $to);
	return true;
}//}}}
function gpf_mod_static($mod)
{//{{{
	//debug/test=1/gpf_mod_static
	$GPF_MODULE = GPF_MODULE;
	//debug/testphp/($GLOBALS['GPF_MODULE'] AND $GPF_MODULE = $GLOBALS['GPF_MODULE']);
	$dir = dirname($GPF_MODULE) . '/';
	$base = basename($GPF_MODULE);
	//zjq@20130405 模块内使用0static目录保存所有静态资源文件
	gpf_static($dir, "{$base}/{$mod}/0static");
}//}}}
function gpf_lib_static($lib)
{//{{{
	//debug/test=1/gpf_lib_static
	$GPF_LIB = GPF_LIB;
	//debug/testphp/($GLOBALS['GPF_LIB'] AND $GPF_LIB = $GLOBALS['GPF_LIB']);
	$dir = dirname($GPF_LIB) . '/';
	$base = basename($GPF_LIB);
	//zjq@20130405 每个lib内使用0static目录保存所有静态资源文件
	gpf_static($dir, "{$base}/{$lib}/0static");
}//}}}
/**
 * 只复制更新过的文件，因为“复制”操作很耗时。
 */
function _gpf_static_copy($sour, $to)
{//{{{
	//debug/test=1/function__gpf_static_copy
	if (is_dir($sour))
		{
		if ('/' !== substr($sour, -1))
			{
			$sour .= '/';
			}
		if ('/' !== substr($to, -1))
			{
			$to .= '/';
			}
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
			//debug/dump/$sour . $entry, $to . $entry
			_gpf_static_copy($sour . $entry, $to . $entry);
			}
		$handle->close();
		return ;
		}
	//debug/testphp/$GLOBALS['t_staticcopy_not_sour'] = false;
	//debug/testphp/$GLOBALS['t_staticcopy_is_copy'] = false;
	if (!is_file($sour))
		{
		//debug/testphp/$GLOBALS['t_staticcopy_not_sour'] = true;
		return ;
		}
	if (is_file($to) && filemtime($to) >= filemtime($sour))
		{
		return ;
		}
	//建立目标目录
	$_mkdir = array();
	$to_dir = dirname($to);
	while (!is_dir($to_dir))
		{
		$_mkdir[] = $to_dir;
		$to_dir = dirname($to_dir);
		}
	if ($_mkdir)
		{
		$_mkdir = array_reverse($_mkdir);
		foreach ($_mkdir as $k => $v)
			{
			mkdir($v);
			}
		}
	//debug/testphp/$GLOBALS['t_staticcopy_is_copy'] = true;
	copy($sour, $to);
}//}}}

/**
 * 调用控制器处理请求
 * 控制器使用类形式定义。直接调用控制器的方法，通过 $in 参数自定义控制器的路径，类名等参数。
 * @param string $mod 控制器所在模块
 * @param string $file 控制器源代码文件名
 * @param string $action 处理方法名。
 * @param array $in 各种细节参数(参见代码实现)。
 */
function gpf_ctrl($mod, $file, $action, $in = array())
{//{{{
	//默认使用index.class.php
	($mod OR $mod = 'main');
	($file OR $file = 'index');
	($action OR $action = 'index');
	preg_match("/^[0-9A-Za-z_-]+$/", $mod) OR gpf_err('Invalid Request.');
	preg_match("/^[0-9A-Za-z_-]+$/", $file) OR gpf_err('Invalid Request.');
	preg_match("/^[0-9A-Za-z_-]+$/", $action) OR gpf_err('Invalid Request.');

	//计算参数
	$in['mod'] = $mod;
	$in['file'] = $file;
	$in['action'] = $action;
	//方法名前序
	(empty($in['pre']) AND $in['pre'] = 'c_');
	//类名, eg. c_cms_index, v_cms_view
	(empty($in['class']) AND $in['class'] = "{$in['pre']}{$mod}_{$file}");
	//方法名前序。不允许直接通过请求参数调用类中的方法，必须加上前序。
	(empty($in['type']) AND $in['type'] = 'action_');
	//控制器目录名
	(empty($in['dir']) AND $in['dir'] = '0c');
	//环境init方法名, eg. _action_init
	(empty($in['func_init']) AND $in['func_init'] = "_{$in['type']}init");
	//处理请求方法名, eg. action_index
	(empty($in['func']) AND $in['func'] = $in['type'] . $action);
	//控制器文件路径
	(empty($in['path']) AND $in['path'] = GPF_MODULE . "{$mod}/{$in['dir']}/{$file}.class.php");
	gpf_log(var_export($in, true), GPF_LOG_INFO, __FILE__, __LINE__, __FUNCTION__);

	//实例化
	do
		{
		$ctrl = NULL;
		if (gpf_is_obj($in['class']))
			{
			$ctrl = gpf_obj($in['class']);
			break;
			}
		//尽可能避免重复加载
		if (class_exists($in['class']))
			{
			$ctrl = new $in['class']();
			gpf_obj($in['class'], $ctrl);
			break;
			}
		if (!is_file($in['path']))
			{
			gpf_log("控制器文件不存在 {$in['path']}", GPF_LOG_NOTEXI, __FILE__, __LINE__, __FUNCTION__);
			gpf_obj($in['class'], false);
			return false;
			}
		gpf_inc($in['path']);
		if (!class_exists($in['class']))
			{
			gpf_log("控制器类不存在", GPF_LOG_NOTEXI, __FILE__, __LINE__, __FUNCTION__);
			gpf_obj($in['class'], false);
			return false;
			}
		$ctrl = new $in['class']();
		}
	while (false);
	if (!$ctrl)
		{
		gpf_log("无法实例化控制器", GPF_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__);
		return false;
		}

	if (method_exists($ctrl, $in['func_init']))
		{
		$ctrl->$in['func_init']();
		}
	if (!method_exists($ctrl, $in['func']))
		{
		gpf_log("处理方法不存在", GPF_LOG_NOTEXI, __FILE__, __LINE__, __FUNCTION__);
		return false;
		}
	$ctrl->$in['func']();
}//}}}

/**
 * 对一个PHP文件开启GPF DEBUG模式
 * 调用方法：让PHP文件内第一条执行语句为（注意大小写）
 * return include gpf_debug(__FILE__);
 * 如果PHP文件是类定义文件（或函数定义），在开启DEBUG时，还需加上一个if块：
 * if(1):
 * ....PHP文件原本的代码。
 * endif;
 * 以避免重复定义致命错误
 */
function gpf_debug($file)
{//{{{
	if (!function_exists('gpfd_file'))
		{
		require dirname(__FILE__). '/debug.inc.php';
		}
	return gpfd_file($file);
}//}}}

/**
 * JS调试信息记录，在接受JS调试信息的页面(GPF_DEBUG_JS常量指向的地址)中调用即可
 */
function gpf_debug_js()
{//{{{
	if (!function_exists('gpfd_file'))
		{
		require dirname(__FILE__). '/debug.inc.php';
		}
	gpfd_js();
}//}}}

/**
 * 用于引用外部代码（lib）文件（单次包含）
 * @param string $path 文件（或目录），不需要最后的“.php”，若以“/”结尾，自动加上init.inc.php
 */
function gpf_lib_load($path, $class_name = '')
{//{{{
	if ('/' === substr($path, -1, 1))
		{
		$path .= 'init.inc';
		}
	$pathfull = GPF_LIB . $path;
	return gpf_load($pathfull, $class_name);
}//}}}

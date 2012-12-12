<?php
/**
 * GPF 主类。
 * 
 * @version 2012-05-05
 * @package default
 * @filesource
 */
class gpf
{
	static private $inc = array(); //保存已加载过的文件标记。
	static private $obj = array(); //保存对象实例。

	//log功能属性{{{
	const SYSTEM = 'SYSTEM';	//系统崩聩，如数据库无法链接
	const ERROR = 'ERROR';		//系统错误，如参数非法
	const WARN = 'WARN';		//警告信息，如调用废弃函数
	const FLOW = 'FLOW';		//流程信息，如是否需要更新html文件
	const SQL = 'SQL';		//数据库查错出错
	const INFO = 'INFO';		//普通信息
	const DEBUG = 'DEBUG';		//临时调试信息
	const DUE = 'DUE';		//调用过期（已废弃）的函数或代码

	const INPUT = 'INPUT';		//输入数据非法
	const NOTEXI = 'NOTEXI';	//数据不存在错误

	//用户自处理函数接口
	static public $user_func_callback = NULL;
	//从浏览器输出的错误等级及接口
	static public $output_level = array(
		self::SYSTEM => true,
		self::ERROR => true,
		self::WARN => true,
		self::FLOW => true,
		self::SQL => true,
		self::INFO => true,
		self::DEBUG => true,
		self::INPUT => true,
		self::NOTEXI => true,
		self::DUE => true,
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
	static public $style = array(
		self::SYSTEM => "background-color:red;color:yellow",
		self::ERROR => "color:red",
		self::WARN => "background-color:yellow",
		self::FLOW => "border:green solid 1px",
		self::SQL => "color:red",
		self::INFO => "",
		self::DEBUG => "color:red",
		self::INPUT => "color:red",
		self::NOTEXI => "color:red",
		self::DUE => "background-color:yellow",
		E_WARNING => "color:red",
		E_ERROR => "color:red",
		E_COMPILE_ERROR => "color:red",
		E_CORE_ERROR => "color:red",
		);

	//php本身的错误等级
	static public $log_txt = array(
			self::SYSTEM => self::SYSTEM,
			self::ERROR => self::ERROR,
			self::WARN => self::WARN,
			self::FLOW => self::FLOW,
			self::SQL => self::SQL,
			self::INFO => self::INFO,
			self::DEBUG => self::DEBUG,
			self::INPUT => self::INPUT,
			self::NOTEXI => self::NOTEXI,
			self::DUE => self::DUE,
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

	static private $log_data = array(); //保存信息数据
	static private $log_is_print = true; //设置是否在页面尾部输出 debug 信息
	//log属性}}}

	static private $shutdown_hook = array(); //挂载在 shutdown_function 调用的函数

	/**
	 * 构造方法声明为private，防止直接创建对象
	 */
	private function __construct() {
	}
	/**
	 * 把一个文件路径设为已加载。
	 * @param string $path 文件绝对路径。
	 */
	static private function _set_inc($path)
	{//{{{
		if (isset($path[33]))
			{
			//长度超过 32 位转为 md5
			$path = md5($path);
			}
		self::$inc[$path] = true;
	}//}}}
	/**
	 * 检测一个文件路径是否已加载
	 * @param string $path 文件绝对路径。
	 * @return bool
	 */
	static public function is_inc($path)
	{//{{{
		if (isset($path[33]))
			{
			//长度超过 32 位转为 md5
			$path = md5($path);
			}
		return isset(self::$inc[$path]);
	}//}}}
	/**
	 * 单次加载(require_once)
	 * @param string $path 文件绝对路径
	 */
	static public function inc($path)
	{//{{{
		if (!self::is_inc($path))
			{
			require $path;
			self::_set_inc($path);
			}
	}//}}}

	/**
	 * 对应索引是否已存在
	 */
	static public function is_obj($name)
	{//{{{
		return isset(self::$obj[$name]);
	}//}}}
	static public function obj_set($name, $obj)
	{//{{{
		self::$obj[$name] = $obj;
	}//}}}
	static public function obj_get($name)
	{//{{{
		return self::$obj[$name];
	}//}}}

	/**
	 * GPF 注册了 shutdown_function，因此需要注册此函数使用此函数注册。
	 * 增加页面结束前调用的函数,用于追加需要在页面结束时才能写入的数据，比如页面运行时间。
	 * @param mixed $callback {NULL: $name 就是对应 callback, false:删除 $name 的注册, callback:以 $name 为索引注册}
	 */
	static public function shutdown($name, $callback = NULL)
	{//{{{
		if (is_null($callback))
			{
			self::$shutdown_hook[] = $name;
			return ;
			}
		if (false === $callback)
			{
			unset(self::$shutdown_hook[$name]);
			return ;
			}
		self::$shutdown_hook[$name] = $callback;
	}//}}}

	/**
	 * 添加日志信息
	 * @param string $msg 信息内容
	 * @param int $level 信息等级
	 * @param string $file 所在文件(__FILE__)
	 * @param int $line 所在行号(__LINE__)
	 * @param string $func 所在函数(__FUNCTION__)
	 */
	static public function log($msg, $level = self::INFO, $file = '', $line = 0, $func = '')
	{//{{{
		self::$log_data[] = array(
			'msg' => $msg,
			'level' => $level,
			"file" => $file,
			"line" => $line,
			"func" => $func ? $func . '()' : '',
			);
		return true;
	}//}}}
	/**
	 * 设置页面结束时是否输出日志信息
	 */
	static public function log_is_print($is_print)
	{//{{{
		self::$log_is_print = $is_print ? true : false;
	}//}}}
	/**
	 * 接管或重置php的错误处理
	 */
	static public function log_php($is_register = true)
	{//{{{
		$err_level = E_ERROR | E_WARNING | E_PARSE;
		return $is_register ? set_error_handler(array("gpf", '_phperror'), $err_level) : restore_error_handler();
	}//}}}
	/**
	 * 用于接管php的错误处理
	 */
	static public function _phperror($errno, $errstr, $errfile, $errline)
	{//{{{
		if (error_reporting())
			{
			self::log($errstr, $errno, $errfile, $errline);
			}
	}//}}}
	/**
	 * 输出信息到浏览器
	 */
	static private function _log_flush()
	{//{{{
		//用户自处理接口
		if (!empty(self::$user_func_callback) && !empty(self::$log_data))
			{
			call_user_func(self::$user_func_callback, self::$log_data);
			}

		if (empty(self::$output_level) || !self::$log_is_print)
			{
			return ;
			}
		$count = count(self::$log_data);
		echo "<br/><font color=blue>Infomation:({$count})</font><hr/><div>\n";
		foreach (self::$log_data as $k => $v)
			{
			if (empty(self::$output_level[$v['level']]))
				{
				continue;
				}
			$br = isset($v['msg'][100]) ? '<br/><br/>' : '<br/>';	//长句加多个换行，更容易查看
			echo "<span style=\"", self::$style[$v['level']], "\">{$v['func']} {$v['msg']} [", self::$log_txt[$v['level']], "] {$v['file']}:{$v['line']}</span>{$br}\n";
			}
		echo "<br/><br/><br/></div>\n";
	}//}}}

	/**
	 * 注册到 register_shutdown_function 的处理函数
	 */
	static public function _shutdown_function()
	{//{{{
		//ggzhu@2012-01-30 若脚本被 Fatal error 中断， php 不会调用 set_error_handler 注册的函数处理。
		$error_last = error_get_last();
		//若没有 Fatal 中断 $error_last = null.
		if ($error_last)
			{
			self::log($error_last['message'], $error_last['type'], $error_last['file'], $error_last['line']);
			}

		//调用持载的函数
		foreach (self::$shutdown_hook as $v)
			{
			call_user_func($v);
			}

		//处理错误日志
		self::_log_flush();
	}//}}}
}

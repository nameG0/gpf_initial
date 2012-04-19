<?php
/**
 * 处理非操作信息，如日志，错误等

 * 2010-10-21 
 * @version 2012-01-30
 * @package default
 * @filesource
 */

/**
 * 把 $log 写入文件 $file, 用 flock() 函数应付对文件的并发访问
 */
function log_write($log, $file)
{//{{{
	$fp = fopen($file, 'ab');
	if (!$fp)
		{
		return false;
		}
	flock($fp, LOCK_EX);
	fwrite($fp, $log);
	flock($fp, LOCK_UN);
	fclose($fp);
	return true;
}//}}}

class log
{
	const SYSTEM = 'SYSTEM';	//系统崩聩，如数据库无法链接
	const ERROR = 'ERROR';		//系统错误，如参数非法
	const WARN = 'WARN';		//警告信息，如调用废弃函数
	const FLOW = 'FLOW';		//流程信息，如是否需要更新html文件
	const SQL = 'SQL';		//数据库查错出错
	const INFO = 'INFO';		//普通信息
	const DEBUG = 'DEBUG';		//临时调试信息
	const DUE = 'DUE';		//调用过期（已废弃）的函数或代码

	const INPUT = 'INPUT';		//输入数据非法
	const NOTEXI = 'NOTEXI';		//数据不存在错误

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

	static public $count = array();	//错误级别计数
	static public $hook = array();	//挂载在输出信息前需调用的函数
	static public $data = array();	//保存信息数据
	static public $is_register_shutdown_function = false;
	static public $is_print = true;	//设置是否在页面尾部输出 debug 信息

	/**
	 * 构造方法声明为private，防止直接创建对象
	 */
	private function __construct() 
	{
	}

	//添加信息
		//$msg(string)	信息内容
		//$level(int)	信息等级
		//$file(string)	所有文件(__FILE__)
		//$line(int)	所有行号(__LINE__)
		//$func(string)	所在函数(__FUNCTION__)
	static public function add($msg, $level = self::INFO, $file = '', $line = 0, $func = '')
	{//{{{
		if (!self::$is_register_shutdown_function)
			{
			register_shutdown_function(array('log', 'flush'));
			self::$is_register_shutdown_function = true;
			}
		self::$count[$level] = isset(self::$count[$level]) ? self::$count[$level] + 1 : 1;	//为了不触发Notice错误 
		self::$data[] = array(
			'msg' => $msg,
			'level' => $level,
			"file" => $file,
			"line" => $line,
			"func" => $func ? $func . '()' : '',
			);
		return true;
	}//}}}

	static public function hook($callback)
	{//{{{
		self::$hook[] = $callback;
	}//}}}

	static public function is_print($is_print)
	{//{{{
		self::$is_print = $is_print ? true : false;
	}//}}}

	/**
	 * 接管或重置php的错误处理
	 */
	static public function phperror($is_register = true)
	{//{{{
		$err_level = E_ERROR | E_WARNING | E_PARSE;
		return $is_register ? set_error_handler(array("log", 'php'), $err_level) : restore_error_handler();
	}//}}}

	/**
	 * 可接管php的错误处理
	 */
	static function php($errno, $errstr, $errfile, $errline)
	{//{{{
		if (error_reporting())
			{
			self::add($errstr, $errno, $errfile, $errline);
			}
	}//}}}

	static function flush()
	{//{{{
		//ggzhu@2012-01-30 若脚本被 Fatal error 中断， php 不会调用 set_error_handler 注册的函数处理。
		$error_last = error_get_last();
		//若没有 Fatal 中断 $error_last = null.
		if ($error_last)
			{
			log::add($error_last['message'], $error_last['type'], $error_last['file'], $error_last['line']);
			}

		//调用持载的函数
		foreach (self::$hook as $v)
			{
			call_user_func($v);
			}
		self::output();
		//用户自处理接口
		if (empty(self::$user_func_callback) || empty(self::$data))
			{
			return ;
			}
		call_user_func(self::$user_func_callback, self::$data);
	}//}}}

	/**
	 * 输出信息到浏览器
	 */
	static private function output()
	{//{{{
		//兼容旧控制常量
		if (defined('LOG_NOT_OUTPUT'))
			{
			self::$is_print = false;
			}
		if (empty(self::$output_level) || !self::$is_print)
			{
			return ;
			}
		$count = count(self::$data);
		echo "<br/><font color=blue>Infomation:({$count})</font><hr/><div>\n";
		foreach (self::$data as $k => $v)
			{
			if (empty(self::$output_level[$v['level']]))
				{
				continue;
				}
			$br = isset($v['msg'][100]) ? '<br/><br/>' : '<br/>';	//长句加多个换行，更容易查看
			echo "<span style=\"", self::$style[$v['level']], "\">{$v['func']} {$v['msg']} [", self::$log_txt[$v['level']], "] {$v['file']}:{$v['line']}</span>{$br}\n";
			}
		//兼容 $debug
		global $debug;
		echo nl2br($debug);
		echo "<br/><br/><br/></div>\n";
	}//}}}
}
?>

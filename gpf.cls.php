<?php
/**
 * GPF 主类。
 * 
 * 在类方法中使用中断式错误提示(showmessage)方案。
 * 在类中直接进行错误提示主要是为方便使用，无需调用者每次都要手动处理错误信息。
 * 需要做错误提示的类直接调用 gpf::err()。
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
	//}}}
	
	//gerr 功能的属性{{{
	static private $error_func = 'exit'; //callback|NULL 操作出错时自动进行提示的提示函数。
	static private $error = ''; //错误提示信息。
	static private $is_pass = false; //标记流程是否正常。
	static private $pass_num = 0; //流程计数器，每调用一次 start() 加1.
	//}}}

	//ghook 功能的属性{{{
	static private $obj_hook = array();
	static private $obj_callback = array();
	//}}}
	
	static private $url_count = 0; //替换词计数器
	static private $url_name = array(); //保存URL替换词
	static private $url_search = array(); //方便URL替换词
	static private $url_replace = array(); //方便URL替换词
	static private $url_func = array(); //保存URL回调函数。

	//input 类配置
	static public $unadds = false; //不进行引号过滤

	static private $shutdown_hook = array(); //挂载在 shutdown_function 调用的函数

	/**
	 * 构造方法声明为private，防止直接创建对象
	 */
	private function __construct() {
	}
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
	 * 一个流程开始时调用
	 */
	static public function pnew()
	{//{{{
		self::$pass_num++;
		if (1 === self::$pass_num)
			{
			self::$is_pass = true;
			self::$error = '';
			}
	}//}}}
	/**
	 * 一个流程结束后调用
	 */
	static public function pend()
	{//{{{
		self::$pass_num--;
		if (self::$pass_num < 1)
			{
			self::$pass_num = 0;
			self::$is_pass = false;
			}
	}//}}}
	/**
	 * 检查流程是否正常，不正常直接提示并中断程序运行。
	 */
	static public function pcheck()
	{//{{{
		if (!self::$is_pass)
			{
			self::err("流程出错");
			}
	}//}}}
	/**
	 * 进行错误提示
	 * @param string $error 错误提示信息。
	 * @param string 所有文件(__FILE__)
	 * @param int 所有行号(__LINE__)
	 * @param string 所在函数(__FUNCTION__)
	 */
	static public function err($error, $file = '', $line = 0, $func = '')
	{//{{{
		self::$error = $error;
		self::$is_pass = false;
		self::log("(GERR){$error}", self::FLOW, $file, $line, $func);
		if (self::$error_func)
			{
			if (!is_callable(self::$error_func))
				{
				exit($error);
				}
			call_user_func(self::$error_func, $error);
			}
	}//}}}
	/**
	 * 取错误提示内容。
	 */
	static public function err_get()
	{//{{{
		return self::$error;
	}//}}}
	/**
	 * 设置自动提示函数
	 * @param NULL|callback $func_name 错误处理函数，NULL 表示自动提示函数。
	 */
	static public function err_func($func_name)
	{//{{{
		if (is_null($func_name))
			{
			$func_name = 'exit';
			}
		self::$error_func = $func_name;
	}//}}}

	/**
	 * 实例化并返回 hook 对象。
	 * @param string $mod_name 模块名。eg. member
	 * @param string $class_name hook类名。eg. base -> h_base.class.php -> h_member_base
	 */
	static public function hook($mod_name, $class_name)
	{//{{{
		$class_name_full = "h_{$mod_name}_{$class_name}";
		if (isset(self::$obj_hook[$class_name_full]))
			{
			return self::$obj_hook[$class_name_full];
			}
		if (!class_exists($class_name_full))
			{
			$_path = PHPCMS_ROOT . "{$mod_name}/hook/h_{$class_name}.class.php";
			if (!is_file($_path))
				{
				log::add("hook 类不存在[{$_path}]", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
				self::$obj_hook[$class_name_full] = false;
				return false;
				}
			require $_path;
			if (!class_exists($class_name_full))
				{
				log::add("hook 类未定义[{$class_name_full}]", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
				self::$obj_hook[$class_name_full] = false;
				return false;
				}
			}
		$obj = new $class_name_full();
		self::$obj_hook[$class_name_full] = $obj;
		return $obj;
	}//}}}
	/**
	 * 加载并返回 callback 对象数组。
	 * <pre>
	 * 提供给 hook 类使用，调用格式：$list = ghook::load(mod_name, __CLASS__, __FUNCTION__);
	 * </pre>
	 * @param string $class_name hook 类完整类名，一般使用 __CLASS__。eg. h_member_base
	 */
	static public function hook_load($mod_name, $class_name, $func_name)
	{//{{{
		if (!isset(self::$obj_callback["{$mod_name}/{$class_name}"]))
			{
			self::$obj_callback["{$mod_name}/{$class_name}"] = self::_load_callback($mod_name, $class_name);
			}
		$obj_list = array();
		foreach (self::$obj_callback["{$mod_name}/{$class_name}"] as $_obj)
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
	static private function _load_callback($mod_name, $class_name)
	{//{{{
		$mod_callback = self::_hook_mod_file($mod_name);
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
					log::add("callback 文件不存在[{$_path}]", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
					continue;
					}
				require $_path;
				if (!class_exists($class_name_full))
					{
					log::add("callback 类未定义[{$class_name_full}]", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
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
	static private function _hook_mod_file($mod_name)
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

	/**
	 * 用于输出完整的URL
	 * @param string $url 用 @ 表示替换词，用 {func_name:args} 表示执行函数
	 */
	static public function url($url)
	{//{{{
		return 'http://' . str_replace(self::$url_search, self::$url_replace, $url);
	}//}}}
	/**
	 * 设置URL替换词
	 */
	static public function url_name($name, $value)
	{//{{{
		$name = '@' . $name;
		if (isset(self::$url_name[$name]))
			{
			$k = self::$url_name[$name];
			self::$url_search[$k] = $name;
			self::$url_replace[$k] = $value;
			return ;
			}
		$k = self::$url_count++;
		self::$url_name[$name] = $k;
		self::$url_search[$k] = $name;
		self::$url_replace[$k] = $value;
	}//}}}
	static public function url_func($name, $value)
	{//{{{
		//todo 未实现
		//开始和结束标记使用类静态变量定义（即 { 和 }）
		//因为模板引擎常用占用 { 和 }，所以可考虑改用 [ 和 ]
	}//}}}

	//如果取不到数据，直接返回默认值，无需过滤。
	//所以取值和过滤引号和过滤函数作为一个函数。返回默认值为另一个函数。
	static private function _input_get($data, $name, $filter)
	{//{{{
		if (!isset($data[$name]))
			{
			return NULL;
			}
		$value = $data[$name];
		if (!get_magic_quotes_gpc())
			{
			$value = gpf_adds($value);
			}
		if ($filter)
			{
			
			}
		return $value;
	}//}}}
	/**
	 * 取 $_GET 数据
	 */
	static public function get($name, $filter = 'gpf_html', $def_val = NULL, $def_if = '!isset')
	{//{{{
		//取数据（包括处理默认值）
		//过滤引号
		//过滤函数
	}//}}}
}

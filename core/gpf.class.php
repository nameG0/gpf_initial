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
	static private $cfg = array(); //存放非常量配置项的值.

	/**
	 * 构造方法声明为private，防止直接创建对象
	 */
	private function __construct() {}
	/**
	 * 进行初始化工作
	 */
	static function init()
	{//{{{
		//默认加载的模块。
		mod_init('rdb');
		mod_init('siud');
		mod_init('tpl');
		mod_init('admin');
		//默认加载的 gpf/lib
		require_once GPF_PATH_LIB . "input.cls.php";
		require_once GPF_PATH_LIB . "gpf.func.php";
		require_once GPF_PATH_LIB . "array.cls.php";
		require_once GPF_PATH_LIB . "filesystem.lib.php";
		require_once GPF_PATH_LIB . "cache_file.lib.php";

		self::$cfg = require GPF_PATH . "cfg.inc.php";
	}//}}}
	/**
	 * 调度器
	 * <pre>
	 * 默认使用 $_GET["a"] 做调度器。a 是第一个英文字母，也作 action.
	 * 格式： a=m,f,a
	 * 用 , 分隔，m为模块，f为控制器文件，a为动作。
	 * </pre>
	 */
	static function a()
	{//{{{
		list($mod, $file, $action) = explode(",", $_GET['a']);
		//默认使用 main,index,
		if (!$mod)
			{
			$mod = 'main';
			}
		if (!$file)
			{
			$file = 'index';
			}
		if (!$action)
			{
			$action = 'index';
			}
		if (!defined('CTRL_MOD'))
			{
			//此常量只用于方便取默认值。
			define('CTRL_MOD', $mod);
			define('CTRL_FILE', $file);
			define('CTRL_ACTION', $action);
			}
		mod_init($mod);

		//------ 源与副本中的控制器 ------
		//若源目录中有指定的控制器，总是会 include 。
		//若副本中定义有指定的控制器，将实例化副本中定义的控制器。
		//副本中控制器定义文件名与源中的定义文件名一致。
		//副本中的控制器类名为 {源中控制器类名}_inst. eg. ctrl_control_inst
		//若控制器只定义在副本中，类名不需要加 _inst 后序。 eg. ctrl_control
		//因为已加载源中的控制器，建议副本中定义的控制器直接继承源中的控制器。
		//注：此处的“控制器”都是指 GET 参数中指定要调用的控制器。
		//------
		$ModInfo = mod_info($mod);
		$is_include_success = false; //标记是否至少已加载一个控制器。
		$ctrl_path = $ModInfo['path_sour'] . 'control' . DS . $file . '.class.php';
		if (is_file($ctrl_path))
			{
			include_once $ctrl_path;
			$is_include_success = true;
			}
		$ctrl_path = $ModInfo['path_inst'] . 'control' . DS . $file . '.class.php';
		if (is_file($ctrl_path))
			{
			include_once $ctrl_path;
			$is_include_success = true;
			}
		if (!$is_include_success)
			{
			echo "control not exists.";
			exit;
			}

		//实例化控制器类。
		$ctrl_class_inst = "ctrl_{$file}_inst";
		$ctrl_class = "ctrl_{$file}";
		if (class_exists($ctrl_class_inst))
			{
			$o_ctrl = new $ctrl_class_inst();
			log::add("control:{$ctrl_class_inst}", log::INFO, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			}
		else if (class_exists($ctrl_class))
			{
			$o_ctrl = new $ctrl_class();
			log::add("control:{$ctrl_class}", log::INFO, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			}
		else
			{
			echo 'class not exists';
			exit;
			}

		//ggzhu@2012-07-18 控制器方法使用 action_ 作为命名前序，这样就可以使用像 list 这样的关键字作为动作名。
		$action = 'action_' . $action;
		//调用控制器方法。
		if (!method_exists($o_ctrl, $action))
			{
			echo "action not exists";
			exit;
			}
		log::add("action:{$action}", log::INFO, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
		$o_ctrl->$action();
	}//}}}
	static function url($arg)
	{//{{{
		return ctrl_url($arg);
	}//}}}
	/**
	 * 取非常量配置项的值
	 */
	static function cfg($name)
	{//{{{
		return self::$cfg[$name];
	}//}}}
	static function cfg_set($name, $value)
	{//{{{
		self::$cfg[$name] = $value;
	}//}}}
}

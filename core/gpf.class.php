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
	/**
	 * 进行初始化工作
	 */
	static function init()
	{//{{{
		//加载 rdb 模块。
		mod_init('rdb');
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
		if (!defined('CURRENT_MOD'))
			{
			//此常量只用于方便取默认值。
			define('CURRENT_MOD', $mod);
			}
		$mod_path = mod_info($mod, 'path_full');
		//实例化控制器类。
		$ctrl_path = $mod_path . 'control' . DS . $file . '.class.php';
		log::add("control:{$ctrl_path}", log::INFO, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
		if (!is_file($ctrl_path))
			{
			echo "control not exists.";
			exit;
			}
		include_once $ctrl_path;
		$ctrl_class = "ctrl_{$file}";
		$o_ctrl = new $ctrl_class();
		//调用控制器方法。
		if (!method_exists($o_ctrl, $action))
			{
			echo "action not exists";
			exit;
			}
		log::add("action:{$action}", log::INFO, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
		$o_ctrl->$action();
	}//}}}
}

<?php 
/**
 * 表单项输出库, html_dom(hd)
 * <pre>
 * 2011-05-25
 * callback:siud_hd.func.php
 * 扩展函数命名规则：hd_{mod}__{name}
 * eg. hd_editor__editor
 * 定义原形：
 * string IO(array $attr)
 *
 * 通用特殊属性：
 * 	_attr	在表单项标签内输出的 html 代码，如 <input 在这个位置输出>
 * 	_html	在表单项标签外输出的 html 代码，如 <input />在这个位置输出
 * </pre>
 * 
 * @package api
 * @filesource
 */

/**
 * 入口。
 * @param string $first 以字符串形式表达的参数，格式为 "text|id=aaa|..." 第一个为命名，格式为 {mod}.{name}, 不输入 mod 则默认为 html_dom 本身的表单元素。
 * @param array|string $attr 以数组形式表达的参数,若所调用的功能支持字符串参数(如html),也可以为字符串。为字符串时不能在$first中表达参数。
 * @return string
 */
function hd($first, $attr = array())
{//{{{
	static $mod_ed = array();	//记录已加载的模块扩展。

	list($call, $attr_str) = explode("|", $first, 2);
	//------ 加载模块扩展文件 ------
	list($mod, $name) = explode(".", $call);
	if (!$name)
		{
		//eg. "html"
		$name = $mod;
		$mod = ''; //本模块不需模块前序。
		}
	if (!isset($mod_ed[$mod]))
		{
		$mod_ed[$mod] = false;

		//html_dom 本身的功能 $mod 为空。
		$_mod = $mod ? $mod : 'siud';
		//加载对应模块的扩展。
		$callback = mod_callback($_mod, 'p');
		foreach ($callback as $k => $v)
			{
			$path = $v . "siud_hd.func.php";
			if (is_file($path))
				{
				include_once $path;
				$mod_ed[$mod] = true;
				break;
				}
			}
		unset($_mod);
		if (!$mod_ed[$mod])
			{
			log::add("找不到 {$mod} 模块 callback 扩展", log::WARN, __FILE__, __LINE__, __FUNCTION__);
			return '';
			}
		}

	if ($mod)
		{
		//扩展函数的命名规则： hd_{mod}__{name}
		$mod .= '__';
		}
	$func_name = "hd_{$mod}{$name}";
	if (!function_exists($func_name))
		{
		log::add("扩展函数 {$func_name} 不存在", log::WARN, __FILE__, __LINE__, __FUNCTION__);
		//todo 支持别名，查不到函数后从别名中查。
		return '';
		}

	//------ 把字符串参数分解为数组 ------
	if ($attr_str)
		{
		$_attr = explode("|", $attr_str);
		foreach ($_attr as $v)
			{
			//只认左边第一个 = 号，后面的内容都作为参数值。
			//eg. ...|value=aaa=1\nbbb=2|...
			list($_k, $_v) = explode("=", $v, 2);
			if (is_null($_v))
				{
				//eg. ...|br|...
				$_k = $v;
				$_v = true;
				}
			$attr[$_k] = $_v;
			}
		unset($attr_str, $_attr, $v, $_k, $_v);
		}

	return $func_name($attr);
}//}}}

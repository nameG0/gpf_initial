<?php
/**
 * 分页函数库
 * <pre>
 * callback:siud_paging
 * </pre>
 * 
 * @package api
 * @filesource
 */

/*
==============================常量==============================
*/
define('PAGING_PAGESIZE', 20);	//默认 pagesize 大小

/*
==============================函数==============================
*/

/**
 * 从get参数中取得当前页数
 * @param string $name 记录当前页数的get变量 -- 'page'
 */
function paging_current($name = 'page')
{//{{{
	return max(intval($_GET[$name]), 1);
}//}}}

/**
 * 取 pagesize，按 $default , $_GET, PAGING_PAGESIZE 的优先级取值
 * @param string $name	表示 pagesize 的 GET 键名
 * @param int $max	pagesize 允许的最大值
 */
function paging_pagesize($default = 0, $name = 'pagesize', $max = 1000)
{//{{{
	$pagesize = intval($default);
	$pagesize = $pagesize ? $pagesize : intval($_GET[$name]);
	$pagesize = $pagesize ? $pagesize : PAGING_PAGESIZE;
	return min($pagesize, $max);
}//}}}

/**
 * 计算当前页数开始的记录数，则offset
 * @param int $pagesize 每页的记录数
 * @param int $current 当前页数标记，可直接指定为数字，如为字符串，则以此为参数调用 paging_current() 取出，如不指定，则无参数调用 paging_current() 取得
 */
function paging_offset($pagesize = 0, $current = 0)
{//{{{
	if (!$current)
		{
		$current = paging_current();
		}
	else if (is_string($current))
		{
		$current = paging_current($current);
		}
	$pagesize = paging_pagesize($pagesize);
	
	return $pagesize * ($current - 1);
}//}}}

/**
 * 分页入口
 * @param int $count 总数
 * @param int $pagesize 每页记录数
 * @param int $current 当前页,说明参见 paging_offset() 同名参数
 * @param int $urlrule URL规则
 * @param int $style 分而显示的样式
 */
function paging($count, $pagesize = 0, $current = 0, $urlrule = '', $style = 'default')
{//{{{
	if (!$current)
		{
		$current = paging_current();
		}
	else if (is_string($current))
		{
		$current = paging_current($current);
		}
	
	$pagesize = paging_pagesize($pagesize);

	if (!$urlrule)
		{
		$urlrule = $_SERVER['PHP_SELF'] . '?' . preg_replace(array("/&?page=\d*/", '/&?pagesize=[^&]*/'), '', $_SERVER['QUERY_STRING']) . "&page={\$page}&pagesize={$pagesize}";
		}
	$page_count = ceil($count / $pagesize);
	$func = "paging_style_{$style}";
	if (!function_exists($func))
		{
		$func = "paging_style_default";
		}
	$page_current = $current;
	$path = _paging_style($style);
	if (!$path)
		{
		return '';
		}
	include $path;
	return $pages;
}//}}}

/**
 * 生成分页链接,一般供分页输出函数调用
 */
function _paging_url($urlrule, $current)
{//{{{
	return str_replace('{$page}', $current, $urlrule);
}//}}}

/**
 * 取指定分页样式的绝对路径
 * <pre>
 * 可以通过重写此函数自定义分页样式的存放规则。
 * </pre>
 * @param string $style 分页样式。格式：[mod/]name,省略模块则使用本模块的样式。eg. content/paging = content模块的paging样式。eg. default_paging = 使用本模声的 default_paging 样式。
 * @return string|false 分页样式绝对路径，找不到分页样式时返回false
 */
function _paging_style($style)
{//{{{
	list($mod, $name) = explode("/", $style, 2);
	if (!$name)
		{
		$name = $mod;
		$mod = 'siud';
		}
	$dir_list = mod_callback($mod, 'p');
	foreach ($dir_list as $v)
		{
		$path = "{$v}siud_paging/{$name}.php";
		if (is_file($path))
			{
			return $path;
			}
		}
	return false;
}//}}}

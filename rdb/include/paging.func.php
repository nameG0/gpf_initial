<?php
/*
2010-9-3 分页函数库
*/

/*
==============================常量==============================
*/
define('PAGING_PAGESIZE', 20);	//默认 pagesize 大小

/*
==============================语言==============================
*/
global $PAGING_LANG;
$PAGING_LANG['first'] = 'First Page';
$PAGING_LANG['first_button'] = '&lt;&lt;';	//<<
$PAGING_LANG['previous'] = 'Previous Page';
$PAGING_LANG['previous_button'] = '&lt;';	//<
$PAGING_LANG['next'] = 'Next Page';
$PAGING_LANG['next_button'] = '&gt;';		//>
$PAGING_LANG['last'] = 'Last Page';
$PAGING_LANG['last_button'] = '&gt;&gt;';	//>>
$PAGING_LANG['count'] = '共';
$PAGING_LANG['page'] = '页';
$PAGING_LANG['units'] = '项';
$PAGING_LANG['pagesize'] = '每页';
$PAGING_LANG['current'] = '第';
$PAGING_LANG['setting'] = '设置';

/*
==============================函数==============================
*/

/**
 * 从get参数中取得当前页数
 * $name	记录当前页数的get变量 -- 'page'
 */
function paging_current($name = 'page')
{//{{{
	return max(intval($_GET[$name]), 1);
}//}}}

//取 pagesize，按 $default , $_GET, PAGING_PAGESIZE 的优先级取值
	//$name	表示 pagesize 的 GET 键名
	//$max	pagesize 允许的最大值
function paging_pagesize($default = 0, $name = 'pagesize', $max = 1000)
{
	$pagesize = intval($default);
	$pagesize = $pagesize ? $pagesize : intval($_GET[$name]);
	$pagesize = $pagesize ? $pagesize : PAGING_PAGESIZE;
	return min($pagesize, $max);
}

//计算当前页数开始的记录数，则offset
//require	paging_current()
//$pagesize	每页的记录数
//$current	当前页数标记，可直接指定为数字，如为字符串，则以此为参数调用 paging_current() 取出，如不指定，则无参数调用 paging_current() 取得
function paging_offset($pagesize = 0, $current = 0)
{
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
}

//分页入口
//require	paging_current()
//$count	总数
//$pagesize	每页记录数
//$current	当前页,说明参见 paging_offset() 同名参数 -- 0
//$urlrule	URL规则 -- ''
//$style	分而显示的样式 -- 'default'
function paging($count, $pagesize = 0, $current = 0, $urlrule = '', $style = 'default')
{
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
	return $func($page_count, $current, $urlrule, $count, $pagesize);
}

//生成分页链接
//一般供分页输出函数调用
function paging_url($urlrule, $current)
{
	return str_replace('{$page}', $current, $urlrule);
}

/*
==============================分页输出样式函数==============================
函数名规则： paging_style_样式名
*/

//分页输出
//分页格式 << < 1 2 3 4 ... 100 > >>
function paging_style_default($pageCount, $current, $urlrule)
{
	global $PAGING_LANG;
	$ret = '';
	if ($pageCount > 1)
		{
		$page = 11;
		$offset = 4;
		$from = $current - $offset;
		$to = $current + $offset;
		$more = 0;
		if($page >= $pageCount)
			{
			$from = 2;
			$to = $pageCount - 1;
			}
		else
			{
			if($from <= 1)
				{
				$to = $page - 1;
				$from = 2;
				}
			elseif($to >= $pageCount)
				{
				$from = $pageCount- ($page - 2);
				$to = $pageCount-1;
				}
			$more = 1;
			}

		$ret .= ' <a href="' . paging_url($urlrule, 1) . "\" title=\"{$PAGING_LANG['first']}\">{$PAGING_LANG['first_button']}</a> ";
		if ($current > 0)
			{
			$ret .= ' <a href="' . paging_url($urlrule, $current - 1) . "\" title=\"{$PAGING_LANG['previous']}\">{$PAGING_LANG['previous_button']}</a> ";
			if ($current == 1)
				{
				$ret .= '<a id="pageNow"><b title="1">1</b></a> ';
				}
			else if ($current > 6 && $more)
				{
				$ret .= '<a href="' . paging_url($urlrule, 1) . '" title="1">1</a><a>..</a>';
				}
			else
				{
				$ret .= '<a href="' . paging_url($urlrule, 1) . '" title="1">1</a> ';
				}
			}
		for ($i = $from; $i <= $to; $i++)
			{
			if($i != $current)
				{
				$ret .= '<a href="' . paging_url($urlrule, $i) . "\" title=\"{$i}\" >{$i}</a> ";
				}
			else
				{
				$ret .= " <a id=\"pageNow\"><b title=\"{$i}\">{$i}</b></a> ";
				}
			}
		if ($current < $pageCount)
			{
			if ($current < $pageCount - 5 && $more)
				{
				$ret .= '<a>...</a>';
				}
			$ret .= '<a href="' . paging_url($urlrule, $pageCount) . "\" title=\"{$pageCount}\">{$pageCount}</a> <a href=\"" . paging_url($urlrule, $current + 1) . "\" title=\"{$PAGING_LANG['next']}\">{$PAGING_LANG['next_button']}</a> ";
			}
		else if ($current == $pageCount)
			{
			$ret .= " <a id=\"pageNow\"><b title=\"{$pageCount}\">{$pageCount}</b></a><a href=\"" . paging_url($urlrule, $current) . "\" title=\"{$PAGING_LANG['next']}\" >{$PAGING_LANG['next_button']}</a> ";
			}
		$ret .= ' <a href="' . paging_url($urlrule, $pageCount) . "\" title=\"{$PAGING_LANG['last']}\" >{$PAGING_LANG['last_button']}</a> ";
		}
	return $ret;
}

//比 default 样式多了一些表单输出
function paging_style_default_form($page_count, $current, $urlrule, $count, $pagesize)
{
	global $PAGING_LANG;
	$html = paging_style_default($page_count, $current, $urlrule);
	$html .= "{$PAGING_LANG['count']} {$page_count} {$PAGING_LANG['page']} {$count} {$PAGING_LANG['units']} \n";
	$html .= "{$PAGING_LANG['pagesize']} <input id=\"paging_pagesize\" type=\"text\" value=\"{$pagesize}\" style=\"width:50px;\" /> {$PAGING_LANG['units']} \n";
	$html .= "{$PAGING_LANG['current']} <input id=\"paging_current\" type=\"text\" value=\"{$current}\" style=\"width:50px;\" {$PAGING_LANG['page']} /> ";
	$urlrule = preg_replace(array("/&?page=\d*/", '/&?pagesize=[^&]*/'), '', $urlrule);
	$html .= "<input type=\"button\" value=\"{$PAGING_LANG['setting']}\" onclick=\"location.href='{$urlrule}&pagesize='+$(this).prev().prev().val()+'&page='+$(this).prev().val();\" />";
	//$html .= "<input type=\"button\" value=\"{$PAGING_LANG['setting']}\" onclick=\"location.href='{$urlrule}&pagesize='+document.getElementByID('paging_pagesize').value+'&page='+document.getElementByID('paging_current').value;\" />";
	return $html;
}

//分页输出
//分页格式 << < 1/334 > >>
function paging_style_next($pageCount, $current, $urlrule)
{
	global $PAGING_LANG;
	$ret = "";
	if ($pageCount > 1)
	{
		$ret .= ' <a href="' . paging_url($urlrule, 1) . "\" title=\"{$PAGING_LANG['first']}\" >{$PAGING_LANG['first_button']}</a> ";
		if ($current > 0)
			{
			$ret .= ' <a href="' . paging_url($urlrule, $current - 1) . "\" title=\"{$PAGING_LANG['previous']}\" >{$PAGING_LANG['previous_button']}</a> ";
			}
		else
			{
			$ret .= " {$PAGING_LANG['previous_button']} ";
			}
		$ret .= " {$current}/{$pageCount} ";
		if ($current < $pageCount)
			{
			$ret .= ' <a href="' . paging_url($urlrule, $current + 1) . "\" title=\"{$PAGING_LANG['next']}\" >{$PAGING_LANG['next_button']}</a> ";
			}
		else
			{
			$ret .= " {$PAGING_LANG['next_button']} ";
			}
		$ret .= ' <a href="' . paging_url($urlrule, $pageCount) . "\" title=\"{$PAGING_LANG['last']}\" >{$PAGING_LANG['last_button']}</a> ";
	}
	return $ret;
}

//2011-1-10 新分页函数，直接调用 phppages() ，但多了一些表单输出
function pagesi($count, $pagesize = 0, $page = 0, $url = '')
{
	if (!$pagesize)
		{
		$pagesize = paging_pagesize();
		}
	if (!$page)
		{
		$page = paging_current();
		}
	$pages = ceil($count / $pagesize);
	$_url = $url ? $url : preg_replace(array('/&?page=[^&]*/', '/&?pagesize=[^&]*/'), array('', ''), URL);
	if ($pages > 1)
		{
		$html .= "<a href=\"{$_url}&pagesize={$pagesize}&page=1\">首页</a>\n";
		$html .= phppages($count, $page, $pagesize, $url);
		$html .= "<a href=\"{$_url}&pagesize={$pagesize}&page={$pages}\">尾页</a>\n";
		}
	if ($page)
		{
		$html .= "共 {$pages} 页 {$count} 条记录 \n";
		$html .= "每页  <input type=\"text\" value=\"{$pagesize}\" style=\"width:50px;\" /> 条记录 \n";
		$html .= "第 <input type=\"text\" value=\"{$page}\" style=\"width:50px;\" />页";
		$html .= "<input type=\"button\" value=\"设置\" onclick=\"location.href='{$_url}&pagesize='+$(this).prev().prev().val()+'&page='+$(this).prev().val();\" />";
		}
	return $html;
}
?>

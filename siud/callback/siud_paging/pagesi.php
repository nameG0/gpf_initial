<?php
/**
 * 新分页函数，直接调用 phppages() ，但多了一些表单输出
 * <pre>
 * << < 1/334 > >>
 * </pre>
 * 
 * @package default
 * @filesource
 */

$LANG['paging/first'] = 'First Page';
$LANG['paging/first_button'] = '&lt;&lt;';	//<<
$LANG['paging/previous'] = 'Previous Page';
$LANG['paging/previous_button'] = '&lt;';	//<
$LANG['paging/next'] = 'Next Page';
$LANG['paging/next_button'] = '&gt;';		//>
$LANG['paging/last'] = 'Last Page';
$LANG['paging/last_button'] = '&gt;&gt;';	//>>

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

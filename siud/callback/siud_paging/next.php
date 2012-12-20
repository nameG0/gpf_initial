<?php
/**
 * 上一页与下一页
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

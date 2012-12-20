<?php
/**
 * 默认分页样式
 * <pre>
 * << < 1 2 3 4 ... 100 > >>
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

$pages = '';
if ($page_count > 1)
	{
	$page = 11;
	$offset = 4;
	$from = $page_current - $offset;
	$to = $page_current + $offset;
	$more = 0;
	if($page >= $page_count)
		{
		$from = 2;
		$to = $page_count - 1;
		}
	else
		{
		if($from <= 1)
			{
			$to = $page - 1;
			$from = 2;
			}
		elseif($to >= $page_count)
			{
			$from = $page_count- ($page - 2);
			$to = $page_count-1;
			}
		$more = 1;
		}

	$pages .= ' <a href="' . _paging_url($urlrule, 1) . "\" title=\"{$LANG['paging/first']}\">{$LANG['paging/first_button']}</a> ";
	if ($page_current > 0)
		{
		$pages .= ' <a href="' . _paging_url($urlrule, $page_current - 1) . "\" title=\"{$LANG['paging/previous']}\">{$LANG['paging/previous_button']}</a> ";
		if ($page_current == 1)
			{
			$pages .= '<a id="pageNow"><b title="1">1</b></a> ';
			}
		else if ($page_current > 6 && $more)
			{
			$pages .= '<a href="' . _paging_url($urlrule, 1) . '" title="1">1</a><a>..</a>';
			}
		else
			{
			$pages .= '<a href="' . _paging_url($urlrule, 1) . '" title="1">1</a> ';
			}
		}
	for ($i = $from; $i <= $to; $i++)
		{
		if($i != $page_current)
			{
			$pages .= '<a href="' . _paging_url($urlrule, $i) . "\" title=\"{$i}\" >{$i}</a> ";
			}
		else
			{
			$pages .= " <a id=\"pageNow\"><b title=\"{$i}\">{$i}</b></a> ";
			}
		}
	if ($page_current < $page_count)
		{
		if ($page_current < $page_count - 5 && $more)
			{
			$pages .= '<a>...</a>';
			}
		$pages .= '<a href="' . _paging_url($urlrule, $page_count) . "\" title=\"{$page_count}\">{$page_count}</a> <a href=\"" . _paging_url($urlrule, $page_current + 1) . "\" title=\"{$LANG['paging/next']}\">{$LANG['paging/next_button']}</a> ";
		}
	else if ($page_current == $page_count)
		{
		$pages .= " <a id=\"pageNow\"><b title=\"{$page_count}\">{$page_count}</b></a><a href=\"" . _paging_url($urlrule, $page_current) . "\" title=\"{$LANG['paging/next']}\" >{$LANG['paging/next_button']}</a> ";
		}
	$pages .= ' <a href="' . _paging_url($urlrule, $page_count) . "\" title=\"{$LANG['paging/last']}\" >{$LANG['paging/last_button']}</a> ";
	}

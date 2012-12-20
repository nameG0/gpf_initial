<?php
/**
 * 比 default 样式多了一些表单输出
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

$PAGING_LANG['count'] = '共';
$PAGING_LANG['page'] = '页';
$PAGING_LANG['units'] = '项';
$PAGING_LANG['pagesize'] = '每页';
$PAGING_LANG['current'] = '第';
$PAGING_LANG['setting'] = '设置';

$html = paging_style_default($page_count, $current, $urlrule);
$html .= "{$PAGING_LANG['count']} {$page_count} {$PAGING_LANG['page']} {$count} {$PAGING_LANG['units']} \n";
$html .= "{$PAGING_LANG['pagesize']} <input id=\"paging_pagesize\" type=\"text\" value=\"{$pagesize}\" style=\"width:50px;\" /> {$PAGING_LANG['units']} \n";
$html .= "{$PAGING_LANG['current']} <input id=\"paging_current\" type=\"text\" value=\"{$current}\" style=\"width:50px;\" {$PAGING_LANG['page']} /> ";
$urlrule = preg_replace(array("/&?page=\d*/", '/&?pagesize=[^&]*/'), '', $urlrule);
$html .= "<input type=\"button\" value=\"{$PAGING_LANG['setting']}\" onclick=\"location.href='{$urlrule}&pagesize='+$(this).prev().prev().val()+'&page='+$(this).prev().val();\" />";
//$html .= "<input type=\"button\" value=\"{$PAGING_LANG['setting']}\" onclick=\"location.href='{$urlrule}&pagesize='+document.getElementByID('paging_pagesize').value+'&page='+document.getElementByID('paging_current').value;\" />";
return $html;

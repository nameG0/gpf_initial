<?php
/**
 * API
 * 
 * @package default
 * @filesource
 */

/**
 * 方便地在列表页输出文章 title 字段，带 a 标签，自动截取标题长度
 *
 * 输出的 html 代码看上去像：
 * <code>
 * <a href="content/show.php?contentid=1" title="full title">short title ...<a/>
 * </code>
 */
function content_output_title($r, $length = 18, $target = '', $title_before = '')
{//{{{
	$url = $r['url'] ? $r['url'] : "content/show.php?contentid={$r['contentid']}";
	$title = $length ? str_cut($r['title'], $length) : $r['title'];
	$target = $target ? "target=\"{$target}\"" : '';
	return "<a href=\"{$url}\" title=\"{$r['title']}\" {$target} >{$title_before}{$title}</a>";
}//}}}
?>

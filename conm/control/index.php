<?php
/**
 * 内容首页，即显示首页模板
 * 
 * @package default
 * @filesource
 */
require dirname(__FILE__) . '/include/header.inc.php';

$catid = intval($catid);
if(!isset($CATEGORY[$catid])) showmessage('访问的栏目不存在！');

$C = category_get($catid);
extract($C);

//if(!$priv_group->check('catid', $catid, 'browse', $_groupid)) showmessage('您没有浏览权限');

if($type == 0)
	{
	//提取模板
	$page = max(intval($page), 1);
	//取栏目首页模板
	$arrchildid = subcat('phpcms', $catid);
	list($tpl_mod, $template) = explode(',', $template_category);
	}
else if (1 == $type)
	{
	list($tpl_mod, $template) = explode(',', $template);
	}
elseif($type == 2)
	{
	//链接类栏目，跳转
	header('location:'.$url);
	}
if (!$tpl_mod)
	{
	showmessage('此栏目未允许显示');
	}
//$tpl_mod = $tpl_mod ? $tpl_mod : 'content';
//$template = $template ? $template : 'list';

$catlist = submodelcat($modelid);
$arrparentid = explode(',', $arrparentid);
$parentid = $arrparentid[1];

//页面关键词，用于 SEO
$head['title'] = $catname.'_'.($meta_title ? $meta_title : $PHPCMS['sitename']);
$head['keywords'] = $meta_keywords;
$head['description'] = $meta_description;

//浏览器缓存
$ttl = $child == 1 ? CACHE_PAGE_CATEGORY_TTL : CACHE_PAGE_LIST_TTL;
header('Last-Modified: '.gmdate('D, d M Y H:i:s', TIME).' GMT');
header('Expires: '.gmdate('D, d M Y H:i:s', TIME + $ttl).' GMT');
header('Cache-Control: max-age='.$ttl.', must-revalidate');

include template($tpl_mod, $template);
cache_page($ttl);
?>

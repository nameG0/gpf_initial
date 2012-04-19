<?php
/**
 * 内容列表页
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
	//有子栏目时取栏目首页模板
	if($child == 1)
	{
		$arrchildid = subcat('phpcms', $catid);
		list($tpl_mod, $template) = explode(',', $template_category);
	}
	//无子栏目则取列表页模板
	else
	{
		list($tpl_mod, $template) = explode(',', $template_list);
	}
}
elseif($type == 2)
{
	//链接类栏目，跳转
	header('location:'.$url);
}
$tpl_mod = $tpl_mod ? $tpl_mod : 'content';
$template = $template ? $template : 'list';

$catlist = submodelcat($modelid);
$arrparentid = explode(',', $arrparentid);
$parentid = $arrparentid[1];

//页面关键词，用于 SEO
$_head['title'] = $catname.'_'.($meta_title ? $meta_title : $PHPCMS['sitename']);
$_head['keywords'] = $meta_keywords;
$_head['description'] = $meta_description;

//浏览器缓存
$ttl = $child == 1 ? CACHE_PAGE_CATEGORY_TTL : CACHE_PAGE_LIST_TTL;
header('Last-Modified: '.gmdate('D, d M Y H:i:s', TIME).' GMT');
header('Expires: '.gmdate('D, d M Y H:i:s', TIME + $ttl).' GMT');
header('Cache-Control: max-age='.$ttl.', must-revalidate');

//搜索标题关键字
$where = get_sql_catid($catid);
if ($keyword)
	{
	$keyword = str_replace(' ', '%', $keyword);
	$where .= "AND title LIKE '%{$keyword}%'";
	}

$pagesize = 15;
$sql = "SELECT contentid, title, inputtime FROM " . DB_PRE . "content WHERE status=99 {$where} ORDER BY contentid DESC";
list($RESULT, $pages, $total) = page_select($sql, $pagesize);

include template($tpl_mod, $template);
cache_page($ttl);
?>

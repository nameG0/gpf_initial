<?php
/*
2011-10-16
被其它模块回调的函数
*/

//返回可录入文章的栏目菜单
//return array(array({selfif}, {name}, {isfolder}, {isopen}, {target}, {url}));
function c_menu_category_get_child($childid = 0)
{//{{{
	global $db;
	$childid = intval($childid);
	$sql = "SELECT catid, catname,child, modelid FROM " . DB_PRE . "category WHERE type=0 AND module='phpcms' AND parentid={$childid}";
	$result = $db->select($sql);
	$menu = array();
	foreach ($result as $k => $r)
		{
		$menu[] = array(
			"selfid" => $r['catid'],
			"name" => $r['catname'],
			"isfolder" => $r['child'] ? 1 : 0,
			"isopen" => 0,
			"target" => $r['child'] ? '_self' : 'right',
			"url" => $r['child'] ? '' : "?id=content,content&catid={$r['catid']}&modelid={$r['modelid']}",
			);
		}
	return $menu;
}//}}}
?>

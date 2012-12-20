<?php
/**
 * 模块内公用函数
 * 
 * @package default
 * @filesource
 */
function menu($parentid, $code = '')
{//{{{
	global $_userid, $_roleid, $_groupid;
	$db = rdbApi::obj();

	$code = str_replace('"', '\"', $code);
	$where = $parentid == 99 ? "AND userid=$_userid" : '';
	$menus = $db->select("SELECT * FROM `".DB_PRE."menu` WHERE `parentid`='$parentid' $where ORDER BY `listorder`,`menuid`", 'menuid');
	if($code)
	{
		foreach($menus as $m)
		{
			extract($m);
			if(($roleids && defined('IN_ADMIN') && !check_in($_roleid, $roleids)) || ($groupids && !defined('IN_ADMIN') && !check_in($_groupid, $groupids))) continue;
			eval("\$menu .= \"$code\";");
		}
		$menus = $menu;
	}
	return $menus;
}//}}}

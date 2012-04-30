<?php 
/**
 * category 模块公用函数
 * 
 * @package default
 * @filesource
 */

function category_get($catid = 0)
{//{{{
	if (!$catid)
		{
		$cache = cache_read('category.php', CATEGORY_DATA_DIR, 0, true);
		if (!$cache)
			{
			$cache = _category_cache_all();
			}
		}
	else
		{
		$cache = cache_read("category_{$catid}.php", CATEGORY_DATA_DIR, 0, true);
		if (!$cache)
			{
			$cache = _category_cache_catid($catid);
			}
		}
	return $cache;
}//}}}

//ggzhu 2010-08-19 添加 $is_all 参数，可返回所有子栏目
function subcat($module = 'phpcms', $parentid = NULL, $type = NULL, $is_all = false)
{
	global $CATEGORY;
	$subcat = array();
	if ($is_all)
		{
		$childs = explode(",", $CATEGORY[$parentid]['arrchildid']);
		foreach ($childs as $catid)
			{
			$subcat[$catid] = $CATEGORY[$catid];
			}
		}
	else
		{
		foreach ($CATEGORY as $id => $cat)
			{
			if($cat['module'] == $module && ($parentid === NULL || $cat['parentid'] == $parentid) && ($type === NULL || $cat['type'] == $type))
				{
				$subcat[$id] = $cat;
				}
			}
		}
	return $subcat;
}

function submodelcat($modelid = 1, $parentid = NULL, $type = NULL)
{
	global $CATEGORY;
	$subcat = array();
	foreach($CATEGORY as $id=>$cat)
	{
		if($cat['modelid'] == $modelid && ($parentid === NULL || $cat['parentid'] == $parentid) && ($type === NULL || $cat['type'] == $type)) $subcat[$id] = $cat;
	}
	return $subcat;
}

function catpos($catid, $urlrule = '',$hidecat=array(),$html=0)
{
	global $CATEGORY;
	if(!isset($CATEGORY[$catid])) return '';
	$pos = '';
	$arrparentid = array_filter(explode(',', $CATEGORY[$catid]['arrparentid'].','.$catid));
	foreach($arrparentid as $catid)
	{
		if(!empty($hidecat) && in_array($catid,$hidecat)) continue;
		if($urlrule) eval("\$url = \"$urlrule\";");
		elseif($html) $url = $CATEGORY[$catid]['parentdir'].$CATEGORY[$catid]['catdir']."/";
		else $url = $CATEGORY[$catid]['url'];
		$pos .= '&gt;<a href="'.$url.'">'.$CATEGORY[$catid]['catname'].'</a>';
	}
	return $pos;
}

function get_urlruleid_catid($catid)
{
	global $MODEL,$CATEGORY;
	$catid = intval($catid);
	if(!isset($CATEGORY[$catid])) return false;
	$modelid = $CATEGORY[$catid]['modelid'];
	$urlruleid = $MODEL[$modelid]['category_urlruleid'];
	return $urlruleid;
}
function get_sql_catid($catid)
{
	global $CATEGORY;
	$catid = intval($catid);
	if(!isset($CATEGORY[$catid])) return false;
	return $CATEGORY[$catid]['child'] ? " AND `catid` IN(".$CATEGORY[$catid]['arrchildid'].") " : " AND `catid`=$catid ";
}
?>

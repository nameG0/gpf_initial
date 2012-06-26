<?php 
/**
 * category 模块公用函数
 * 
 * @package default
 * @filesource
 */
function _category_cache_all()
{//{{{
	$data = array();
	$result = siud::select('category')->tfield('`catid`,`module`,`type`,`modelid`,`catname`,`style`,`image`,`catdir`,`url`,`parentid`,`arrparentid`,`parentdir`,`child`,`arrchildid`,`items`,`citems`,`pitems`,`ismenu`,`letter`')->order("`listorder`,`catid`")->ing();
	foreach ($result as $k => $r)
		{
		//$r['url'] = url($r['url']);
		$data[$r['catid']] = $r;
		}
	cache_file_write('category.php', $data, CMS_PATH_DATA);
	return $data;
}//}}}
/**
 * 若 $catid === 0 则表示缓存所有栏目数据
 */
function _category_cache_catid($catid)
{//{{{
	$siud = siud::select('category');
	// global $db;
	if (0 === $catid)
		{
		// $sql = "SELECT * FROM " . DB_PRE . "category";
		}
	else
		{
		$siud->wis('catid', $catid);
		// $sql = "SELECT * FROM " . DB_PRE . "category WHERE catid={$catid}";
		}
	$result = $siud->ing();
	// $result = $db->select($sql);
	foreach ($result as $k => $r)
		{
		a::i($r)->unsers('setting');
		// if (!empty($r['setting']))
			// {
			// $setting = $r['setting'];
			// eval("\$setting = $setting;"); 
			// unset($r['setting']);
			// if (is_array($setting))
				// {
				// foreach ($setting as $k => $v)
					// {
					// $r[$k] = $v;
					// }
				// }
			// }
		cache_file_write(CATEGORY_DATA_DIR . "category_{$r['catid']}.php", $r, GPF_SER);
		}
	return $r;
}//}}}
function cache_category()
{//{{{
	_category_cache_all();
	_category_cache_catid(0);
	//cache_table(DB_PRE.'category', '*', '', '', 'listorder,catid', 1);
}//}}}
function category_get($catid = 0)
{//{{{
	if (!$catid)
		{
		$cache = cache_file_read('category.php', CMS_PATH_DATA);
		if (!$cache)
			{
			$cache = _category_cache_all();
			}
		}
	else
		{
		$cache = cache_file_read("category_{$catid}.php", CMS_PATH_DATA);
		if (!$cache)
			{
			$cache = _category_cache_catid($catid);
			}
		}
	return $cache;
}//}}}
/**
 * ggzhu@2010-08-19 添加 $is_all 参数，可返回所有子栏目 
 */
function subcat($module = 'phpcms', $parentid = NULL, $type = NULL, $is_all = false)
{//{{{
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
}//}}}
function submodelcat($modelid = 1, $parentid = NULL, $type = NULL)
{//{{{
	global $CATEGORY;
	$subcat = array();
	foreach($CATEGORY as $id=>$cat)
	{
		if($cat['modelid'] == $modelid && ($parentid === NULL || $cat['parentid'] == $parentid) && ($type === NULL || $cat['type'] == $type)) $subcat[$id] = $cat;
	}
	return $subcat;
}//}}}
function catpos($catid, $urlrule = '',$hidecat=array(),$html=0)
{//{{{
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
}//}}}
function get_urlruleid_catid($catid)
{//{{{
	global $MODEL,$CATEGORY;
	$catid = intval($catid);
	if(!isset($CATEGORY[$catid])) return false;
	$modelid = $CATEGORY[$catid]['modelid'];
	$urlruleid = $MODEL[$modelid]['category_urlruleid'];
	return $urlruleid;
}//}}}
function get_sql_catid($catid)
{//{{{
	global $CATEGORY;
	$catid = intval($catid);
	if(!isset($CATEGORY[$catid])) return false;
	return $CATEGORY[$catid]['child'] ? " AND `catid` IN(".$CATEGORY[$catid]['arrchildid'].") " : " AND `catid`=$catid ";
}//}}}

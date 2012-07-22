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
function cmsCateCache_dir2id()
{//{{{
	return cache_file_read(CMS_PATH_DATA . "category_dir2id.php", GPF_SER);
}//}}}
/**
 * $CATEGORY 变量
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
	cache_file_write(CMS_PATH_DATA . 'category.php', $data, GPF_SER);
	return $data;
}//}}}
/**
 * 缓存单个栏目数据 -> category_{catid}
 * 若 $catid === 0 则表示缓存所有栏目数据
 */
function _category_cache_catid($catid)
{//{{{
	$siud = siud::select('category');
	if ($catid)
		{
		$siud->wis('catid', $catid);
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
		cache_file_write(CMS_PATH_DATA . "category_{$r['catid']}.php", $r, GPF_SER);
		}
	return $r;
}//}}}
function cache_category()
{//{{{
	_category_cache_all();
	_category_cache_catid(0);
	_cmsCateCache_dir2id();
	//cache_table(DB_PRE.'category', '*', '', '', 'listorder,catid', 1);
}//}}}
/**
 * todo 某个栏目下属一级子栏目数据。
 */
function _cmsCateCache_id2child()
{//{{{
	
}//}}}
/**
 * 更新由栏目完整路径到栏目ID的映射缓存。
 * <pre>
 * 栏目完整路径为：栏目所属模块名/栏目完整路径。
 * eg. phpcms/product/jieju
 * eg. ask/taoci
 * 注，此映射的路径是使用 catdir 生成的，与栏目的 url 无关。之所以使用此缓存是因为这种定位方法比 CATEGORY_NAME 的定位更优。
 * </pre>
 * @param array $result 可以传入栏目查询记录集，节省一次查询。
 */
function _cmsCateCache_dir2id($result = array())
{//{{{
	if (!$result)
		{
		// $sql = "SELECT catid, catdir, arrparentid, module FROM " . DB_PRE . "category";
		// $result = $db->select($sql);
		$result = siud::select('category')->tfield('catid, catdir, arrparentid, module')->ing();
		}
	$category = array(); //[catid] = CateRow
	$PidChild = array(); //[pid] = array(child_catid, ...)
	foreach ($result as $k => $r)
		{
		$category[$r['catid']] = $r;
		}
	unset($result);

	$dir2catid = array();
	foreach ($category as $catid => $r)
		{
		$parentid = explode(",", $r['arrparentid']);
		unset($parentid[0]); //第一个一定是 0
		$dir = array();
		foreach ($parentid as $id)
			{
			$dir[] = $category[$id]['catdir'];
			}
		$dir[] = $r['catdir'];
		$path = $r['module'] . '/' . join("/", $dir);
		$dir2catid[$path] = $catid;
		}
	cache_file_write(CMS_PATH_DATA . 'category_dir2id.php', $dir2catid, GPF_SER);
	return true;
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
function get_sql_catid($catid, $ao = 'AND')
{//{{{
	global $CATEGORY;
	$catid = intval($catid);
	if(!isset($CATEGORY[$catid])) return false;
	return $CATEGORY[$catid]['child'] ? " {$ao} `catid` IN(".$CATEGORY[$catid]['arrchildid'].") " : " {$ao} `catid`={$catid} ";
}//}}}

<?php 
/*
2011-10-14
缓存 category 模块自己的数据
*/

//私有函数{{{
function _category_cache_all()
{//{{{
	global $db;
	$data = array();
	$result = $db->select("SELECT `catid`,`module`,`type`,`modelid`,`catname`,`style`,`image`,`catdir`,`url`,`parentid`,`arrparentid`,`parentdir`,`child`,`arrchildid`,`items`,`citems`,`pitems`,`ismenu`,`letter` FROM `".DB_PRE."category` ORDER BY `listorder`,`catid`");
	foreach ($result as $k => $r)
		{
		//$r['url'] = url($r['url']);
		$data[$r['catid']] = $r;
		}
	cache_write('category.php', $data, CATEGORY_DATA_DIR, true);
	return $data;
}//}}}
/**
 * 若 $catid === 0 则表示缓存所有栏目数据
 */
function _category_cache_catid($catid)
{//{{{
	global $db;
	if (0 === $catid)
		{
		$sql = "SELECT * FROM " . DB_PRE . "category";
		}
	else
		{
		$sql = "SELECT * FROM " . DB_PRE . "category WHERE catid={$catid}";
		}
	$result = $db->select($sql);
	foreach ($result as $k => $r)
		{
		if (!empty($r['setting']))
			{
			$setting = $r['setting'];
			eval("\$setting = $setting;"); 
			unset($r['setting']);
			if (is_array($setting))
				{
				foreach ($setting as $k => $v)
					{
					$r[$k] = $v;
					}
				}
			}
		cache_write("category_{$r['catid']}.php", $r, CATEGORY_DATA_DIR, true);
		}
	return $r;
}//}}}
//}}}

function cache_category()
{
	_category_cache_all();
	_category_cache_catid(0);
	//cache_table(DB_PRE.'category', '*', '', '', 'listorder,catid', 1);
}
?>

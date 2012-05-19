<?php 
function content_model($modelid = 0)
{//{{{
	if (!$modelid)
		{
		$cache = cache_read('model.php', CONTENT_DATA_DIR, 0, true);
		if (!$cache)
			{
			$cache = _content_model_cache_all();
			}
		}
	else
		{
		$cache = cache_read("model_{$catid}.php", CONTENT_DATA_DIR, 0, true);
		if (!$cache)
			{
			$cache = _content_model_cache_modelid($modelid);
			}
		}
	return $cache;
}//}}}

function _content_model_cache_all()
{//{{{
	global $db;
	$data = array();
	$result = $db->select("SELECT * FROM `".DB_PRE."model` WHERE `disabled`=0");
	foreach ($result as $k => $r)
		{
		$data[$r['modelid']] = $r;
		}
	cache_write('model.php', $data, CONTENT_DATA_DIR, true);
	return $data;
}//}}}
function _content_model_cache_modelid($modelid)
{//{{{
	global $db;
	$sql = "SELECT * FROM " . DB_PRE . "model WHERE modelid={$catid}";
	$r = $db->get_one($sql);
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
	cache_write("modelid_{$modelid}.php", $r, CONTENT_DATA_DIR, true);
	return $r;
}//}}}
?>

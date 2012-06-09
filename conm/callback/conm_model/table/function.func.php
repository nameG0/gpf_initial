<?php 
/**
 * 单表模型类型
 * 
 * @package default
 * @filesource
 */

function cm_mt_conm__table_is_make($CMMr)
{//{{{
	$o_db = rdb::obj();

	$table_name = RDB_PRE . $CMMr['tablename'];
	return $o_db->table_exists($table_name);
}//}}}

function cm_mt_conm__table_make($CMMr, $CMFl)
{//{{{
	$o_db = rdb::obj();

	$table_name = RDB_PRE . $CMMr['tablename'];
	$sql = "CREATE TABLE  `{$table_name}` (\n";
	foreach ($CMFl as $f => $CMFr)
		{
		list($mod, $name) = explode("/", $CMFr['formtype']);
		$func_name = "cm_ft_{$mod}__{$name}_sql";
		if (!function_exists($func_name))
			{
			$callback = mod_callback($mod, 'p');
			foreach ($callback as $k => $v)
				{
				$p = "{$v}conm_field/{$name}.func.php";
				if (is_file($p))
					{
					include_once $p;
					break;
					}
				}
			}
		$sql .= "`{$f}` " . $func_name($CMMr['setting']) . ",\n";
		}
	//去除最后一个,号
	$sql = substr($sql, 0, -2);
	$sql .= "\n) ENGINE = MYISAM";
	$o_db->query($sql);
}//}}}

function cm_mt_conm__table_sync($CMMr, $CMFl)
{//{{{
	
}//}}}

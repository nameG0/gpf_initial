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
	return $sql;
}//}}}

/**
 * 同步表结构
 * @param array $CMFl 有实际表字段的实际字段列表
 */
function cm_mt_conm__table_sync($CMMr, $CMFl)
{//{{{
	$ret = array();

	//------ 加载字段类型文件 ------
	$field_list = array();
	foreach ($CMFl as $k => $v)
		{
		$field_list[] = $v['formtype'];
		}
	cm_f_field_load($field_list);
	
	$FString = cm_m_get_FString($CMMr['tablename']);
	$table = RDB_PRE . $CMMr['tablename'];

	//------ 删除字段 -----
	foreach ($FString as $k => $v)
		{
		//字段需要重建时亦删除原字段.
		if (!$CMFl[$k] || $CMFl[$k]['is_rebuild'])
			{
			$sql = "ALTER TABLE `{$table}` DROP `{$k}`";
			$ret[] = $sql;
			unset($FString[$k]);
			}
		}

	//------ 更改字段 ------
	foreach ($FString as $k => $v)
		{
		list($mod, $name) = explode("/", $CMFl[$k]['formtype']);
		//比较字段指纹
		$func_name = "cm_ft_{$mod}__{$name}_FString";
		$FString_ft = $func_name($CMFl[$k]);
		if ($FString_ft == $v)
			{
			unset($FString[$k], $CMFl[$k]);
			continue;
			}
		$func_name = "cm_ft_{$mod}__{$name}_sql";
		$sql_field = $func_name($CMFl[$k]);
		$change_name = $CMFl[$k]['new_name'] ? $CMFl[$k]['new_name'] : $k;
		$sql = "ALTER TABLE `{$table}` CHANGE `{$k}` `{$change_name}` {$sql_field}";
		$ret[] = $sql;
		//更改后不再需要在后面的流程中处理
		unset($CMFl[$k], $FString[$k]);
		}

	//------ 添加字段 ------
	//运行到这里 $CMFl 中所剩的都是新添加的字段
	//------
	foreach ($CMFl as $k => $v)
		{
		list($mod, $name) = explode("/", $v['formtype']);
		$func_name = "cm_ft_{$mod}__{$name}_sql";
		$sql_field = $func_name($v);
		$field_name = $v['new_name'] ? $v['new_name'] : $k;
		$sql = "ALTER TABLE `{$table}` ADD `{$field_name}` {$sql_field}";
		$ret[] = $sql;
		}
	return $ret;
}//}}}

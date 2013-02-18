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

function cm_mt_conm__table_make($CMMR)
{//{{{
	$CMFl = $CMMR['CMFL'];
	unset($CMFl['_info'], $CMMR['CMFL']);
	$CMMr = $CMMR;

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
	return array($sql);
}//}}}

/**
 * 同步表结构
 * @param array $CMFl 有实际表字段的实际字段列表
 */
function cm_mt_conm__table_sync($CMMR)
{//{{{
	$ret = array();
	$CMFl = $CMMR['CMFL'];
	unset($CMFl['_info'], $CMMR['CMFL']);
	$CMMr = $CMMR;

	//------ 加载字段类型文件 ------
	$field_list = array();
	foreach ($CMFl as $k => $v)
		{
		$field_list[] = $v['formtype'];
		}
	cm_f_load($field_list);
	
	$FString = cm_m_get_FString($CMMr['tablename']);
	$table = RDB_PRE . $CMMr['tablename'];

	//------ 添加字段 ------
	//如果表中原只有一个字段，若把这个字段删除，MySQL会报错，不让删除表中最后一个字段。
	//所以需要先添加新字段，避免出现删除最后一个字段的情况。
	//------
	foreach ($CMFl as $k => $v)
		{
		if ($FString[$k])
			{
			continue;
			}
		list($mod, $name) = explode("/", $v['formtype']);
		$func_name = "cm_ft_{$mod}__{$name}_sql";
		$sql_field = $func_name($v['setting']);
		$field_name = $v['new_name'] ? $v['new_name'] : $k;
		$sql = "ALTER TABLE `{$table}` ADD `{$field_name}` {$sql_field}";
		$ret[] = $sql;
		}

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
		$FString_ft = $func_name($CMFl[$k]['setting']);
		if ($FString_ft == $v)
			{
			unset($FString[$k], $CMFl[$k]);
			continue;
			}
		log::add("FString {$k}:{$v} ---> {$FString_ft}", log::INFO, __FILE__, __LINE__, __FUNCTION__);
		$func_name = "cm_ft_{$mod}__{$name}_sql";
		$sql_field = $func_name($CMFl[$k]['setting']);
		$change_name = $CMFl[$k]['new_name'] ? $CMFl[$k]['new_name'] : $k;
		$sql = "ALTER TABLE `{$table}` CHANGE `{$k}` `{$change_name}` {$sql_field}";
		$ret[] = $sql;
		//更改后不再需要在后面的流程中处理
		unset($CMFl[$k], $FString[$k]);
		}

	return $ret;
}//}}}

function cm_mt_conm__table_fill_info(& $info, $set)
{//{{{
	$info['pk'] = $set['pk'];
}//}}}

function cm_mt_conm__table_delete($CMMR)
{//{{{
	$table = RDB_PRE . $CMMR['tablename'];
	return array("DROP TABLE `{$table}`");
}//}}}

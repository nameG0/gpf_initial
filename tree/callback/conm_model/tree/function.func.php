<?php 
/**
 * 树状结构（如栏目）模型类型
 * 
 * @package default
 * @filesource
 */
function cm_mt_category__tree_default_field()
{//{{{
	return array(
		"catid" => array("formtype" => 'conm/auto_increment', "name" => '栏目ID', "iscore" => 1, "setting" => array("key_type" => 'pri',),),
		"catname" => array("formtype" => 'conm/text', "name" => '栏目名', "setting" => array("field_type" => 'char', "maxlength" => 15,),),
		"module" => array("formtype" => 'conm/text', "name" => '所属模块', "is_input" => 0, "setting" => array("field_type" => 'char', "maxlength" => '15',),),
		"type" => array("formtype" => 'conm/number', "name" => '栏目类型',),
		"modelid" => array("formtype" => 'conm/number', "name" => '关联模型',),
		"parentid" => array("formtype" => 'conm/number', "name" => '上级栏目', "iscore" => 1,),
		"arrparentid" => array("formtype" => 'conm/text', "name" => '上级栏目ID路径', "is_input" => 0, "setting" => array("field_type" => 'char', "maxlength" => '100',),),
		"child" => array("formtype" => 'conm/number', "name" => '是否有子栏目', "is_input" => 0,),
		"arrchildid" => array("formtype" => 'conm/textarea', "name" => '所有子栏目ID', "is_input" => 0, "setting" => array("field_type" => 'text',),),
		"catdir" => array("formtype" => 'conm/text', "name" => '栏目路径', "setting" => array("field_type" => 'char', "maxlength" => '20',),),
		"parentdir" => array("formtype" => 'conm/text', "name" => '上级栏目路径', "is_input" => 0, "setting" => array("field_type" => 'char', "maxlength" => 100,),),
		"url" => array("formtype" => 'conm/text', "name" => '栏目访问URL', "is_input" => 0, "setting" => array("field_type" => 'char', "maxlength" => '100',),),
		"setting" => array("formtype" => 'conm/textarea', "name" => '栏目设置', "is_input" => 0, "setting" => array("field_type" => 'text',),),
		);
}//}}}

function cm_mt_category__tree_is_make($CMMr)
{//{{{
	$o_db = rdb::obj();

	$table_name = RDB_PRE . $CMMr['tablename'];
	return $o_db->table_exists($table_name);
}//}}}

function cm_mt_category__tree_make($CMMR)
{//{{{
	$table_name = RDB_PRE . $CMMR['tablename'];
	$sql = "CREATE TABLE  `{$table_name}` (\n";
	unset($CMMR['CMFL']['_info']);
	foreach ($CMMR['CMFL'] as $f => $CMFr)
		{
		cm_f_load($CMFr['formtype']);
		list($mod, $name) = explode("/", $CMFr['formtype']);
		$func_name = "cm_ft_{$mod}__{$name}_sql";
		$sql .= "`{$f}` " . $func_name($CMFr['setting']) . ",\n";
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
function cm_mt_category__tree_sync($CMMR)
{//{{{
	$ret = array();
	unset($CMMR['CMFL']['_info']);

	//------ 加载字段类型文件 ------
	$field_list = array();
	foreach ($CMMR['CMFL'] as $k => $v)
		{
		$field_list[] = $v['formtype'];
		}
	cm_f_load($field_list);
	
	$FString = cm_m_get_FString($CMMR['tablename']);
	$table = RDB_PRE . $CMMR['tablename'];

	//------ 添加字段 ------
	//如果表中原只有一个字段，若把这个字段删除，MySQL会报错，不让删除表中最后一个字段。
	//所以需要先添加新字段，避免出现删除最后一个字段的情况。
	//------
	foreach ($CMMR['CMFL'] as $k => $v)
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
		if (!$CMMR['CMFL'][$k] || $CMMR['CMFL'][$k]['is_rebuild'])
			{
			$sql = "ALTER TABLE `{$table}` DROP `{$k}`";
			$ret[] = $sql;
			unset($FString[$k]);
			}
		}

	//------ 更改字段 ------
	foreach ($FString as $k => $v)
		{
		list($mod, $name) = explode("/", $CMMR['CMFL'][$k]['formtype']);
		//比较字段指纹
		$func_name = "cm_ft_{$mod}__{$name}_FString";
		$FString_ft = $func_name($CMMR['CMFL'][$k]['setting']);
		if ($FString_ft == $v)
			{
			unset($FString[$k], $CMMR['CMFL'][$k]);
			continue;
			}
		log::add("FString {$k}({$mod}/{$name}):{$v} ---> {$FString_ft}", log::INFO, __FILE__, __LINE__, __FUNCTION__);
		$func_name = "cm_ft_{$mod}__{$name}_sql";
		$sql_field = $func_name($CMMR['CMFL'][$k]['setting']);
		$change_name = $CMMR['CMFL'][$k]['new_name'] ? $CMMR['CMFL'][$k]['new_name'] : $k;
		$sql = "ALTER TABLE `{$table}` CHANGE `{$k}` `{$change_name}` {$sql_field}";
		$ret[] = $sql;
		//更改后不再需要在后面的流程中处理
		unset($CMMR['CMFL'][$k], $FString[$k]);
		}

	return $ret;
}//}}}

function cm_mt_category__tree_fill_info(& $info, $set)
{//{{{
	$info['pk'] = $set['pk'];
}//}}}

<?php 
/**
 * 字段类型示例
 * 
 * @package default
 * @filesource
 */

/**
 * 字段类型生成 sql 语句的
 * @param array $set 字段类型设置
 * @param int $rdb_type 关系数据名， eg. CM_MYSQL ，常量由 conm 模块定义。
 */
function cm_ft_conm__example_sql($set, $rdb_type)
{//{{{
	return "INT( 10 ) NOT NULL DEFAULT  '0'";
}//}}}

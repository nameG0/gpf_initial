<?php 
/**
 * 字段类型示例
 * 
 * @package default
 * @filesource
 */
define('CM_FT_CONM__EXAMPLE', true); //必须定义的常量，方便判断是否已加载。
define('CM_FT_CONM__EXAMPLE_USE_ID', true); //若定义表示需要使用ID关联，需要动态判断使用 use_id() 接口。两者都没定义则不使用ID关联。

/**
 * 字段类型生成 sql 语句的
 * @param array $set 字段类型设置
 * @param int $rdb_type 关系数据名， eg. CM_MYSQL ，常量由 conm 模块定义。
 * @return string 返回从字段类型开始部份。 eg. INT( 10 ) NOT NULL DEFAULT  '0'
 */
function cm_ft_conm__example_sql($set, $rdb_type) {}
/**
 * @param string $field 字段名
 * @param array $data 实际录入数据库数据
 * @param array $keep 辅助操作数据
 * @param array $set 字段类型设置
 * @param mixed $id 数据主键值， eg. 1
 */
function cm_ft_conm__example_save($field, $data, $keep, $set, $id) {}
/**
 * 动态判断字段类型是否需要使用ID关联，此接口优先于 CM_FT_CONM__EXAMPLE_USE_ID 常量。
 * @param array $set 字段类型设置。
 * @return bool
 */
function cm_ft_conm__example_use_id($set) {}

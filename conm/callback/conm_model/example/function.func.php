<?php 
/**
 * 内容模型类型扩展示例
 * <pre>
 * 函数命名前序:cm_mt_{mod}__{name}_
 * eg. cm_mt_conm__example_
 * <b>扩展接口</b>
 * (实际定义的函数名为函数命名前序加上扩展接口名. eg. cm_mt_conm__example_is_make)
 * is_make($CMMr):模型是否初始化,即数据库中是否有实际对应的表
 * string make($CMMr, $CMFl):返回模型建表SQL语句。
 * array sync($CMMr, $CMFl):返回对初始化的模型进行同步的SQL语句.
 * </pre>
 * 
 * @package default
 * @filesource
 */

/**
 * 检查模型是否初始化,即数据库中是否有实际对应的表
 * @return bool
 */
function cm_mt_conm__example_is_make($CMMr) {}
/**
 * 返回模型建表SQL语句 
 * @return string 建表SQL语句
 */
function cm_mt_conm__example_make($CMMr, $CMFl) {}
/**
 * 返回对初始化的模型进行同步的SQL语句.
 * @param array $CMFl 有实际表字段的实际字段列表
 * @return array [] => 一条SQL语句
 */
function cm_mt_conm__example_sync($CMMr, $CMFl) {}

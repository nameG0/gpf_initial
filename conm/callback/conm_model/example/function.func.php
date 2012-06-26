<?php 
/**
 * 内容模型类型扩展示例
 * <pre>
 * 函数命名前序:cm_mt_{mod}__{name}_
 * eg. cm_mt_conm__example_
 * 实际定义的函数名为函数命名前序加上扩展接口名.
 * eg. cm_mt_conm__example_is_make
 * </pre>
 * 
 * @package default
 * @filesource
 */
/**
 * 模型类型默认带有的字段的字段信息(可选)
 * @return array $CMFl
 */
function cm_mt_conm__example_default_field() {}

/**
 * 检查模型是否初始化,即数据库中是否有实际对应的表(必须)
 * @return bool
 */
function cm_mt_conm__example_is_make($CMMr) {}
/**
 * 返回模型建表SQL语句(必须)
 * @return array [] => 建表SQL语句
 */
function cm_mt_conm__example_make($CMMR) {}
/**
 * 返回对初始化的模型进行同步的SQL语句.(必须)
 * @param array $CMFl 有实际表字段的实际字段列表
 * @return array [] => 一条SQL语句
 */
function cm_mt_conm__example_sync($CMMR) {}

/**
 * 删除模型时执行的SQL语句（必须）
 * @return array [] => 一条SQL语句
 */
function cm_mt_conm__example_delete($CMMr) {}

/**
 * 填充 CMTL 的 _info 键数据。(可选)
 * @param array $set 模型类型设置，即 $CMMr['setting']
 * @return void
 */
function cm_mt_conm__example_fill_info(& $info, $set) {}


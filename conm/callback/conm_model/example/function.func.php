<?php 
/**
 * 内容模型类型扩展示例
 * <pre>
 * 函数命名前序:cm_mt_{mod}__{name}_
 * eg. cm_mt_conm__example_
 * <b>扩展接口</b>
 * (实际定义的函数名为函数命名前序加上扩展接口名. eg. cm_mt_conm__example_is_make)
 * is_make($CMMr):模型是否初始化,即数据库中是否有实际对应的表
 * make($CMMr, $CMFl):执行模型的初始化
 * sync($CMMr, $CMFl):对已初始化的模型进行同步.
 * </pre>
 * 
 * @package default
 * @filesource
 */


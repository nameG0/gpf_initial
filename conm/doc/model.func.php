<?php
/**
 * 内容模型相关函数
 * <pre>
 * <b>文件路径</b>
 * 各模块字段类型存放规则：
 * 存放目录： {module_name}/callback/conm_field/
 * 字段类型索引文件： fields.inc.php
 * 字段类型定义文件： {field_name}.func.php
 * 索引文件定义格式： return array('字段名' => '字段昵称', ...);
 * 其中字段名可由英文和数字组可，昵称可使用中文。若需要禁用某个字段类型，只需不包含在索引文件中即或。
 *
 * <b>数据类型</b>
 * FString(field string) string 一个字段的指纹值,用于比较内容模型字段设置是否与实际表字段一致.
 * MySQL的指纹值为SHOW COLUMNS语句返回的字段值组合,但不包含Field字段的值.
 *
 * CMMTid(content module model type ID):模型类型ID
 * 命名规则：{module_name}/{model_name} eg. conm/table
 *
 * $CMFTid(content module field type ID): 字段类型ID
 * 字段类型ID命名规则：{module_name}/{field_name} eg. conm/title
 *
 * $CMFTr(content module field type row): 一个字段类型.
 * array[m]=模块(module), [n]=字段类型名称(name), [nn]=中文昵称(nickname), [p]=绝对路径(path)
 *
 * $CMFTl(content module field type list): 字段类型列表
 * array[字段类型ID] = $CMFTr
 *
 * $CMFr(content module field row) array 一条字段数据。
 * {field:字段名, modelid:所属模型, formtype:字段类型, ...}
 *
 * $CMFs(content module field s): 多条内容字段数据。数组键只是数字编号。
 * array[] => $CMFr
 *
 * $CMFl(content module field list):多条内容字段数据，且数组键为字段名。
 * array[field] => $CMFr
 *
 * $CMFL:同 $CMFl ，只是多了保存所属模型信息的 _info 键。
 * array[field] => $CMFr, [_info] => 所属模型信息。
 *
 * $CMMr:一条模型数据
 *
 * $CMMR:同 $CMMr, 只是多了保存字段信息的 CMML 键。
 *
 * <b>字段类型接口</b>
 * 前序:cm_ft_ (content model field type)
 * 命名规则:前序_{mod}_{name}_接口名. eg. cm_ft_conm__example_setting
 * void setting($setting): 显示字段类型设置表单
 * string FString($set) 返回字段类型指纹值.
 * string form($field, $value, $set): 返回字段类型编辑表单HTML代码,$field方便输出表单项名
 *
 * <b>字段类型控制常量</b>
 * (使用大写字母作为常量名)
 * _{mod}__{name}_VIRTUAL_FIELD true 是否虚拟字段（即非数据表实际字段），不定义时默认为否。
 * </pre>
 * 
 * @package doc
 * @filesource
 */

<?php 
/**
 * hd 扩展
 * 
 * @package default
 * @filesource
 */
mod_init('conm');

/**
 * 输出模型下拉选择框。
 * @param string $CMMTid 显示的模型类型。
 */
function hd_conm__model_select($attr)
{//{{{
	$modeltype = cm_m_modeltype($attr['CMMTid']);
	$result = siud::select('model')->tfield('modelid, nickname')->wis('modeltype', $modeltype)->ing();
	$attr['option'] = array();
	foreach ($result as $k => $r)
		{
		$attr['option'][$r['modelid']] = $r['nickname'];
		}
	return hd('select', $attr) . "<a href=\"" . gpf::url("content.model.manage") . "\">管理模型</a>";
}//}}}

/**
 * 输出模型插件类型下拉选框。
 */
function hd_conm__mplug_select($attr)
{//{{{
	$attr['option'] = array('cms/urlrule' => 'URL规则');
	return hd('select', $attr);
}//}}}

/**
 * 输出模型插件用于标记是否自定义配置的多选框
 * @param string $form_name 表单前序
 * @param string $Qid 插件qid
 * @param string $key_name 配置项名
 * @param array $change_quote 标记哪些配置是自定义的数组，全部是自定义时用 array('*' => true) 表示。
 */
function hd_conm__mplug_checkbox($attr)
{//{{{
	$is_checked = 0;
	if ($attr['change_quote']['*'])
		{
		$is_checked = 1;
		}
	else if ($attr['change_quote'][$attr['key_name']])
		{
		$is_checked = 1;
		}
	return hd("checkbox|name={$attr['form_name']}[change][{$attr['key_name']}]|value=1|checked={$is_checked}");
}//}}}

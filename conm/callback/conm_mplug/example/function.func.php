<?php
/**
 * callback 接口函数
 * <pre>
 * <b>接口命名前序</b>
 * cm_mp_{mod}__{name}_ (cm_mp = content model, model plug)
 * eg. cm_mp_conm__example_
 * </pre>
 * 
 * @package callback
 * @filesource
 */

/**
 * 插件基础数据填充接口
 * @param array $data 文章数据
 * @param array $setting 插件设置
 * @return array 基础数据 [key] => value
 */
function cm_mp_conm__example_base(& $data, $setting)
{//{{{
	
}//}}}

/**
 * 插件处理接口
 * @param array $data 文章数据
 * @param array $base 插件基础数据
 * @param array $setting 插件设置
 * @param string $error 错误信息
 * @return bool 是否处理成功
 */
function cm_mp_conm__example_proc(& $data, $base, $setting, & $error)
{//{{{
	
}//}}}

<?php
/**
 * 模型插件管理
 * 
 * @package default
 * @filesource
 */
class ctrl_a_model_plug
{
	function __construct()
	{//{{{
		admin_check();
	}//}}}
	/**
	 * 加载模型设置表单
	 * @param string mplugid
	 * @param int $qid
	 */
	function action_ajax_new_form()
	{//{{{
		list($dom_id, $MPlugID, $qid) = i::g()->val('dom_id', 'mplugid')->int('qid')->end();

		log::is_print(0);
		$MPlugr = array(
			"MPlugID" => $MPlugID,
			"qid" => $qid,
			"pid" => $qid,
			"status" => 0,
			"setting" => array(),
			);
		conm_mplug_form($dom_id, $MPlugr, array('*' => true), GM_CONM_MPLUG_NEW);
	}//}}}
	/**
	 * 初始化插件引用基本的编辑界面
	 */
	function action_ajax_form_init()
	{//{{{
		log::is_print(0);
		//dom_id divID
		//Nt 当前插件数据的指向 eg. namespace,tag_id
		//Nt_extend_from array 允许从哪些插件数据继承 [] => Nt eg. Nt_extend[]=namespace,tag_id,昵称
		list($dom_id, $sQ_Nt, $sQ_Nt_extend_from, $dom_id) = i::pg()->val('dom_id', 'Nt', 'Nt_extend_from')->end();

		list($namespace, $tag_id) = explode(",", $sQ_Nt);

		$Nt_extend_from = array();
		if (is_array($sQ_Nt_extend_from))
			{
			foreach ($sQ_Nt_extend_from as $k => $v)
				{
				list($_namespace, $_tag_id, $_name) = explode(",", $v);
				$Nt_extend_from[] = array("namespace" => $_namespace, "tag_id" => $_tag_id, "name" => $_name,);
				}
			}
		unset($sQ_Nt_extend_from);
		include tpl_admin('mplug_form_init');
	}//}}}
	/**
	 * 输出插件引用编辑界面
	 */
	function action_ajax_form_plug()
	{//{{{
		log::is_print(false);
		//dom_id divID
		//Nt 当前插件数据的指向 eg. namespace,tag_id
		//Nt_extend 继承自哪个指向。格式同 Nt, 空字符串时表示使用 extend 表中的继承关系。
		list($dom_id, $sQ_Nt, $sQ_Nt_extend) = i::pg()->val('dom_id', 'Nt', 'Nt_extend')->end();

		list($namespace, $tag_id) = explode(",", $sQ_Nt);
		list($extend_namespace, $extend_tag_id) = explode(",", $sQ_Nt_extend);
		unset($sQ_Nt, $sQ_Nt_extend);
		list($MPlugs, $change, $namespace_extend, $tag_id_extend) = conm_mplugs($namespace, $tag_id, $extend_namespace, $extend_tag_id);
		include tpl_admin('mplug_form_plug');
	}//}}}
}

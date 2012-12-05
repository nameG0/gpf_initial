<?php
/**
 * 后台初始入口
 * 
 * @package default
 * @filesource
 */

class ctrl_content
{
	/**
	 * 显示一条内容。
	 */
	function action_show()
	{//{{{
		$contentid = i::g()->int('contentid')->end();

		$data = siud::find('content')->wis('contentid', $contentid)->ing();
		// $model_info = siud::find('content')->wis('contentid', $contentid)->ing();
		// $CMMR = conm_CMMR($model_info['modelid'], CONM_ONLY_FIELD);
		$CMMR = conm_CMMR(CMS_MODEL_ID, CONM_ONLY_FIELD);
		$output = conm_output($CMMR['CMFL'], $data);
		include tpl('show', 'main');
		// var_dump($data, $output);
	}//}}}
	/**
	 * 显示栏目页
	 */
	function action_category()
	{//{{{
		global $CATEGORY;

		$catid = i::g()->int('catid')->end();
		if (!$catid)
			{
			showmessage('缺少 catid 参数');
			}
		list($RESULT, $pages, $total) = siud::select('content')->tfield('title, contentid, inputtime')->where(get_sql_catid($catid, ''))->pagesize(20)->ing();
		include tpl('list', 'main');
	}//}}}
	function action_list()
	{//{{{
		$this->action_category();
	}//}}}
}

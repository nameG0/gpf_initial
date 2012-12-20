<?php
/**
 * 菜单数据操作API
 * 
 * @package default
 * @filesource
 */
class adminApi_menu
{
	private $omr_adm_men = NULL;

	function __construct()
	{//{{{
		$this->omr_adm_men = gmod::rm('admin', 'menu');
	}//}}}

	function list_by_pid($parentid, $field = '*')
	{//{{{
		$where = array(
			"parentid" => $parentid,
			);
		return $this->omr_adm_men->select($where, array("field" => $field,), false);
	}//}}}
}

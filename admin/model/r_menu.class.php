<?php
class r_admin_menu
{
	private $o_db = NULL;
	private $t_menu = '';

	function __construct()
	{//{{{
		$this->o_db = rdbApi::obj();
		$this->t_menu = RDB_PRE . 'menu';
	}//}}}

	/**
	 * 组装WHERE
	 * <pre>
	 * parentid int 上级ID
	 * </pre>
	 */
	function _where($where)
	{//{{{
		$sql = array();
		if (isset($where['parentid']))
			{
			$sql[] = "parentid = {$where['parentid']}";
			}
		if (!$sql)
			{
			return '';
			}
		return 'AND ' . join(" AND ", $sql);
	}//}}}

	function select($where = array(), $sql = array(), $pagesize = 0, $page = 0, $other = array())
	{//{{{
		if (!$sql['field'])
			{
			$sql['field'] = '*';
			}
		$where = $this->_where($where);
		$sql = "SELECT {$sql['field']} FROM {$this->t_menu} WHERE 1 {$where} ORDER BY `listorder`,`menuid`";
		return page_select($sql, $pagesize, $page, $other);
	}//}}}

	function list_by_pid($parentid, $field = '*')
	{//{{{
		$where = array(
			"parentid" => $parentid,
			);
		return $this->select($where, array("field" => $field,), false);
	}//}}}
}

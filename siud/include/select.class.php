<?php
/**
 * �������¼������
 * 
 * @package default
 * @filesource
 */

class siud_select
{
	private $obj_where = NULL;
	private $obj_table = NULL;

	private $pagesize = 0; //ÿҳ�����
	private $page = 0; //��ǰҳ��
	private $cache_count = 0; //COUNT(*) ��Ļ���ʱ�䣬���� page_select() ʱʹ�á�
	private $limit = 0; //����ֵΪ1ʱ��ʾ�� get_one

	private $sql_count = ''; //ͳ�� COUNT(*) �� SQL ��䣬���� page_select() ʱʹ�á�
	private $sql_limit = ''; //LIMIT �Ӿ�
	private $sql_order = ''; //ORDER BY �Ӿ�

	function __construct()
	{//{{{
		$this->obj_where = new siud_where();
		$this->obj_table = new siud_table();
	}//}}}
	/**
	 * ��ʼ����ѯ����
	 */
	function init($table = NULL)
	{//{{{
		$this->pagesize = 0;
		$this->page = 0;
		$this->cache_count = 0;
		$this->limit = 0;
		$this->sql_count = '';
		$this->sql_limit = '';
		$this->sql_order = '';

		$this->obj_where->init();
		$this->obj_table->init();
		if (!is_null($table))
			{
			$this->obj_table->t($table);
			}
	}//}}}
	/**
	 * ִ�в�ѯ
	 * <pre>
	 * �������˷�ҳ���򷵻� array [0] = ��ǰҳ��¼��, [1] = ��ҳHTML���� [2] = �ܼ�¼����
	 * ʹ�� list($result, $pages, $total) ����ֵ��
	 * ���򷵻ز�ѯ��¼����
	 * </pre>
	 * @return array 
	 */
	function ing()
	{//{{{
		$where = $this->obj_where->make_where();
		$field = $this->obj_table->make_field();
		$from = $this->obj_table->make_from();
		$sql = "SELECT {$field} FROM {$from} {$where} {$this->sql_order} ";

		$o_db = rdb::obj();
		if ($this->pagesize)
			{
			return page_select($sql, $this->pagesize, $this->page, array("cache_count" => $this->cache_count, "sql_count" => $this->sql_count,));
			}

		$sql .= $this->sql_limit;
		if (1 == $this->limit)
			{
			return $o_db->get_one($sql);
			}
		else
			{
			return $o_db->select($sql);
			}
	}//}}}
	/**
	 * ���� LIMIT �Ӿ�
	 * @param int|string $l eg. 1 = LIMIT 1 eg. 10 = LIMIT 10 eg. '10, 20' = LIMIT 10, 20
	 */
	function limit($l)
	{//{{{
		$this->sql_limit = "LIMIT {$l}";
		if (1 == $l)
			{
			$this->limit = 1;
			}
		else
			{
			$this->limit = 0;
			}
		return $this;
	}//}}}
	/**
	 * ÿҳ�����������˲��������� page_select() ִ�в�ѯ���Զ���ҳ����ʱ limit ������Ч��
	 * @param int $p ÿҳ�����
	 */
	function pagesize($p)
	{//{{{
		$this->pagesize = $p;
		return $this;
	}//}}}
	/**
	 * ��ǰҳ�룬���� page_select() ʱʹ�á�������ʱ page_select() Ҳ���Զ���ȡ��
	 * @param int $p
	 */
	function page($p)
	{//{{{
		$this->page = $p;
		return $this;
	}//}}}
	/**
	 * COUNT(*) ��Ļ���ʱ�䣬���� page_select() ʱʹ�á�
	 * @param int $second �����������
	 */
	function cache_count($second)
	{//{{{
		$this->cache_count = $second;
		return $this;
	}//}}}
	/**
	 * ͳ�� COUNT(*) �� SQL ��䣬���� page_select() ʱʹ�á�
	 * @param string $sql
	 */
	function sql_count($sql)
	{//{{{
		$this->sql_count = $sql;
		return $this;
	}//}}}
	/**
	 * �Զ���� ORDER BY �ؼ��֣����贫�롣
	 */
	function order($sql)
	{//{{{
		$this->sql_order = "ORDER BY {$sql}";
		return $this;
	}//}}}
	function __call($name, $arg)
	{//{{{
		if ('w' == $name[0])
			{
			call_user_func_array(array($this->obj_where, $name), $arg);
			}
		else if ('t' == $name[0])
			{
			call_user_func_array(array($this->obj_table, $name), $arg);
			}
		else
			{
			log::add("undefined function {$name}", log::WARN, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			}
		return $this;
	}//}}}
}

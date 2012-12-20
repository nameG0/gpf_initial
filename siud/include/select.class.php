<?php
/**
 * 查多条记录操作类
 * 
 * @package default
 * @filesource
 */

class siud_select
{
	private $obj_where = NULL;
	private $obj_table = NULL;

	private $pagesize = 0; //每页结果数
	private $page = 0; //当前页码
	private $cache_count = 0; //COUNT(*) 句的缓存时间，调用 page_select() 时使用。
	private $limit = 0; //当此值为1时表示用 get_one

	private $sql_count = ''; //统计 COUNT(*) 的 SQL 语句，调用 page_select() 时使用。
	private $sql_limit = ''; //LIMIT 子句
	private $sql_order = ''; //ORDER BY 子句

	function __construct()
	{//{{{
		$this->obj_where = new siud_where();
		$this->obj_table = new siud_table();
	}//}}}
	/**
	 * 初始化查询参数
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
	 * 执行查询
	 * <pre>
	 * 若设置了分页，则返回 array [0] = 当前页记录集, [1] = 分页HTML代码 [2] = 总记录数。
	 * 使用 list($result, $pages, $total) 来赋值。
	 * 否则返回查询记录集。
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
	 * 设置 LIMIT 子句
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
	 * 每页结果数。定义此参数将调用 page_select() 执行查询并自动分页，此时 limit 参数无效。
	 * @param int $p 每页结果数
	 */
	function pagesize($p)
	{//{{{
		$this->pagesize = $p;
		return $this;
	}//}}}
	/**
	 * 当前页码，调用 page_select() 时使用。不设置时 page_select() 也能自动提取。
	 * @param int $p
	 */
	function page($p)
	{//{{{
		$this->page = $p;
		return $this;
	}//}}}
	/**
	 * COUNT(*) 句的缓存时间，调用 page_select() 时使用。
	 * @param int $second 缓存的秒数。
	 */
	function cache_count($second)
	{//{{{
		$this->cache_count = $second;
		return $this;
	}//}}}
	/**
	 * 统计 COUNT(*) 的 SQL 语句，调用 page_select() 时使用。
	 * @param string $sql
	 */
	function sql_count($sql)
	{//{{{
		$this->sql_count = $sql;
		return $this;
	}//}}}
	/**
	 * 自动添加 ORDER BY 关键字，不需传入。
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

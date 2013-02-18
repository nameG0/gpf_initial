<?php
/**
 * 删除记录操作类
 * 
 * @package default
 * @filesource
 */

class siud_delete
{
	private $obj_where = NULL;
	private $obj_table = NULL;

	private $sql_limit = '';

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
		$this->obj_where->init();
		$this->obj_table->init();
		if (!is_null($table))
			{
			$this->obj_table->t($table);
			}
	}//}}}
	/**
	 * 执行
	 */
	function ing()
	{//{{{
		$where = $this->obj_where->make_where();
		$table = $this->obj_table->get_table();

		$sql = "DELETE FROM {$table} {$where} {$this->sql_limit}";

		return rdb::obj()->query($sql);
	}//}}}
	/**
	 * 设置 LIMIT 子句
	 * @param int $l
	 */
	function limit($l)
	{//{{{
		$this->sql_limit = "LIMIT {$l}";
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
		return $this;
	}//}}}
}

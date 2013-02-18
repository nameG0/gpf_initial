<?php 
/**
 * 负责处理 SQL 语句 FROM 子句的类
 * <pre>
 * SELECT [这一部份] FROM [这一部份] WHERE ...
 * FROM 子句设置方法全部以 t 为前序
 * </pre>
 *
 * @package default
 * @filesource
 */

class siud_table
{
	private $field_str = '';
	private $tfrom = '';
	private $tpre = '';
	private $table = ''; //保存主表完整表名。
	private $table_as = array(); //记录表全名到表别名的映射。
	private $tf = array(); //记录每个表所需取的字段。
	private $tjoin = array(); //记录 JOIN 信息。


	private $sql_table = ''; //FROM [这一部份] 主表的表名部份。

	/**
	 * 重置属性为默认值。
	 */
	function init()
	{//{{{
		$this->field_str = '';
		$this->tfrom = '';
		$this->tpre = RDB_PRE;
		$this->table = '';
		$this->table_as = array();
		$this->tf = array();
		$this->tjoin = array();
		$this->sql_table = '';
		return $this;
	}//}}}
	/**
	 * 生成 SELECT [这部份] FROM 。
	 * @return string
	 */
	function make_field()
	{//{{{
		$sql_field = '';
		$m = '';
		$tf = $this->tf; //复制一份 tf 避免在方法内改变查询参数。

		if ($this->field_str)
			{
			$sql_field = $this->field_str . ' ';
			$m = ', ';
			if (count($tf) == 1 && '*' == $tf[$this->table])
				{
				//在使用 tfield() 设置字段且只从一个表查询时，直接返回 tfield() 设置的值。
				return $sql_field;
				}
			}
		if (count($tf) > 1 && '*' == $tf[$this->table])
			{
			//处理设置多于一个表设置字段且主表为*的情况。不处理会出现：
			//SELECT *, a.b FROM
			$sql_field = "`{$this->table_as[$this->table]}`.*, ";
			unset($tf[$this->table]);
			}
		$sql_field .= $m . join(", ", $tf);
		return $sql_field;
	}//}}}
	/**
	 * 生成 SELECT * FROM [这部份] WHERE
	 * @return string
	 */
	function make_from()
	{//{{{
		$from = '';
		if ($this->tfrom)
			{
			$from .= $this->tfrom . ' ';
			}
		$ret .= $this->sql_table . ' ' . join(" ", $this->tjoin);
		return $ret;
	}//}}}
	/**
	 * 返回主表表名，一般用于 UPDATE,DELETE 一类操作
	 * @return string
	 */
	function get_table()
	{//{{{
		return $this->table;
	}//}}}

	/**
	 * 直接设置 SELECT [这部份] FROM 的内容。
	 * @param string $field
	 */
	function tfield($field)
	{//{{{
		$this->field_str = $field;
		return $this;
	}//}}}
	/**
	 * 直接设置 SELECT * FROM [这部份] WHERE 的内容。
	 * @param string $from
	 */
	function tfrom($from)
	{//{{{
		$this->tfrom = $from;
		return $this;
	}//}}}
	/**
	 * 设置表名前序
	 * <pre>
	 * 设置马上生效：->tpre('pre_a_')->t('content')->tpre('')->t('full_name')->tpre('pre_b_')->...
	 * 此时 content 表完整表名为 pre_a_content, full_name 则为本身。之后的表名前序则且 pre_b_
	 * </pre>
	 * @param string $pre 表名前序，设置后马上生效。
	 */
	function tpre($pre)
	{//{{{
		$this->tpre = $pre;
		return $this;
	}//}}}
	/**
	 * 设置主表完整表名。
	 */
	function tfull($table, $as_name = '')
	{//{{{
		$this->table = $table;
		$this->tf[$table] = '*';
		$this->sql_table = "`{$table}`";
		if ($as_name)
			{
			$this->sql_table .= " AS `{$as_name}`";
			}
		else
			{
			$as_name = $table;
			}
		$this->table_as[$table] = $as_name;
		return $this;
	}//}}}
	/**
	 * 设置主表（自动增加表名前序）
	 */
	function t($table, $as_name = '')
	{//{{{
		$this->tfull($this->tpre . $table, $as_name);
		return $this;
	}//}}}
	/**
	 * 设置指定表所需的字段。
	 * @param string $table 表名，表全名或 AS 后的表别名。
	 * @param string $field 对应的字段。eg. * eg. name, id AS key
	 */
	function tf($table, $field)
	{//{{{
		$t_name = $table;
		//判断表名部份是用别名还是用输入值。
		$tfull = $this->tpre . $table;
		if ($this->table_as[$tfull])
			{
			$t_name = $this->table_as[$tfull];
			}
		if ($t_name)
			{
			$t_name = "`{$t_name}`.";
			}
		if ($table == $this->table_as[$this->table])
			{
			//处理$table为主表别名的情况。
			$table = $this->table;
			}
		$this->tf[$table] = '';
		$field_explort = array_map('trim', explode(",", $field));
		$m = '';
		foreach ($field_explort as $k => $v)
			{
			$this->tf[$table] .= "{$m}{$t_name}{$v}";
			$m = ', ';
			}
		return $this;
	}//}}}
	/**
	 * USING 类连表
	 * @param string $table 所连的表及其在查询中的别名,其中别名为可选。eg. 'content,cc' eg. 'category'
	 * @param string $field USING 的字段
	 * @param string $type 类型['', RIGHT, LEFT]
	 */
	private function _join_using($table, $field, $type)
	{//{{{
		list($t_name, $as_name) = explode(",", $table);
		$join_key = $as_name ? $as_name : $t_name;
		$tfull = $this->tpre . $t_name;
		$this->tjoin[$join_key] = '';
		if ($type)
			{
			$this->tjoin[$join_key] .= $type . ' ';
			}
		$this->tjoin[$join_key] .= "JOIN `{$tfull}`";
		if ($as_name)
			{
			$this->table_as[$tfull] = $as_name;
			$this->tjoin[$join_key] .= " AS `{$as_name}`";
			}
		$this->tjoin[$join_key] .= " USING({$field})";
	}//}}}
	/**
	 * JOIN USING 连表
	 */
	function tjusing($table, $field)
	{//{{{
		$this->_join_using($table, $field, '');
		return $this;
	}//}}}
	/**
	 * RIGHT JOIN USING 连表
	 */
	function trjusing($table, $field)
	{//{{{
		$this->_join_using($table, $field, 'RIGHT');
		return $this;
	}//}}}
	function tljusing($table, $field)
	{//{{{
		$this->_join_using($table, $field, 'LEFT');
		return $this;
	}//}}}
	/**
	 * ON 类连表
	 * @param string $table 参考 _join_using 同名参数
	 * @param string $on ON 部份语句。eg. cc.contentid=c.contentid
	 * @param string $type 参考 _join_using 同名参数
	 */
	private function _join_on($table, $on, $type)
	{//{{{
		list($t_name, $as_name) = explode(",", $table);
		$join_key = $as_name ? $as_name : $t_name;
		$tfull = $this->tpre . $t_name;
		$this->tjoin[$join_key] = '';
		if ($type)
			{
			$this->tjoin .= $type . ' ';
			}
		$this->tjoin[$join_key] .= "JOIN `{$tfull}`";
		if ($as_name)
			{
			$this->table_as[$tfull] = $as_name;
			$this->tjoin[$join_key] .= " AS `{$as_name}`";
			}
		$this->tjoin[$join_key] .= " ON {$on}";
	}//}}}
	/**
	 * JOIN ON 类连表
	 */
	function tjon($table, $on)
	{//{{{
		$this->_join_on($table, $on, '');
		return $this;
	}//}}}
	/**
	 * RIGHT JOIN ON 类连表
	 */
	function trjon($table, $on)
	{//{{{
		$this->_join_on($table, $on, 'RIGHT');
		return $this;
	}//}}}
	/**
	 * LEFT JOIN ON 类连表
	 */
	function tljon($table, $on)
	{//{{{
		$this->_join_on($table, $on, 'LEFT');
		return $this;
	}//}}}
}

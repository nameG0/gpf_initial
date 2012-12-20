<?php 
/**
 * 负责处理 SQL 语句 WHERE 子句的类
 * <pre>
 * WHERE 子句设置方法全部以 w 为前序
 * </pre>
 *
 * @package default
 * @filesource
 */

class siud_where
{
	private $where = array();
	private $where_str = '';

	/**
	 * 重置属性为默认值。
	 */
	function init()
	{//{{{
		$this->where = array();
		$this->where_str = '';
	}//}}}
	/**
	 * 生成 WHERE 子句
	 * @return string
	 */
	function make_where()
	{//{{{
		$where = '';
		if ($this->where_str)
			{
			$where = $this->where_str . ' ';
			}
		foreach ($this->where as $f => $w)
			{
			$where .= $w . ' ';
			}
		if ($where)
			{
			//去除最前面的AND或OR
			$where = ltrim($where);
			$seek = strpos($where, ' ');
			if (2 == $seek || 3 == $seek)
				{
				$where = substr($where, $seek + 1);
				}
			$where = 'WHERE ' . $where;
			}
		return $where;
	}//}}}
	/**
	 * 直接设置 WHERE 子句
	 * @param string $str WHERE句，eg. AND field=1
	 */
	function where($str)
	{//{{{
		$this->where_str = $str;
	}//}}}
	/**
	 * 设置一个字段的条件
	 * <pre>
	 * eg. w('field', "LIKE '%1'") 组成结果为 AND field LIKE '%1'
	 * </pre>
	 * @param string $field 字段名
	 * @param string $where 条件，eg. =12 eg. LIKE '%12'
	 * @param string $ao 标记用AND还是OR连接
	 */
	function w($field, $where, $ao = 'AND')
	{//{{{
		$ao = 'OR' == $ao ? 'OR' : 'AND';
		$this->where[$field] = "{$ao} {$field} {$where}";
	}//}}}
	/**
	 * 设置一个字段相等的条件
	 * <pre>
	 * eg. wis('field', 1) -> AND field = 1
	 * eg. wis('field', NULL, 'OR') -> OR field IS NULL
	 * </pre>
	 * @param int|string|NULL 字段=的值。
	 */
	function wis($field, $value, $ao = 'AND')
	{//{{{
		$ao = 'OR' == $ao ? 'OR' : 'AND';
		if (is_null($value))
			{
			$value_str = 'IS NULL';
			}
		else if (is_int($value) || is_numeric($value))
			{
			$value_str = "= {$value}";
			}
		else
			{
			$value_str = "= '{$value}'";
			}
		$this->where[$field] = "{$ao} {$field} {$value_str}";
	}//}}}
	/**
	 * 设置指定字段的 IN 条件
	 * @param array $in
	 */
	function win($field, $in, $ao = 'AND')
	{//{{{
		if (!is_array($in) || 0 == count($in))
			{
			log::add("不能设置空的 IN 条件", log::WARN, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			return $this;
			}
		$ao = 'OR' == $ao ? 'OR' : 'AND';
		//为非数字类的值加上引号
		foreach ($in as $k => $v)
			{
			if (!is_int($v) && !is_numeric($v))
				{
				$in[$k] = "'{$v}'";
				}
			}
		$this->where[$field] = "{$ao} {$field} IN (" . join(", ", $in) . ')';
		return $this;
	}//}}}
}

<?php 
/**
 * ������ SQL ��� WHERE �Ӿ����
 * <pre>
 * WHERE �Ӿ����÷���ȫ���� w Ϊǰ��
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
	 * ��������ΪĬ��ֵ��
	 */
	function init()
	{//{{{
		$this->where = array();
		$this->where_str = '';
	}//}}}
	/**
	 * ���� WHERE �Ӿ�
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
			//ȥ����ǰ���AND��OR
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
	 * ֱ������ WHERE �Ӿ�
	 * @param string $str WHERE�䣬eg. AND field=1
	 */
	function where($str)
	{//{{{
		$this->where_str = $str;
	}//}}}
	/**
	 * ����һ���ֶε�����
	 * <pre>
	 * eg. w('field', "LIKE '%1'") ��ɽ��Ϊ AND field LIKE '%1'
	 * </pre>
	 * @param string $field �ֶ���
	 * @param string $where ������eg. =12 eg. LIKE '%12'
	 * @param string $ao �����AND����OR����
	 */
	function w($field, $where, $ao = 'AND')
	{//{{{
		$ao = 'OR' == $ao ? 'OR' : 'AND';
		$this->where[$field] = "{$ao} {$field} {$where}";
	}//}}}
	/**
	 * ����һ���ֶ���ȵ�����
	 * <pre>
	 * eg. wis('field', 1) -> AND field = 1
	 * eg. wis('field', NULL, 'OR') -> OR field IS NULL
	 * </pre>
	 * @param int|string|NULL �ֶ�=��ֵ��
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
	 * ����ָ���ֶε� IN ����
	 * @param array $in
	 */
	function win($field, $in, $ao = 'AND')
	{//{{{
		if (!is_array($in) || 0 == count($in))
			{
			log::add("�������ÿյ� IN ����", log::WARN, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			return $this;
			}
		$ao = 'OR' == $ao ? 'OR' : 'AND';
		//Ϊ���������ֵ��������
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

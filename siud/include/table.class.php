<?php 
/**
 * ������ SQL ��� FROM �Ӿ����
 * <pre>
 * SELECT [��һ����] FROM [��һ����] WHERE ...
 * FROM �Ӿ����÷���ȫ���� t Ϊǰ��
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
	private $table = ''; //������������������
	private $table_as = array(); //��¼��ȫ�����������ӳ�䡣
	private $tf = array(); //��¼ÿ��������ȡ���ֶΡ�
	private $tjoin = array(); //��¼ JOIN ��Ϣ��


	private $sql_table = ''; //FROM [��һ����] ����ı������ݡ�

	/**
	 * ��������ΪĬ��ֵ��
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
	 * ���� SELECT [�ⲿ��] FROM ��
	 * @return string
	 */
	function make_field()
	{//{{{
		$sql_field = '';
		$m = '';
		$tf = $this->tf; //����һ�� tf �����ڷ����ڸı��ѯ������

		if ($this->field_str)
			{
			$sql_field = $this->field_str . ' ';
			$m = ', ';
			if (count($tf) == 1 && '*' == $tf[$this->table])
				{
				//��ʹ�� tfield() �����ֶ���ֻ��һ�����ѯʱ��ֱ�ӷ��� tfield() ���õ�ֵ��
				return $sql_field;
				}
			}
		if (count($tf) > 1 && '*' == $tf[$this->table])
			{
			//�������ö���һ���������ֶ�������Ϊ*����������������֣�
			//SELECT *, a.b FROM
			$sql_field = "`{$this->table_as[$this->table]}`.*, ";
			unset($tf[$this->table]);
			}
		$sql_field .= $m . join(", ", $tf);
		return $sql_field;
	}//}}}
	/**
	 * ���� SELECT * FROM [�ⲿ��] WHERE
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
	 * �������������һ������ UPDATE,DELETE һ�����
	 * @return string
	 */
	function get_table()
	{//{{{
		return $this->table;
	}//}}}

	/**
	 * ֱ������ SELECT [�ⲿ��] FROM �����ݡ�
	 * @param string $field
	 */
	function tfield($field)
	{//{{{
		$this->field_str = $field;
		return $this;
	}//}}}
	/**
	 * ֱ������ SELECT * FROM [�ⲿ��] WHERE �����ݡ�
	 * @param string $from
	 */
	function tfrom($from)
	{//{{{
		$this->tfrom = $from;
		return $this;
	}//}}}
	/**
	 * ���ñ���ǰ��
	 * <pre>
	 * ����������Ч��->tpre('pre_a_')->t('content')->tpre('')->t('full_name')->tpre('pre_b_')->...
	 * ��ʱ content ����������Ϊ pre_a_content, full_name ��Ϊ����֮��ı���ǰ������ pre_b_
	 * </pre>
	 * @param string $pre ����ǰ�����ú�������Ч��
	 */
	function tpre($pre)
	{//{{{
		$this->tpre = $pre;
		return $this;
	}//}}}
	/**
	 * ������������������
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
	 * ���������Զ����ӱ���ǰ��
	 */
	function t($table, $as_name = '')
	{//{{{
		$this->tfull($this->tpre . $table, $as_name);
		return $this;
	}//}}}
	/**
	 * ����ָ����������ֶΡ�
	 * @param string $table ��������ȫ���� AS ��ı������
	 * @param string $field ��Ӧ���ֶΡ�eg. * eg. name, id AS key
	 */
	function tf($table, $field)
	{//{{{
		$t_name = $table;
		//�жϱ����������ñ�������������ֵ��
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
			//����$tableΪ��������������
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
	 * USING ������
	 * @param string $table �����ı����ڲ�ѯ�еı���,���б���Ϊ��ѡ��eg. 'content,cc' eg. 'category'
	 * @param string $field USING ���ֶ�
	 * @param string $type ����['', RIGHT, LEFT]
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
	 * JOIN USING ����
	 */
	function tjusing($table, $field)
	{//{{{
		$this->_join_using($table, $field, '');
		return $this;
	}//}}}
	/**
	 * RIGHT JOIN USING ����
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
	 * ON ������
	 * @param string $table �ο� _join_using ͬ������
	 * @param string $on ON ������䡣eg. cc.contentid=c.contentid
	 * @param string $type �ο� _join_using ͬ������
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
	 * JOIN ON ������
	 */
	function tjon($table, $on)
	{//{{{
		$this->_join_on($table, $on, '');
		return $this;
	}//}}}
	/**
	 * RIGHT JOIN ON ������
	 */
	function trjon($table, $on)
	{//{{{
		$this->_join_on($table, $on, 'RIGHT');
		return $this;
	}//}}}
	/**
	 * LEFT JOIN ON ������
	 */
	function tljon($table, $on)
	{//{{{
		$this->_join_on($table, $on, 'LEFT');
		return $this;
	}//}}}
}

<?php
/**
 * 2010-12-24 ����Ϣ����
 * 
 * @version 20111019
 * @package default
 * @filesource
 */
class t
{
	static private $data = array();	//�������ݱ���Ϣ

	// ���췽������Ϊprivate����ֱֹ�Ӵ�������
	private function __construct() 
	{
	}

	//����ָ��ģ��ı���Ϣ
	function load($mod)
	{
		//����
		$mod = 'phpcms' == $mod ? 'include' : $mod;

		$path = PHPCMS_ROOT . "{$mod}/table/table.inc.php";
		if (!is_file($path))
			{
			return false;
			}
		$data = include $path;
		foreach ($data as $k => $v)
			{
			self::$data[$k] = $v;
			}
		return true;
	}

	//���ر���Ϣ
	function read($table, $key = NULL)
	{
		if (!self::$data[$table])
			{
			log::add("δ���صı� {$table}", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			return '';
			}
		return is_null($key) ? self::$data[$table] : self::$data[$table][$key];
	}

	//���ر���
	static function t($table)
	{
		return self::read($table, 'name');
	}

	//ȡ��where��������
		//$begin ǰ��and �� or
		//$table ����
		//$alias ����sql����еı���
	function where($begin, $table, $alias = '', $end = '')
	{
		if (!$sql = self::read($table, 'where'))
			{
			return '';
			}
		$alias = $alias ? $alias."." : '';
		$ret = str_replace('{t}', $alias, $sql);
		return "{$begin} {$ret} {$end} ";
	}

	//����Ĭ�� order by �Ӿ�
		//$begin	ǰ��
		//$table	����
		//$alias	sql����б����
		//$end		����
	function order($begin, $table, $alias = '', $end = '')
	{
		if (!$sql = self::read($table, 'order'))
			{
			return '';
			}
		$alias = $alias ? $alias."." : '';
		$ret = str_replace('{t}', $alias, $sql);
		return "{$begin} {$ret} {$end} ";
	}

	function _merge_where($where)
	{
		//�Զ�׷�� WHERE �ؼ��֣�Ϊ��ʵ���Զ�׷�ӣ���ʶ��� $where ������ʼ�����Ƿ�Ϊ������
		$where = trim($where);
		if ($where)
			{
			$keyword = 'WHERE';	//$where �׹ؼ���ʶ��
			switch ($where[0])
				{
				//order by
				case "o":
				case "O":
					$keyword = 'ORDER BY';
					break;
				//limit
				case "l":
				case "L":
					$keyword = 'LIMIT';
					break;
				//group
				case "g":
				case "G":
					$keyword = 'GROUP BY';
					break;
				}
			if ($keyword != strtoupper(substr($where, 0, strlen($keyword))))
				{
				$where = 'WHERE ' . $where;
				}
			}
		return $where;
	}

	//���ؼ򵥵� select ���
		//$table	����
		//$field	�����ֶ�
		//$where	�������ֶΣ�����where��ͷ���� status=99 order by contentid �� order by status desc
		//$safe		�������Ϊ true,���Զ����� where() ��Ϊ���Ҹ���������
	function select($table, $field = '*', $where = '', $safe = false)
	{
		if (!$table_data = self::read($table))
			{
			return '';
			}
		$field = $field ? $field : '*';
		$where = self::_merge_where($where);
		if ($safe && $safe_where = self::where('', $table))
			{
			//�������where�䣬���� and ���� where������׷�Ӳ�ѯ����
			if ('WHERE' == strtoupper(substr($where, 0, 5)))
				{
				$where = 'AND ' . substr($where, 5);
				}
			$where = "WHERE {$safe_where} {$where}";
			}
		$sql = "SELECT {$field} FROM {$table_data['name']} {$where} {$order}";
		return $sql;
	}

	//���ؼ򵥵����� get_one �Ĳ������
		//$table	����
		//$field	select���field�Ӿ�
		//$where	where����,int(��$pk���ֶ�) or string(������,���� where �ؼ���)
		//$safe		�������Ϊ true,���Զ����� where() ��Ϊ���Ҹ���������
	function get($table, $field, $where, $safe = false)
	{
		if (!$table_data = self::read($table))
			{
			return '';
			}
		if (is_numeric($where))
			{
			$pk = $table_data['field_get'] ? $table_data['field_get'] : $table_data['pk'];
			if (!$pk)
				{
				log::add("�� {$table} δ�������� field_get �� pk", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
				return '';
				}
			$where = "{$pk} = '{$where}' ";
			}
		$where = self::_merge_where($where);
		//2011-05-03 ggzhu δ��ɰ�ȫwhere������룬���Է��� select() �����������Ϊһ�������ķ�����
		/*
		if ($safe)
			{
			$where .= self::where('AND', $table);
			}
		$where and $where = "WHERE {$where}";
		*/
		$sql = "SELECT {$field} FROM {$table_data['name']} {$where} LIMIT 1";
		return $sql;
	}

	//���ؼ򵥵�ɾ�����
		//$table	����
		//$where	where����,int(��$pk���ֶ�) or string(������,���� where �ؼ���)
		//$safe		�������Ϊ true,���Զ����� ��ȫɾ��������Ϊ����������
	function del($table, $where, $safe = false)
	{
		if (!$where)
			{
			log::add("ɾ����������Ϊ��", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			return '';
			}
		if (!$table_data = self::read($table))
			{
			return '';
			}
		$pk = $table_data['field_del'] ? $table_data['field_del'] : $table_data['pk'];
		if (!$pk && (is_numeric($where) || is_array($where)))
			{
			log::add("�� {$table} δ�������� field_del �� pk", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			return '';
			}
		if (is_numeric($where))
			{
			$where = "{$pk} = '{$where}' ";
			}
		else if (is_array($where))
			{
			$where = "{$pk} IN ('".join("','", $where)."')";
			}
		if ($safe && $table_data['del_where'])
			{
			$where .= 'AND ' . $table_data['del_where'] . ' ';
			}
		$where and $where = "WHERE {$where}";
		$sql = "DELETE FROM {$table_data['name']} {$where} ";
		return $sql;
	}

	// * �ѱ� make_search ���
	//�Զ�ת���û��������������Ϊ��ѯ����
		//$begin	���ؽ��ǰ�� eg. 'WHERE'
		//$field(array)	�����ֶ�,eg. array({field}, {field}, ...)
			//���ʶ��壺
				//�ֶ���������
					//�ֶ���	eg. status
					//�����.�ֶ���	eg. c.status
				//�����߼���ָ��AND������OR������
				//�Ƚ��߼���ָ��=������>������
				//ֵ�ӹ����������Ϳ�Ϊ �ַ��� �� ����
					//�ַ�����������'{var}'��ʾ����ֵ��λ�ã���ִ��ʱ�����滻��һ������'LIKE'�Ƚ��߼��С�
						//eg. '%{var}%'
						//eg. '%{var}'
					//���飬һά���飬array(�û�����ֵ => ��ʵֵ)
						//eg. array(1 => 0, 2 => 1);
						//eg. array('����' => 0, 'ͨ��' => 99);
			//{field} �������Ϳ�Ϊ �ַ��� �� ���飺
			//�ַ�����ֻʹ�á��ֶ����������������߼�Ϊ��AND�����Ƚ��߼�Ϊ��=��:
				//eg. 'status'
				//eg. 'c.contentid'
			//����,�����������߼�, �ֶ�������, [�Ƚ��߼�]��[ֵ�ӹ�]��[ֵ]�����С�
			//���С�ֵ�������ڶ�ͬһ���ֶν��ж����������ģ� eg. a > 1 AND a < 10:
				//eg. array('AND', 'name', 'LIKE', '%{var}%')
				//eg. array('OR', 'inputtime', '>', '', $star_time);
				//eg. array('AND', 'inputtime', '<', '', $end_time);
		//$value	�û��������������
		//$end		���ؽ������ eg. 'AND'
	//eg:where_select('WHERE', array('status'))
	function where_select($begin, $field, $value = array(), $end = '')
	{
		$where = '';
		$middle = '';
		foreach ($field as $k => $v)
			{
			//��ʽ������
			$v = is_array($v) ? $v : array('AND', $v);
			$v[1] = explode(".", $v[1]);
			$field_name = $v[1][1] ? $v[1][1] : $v[1][0];
			$alias = $v[1][1] ? '`'.$v[1][0] . '`.' : '';
			$compare = $v[2] ? $v[2] : '=';
			$field_value = $v[4] ? $v[4] : $value[$field_name];
			//���˿��ַ������������������������п�����Ҫ ='0'
			if ($field_value != '')
				{
				//ֵ�ӹ�
				$field_value = $v[3] ? (is_string($v[3]) ? str_replace('{var}', $field_value, $v[3]) : $v[3][$field_value]) : $field_value;
				$middle = $middle ? $v[0] : '';
				$where .= "{$middle} {$alias}`{$field_name}` {$compare} '{$field_value}' ";
				$middle = 'AND';
				}
			}
		return $where ? "{$begin} {$where} {$end}" : '';
	}

	//ͨ��get�����Զ���װorder�Ӿ䣬�����ο� order()
		//$field(array)	����������ֶ�
	function order_select($begin, $field, $alias = '', $end = '')
	{
		//��ֱ�Ӵ� $_GET �� $_POST ��ȡ���ݣ�ȡ���� common.inc.php �������ȫ�ֱ�����
		global $user_order;
		if (!$user_order)
			{
			return '';
			}
		$arg = explode(".", $user_order);
		if (!in_array($arg[0], $field))
			{
			return '';
			}
		$arg[1] = 'DESC' == strtoupper($arg[1]) ? 'DESC' : 'ASC';
		$alias = $alias ? $alias . '.' : '';
		return "{$begin} {$alias}{$arg[0]} {$arg[1]} {$end}";
	}

	//����ֶ���������
		//$field	�����ֶ�
		//$title	��������
	function echo_order($field, $title)
	{
		global $user_order;
		$url = preg_replace("/&?user_order=[^&]*/", '', URL);
		$url .= "&user_order={$field}.";
		$action = 'ASC';
		if ($user_order)
			{
			$arg = explode(".", $user_order);
			if ($arg[0] == $field)
				{
				$action = 'DESC' == strtoupper($arg[1]) ? 'ASC' : 'DESC';
				}
			}
		$url .= $action;
		return "<a href=\"{$url}\" title=\"��{$title}����\">{$title}</a>";
	}

	/**
	 * �Զ���ҳ��ѯ
	 * 
	 * 	$sql	��ѯ��䣬������ limit ����
	 * 	$page	���ֶ�ָ����ǰҳ��
	 * 	$other	��������
	 * 		url	��ҳ����url
	 * 		page_func	��ҳ���������ò���Ϊ($count, $pagesize, $page, $url)
	 * 		cache_count	COUNT() ��仺����������Ϊ 0 �򲻻��棬Ĭ��Ϊ 0 ��
	 * 		sql_count	��ѯ COUNT() �����
	 * @return array 0=result,1=page,2=count,������ list($result, $pages, $total) ֱ�Ӹ�ֵ��
	 */
	function page_select($sql, $pagesize = 0, $page = 0, $other = array())
	{
		$url = $other['url'] ? $other['url'] : '';
		$page_func = $other['page_func'] ? $other['page_func'] : 'phppages';
		global $db;
		if ($other['sql_count'])
			{
			$sql_count = $other['sql_count'];
			}
		else
			{
			//����ѯ���򼶱�3����ʱ�ǳ�����
			//$sql_count = "SELECT COUNT(*) AS count FROM ({$sql}) AS c";
			//Ҫ�滻���֣�SELECT c.contentid,c.catid,c.title,c.modelno,c.size,c.price,c.thumb,c.price,c.userid, c.brandid, (SELECT name FROM phpcms2008_brand b WHERE b.brandid=c.brandid) AS Brand FROM phpcms2008_content c WHERE c.userid = '2936'
			//Ҫ�滻����л��е�,������һ���ַ����ǻ���
			$sql_count = str_replace(array("\r", "\n"), array('', ' '), $sql);
			//Ҫ�滻��ѯ�ֶΰ��� DISTINCT �ؼ��ֵ����
			if (false === stripos($sql_count, 'DISTINCT'))
				{
				$sql_count = preg_replace('/^\s*SELECT.*FROM/i', 'SELECT COUNT(*) AS `count` FROM', $sql_count, 1);
				}
			else
				{
				//SELECT DISTINCT userid >> SELECT COUNT(userid)
				preg_match("/DISTINCT\s+([^ ,]+)[ ,]/i", $sql_count, $match);
				$field = $match[1];
				unset($match);
				$sql_count = preg_replace('/^\s*SELECT.*FROM/i', "SELECT COUNT(DISTINCT {$field}) AS `count` FROM", $sql_count, 1);
				}
			$sql_count = preg_replace("/ORDER BY.*/i", '', $sql_count);
			}
		$count = cache_count($sql_count, intval($other['cache_count']));

		$page = intval($page);
		if (!$page)
			{
			$page = paging_current();
			}
		$pagesize = paging_pagesize($pagesize);
		$offset = paging_offset($pagesize, $page);
		$sql .= " LIMIT {$offset}, {$pagesize}";
		$result = $db->select($sql);
		//����
		if ('phppages' == $page_func)
			{
			$pages = phppages($count, $page, $pagesize);
			}
		else
			{
			$pages = $page_func($count, $pagesize, $page, $url);
			}
		return array($result, $pages, $count);
	}
}
//�ɽӿ�
function t_load($mod)
{
	return t::load($mod);
}
function t_where($begin, $table, $alias = '', $end = '')
{
	return t::where($begin, $table, $alias, $end);
}
function t_order($begin, $table, $alias = '', $end = '')
{
	return t::order($begin, $table, $alias, $end);
}
function t_select($table, $field = '*', $where = '', $safe = false)
{
	return t::select($table, $field, $where, $safe);
}
function t_get($table, $field, $where, $safe = false)
{
	return t::get($table, $field, $where, $safe);
}
function t_del($table, $where, $safe = false)
{
	return t::del($table, $where, $safe);
}
function t($table)
{
	return t::t($table);
}
function order_select($begin, $field, $alias = '', $end = '')
{
	return t::order_select($begin, $field, $alias, $end);
}
function echo_order($field, $title)
{
	return t::echo_order($field, $title);
}
function page_select($sql, $pagesize = 0, $page = 0, $other = array())
{
	return t::page_select($sql, $pagesize, $page, $other);
}

//ggzhu 2011-10-13 needdel
//�������������˺���Ϊ�����ݣ�һ����ϱ�������� block_search_form.tpl.php һ��ʹ�á�
//$arg
	//str_replace ����ֵ�滻��{field} => array('�黻ֵ' => '�滻ֵ', ...)
	//value_replace ֵ�ӹ���ʵ��Ҳ���滻������ {var} ��ʾ����ֵ�������� LIKE �ȽϷ�,�� '%{var}%',������ֵΪ "keyword" ʱ������Ϊ "%keyword%"
function make_search($begin, $arg, $s, $end = '')
{
	$where = "";
	//�� display ��Ϊһά����
	$display = array();
	foreach ($arg['display'] as $v)
		{
		if (!is_array($v))
			{
			$display[] = $v;
			}
		else
			{
			foreach ($v as $_v)
				{
				$display[] = $_v;
				}
			}
		}
	foreach ($display as $v)
		{
		//ȡĬ��ֵ
		if (!isset($s[$v]) && isset($arg['display_default'][$v]))
			{
			$s[$v] = $arg['display_default'][$v];
			}
		if (!is_array($s[$v]))
			{
			$s[$v] = trim($s[$v]);
			}
		if (isset($s[$v]) && '' !== $s[$v])
			{
			//��ֵ���д���
			if ($arg['str_replace'][$v])
				{
				$s[$v] = str_replace(array_keys($arg['str_replace'][$v]), array_values($arg['str_replace'][$v]), $s[$v]);
				}
			if ($arg['value_replace'][$v])
				{
				$s[$v] = str_replace('{var}', $s[$v], $arg['value_replace'][$v]);
				}

			$join = 'AND';
			if (!$where)
				{
				$join = '';
				}
			//�ȽϷ�
			$compare = '=';
			if ($s["c_{$v}"])
				{
				$compare = $s["c_{$v}"];
				}
			else if ($arg['input_compare'][$v])
				{
				if (is_array($arg['input_compare'][$v]))
					{
					$compare = $arg['input_compare'][$v][0];
					}
				else
					{
					$compare = $arg['input_compare'][$v];
					}
				}
			if (is_array($s[$v]) && count($s[$v]) > 1)
				{
				$compare = '=' == $compare ? 'IN' : 'NOT IN';
				$value = "('" . join("','", $s[$v]) . "')";
				}
			else
				{
				if (is_array($s[$v]))
					{
					$s[$v] = current($s[$v]);
					}
				if ('between' == $compare)
					{
					$_start = $s[$v . '_start'];
					$_end = $s[$v . '_end'];
					if ($_start || $_end)
						{
						//���߶���������
						if ($_start && $_end)
							{
							if ($_start == $_end)
								{
								$compare = '=';
								$value = "'{$_start}'";
								}
							else
								{
								$value = "'{$_start}' AND '{$_end}'";
								}
							}
						//ֻ��������֮һ�����
						else if ($_start)
							{
							$compare = '>=';
							$value = "'{$_start}'";
							}
						else if ($_end)
							{
							$compare = '<=';
							$value = "'{$_start}'";
							}
						}
					else
						{
						$compare = '=';
						$value = "'{$s[$v]}'";
						}
					}
				else if ('in' == $compare && false !== strpos($s[$v], ','))
					{
					$s[$v] = stripslashes($s[$v]);
					$s[$v] = str_replace(array('"', "'"), '', $s[$v]);
					$_tmp = explode(",", $s[$v]);
					$_tmp = array_map('trim', $_tmp);
					$value = "('" . join("','", $_tmp) . "')";
					unset($_tmp);
					}
				else
					{
					$compare = '=';
					$value = "'{$s[$v]}'";
					}
				}
			$where .= "{$join} {$v} {$compare} {$value} ";
			}
		}
	return $where ? $begin . $where . $end : '';
}
?>

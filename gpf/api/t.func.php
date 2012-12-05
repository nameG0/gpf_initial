<?php
/**
 * 2010-12-24 表信息管理
 * 
 * @version 20111019
 * @package default
 * @filesource
 */
class t
{
	static private $data = array();	//保存数据表信息

	// 构造方法声明为private，防止直接创建对象
	private function __construct() 
	{
	}

	//加载指定模块的表信息
	function load($mod)
	{
		//例外
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

	//返回表信息
	function read($table, $key = NULL)
	{
		if (!self::$data[$table])
			{
			log::add("未加载的表 {$table}", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			return '';
			}
		return is_null($key) ? self::$data[$table] : self::$data[$table][$key];
	}

	//返回表名
	static function t($table)
	{
		return self::read($table, 'name');
	}

	//取得where部份语句段
		//$begin 前序，and 或 or
		//$table 表名
		//$alias 表在sql语句中的别名
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

	//返回默认 order by 子句
		//$begin	前序
		//$table	表名
		//$alias	sql语句中表别名
		//$end		后序
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
		//自动追加 WHERE 关键字，为了实现自动追加，需识别出 $where 变量开始部份是否为条件句
		$where = trim($where);
		if ($where)
			{
			$keyword = 'WHERE';	//$where 首关键字识别
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

	//返回简单的 select 语句
		//$table	表名
		//$field	返回字段
		//$where	表名后字段，无需where起头，如 status=99 order by contentid 或 order by status desc
		//$safe		如果此项为 true,则自动调用 where() 作为查找附加条件。
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
			//如果已有where句，则用 and 代替 where，方便追加查询条件
			if ('WHERE' == strtoupper(substr($where, 0, 5)))
				{
				$where = 'AND ' . substr($where, 5);
				}
			$where = "WHERE {$safe_where} {$where}";
			}
		$sql = "SELECT {$field} FROM {$table_data['name']} {$where} {$order}";
		return $sql;
	}

	//返回简单的类拟 get_one 的查找语句
		//$table	表名
		//$field	select语句field子句
		//$where	where参数,int(用$pk作字段) or string(条件句,不含 where 关键字)
		//$safe		如果此项为 true,则自动调用 where() 作为查找附加条件。
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
				log::add("表 {$table} 未设置主键 field_get 或 pk", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
				return '';
				}
			$where = "{$pk} = '{$where}' ";
			}
		$where = self::_merge_where($where);
		//2011-05-03 ggzhu 未完成安全where句的输入，可以访照 select() 的做法或分离为一个独立的方法。
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

	//返回简单的删除语句
		//$table	表名
		//$where	where参数,int(用$pk作字段) or string(条件句,不含 where 关键字)
		//$safe		如果此项为 true,则自动调用 安全删除条件作为附加条件。
	function del($table, $where, $safe = false)
	{
		if (!$where)
			{
			log::add("删除条件不能为空", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			return '';
			}
		if (!$table_data = self::read($table))
			{
			return '';
			}
		$pk = $table_data['field_del'] ? $table_data['field_del'] : $table_data['pk'];
		if (!$pk && (is_numeric($where) || is_array($where)))
			{
			log::add("表 {$table} 未设置主键 field_del 或 pk", log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
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

	// * 已被 make_search 替代
	//自动转换用户输入的搜索参数为查询条件
		//$begin	返回结果前序， eg. 'WHERE'
		//$field(array)	搜索字段,eg. array({field}, {field}, ...)
			//名词定义：
				//字段描述符：
					//字段名	eg. status
					//表别名.字段名	eg. c.status
				//连接逻辑：指“AND”，“OR”这类
				//比较逻辑：指“=”，“>”这类
				//值加工：数据类型可为 字符串 或 数组
					//字符串，里面用'{var}'表示参数值的位置，在执行时进行替换，一般用于'LIKE'比较逻辑中。
						//eg. '%{var}%'
						//eg. '%{var}'
					//数组，一维数组，array(用户输入值 => 真实值)
						//eg. array(1 => 0, 2 => 1);
						//eg. array('待审' => 0, '通过' => 99);
			//{field} 数据类型可为 字符串 或 数组：
			//字符串，只使用“字段描述符”，连接逻辑为“AND”，比较逻辑为“=”:
				//eg. 'status'
				//eg. 'c.contentid'
			//数组,按：（连接逻辑, 字段描述符, [比较逻辑]，[值加工]，[值]）排列。
			//其中“值”是用于对同一个字段进行多条件搜索的， eg. a > 1 AND a < 10:
				//eg. array('AND', 'name', 'LIKE', '%{var}%')
				//eg. array('OR', 'inputtime', '>', '', $star_time);
				//eg. array('AND', 'inputtime', '<', '', $end_time);
		//$value	用户输入的搜索参数
		//$end		返回结果后序， eg. 'AND'
	//eg:where_select('WHERE', array('status'))
	function where_select($begin, $field, $value = array(), $end = '')
	{
		$where = '';
		$middle = '';
		foreach ($field as $k => $v)
			{
			//格式化参数
			$v = is_array($v) ? $v : array('AND', $v);
			$v[1] = explode(".", $v[1]);
			$field_name = $v[1][1] ? $v[1][1] : $v[1][0];
			$alias = $v[1][1] ? '`'.$v[1][0] . '`.' : '';
			$compare = $v[2] ? $v[2] : '=';
			$field_value = $v[4] ? $v[4] : $value[$field_name];
			//除了空字符串不处理，其它都处理，比如有可能需要 ='0'
			if ($field_value != '')
				{
				//值加工
				$field_value = $v[3] ? (is_string($v[3]) ? str_replace('{var}', $field_value, $v[3]) : $v[3][$field_value]) : $field_value;
				$middle = $middle ? $v[0] : '';
				$where .= "{$middle} {$alias}`{$field_name}` {$compare} '{$field_value}' ";
				$middle = 'AND';
				}
			}
		return $where ? "{$begin} {$where} {$end}" : '';
	}

	//通过get数据自动组装order子句，参数参考 order()
		//$field(array)	允许排序的字段
	function order_select($begin, $field, $alias = '', $end = '')
	{
		//不直接从 $_GET 或 $_POST 中取数据，取经过 common.inc.php 处理过的全局变量。
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

	//输出字段排序链接
		//$field	排序字段
		//$title	链接文字
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
		return "<a href=\"{$url}\" title=\"按{$title}排序\">{$title}</a>";
	}

	/**
	 * 自动分页查询
	 * 
	 * 	$sql	查询语句，不包含 limit 部份
	 * 	$page	可手动指定当前页数
	 * 	$other	其它参数
	 * 		url	分页链接url
	 * 		page_func	分页函数，调用参数为($count, $pagesize, $page, $url)
	 * 		cache_count	COUNT() 语句缓存秒数，设为 0 则不缓存，默认为 0 。
	 * 		sql_count	查询 COUNT() 的语句
	 * @return array 0=result,1=page,2=count,可以用 list($result, $pages, $total) 直接赋值。
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
			//这句查询百万级表（3百万）时非常慢。
			//$sql_count = "SELECT COUNT(*) AS count FROM ({$sql}) AS c";
			//要替换这种：SELECT c.contentid,c.catid,c.title,c.modelno,c.size,c.price,c.thumb,c.price,c.userid, c.brandid, (SELECT name FROM phpcms2008_brand b WHERE b.brandid=c.brandid) AS Brand FROM phpcms2008_content c WHERE c.userid = '2936'
			//要替换语句有换行的,包括第一个字符就是换行
			$sql_count = str_replace(array("\r", "\n"), array('', ' '), $sql);
			//要替换查询字段包含 DISTINCT 关键字的语句
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
		//例外
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
//旧接口
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
//搜索生成器，此函数为处理部份，一般配合表单输出部份 block_search_form.tpl.php 一起使用。
//$arg
	//str_replace 输入值替换，{field} => array('查换值' => '替换值', ...)
	//value_replace 值加工（实质也是替换），用 {var} 表示输入值，常用于 LIKE 比较符,如 '%{var}%',当输入值为 "keyword" 时处理结果为 "%keyword%"
function make_search($begin, $arg, $s, $end = '')
{
	$where = "";
	//把 display 变为一维数组
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
		//取默认值
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
			//对值进行处理
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
			//比较符
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
						//两者都输入的情况
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
						//只输入其中之一的情况
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

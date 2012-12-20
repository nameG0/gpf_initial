<?php
/**
 * 查询器（SELECT）
 *
 * 2011-09-27
 * <br/>
 * input:
 * - $siud_select 查询参数
 *
 * output:
 * - 	查询结果集
 * - 	$siud_error	出错信息，此变量为空则没错，否则为出错信息。
 * 
 * <b>使用方法：</b><br/>
 * 用 $siud_select 变量保存查询参数：
 * <code>
 * $siud_select = array(
 *	'保存结果集的变量名' => array(查询参数),
 *	'第二个查询' => array(),
 *	'可定义多个查询' => array(),
 *	'result' => array(),
 * 	);
 * </code>
 *
 * 执行查询器：
 * <code>
 * include SIUD_SELECT;
 * </code>
 *
 * 检查错误：
 * <code>
 * include SIUD_SELECT;
 * if ($siud_error)
 * 	{
 * 	showmessage($siud_error);
 * 	}
 * </code>
 * 
 * 使用查询记录：<br/>
 * <code>
 * print_r($result);
 * </code>
 * $result 就是上面定义的第四个查询的结果，直接使用即可。
 *
 * <b>查询参数：</b><br/>
 * table string 查询的表的完整表名，除非定义 sql 参数，否则此参数是必须的。
 * <code>
 * 'table' => DB_PRE . 'content',
 * </code>
 * 
 * limit int|string LIMIT 子句，当为数字 1 时，用 $db->get_one() 查询，否则用 $db->select() 查询。
 * <code>
 * 'limit' => 1, //LIMIT 1
 * 'limit' => 10, //LIMIT 10
 * 'limit' => '10, 20', //LIMIT 10, 20
 * </code>
 *
 * sql string 直接定义 SQL 查询语句
 * <code>
 * 'sql' => 'SELECT * FROM table',
 * 'limit' => 10,
 * </code>
 *
 * enable bool 若定义了此参数，只有当参数值为 true 时才执行查询，否则跳过。
 * <code>
 * 'enable' => $_userid == 1, //$_userid 为 1 时执行查询，否则不执行。
 * </code>
 *
 * disable bool 若定义了此参数，当参数值为 true 时不执行查询。
 * <code>
 * 'disable' => $_userid == 1, //$_userid 为 1 时不执行查询，否则执行。
 * </code>
 *
 * empty_exist boot 若此查询的结果为空，则中断返回，不执行后面的查询。
 * <code>
 * 'empty_exist' => true, 
 * </code>
 *
 * pagesize int 每页结果数。定义此参数将调用 page_select() 执行查询并自动分页，此时 limit 参数无效。<br/>
 * <code>
 * 'result' => array(
 *	'pagesize' => 20,
 *	),
 * $result //当前页 20 条结果集
 * $result_pages //分页 html 代码
 * $result_total //结果集总数，即 COUNT(*) 的结果
 * </code>
 *
 * page int 当前页码，调用 page_select() 时使用。不设置时 page_select() 也能自动提取。
 * 
 * cache_count int COUNT(*) 句的缓存时间，调用 page_select() 时使用。
 *
 * sql_count string 统计 COUNT(*) 的 SQL 语句，调用 page_select() 时使用。
 *
 * <b>更多查询参数：</b>
 * @see siud_select_sql
 * @see siud_where_init
 * @see siud_order_init
 * @see siud_link_select
 *
 * @package default
 * @filesource
 */

//先检查及生成所有 SQL 语句
$siud_select_sql = array();
$siud_error = '';
foreach ($siud_select as $siud_k => $siud_v)
	{
	if (!is_array($siud_v))
		{
		$siud_error = "Args Must be Array()";
		return ;
		}
	if ($siud_v['disable'] || (isset($siud_v['enable']) && !$siud_v['enable']))
		{
		//跳过查询
		unset($siud_select[$siud_k]);
		continue;
		}
	if (!$siud_v['table'] && !$siud_v['sql'])
		{
		$siud_error = "Require {$siud_k}[table]";
		return ;
		}
	if ($siud_v['sql'])
		{
		$siud_sql = $siud_v['sql'];
		}
	else
		{
		$siud_sql = siud_select_sql($siud_v);
		//where
		$t_w = siud_where_init($siud_v);
		$siud_error = siud_where_check($t_w);
		if ($siud_error)
			{
			return ;
			}
		$siud_sql .= siud_where_make($t_w);
		unset($t_w);
		//order
		$t_o = siud_order_init($siud_v);
		$siud_error .= siud_order_check($t_o);
		if ($siud_error)
			{
			return ;
			}
		$siud_sql .= siud_order_make($t_o);
		${$siud_k . '_order'} = siud_order_url($t_o);
		unset($t_o);
		}
	//limit
	if ($siud_v['limit'] && 1 !== $siud_v['limit'] && !$siud_v['pagesize'])
		{
		//ggzhu@2012-03-20 增加 offset 参数。
		$siud_sql .= " LIMIT ";
		if ($siud_v['offset'])
			{
			$siud_sql .= "{$siud_v['offset']}, ";
			}
		$siud_sql .= $siud_v['limit'];
		}
	$siud_select_sql[$siud_k] = $siud_sql;
	}

//执行查询
foreach ($siud_select as $siud_k => $siud_v)
	{
	$siud_sql = $siud_select_sql[$siud_k];
	//三种不同类型的查询
	if (is_array($siud_v) && 1 === $siud_v['limit'])
		{
		$$siud_k = $db->get_one($siud_sql);
		}
	else if (is_array($siud_v) && $siud_v['pagesize'])
		{
		list($$siud_k, ${$siud_k . '_pages'}, ${$siud_k . '_total'}) = page_select($siud_sql, $siud_v['pagesize'], $siud_v['page'], array("cache_count" => $siud_v['cache_count'], "sql_count" => $siud_v['sql_count'],));
		}
	else
		{
		$$siud_k = $db->select($siud_sql);
		}
	//检查是否需要退出循环
	if (is_array($siud_v) && $siud_v['empty_exist'] && empty($$siud_k))
		{
		log::add("{$siud_k} 数据为空，退出查询器", log::INFO, __FILE__, __LINE__, 'siud');
		break;
		}
	if ($siud_v['link'])
		{
		siud_link_select($$siud_k, $siud_v['link']);
		}
	}
unset($siud_select, $siud_select_sql, $siud_k, $siud_v, $siud_sql);
?>

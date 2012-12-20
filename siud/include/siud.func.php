<?php 
/**
 * SIUD 自动操作函数库
 *
 * 2011-09-06
 *
 * @package default
 * @filesource
 */

/**
 * 区分 ajax/http 请求进行提示
 */
function _siud_msg($msg, $url = 'goback')
{//{{{
	if (!IS_AJAX)
		{
		if (function_exists('showmessage'))
			{
			showmessage($msg, $url);
			}
		else
			{
			echo $msg;
			if ($url)
				{
			?>
			<br /><a href="<?=$url?>">Go</a>
			<?php
				}
			exit;
			}
		}
	else
		{
		echo $msg;
		exit;
		}
}//}}}

/**
 * 用字符串增减数组的值
 *
 * 可以定义一个默认的选项数组，如 $default = array(1, 2, 3, 4),
 * 可以用字符串进行选项的增删，如 $input = "+5 -1", $default 将变为 array(2, 3, 4, 5),
 * 若 $input = true 则 $default 不变。若 $input 为数组，则用 $input 覆盖 $default
 */
function array_str_change_value($default, $input)
{//{{{
	if (true === $input)
		{
		return $default;
		}
	if (is_array($input))
		{
		return $input;
		}
	if (is_string($input))
		{
		$input = explode(" ", $input);
		foreach ($input as $v)
			{
			$mode = substr($v, 0, 1);
			$value = substr($v, 1);
			if ('+' == $mode)
				{
				$default[] = $value;
				}
			else if ('-' == $mode)
				{
				$k = array_search($value, $default);
				if (false !== $k)
					{
					unset($default[$k]);
					}
				}
			}
		}
	return $default;
}//}}}

/**
 * 过滤 GET 参数，去除指定的键，如 array('where', 'abc')
 *
 * @return mixed 字符串或数组
 */
function filter_get($filter, $return_type = 'string')
{//{{{
	$filter = (array)$filter;
	$get = $_GET;
	foreach ($filter as $k)
		{
		unset($get[$k]);
		}
	$str = http_build_query($get);
	if ('string' == $return_type)
		{
		return $str;
		}
	$arr = array();
	$new_get = explode("&", $str);
	foreach ($new_get as $v)
		{
		list($name, $value) = explode("=", $v);
		$name = urldecode($name);
		$value = urldecode($value);
		$arr[$name] = $value;
		}
	return $arr;
}//}}}

/**
 * 格式化 d w 这类形式的设置，分解出每个键名及把数字索引的属性改为字符索引。
 */
function _siud_set_format($set)
{//{{{
	$_set = array();
	$set = is_array($set) ? $set : array();
	foreach ($set as $k => $v)
		{
		if (is_int($k))
			{
			//转换 array('{field}', ) 为 array('{field}' => array(), )
			$k = $v;
			$v = array();
			}
		//转换所有数字索引类的属性，如 'require' 属性为 'require' => ''
		$new_v = array();
		foreach ($v as $_k => $_v)
			{
			if (is_int($_k))
				{
				$_k = $_v;
				$_v = '';
				}
			$new_v[$_k] = $_v;
			}
		$v = $new_v;
		unset($new_v);
		//分解键名及数据类型
		$ks = explode(",", $k);
		$v['type'] = array_pop($ks);
		//必须要定义数据类型，没定义的设为 str
		if ('int' != $v['type'] && 'str' != $v['type'])
			{
			$ks[] = $v['type'];
			$v['type'] = 'str';
			}
		foreach ($ks as $field)
			{
			$_set[$field] = $v;
			}
		}
	return $_set;
}//}}}

/**
 * 把 siud_select 的定义格式转换为查询 SQL 语句, w,where,o,order 由外部完成
 *
 * <b>查询参数：</b><br/>
 * table string 表名
 *
 * as string AS 子句
 * <code>
 * 'table' => 'content',
 * 'as' => 'c', 
 * //组成的 SQL 语句为：
 * "SELECT ... FROM content AS c ..."
 * </code>
 *
 * field string 返回的字段
 * <code>
 * 'field' => 'userid, username', 
 * //SQL:
 * "SELECT userid, username FROM ..."
 * </code>
 * 
 * join array 联表设置
 * <code>
 * 'join' => array(
 *	'联表类型,表别名' => array(join 参数),
 *	'联表类型,表别名' => array(join 参数),
 *	//可以定义多个 join
 *	),
 * </code>
 * 联表类型包括： join, left_join, right_join
 * <code>
 * 'join' => array(
 *	'join,c' => array(),
 *	'left_join,m' => array(),
 *	'right_join,h' => array(),
 *	),
 * </code>
 *
 * <b>Join 参数：</b><br/>
 * table string 查询的表的完整表名，参数是必须的。
 * <code>
 * 'table' => DB_PRE . 'content',
 * </code>
 *
 * enable bool 若定义了此参数，只有当参数值为 true 时才执行join，否则跳过。
 * <code>
 * 'enable' => $_userid == 1, //$_userid 为 1 时执行join，否则不执行。
 * </code>
 *
 * disable bool 若定义了此参数，当参数值为 true 时不执行join。
 * <code>
 * 'disable' => $_userid == 1, //$_userid 为 1 时不执行join，否则执行。
 * </code>
 *
 * on string ON 子句
 * <code>
 * 'join,c' => array(
 *	'table' => 'content',
 *	'on' => 'c.contentid=a.contentid',
 *	),
 * //SQL 语句为：
 * "... JOIN content AS c ON c.contentid=a.contentid ..."
 * </code>
 *
 * key string 联表的字段，key 与 on 参数只能二选一
 * <code>
 * 'table' => 'content',
 * 'join' => array(
 *	'join,m' => array(
 *		'table' => 'member',
 *		'key' => 'userid',
 *		),
 *	),
 * //SQL:
 * "SELECT ... FROM content JOIN member AS m ON content.userid=member.userid ..."
 * </code>
 *
 * using string 联表的字段，on, key, using 只能选一个。
 * <code>
 * //SQL:
 * "SELECT ... FROM content JOIN member AS m USING(userid) ..."
 * </code>
 *
 * field string 取出的字段
 * <code>
 * 'field' => 'userid',
 * //SQL:
 * "SELECT ..., userid FROM ..."
 * </code>
 *
 * @todo 把 join 的 key 参数改为 using ，这样就与 SQL 语句一致，而不是自己定义一个出来。
 * @param array $set
 * @return string SELECT 语句
 * @see select.inc.php
 */
function siud_select_sql($set)
{//{{{
	$siud_sql_field = $set['field'] ? $set['field'] : '*';
	$siud_sql_table = $set['table'];
	$siud_sql_from = "`{$siud_sql_table}` ";
	if ($set['as'])
		{
		$siud_sql_from .= " AS `{$set['as']}` ";
		$siud_sql_table = $set['as'];
		}
	$siud_sql_field = "`{$siud_sql_table}`.*";
	if ($set['field'])
		{
		$siud_sql_field = $set['field'];
		}
	//join
	if (is_array($set['join']))
		{
		foreach ($set['join'] as $k => $v)
			{
			//可以用 disable, enable 动态开关 join
			if ($v['disable'] || (isset($v['enable']) && !$v['enable']))
				{
				continue;
				}
			list($join_type, $_sql_table_as) = explode(",", $k);
			$join_type = str_replace('_', ' ', strtoupper($join_type));
			$_sql_table_as = $_sql_table_as ? $_sql_table_as : $v['table'];
			$_sql_as = $_sql_table_as ? " AS `{$_sql_table_as}` " : '';
			//ggzhu@2012-01-04 之前试过用 using 在某些情况下会出错的。
			if ($v['on'])
				{
				$_sql_on = "ON {$v['on']}";
				}
			else if ($v['key'])
				{
				$_sql_on = "ON `{$siud_sql_table}`.`{$v['key']}`=`{$_sql_table_as}`.`{$v['key']}`";
				}
			else if ($v['using'])
				{
				$_sql_on = "USING(`{$v['using']}`)";
				}
			else
				{
				//ggzhu@2012-01-04 到这里应报错。
				}
			$siud_sql_from .= "{$join_type} `{$v['table']}`{$_sql_as} {$_sql_on} ";
			if ($v['field'])
				{
				$siud_sql_field .= ", {$v['field']}";
				}
			//todo:ggzhu@2012-01-04 这里的 where 不够用，比如我连表后想加个条件句都没有正常的接口，只能用 w 或 where，如果可以有一个数组式的，直接 $arr[] 就可以的话就方便了。
			}
		unset($_sql_on, $_sql_table_as, $_sql_as);
		}
	$siud_sql = "SELECT {$siud_sql_field} FROM {$siud_sql_from} ";
	return $siud_sql;
}//}}}

/**
 * 执行 SELECT $link 设置参数
 */
function siud_link_select(& $RESULT, $set)
{//{{{
	if (!$set || !is_array($set))
		{
		return ;
		}
	echo 'neededit', __FILE__, __LINE__;
	return ;
	foreach ($set as $link_type => $link_set)
		{
		list($link_type, $link_field) = explode(",", $link_type);
		if (!$link_field)
			{
			$link_field = $link_type;
			}
		if ('many_to_many' == $link_type)
			{
			list($_main_table_field, $_join_table_field) = $link_set['key'];
			if ($RESULT)
				{
				$_id2r = array();
				$ids = array();
				foreach ($RESULT as $k => $r)
					{
					$_id2r[$r[$_main_table_field]] = $k;
					$ids[] = $r[$_main_table_field];
					$RESULT[$k][$link_field] = array();
					}
				$sql = "SELECT `{$link_set['join']}`.`{$_main_table_field}` AS `main_table_field`, `{$link_set['table']}`.* FROM `{$link_set['join']}` INNER JOIN `{$link_set['table']}` ON `{$link_set['table']}`.`{$_join_table_field}` = `{$link_set['join']}`.`{$_join_table_field}` WHERE `{$link_set['join']}`.`{$_main_table_field}` IN ('" . join("','", $ids) . "')";
				$result = $db->select($sql);
				foreach ($result as $k => $r)
					{
					$RESULT[$_id2r[$r['main_table_field']]][$link_field][] = $r;
					}
				unset($result, $_id2r, $ids);
				}
			else if ($DATA)
				{
				$sql = "SELECT {$link_set['table']}.* FROM {$link_set['join']} INNER JOIN {$link_set['table']} ON {$link_set['table']}.{$_join_table_field}={$link_set['join']}.{$_join_table_field} WHERE {$link_set['join']}.{$_main_table_field} = '{$DATA[$_main_table_field]}'";
				$DATA[$link_field] = $db->select($sql);
				unset($sql);
				}
			unset($_main_table_field, $_join_table_field);
			}
		else if ('has_many' == $link_type)
			{
			if ($RESULT)
				{
				echo 'neededit', __FILE__, __LINE__;
				}
			else if ($DATA)
				{
				$sql = "SELECT * FROM {$link_set['table']} WHERE `{$link_set['key']}` = '{$DATA[$link_set['key']]}'";
				$DATA[$link_field] = $db->select($sql);
				unset($sql);
				}
			}
		}
}//}}}

/**
 * w 系列参数，用于 select, delete, update, 共包括 where, w, w_value 三个参数。
 *
 * 初始化 where, w, w_value 参数的设置，返回格式化后的设置数据，此数据作为 siud_where_check,siud_where_make 的参数传入
 * 
 * <br/>
 * <b>查询参数：</b><br/>
 * where string WHERE 子句
 * <code>
 * 'where' => 'userid = 1',
 * //SQL:
 * "... WHERE userid = 1 ...
 * </code>
 *
 * w array 搜索参数，比如需要根据 GET[catid] 生成 WHERE 句这种情况就会用上此参数。
 * <code>
 * 'w' => array(
 *	'字段名,字段类型' => array(w 参数),
 *	'字段名,字段类型' => array(w 参数),
 *	//可定义多个字段
 *	),
 * </code>
 * 字段类型包括 int, str 。当类型为 int 时，将用 intval() 函数对输入的数据进行转换。<br/>
 * 可省略字段类型，可多个字段共用一个设置：
 * <code>
 * 'w' => array(
 *	'userid,catid' => array(),
 *	'username,email' => array(),
 *	),
 * </code>
 *
 * w_value array 给 w 中定义的字段批量赋值，比如从 $_GET 中取数据的话：
 * <code>
 * 'w' => array(),
 * 'w_value' => $_GET,
 * </code>
 *
 * <b>w 参数：</b><br/>
 * table 表名，可选
 * <code>
 * 'w' => array(
 *	'userid' => array('table' => 'member'),
 *	),
 * //SQL:
 * "member.userid = ..."
 * </code>
 *
 * field 真实的字段名，可用于对外隐藏字段名。
 *
 * ao 连接符，可选的值为 and, or
 *
 * compare 比较符
 * 
 * require string 表示此参数是必须的，参数值为错误提示。若用户没输入此字段，则中断查询并返回错误信息：
 * <code>
 * 'w' => array(
 *	'catid,int' => array('require' => '请输入 catid',),
 *	),
 * 'w_value' => $_GET
 * //若用户没输入 catid
 * $siud_error = '请输入 catid';
 * </code>
 *
 * output string 自定义组装格式，用 {var} 表示用户输入值的位置,用于定义复杂的条件句。
 * <code>
 * 'w' => array(
 *	'keyword' => array('output' => "(name LIKE '%{var}%' OR username LIKE '%{var}%')",),
 *	),
 * </code>
 * 
 * func string 自定义函数组装，调用参数为 f($value) ,返回同 output 参数一致。
 *
 * value mixed 设置字段的输入值。实际上， w_value 参数就是批量设置各字段的此参数值的。
 *
 * in array 限制输入数据的范围,用 in_array() 函数做验证<br/>
 * _in string 检查不通过时的提示信息
 * <code>
 * 'w' => array(
 *	'status,int' => array('in' => array(1, 2), '_in' => 'status 非法'),
 *	),
 * //若用户输入 status=3 则
 * $siud_error = 'status 非法';
 * </code>
 *
 * not_in array 跟 in 差不多，不同之外为用 !in_array() 做验证<br/>
 * _not_in string 提示信息
 *
 * map array 值映射，比如 $value =1 时把 $value 变为 2， _else 为特殊键，表示其它值的映射
 *
 * @param array $set SIUD_SELECT 的一个设置数组。
 * @return string
 * @see select.inc.php
 */
function siud_where_init($set)
{//{{{
	$_set = _siud_set_format($set['w']);
	//把 w_value 中不为空字符串的值写入到 value 属性中 {{{
	if (is_array($set['w_value']) && $set['w_value'])
		{
		foreach ($_set as $_f => $_s)
			{
			if (!isset($_s['value']) && isset($set['w_value'][$_f]) && '' !== $set['w_value'][$_f])
				{
				$_set[$_f]['value'] = $set['w_value'][$_f];
				}
			}
		}
	//}}}
	//格式强制转换
	foreach ($_set as $k => $v)
		{
		if (isset($v['value']) && 'int' == $v['type'])
			{
			$_set[$k]['value'] = intval($v['value']);
			}
		}
	//把 table 写入到每个设置中{{{
	if ($set['table'])
		{
		foreach ($_set as $f => $s)
			{
			if (!$s['table'])
				{
				if ($set['as'])
					{
					$_set[$f]['table'] = $set['as'];
					}
				else
					{
					$_set[$f]['table'] = $set['table'];
					}
				}
			}
		}
	//}}}
	//用两个特殊键保存其它设置
	$_set['__where'] = $set['where'];
	$_set['__init'] = true;
	return $_set;
}//}}}

/**
 * 检查 where 输入参数的合法性,$set 为已 init 过的设置数据，下同
 * 
 * @param array $set
 * @return string 空字符串或错误信息
 */
function siud_where_check($set)
{//{{{
	if (!$set['__init'])
		{
		return 'Require Where Init';
		}
	unset($set['__init'], $set['__where']);

	foreach ($set as $f => $s)
		{
		//检查 require 属性
		if (isset($s['require']) && !isset($s['value']))
			{
			return $s['require'] ? $s['require'] : "Require w[{$f}]";
			}
		//in
		if (is_array($s['in']) && !in_array($s['value'], $s['in']))
			{
			return $s['_in'] ? $s['_in'] : "w[{$f}] Cannot is {$s['value']}";
			}
		//not in
		if (is_array($s['not_in']) && in_array($s['value'], $s['not_in']))
			{
			return $s['_not_in'] ? $s['_not_in'] : "w[{$f}] Cannot is {$s['value']}";
			}
		}
	return '';
}//}}}

/**
 * 组装 WHERE 子句
 *
 * @return string where子句，包含 WHERE 关键字
 */
function siud_where_make($set)
{//{{{
	if (!$set['__init'])
		{
		log::add("where set 数据未初始化", log::WARN, __FILE__, __LINE__, __FUNCTION__);
		return '';
		}
	$sql_where = '';
	$is_where = false;
	if ($set['__where'])
		{
		$sql_where = "WHERE {$set['__where']} ";
		$is_where = true;
		}
	unset($set['__init'], $set['__where']);

	//从输入参数中组装 WHERE
	if (!$set)
		{
		return $sql_where;
		}

	//进行数据类型过滤 {{{
	foreach ($set as $k => $v)
		{
		if (isset($v['value']) && 'int' == $v['type'])
			{
			$set[$k]['value'] = intval($v['value']);
			}
		//map
		if (is_array($v['map']))
			{
			if (isset($v['map'][$v['value']]))
				{
				$set[$k]['value'] = $v['map'][$v['value']];
				}
			else if (isset($v['map']['_else']))
				{
				$set[$k]['value'] = $v['map']['_else'];
				}
			}
		}
	//}}}
	$join = '';	//保存连接组装 where 句的连接符
	$where = '';	//组装 where 句
	foreach ($set as $k => $v)
		{
		if (!isset($v['value']))
			{
			continue;
			}
		if (!$join && $v['ao'])
			{
			$join = $v['ao'];
			}
		if ($where)
			{
			$where .= ($v['ao'] ? strtoupper($v['ao']) : 'AND') . ' ';
			}
		$v['table'] = $v['table'] ? "`{$v['table']}`." : '';
		$v['field'] = $v['field'] ? $v['field'] : $k;
		//output
		if ($v['output'])
			{
			$where .= str_replace('{var}', $v['value'], $v['output']);
			}
		//func
		else if ($v['func'] && function_exists($v['func']))
			{
			$where .= $v['func']($v['value']);
			}
		else
			{
			if (is_array($v['value']))
				{
				$_str = "IN ('" . join("','", $v['value']) . "')";
				}
			else
				{
				$_str = "= '{$v['value']}'";
				}
			$where .= "{$v['table']}`{$v['field']}` {$_str} ";
			}
		}
	if (!$join)
		{
		$join = 'AND';
		}
	if ($where)
		{
		if (!$sql_where)
			{
			$sql_where = 'WHERE ';
			}
		if ($is_where)
			{
			$sql_where .= $join . ' ';
			}
		$sql_where .= $where;
		}
	return $sql_where;
}//}}}

/**
 * 分解形如 id,desc,name,addtime,desc 的排序参数为 [{field}] => ''/ASC/DESC 的格式数组
 * @return array
 */
function order_parse($order)
{//{{{
	$order_current = array();
	$order = explode(",", $order);
	$field = '';
	foreach ($order as $v)
		{
		$tmp = strtoupper($v);
		//排序关键字
		if ('ASC' == $tmp || 'DESC' == $tmp)
			{
			if ($field)
				{
				$order_current[$field] = $tmp;
				}
			}
		//字段名
		else
			{
			$field = $v;
			$order_current[$field] = '';
			}
		}
	return $order_current;
}//}}}

/**
 * 初始化 order 设置数据
 * 
 * <b>查询参数：</b><br/>
 * <code>
 * 'order' => '',
 * 'o' => array(o 参数),
 * 'o_value' => $_GET['order'],
 * 'o_before' => '',
 * 'o_after' => '',
 * </code>
 * o_value 用法跟 w_value 相同。
 *
 * order string 固定的排序语句，设置后 o, o_value, o_before, o_after 参数无效。
 *
 * o_before string 放在用户定义排序之前的排序语句
 * <code>
 * 'o_before' => 'contentid DESC',
 * 'o' => array('userid'),
 * //设用户定义 userid DESC
 * "ORDER BY contentid DESC, userid DESC"
 * </code>
 *
 * o_after string 与 o_before 相反，放在之后的排序语句。上面的例子会变成：
 * <code>
 * "ORDER BY userid DESC, contentid DESC"
 * </code>
 *
 * <b>o 参数：</b>
 * <code>
 * 'o' => array('排序字段[,表名]', ...),
 * //如：
 * 'o' => array('project_id,project', 'userid'),
 * </code>
 * 
 * @param array $set
 * @return array 已初始化后的设置
 */
function siud_order_init($set)
{//{{{
	$_set = _siud_set_format($set['o']);
	if ($set['table'])
		{
		foreach ($_set as $k => $v)
			{
			if (!isset($v['table']))
				{
				$_set[$k]['table'] = $set['table'];
				}
			}
		}
	//o_value >> [value]
	if ($set['o_value'])
		{
		$value = order_parse($set['o_value']);
		foreach ($_set as $k => $v)
			{
			if (isset($value[$k]))
				{
				$_set[$k]['value'] = $value[$k];
				}
			}
		}
	//保存特殊参数
	$_set['__order'] = $set['order'];
	$_set['__before'] = $set['o_before'];
	$_set['__after'] = $set['o_after'];
	$_set['__init'] = true;
	return $_set;
}//}}}

/**
 * 检查输入参数合法性，$set 为已初始化的设置，下同
 */
function siud_order_check($set)
{//{{{
	return '';
}//}}}

/**
 * 生成 ORDER 子句
 */
function siud_order_make($set)
{//{{{
	if (!$set['__init'])
		{
		log::add("Other set not init", log::WARN, __FILE__, __LINE__, __FUNCTION__);
		return '';
		}
	if ($set['__order'])
		{
		return 'ORDER BY ' . $set['__order'];
		}
	$before = $set['__before'];
	$after = $set['__after'];
	unset($set['__init'], $set['__before'], $set['__after'], $set['__order']);
	//保存 , 号
	$tmp_middle = '';
	if ($before)
		{
		$sql_order .= $before;
		$tmp_middle = ', ';
		}
	foreach ($set as $k => $v)
		{
		$v['table'] = $v['table'] ? "`{$v['table']}`." : '';
		if (isset($v['value']))
			{
			$sql_order .= "{$tmp_middle}{$v['table']}`{$k}` {$v['value']}";
			$tmp_middle = ', ';
			}
		}
	if ($after)
		{
		$sql_order .= $tmp_middle . $after;
		}
	if ($sql_order)
		{
		$sql_order = 'ORDER BY ' . $sql_order;
		}
	return $sql_order;
}//}}}

/**
 * 生成排序链接
 */
function siud_order_url($set)
{//{{{
	unset($set['__init'], $set['__before'], $set['__after'], $set['__order']);
	$order_url = array();
	//生成排序链接，链接需要根据输入的参数生成相反的排序关键字（asc/desc）
	$tmp_url_begin = preg_replace('/&?o=[^&]*/i', '', RELATE_URL);
	foreach ($set as $k => $v)
		{
		$_type = 'ASC' == $v['value'] ? 'desc' : 'asc';
		$order_url[$k] = "{$tmp_url_begin}&o={$k},{$_type}";
		}
	return $order_url;
}//}}}
?>

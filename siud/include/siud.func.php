<?php 
/**
 * SIUD �Զ�����������
 *
 * 2011-09-06
 *
 * @package default
 * @filesource
 */

/**
 * ���� ajax/http ���������ʾ
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
 * ���ַ������������ֵ
 *
 * ���Զ���һ��Ĭ�ϵ�ѡ�����飬�� $default = array(1, 2, 3, 4),
 * �������ַ�������ѡ�����ɾ���� $input = "+5 -1", $default ����Ϊ array(2, 3, 4, 5),
 * �� $input = true �� $default ���䡣�� $input Ϊ���飬���� $input ���� $default
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
 * ���� GET ������ȥ��ָ���ļ����� array('where', 'abc')
 *
 * @return mixed �ַ���������
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
 * ��ʽ�� d w ������ʽ�����ã��ֽ��ÿ�����������������������Ը�Ϊ�ַ�������
 */
function _siud_set_format($set)
{//{{{
	$_set = array();
	$set = is_array($set) ? $set : array();
	foreach ($set as $k => $v)
		{
		if (is_int($k))
			{
			//ת�� array('{field}', ) Ϊ array('{field}' => array(), )
			$k = $v;
			$v = array();
			}
		//ת��������������������ԣ��� 'require' ����Ϊ 'require' => ''
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
		//�ֽ��������������
		$ks = explode(",", $k);
		$v['type'] = array_pop($ks);
		//����Ҫ�����������ͣ�û�������Ϊ str
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
 * �� siud_select �Ķ����ʽת��Ϊ��ѯ SQL ���, w,where,o,order ���ⲿ���
 *
 * <b>��ѯ������</b><br/>
 * table string ����
 *
 * as string AS �Ӿ�
 * <code>
 * 'table' => 'content',
 * 'as' => 'c', 
 * //��ɵ� SQL ���Ϊ��
 * "SELECT ... FROM content AS c ..."
 * </code>
 *
 * field string ���ص��ֶ�
 * <code>
 * 'field' => 'userid, username', 
 * //SQL:
 * "SELECT userid, username FROM ..."
 * </code>
 * 
 * join array ��������
 * <code>
 * 'join' => array(
 *	'��������,�����' => array(join ����),
 *	'��������,�����' => array(join ����),
 *	//���Զ����� join
 *	),
 * </code>
 * �������Ͱ����� join, left_join, right_join
 * <code>
 * 'join' => array(
 *	'join,c' => array(),
 *	'left_join,m' => array(),
 *	'right_join,h' => array(),
 *	),
 * </code>
 *
 * <b>Join ������</b><br/>
 * table string ��ѯ�ı�����������������Ǳ���ġ�
 * <code>
 * 'table' => DB_PRE . 'content',
 * </code>
 *
 * enable bool �������˴˲�����ֻ�е�����ֵΪ true ʱ��ִ��join������������
 * <code>
 * 'enable' => $_userid == 1, //$_userid Ϊ 1 ʱִ��join������ִ�С�
 * </code>
 *
 * disable bool �������˴˲�����������ֵΪ true ʱ��ִ��join��
 * <code>
 * 'disable' => $_userid == 1, //$_userid Ϊ 1 ʱ��ִ��join������ִ�С�
 * </code>
 *
 * on string ON �Ӿ�
 * <code>
 * 'join,c' => array(
 *	'table' => 'content',
 *	'on' => 'c.contentid=a.contentid',
 *	),
 * //SQL ���Ϊ��
 * "... JOIN content AS c ON c.contentid=a.contentid ..."
 * </code>
 *
 * key string ������ֶΣ�key �� on ����ֻ�ܶ�ѡһ
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
 * using string ������ֶΣ�on, key, using ֻ��ѡһ����
 * <code>
 * //SQL:
 * "SELECT ... FROM content JOIN member AS m USING(userid) ..."
 * </code>
 *
 * field string ȡ�����ֶ�
 * <code>
 * 'field' => 'userid',
 * //SQL:
 * "SELECT ..., userid FROM ..."
 * </code>
 *
 * @todo �� join �� key ������Ϊ using ���������� SQL ���һ�£��������Լ�����һ��������
 * @param array $set
 * @return string SELECT ���
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
			//������ disable, enable ��̬���� join
			if ($v['disable'] || (isset($v['enable']) && !$v['enable']))
				{
				continue;
				}
			list($join_type, $_sql_table_as) = explode(",", $k);
			$join_type = str_replace('_', ' ', strtoupper($join_type));
			$_sql_table_as = $_sql_table_as ? $_sql_table_as : $v['table'];
			$_sql_as = $_sql_table_as ? " AS `{$_sql_table_as}` " : '';
			//ggzhu@2012-01-04 ֮ǰ�Թ��� using ��ĳЩ����»����ġ�
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
				//ggzhu@2012-01-04 ������Ӧ����
				}
			$siud_sql_from .= "{$join_type} `{$v['table']}`{$_sql_as} {$_sql_on} ";
			if ($v['field'])
				{
				$siud_sql_field .= ", {$v['field']}";
				}
			//todo:ggzhu@2012-01-04 ����� where �����ã��������������Ӹ������䶼û�������Ľӿڣ�ֻ���� w �� where�����������һ������ʽ�ģ�ֱ�� $arr[] �Ϳ��ԵĻ��ͷ����ˡ�
			}
		unset($_sql_on, $_sql_table_as, $_sql_as);
		}
	$siud_sql = "SELECT {$siud_sql_field} FROM {$siud_sql_from} ";
	return $siud_sql;
}//}}}

/**
 * ִ�� SELECT $link ���ò���
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
 * w ϵ�в��������� select, delete, update, ������ where, w, w_value ����������
 *
 * ��ʼ�� where, w, w_value ���������ã����ظ�ʽ������������ݣ���������Ϊ siud_where_check,siud_where_make �Ĳ�������
 * 
 * <br/>
 * <b>��ѯ������</b><br/>
 * where string WHERE �Ӿ�
 * <code>
 * 'where' => 'userid = 1',
 * //SQL:
 * "... WHERE userid = 1 ...
 * </code>
 *
 * w array ����������������Ҫ���� GET[catid] ���� WHERE ����������ͻ����ϴ˲�����
 * <code>
 * 'w' => array(
 *	'�ֶ���,�ֶ�����' => array(w ����),
 *	'�ֶ���,�ֶ�����' => array(w ����),
 *	//�ɶ������ֶ�
 *	),
 * </code>
 * �ֶ����Ͱ��� int, str ��������Ϊ int ʱ������ intval() ��������������ݽ���ת����<br/>
 * ��ʡ���ֶ����ͣ��ɶ���ֶι���һ�����ã�
 * <code>
 * 'w' => array(
 *	'userid,catid' => array(),
 *	'username,email' => array(),
 *	),
 * </code>
 *
 * w_value array �� w �ж�����ֶ�������ֵ������� $_GET ��ȡ���ݵĻ���
 * <code>
 * 'w' => array(),
 * 'w_value' => $_GET,
 * </code>
 *
 * <b>w ������</b><br/>
 * table ��������ѡ
 * <code>
 * 'w' => array(
 *	'userid' => array('table' => 'member'),
 *	),
 * //SQL:
 * "member.userid = ..."
 * </code>
 *
 * field ��ʵ���ֶ����������ڶ��������ֶ�����
 *
 * ao ���ӷ�����ѡ��ֵΪ and, or
 *
 * compare �ȽϷ�
 * 
 * require string ��ʾ�˲����Ǳ���ģ�����ֵΪ������ʾ�����û�û������ֶΣ����жϲ�ѯ�����ش�����Ϣ��
 * <code>
 * 'w' => array(
 *	'catid,int' => array('require' => '������ catid',),
 *	),
 * 'w_value' => $_GET
 * //���û�û���� catid
 * $siud_error = '������ catid';
 * </code>
 *
 * output string �Զ�����װ��ʽ���� {var} ��ʾ�û�����ֵ��λ��,���ڶ��帴�ӵ������䡣
 * <code>
 * 'w' => array(
 *	'keyword' => array('output' => "(name LIKE '%{var}%' OR username LIKE '%{var}%')",),
 *	),
 * </code>
 * 
 * func string �Զ��庯����װ�����ò���Ϊ f($value) ,����ͬ output ����һ�¡�
 *
 * value mixed �����ֶε�����ֵ��ʵ���ϣ� w_value ���������������ø��ֶεĴ˲���ֵ�ġ�
 *
 * in array �����������ݵķ�Χ,�� in_array() ��������֤<br/>
 * _in string ��鲻ͨ��ʱ����ʾ��Ϣ
 * <code>
 * 'w' => array(
 *	'status,int' => array('in' => array(1, 2), '_in' => 'status �Ƿ�'),
 *	),
 * //���û����� status=3 ��
 * $siud_error = 'status �Ƿ�';
 * </code>
 *
 * not_in array �� in ��࣬��֮ͬ��Ϊ�� !in_array() ����֤<br/>
 * _not_in string ��ʾ��Ϣ
 *
 * map array ֵӳ�䣬���� $value =1 ʱ�� $value ��Ϊ 2�� _else Ϊ���������ʾ����ֵ��ӳ��
 *
 * @param array $set SIUD_SELECT ��һ���������顣
 * @return string
 * @see select.inc.php
 */
function siud_where_init($set)
{//{{{
	$_set = _siud_set_format($set['w']);
	//�� w_value �в�Ϊ���ַ�����ֵд�뵽 value ������ {{{
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
	//��ʽǿ��ת��
	foreach ($_set as $k => $v)
		{
		if (isset($v['value']) && 'int' == $v['type'])
			{
			$_set[$k]['value'] = intval($v['value']);
			}
		}
	//�� table д�뵽ÿ��������{{{
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
	//�����������������������
	$_set['__where'] = $set['where'];
	$_set['__init'] = true;
	return $_set;
}//}}}

/**
 * ��� where ��������ĺϷ���,$set Ϊ�� init �����������ݣ���ͬ
 * 
 * @param array $set
 * @return string ���ַ����������Ϣ
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
		//��� require ����
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
 * ��װ WHERE �Ӿ�
 *
 * @return string where�Ӿ䣬���� WHERE �ؼ���
 */
function siud_where_make($set)
{//{{{
	if (!$set['__init'])
		{
		log::add("where set ����δ��ʼ��", log::WARN, __FILE__, __LINE__, __FUNCTION__);
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

	//�������������װ WHERE
	if (!$set)
		{
		return $sql_where;
		}

	//�����������͹��� {{{
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
	$join = '';	//����������װ where ������ӷ�
	$where = '';	//��װ where ��
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
 * �ֽ����� id,desc,name,addtime,desc ���������Ϊ [{field}] => ''/ASC/DESC �ĸ�ʽ����
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
		//����ؼ���
		if ('ASC' == $tmp || 'DESC' == $tmp)
			{
			if ($field)
				{
				$order_current[$field] = $tmp;
				}
			}
		//�ֶ���
		else
			{
			$field = $v;
			$order_current[$field] = '';
			}
		}
	return $order_current;
}//}}}

/**
 * ��ʼ�� order ��������
 * 
 * <b>��ѯ������</b><br/>
 * <code>
 * 'order' => '',
 * 'o' => array(o ����),
 * 'o_value' => $_GET['order'],
 * 'o_before' => '',
 * 'o_after' => '',
 * </code>
 * o_value �÷��� w_value ��ͬ��
 *
 * order string �̶���������䣬���ú� o, o_value, o_before, o_after ������Ч��
 *
 * o_before string �����û���������֮ǰ���������
 * <code>
 * 'o_before' => 'contentid DESC',
 * 'o' => array('userid'),
 * //���û����� userid DESC
 * "ORDER BY contentid DESC, userid DESC"
 * </code>
 *
 * o_after string �� o_before �෴������֮���������䡣��������ӻ��ɣ�
 * <code>
 * "ORDER BY userid DESC, contentid DESC"
 * </code>
 *
 * <b>o ������</b>
 * <code>
 * 'o' => array('�����ֶ�[,����]', ...),
 * //�磺
 * 'o' => array('project_id,project', 'userid'),
 * </code>
 * 
 * @param array $set
 * @return array �ѳ�ʼ���������
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
	//�����������
	$_set['__order'] = $set['order'];
	$_set['__before'] = $set['o_before'];
	$_set['__after'] = $set['o_after'];
	$_set['__init'] = true;
	return $_set;
}//}}}

/**
 * �����������Ϸ��ԣ�$set Ϊ�ѳ�ʼ�������ã���ͬ
 */
function siud_order_check($set)
{//{{{
	return '';
}//}}}

/**
 * ���� ORDER �Ӿ�
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
	//���� , ��
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
 * ������������
 */
function siud_order_url($set)
{//{{{
	unset($set['__init'], $set['__before'], $set['__after'], $set['__order']);
	$order_url = array();
	//�����������ӣ�������Ҫ��������Ĳ��������෴������ؼ��֣�asc/desc��
	$tmp_url_begin = preg_replace('/&?o=[^&]*/i', '', RELATE_URL);
	foreach ($set as $k => $v)
		{
		$_type = 'ASC' == $v['value'] ? 'desc' : 'asc';
		$order_url[$k] = "{$tmp_url_begin}&o={$k},{$_type}";
		}
	return $order_url;
}//}}}
?>

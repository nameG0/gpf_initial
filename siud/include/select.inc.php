<?php
/**
 * ��ѯ����SELECT��
 *
 * 2011-09-27
 * <br/>
 * input:
 * - $siud_select ��ѯ����
 *
 * output:
 * - 	��ѯ�����
 * - 	$siud_error	������Ϣ���˱���Ϊ����û������Ϊ������Ϣ��
 * 
 * <b>ʹ�÷�����</b><br/>
 * �� $siud_select ���������ѯ������
 * <code>
 * $siud_select = array(
 *	'���������ı�����' => array(��ѯ����),
 *	'�ڶ�����ѯ' => array(),
 *	'�ɶ�������ѯ' => array(),
 *	'result' => array(),
 * 	);
 * </code>
 *
 * ִ�в�ѯ����
 * <code>
 * include SIUD_SELECT;
 * </code>
 *
 * ������
 * <code>
 * include SIUD_SELECT;
 * if ($siud_error)
 * 	{
 * 	showmessage($siud_error);
 * 	}
 * </code>
 * 
 * ʹ�ò�ѯ��¼��<br/>
 * <code>
 * print_r($result);
 * </code>
 * $result �������涨��ĵ��ĸ���ѯ�Ľ����ֱ��ʹ�ü��ɡ�
 *
 * <b>��ѯ������</b><br/>
 * table string ��ѯ�ı���������������Ƕ��� sql ����������˲����Ǳ���ġ�
 * <code>
 * 'table' => DB_PRE . 'content',
 * </code>
 * 
 * limit int|string LIMIT �Ӿ䣬��Ϊ���� 1 ʱ���� $db->get_one() ��ѯ�������� $db->select() ��ѯ��
 * <code>
 * 'limit' => 1, //LIMIT 1
 * 'limit' => 10, //LIMIT 10
 * 'limit' => '10, 20', //LIMIT 10, 20
 * </code>
 *
 * sql string ֱ�Ӷ��� SQL ��ѯ���
 * <code>
 * 'sql' => 'SELECT * FROM table',
 * 'limit' => 10,
 * </code>
 *
 * enable bool �������˴˲�����ֻ�е�����ֵΪ true ʱ��ִ�в�ѯ������������
 * <code>
 * 'enable' => $_userid == 1, //$_userid Ϊ 1 ʱִ�в�ѯ������ִ�С�
 * </code>
 *
 * disable bool �������˴˲�����������ֵΪ true ʱ��ִ�в�ѯ��
 * <code>
 * 'disable' => $_userid == 1, //$_userid Ϊ 1 ʱ��ִ�в�ѯ������ִ�С�
 * </code>
 *
 * empty_exist boot ���˲�ѯ�Ľ��Ϊ�գ����жϷ��أ���ִ�к���Ĳ�ѯ��
 * <code>
 * 'empty_exist' => true, 
 * </code>
 *
 * pagesize int ÿҳ�����������˲��������� page_select() ִ�в�ѯ���Զ���ҳ����ʱ limit ������Ч��<br/>
 * <code>
 * 'result' => array(
 *	'pagesize' => 20,
 *	),
 * $result //��ǰҳ 20 �������
 * $result_pages //��ҳ html ����
 * $result_total //������������� COUNT(*) �Ľ��
 * </code>
 *
 * page int ��ǰҳ�룬���� page_select() ʱʹ�á�������ʱ page_select() Ҳ���Զ���ȡ��
 * 
 * cache_count int COUNT(*) ��Ļ���ʱ�䣬���� page_select() ʱʹ�á�
 *
 * sql_count string ͳ�� COUNT(*) �� SQL ��䣬���� page_select() ʱʹ�á�
 *
 * <b>�����ѯ������</b>
 * @see siud_select_sql
 * @see siud_where_init
 * @see siud_order_init
 * @see siud_link_select
 *
 * @package default
 * @filesource
 */

//�ȼ�鼰�������� SQL ���
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
		//������ѯ
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
		//ggzhu@2012-03-20 ���� offset ������
		$siud_sql .= " LIMIT ";
		if ($siud_v['offset'])
			{
			$siud_sql .= "{$siud_v['offset']}, ";
			}
		$siud_sql .= $siud_v['limit'];
		}
	$siud_select_sql[$siud_k] = $siud_sql;
	}

//ִ�в�ѯ
foreach ($siud_select as $siud_k => $siud_v)
	{
	$siud_sql = $siud_select_sql[$siud_k];
	//���ֲ�ͬ���͵Ĳ�ѯ
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
	//����Ƿ���Ҫ�˳�ѭ��
	if (is_array($siud_v) && $siud_v['empty_exist'] && empty($$siud_k))
		{
		log::add("{$siud_k} ����Ϊ�գ��˳���ѯ��", log::INFO, __FILE__, __LINE__, 'siud');
		break;
		}
	if ($siud_v['link'])
		{
		siud_link_select($$siud_k, $siud_v['link']);
		}
	}
unset($siud_select, $siud_select_sql, $siud_k, $siud_v, $siud_sql);
?>

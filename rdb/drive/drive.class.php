<?php
/**
 * ���ݿ������Ļ���
 * 20120430
 * 
 * @version 20120430
 * @package default
 * @filesource
 */

class rdb_drive
{
	var $connid;
	var $dbname;
	var $querynum = 0;	//SQL ִ�д���
	public $error_num = 0;	//��ѯ�������
	public $sql_select_time_total = 0;	//���� SELECT �������ʱ
	public $sql_insert_time_total = 0;
	public $sql_update_time_total = 0;

	var $debug = 1;

	/**
	 * ִ�в�ѯ SQL ���ѽ����ת��Ϊ�����ʽ����.
	 * @return array ��ѯ�����¼��.
	 */
	function select($sql, $keyfield = '')
	{//{{{
		$array = array();
		$tmp = get_time();
		$result = $this->query($sql);
		$tmp = run_time($tmp);
		$this->sql_select_time_total += $tmp;
		log::add("{$tmp} {$sql}", log::INFO, '', '', __CLASS__.'->'.__FUNCTION__);
		unset($tmp);
		while($r = $this->fetch_array($result))
			{
			if($keyfield)
				{
				$key = $r[$keyfield];
				$array[$key] = $r;
				}
			else
				{
				$array[] = $r;
				}
			}
		$this->free_result($result);
		return $array;
	}//}}}
	function find($sql, $type = '', $expires = 3600, $dbname = '')
	{//{{{
		//select ǿ�Ƽ��� limit �Ӿ�
		if ('S' == strtoupper($sql[0]) && 'E' == strtoupper($sql[1]) && !stripos($sql, 'limit'))
			{
			$sql .= ' LIMIT 1';
			}
		$tmp = get_time();
		$query = $this->query($sql, $type, $expires, $dbname);
		$tmp = run_time($tmp);
		$this->sql_select_time_total += $tmp;
		log::add("{$tmp} {$sql}", log::INFO, '', '', __CLASS__.'->'.__FUNCTION__);
		unset($tmp);
		$rs = $this->fetch_array($query);
		$this->free_result($query);
		return $rs ;
	}//}}}
	/**
	 * �����¼
	 * <b>hisgory</b>
	 * <pre>
	 * ggzhu@2011-03-17 ���� $other ���������������� IGNORE �����Ĳ�����
	 * ggzhu@2011-03-24 ����Զ�ά�����֧��
	 * </pre>
	 */
	function insert($table, $data, $other = '')
	{//{{{
		$type = is_array(current($data)) ? 2 : 1;	//��������ά��
		//һά����ʱ����ֶκϷ���
		if (1 == $type)
			{
			$fields = $this->get_fields($table);
			foreach($data as $key => $value)
				{
				if(!in_array($key,$fields))unset($data[$key]);
				}
			if (!$data)
				{
				return false;
				}
			}
		$sql_field = join("`,`", array_keys((1 == $type ? $data : current($data))));
		//��if��֧,��ǿ��ת��Ϊ��ά���鼰 switch �ṹ����ôһ���
		if (1 == $type)
			{
			$sql_value = "('" . join("','", $data) . "')";
			}
		else
			{
			$middle = '';
			$sql_value = '';
			foreach ($data as $r)
				{
				$sql_value .= "{$middle}('" . join("','", $r) . "')";
				$middle = ', ';
				}
			}
		$sql = "INSERT {$other} INTO `{$table}` (`{$sql_field}`) VALUES {$sql_value}";
		$tmp = get_time();
		$ret = $this->query($sql);
		$tmp = run_time($tmp);
		$this->sql_insert_time_total += $tmp;
		return $ret;
	}//}}}
	/**
	 * ���¼�¼
	 * <b>hisgory</b>
	 * <pre>
	 * ggzhu@2010-10-22 ��� $is_query ��������Ϊfalse,�򷵻�sql���,��ִ��
	 * </pre>
	 */
	function update($tablename, $array, $where = '', $is_query = true)
	{//{{{
		$fields = $this->get_fields($tablename);
		foreach($array as $key => $value)
			{
			if(!in_array($key,$fields))
				{
				unset($array[$key]);
				}
			}
		//��ֹ�ո���
		if (empty($array))
			{
			return ;
			}
		if($where)
			{
			$sql = '';
			foreach($array as $k=>$v)
				{
				$sql .= ", `$k`='$v'";
				}
			$sql = substr($sql, 1);
			$sql = "UPDATE `$tablename` SET $sql WHERE $where";
			}
		else
			{
			$sql = "REPLACE INTO `$tablename`(`".implode('`,`', array_keys($array))."`) VALUES('".implode("','", $array)."')";
			}
		if (!$is_query)
			{
			return $sql;
			}
		$tmp = get_time();
		$ret = $this->query($sql);
		$tmp = run_time($tmp);
		$this->sql_update_time_total += $tmp;
		return $ret;
	}//}}}
	/**
	 * ɾ����¼
	 */
	function delete($table)
	{//{{{
		
	}//}}}

	/**
	 * �������ݿ�����
	 *
	 */
	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $charset = '')
	{//{{{
		$func = $pconnect == 1 ? 'mysql_pconnect' : 'mysql_connect';
		if(!$this->connid = @$func($dbhost, $dbuser, $dbpw))
			{
			if(DB_NAME == '' && file_exists(PHPCMS_ROOT.'install.php'))
				{
				header('location:./install.php');
				exit;
				}
			$this->halt('Can not connect to MySQL server');
			return false;
			}
		if($this->version() > '4.1')
			{
			$serverset = $charset ? "character_set_connection='$charset',character_set_results='$charset',character_set_client=binary" : '';
			$serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',')." sql_mode='' ") : '';
			$serverset && mysql_query("SET $serverset", $this->connid);
			}
		if($dbname && !@mysql_select_db($dbname , $this->connid))
			{
			$this->halt('Cannot use database '.$dbname);
			return false;
			}
		$this->dbname = $dbname;
		return $this->connid;
	}//}}}

	/**
	 * ѡ�����ݿ�
	 * ����ʵ�ַ���
	 * @param string $dbname ���ݿ���
	 *
	 */
	function select_db($dbname) {}
	/**
	 * ִ�� SQL ,����ִ�н��.
	 * ����ʵ�ַ���
	 * @param string $sql SQL ���.
	 * @param bool $is_buffer �Ƿ񻺴��ѯ���.
	 */
	function query($sql , $is_buffer = true) {}


	/**
	 * ȡ�������ֶ�
	 * ����ʵ�ַ���
	 */
	function get_primary($table) {}

	function get_fields($table)
	{//{{{
		$fields = array();
		$result = $this->query("SHOW COLUMNS FROM $table");
		while($r = $this->fetch_array($result))
			{
			$fields[] = $r['Field'];
			}
		$this->free_result($result);
		return $fields;
	}//}}}


	function fetch_array($query, $result_type = MYSQL_ASSOC)
	{//{{{
		return mysql_fetch_array($query, $result_type);
	}//}}}

	function affected_rows()
	{//{{{
		return mysql_affected_rows($this->connid);
	}//}}}

	function num_rows($query)
	{//{{{
		return mysql_num_rows($query);
	}//}}}

	function num_fields($query)
	{//{{{
		return mysql_num_fields($query);
	}//}}}

	function result($query, $row)
	{//{{{
		return @mysql_result($query, $row);
	}//}}}

	function free_result(&$query)
	{//{{{
		return mysql_free_result($query);
	}//}}}

	function insert_id()
	{//{{{
		return mysql_insert_id($this->connid);
	}//}}}

	function fetch_row($query)
	{//{{{
		return mysql_fetch_row($query);
	}//}}}

	function escape($string)
	{//{{{
		if(!is_array($string)) return str_replace(array('\n', '\r'), array(chr(10), chr(13)), mysql_real_escape_string(preg_replace($this->search, $this->replace, $string), $this->connid));
		foreach($string as $key=>$val) $string[$key] = $this->escape($val);
		return $string;
	}//}}}

	function table_status($table)
	{//{{{
		return $this->get_one("SHOW TABLE STATUS LIKE '$table'");
	}//}}}

	function tables($like = '')
	{//{{{
		if ($like)
			{
			$like = " LIKE '{$like}'";
			}
		$tables = array();
		$sql = "SHOW TABLES {$like}";
		//����� $this->select() �� select() ����һ��ѭ����������Ҫһ��ѭ��
		$result = $this->query($sql);
		while($r = $this->fetch_array($result))
			{
			$tables[] = array_shift($r);
			}
		$this->free_result($result);
		return $tables;
	}//}}}

	function table_exists($table)
	{//{{{
		$tables = $this->tables($table);
		return in_array($table, $tables);
	}//}}}

	function field_exists($table, $field)
	{//{{{
		$fields = $this->get_fields($table);
		return in_array($field, $fields);
	}//}}}

	function version()
	{//{{{
		return mysql_get_server_info($this->connid);
	}//}}}

	function close()
	{//{{{
		return mysql_close($this->connid);
	}//}}}

	function error()
	{//{{{
		return @mysql_error($this->connid);
	}//}}}

	function errno()
	{//{{{
		return intval(@mysql_errno($this->connid)) ;
	}//}}}

	function halt($message = '', $sql = '')
	{//{{{
		global $debug;
		$this->error_num++;
		//�ҵ����ñ����λ��
		$traces = debug_backtrace(0);
		foreach ($traces as $k => $v)
			{
			if (__FILE__ != $v["file"])
				{
				$trace = $v;
				break;
				}
			}
		unset($traces);
		$this->errormsg = "<b>MySQL Query : </b>$sql <br /><b> MySQL Error : </b>".$this->error()." <br /> <b>MySQL Errno : </b>".$this->errno()." <br /><b> Message : </b> $message <br/> <b>FILE :</b> {$trace['file']} : {$trace['line']}";
		log::add($this->errormsg, log::SQL, $trace['file'], $trace['line'], $trace['func']);
		if($this->debug)
			{
			$msg = (defined('IN_ADMIN') || DEBUG) ? $this->errormsg : "Bad Request. {$LANG['illegal_request_return']}";
			echo '<div style="font-size:12px;text-align:left; border:1px solid #9cc9e0; padding:1px 4px;color:#000000;font-family:Arial, Helvetica,sans-serif;"><span>'.$msg.'</span></div>';
			//ggzhu 2011-1-7 ����Ҳ���ж����С�
			//exit;
			}
	}//}}}
}

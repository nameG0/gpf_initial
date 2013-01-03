<?php
/**
 * MySQL 数据库的驱动类
 * 
 * @package default
 * @filesource
 */
class rdb_mysql extends rdb_drive
{
	public $connid = NULL;
	public $search = array('/union(\s*(\/\*.*\*\/)?\s*)+select/i', '/load_file(\s*(\/\*.*\*\/)?\s*)+\(/i', '/into(\s*(\/\*.*\*\/)?\s*)+outfile/i');
	public $replace = array('union &nbsp; select', 'load_file &nbsp; (', 'into &nbsp; outfile');

	function connect($host, $user, $pw)
	{//{{{
		$this->connid = mysql_connect($host, $user, $pw);
		if ($this->connid)
			{
			return true;
			}
		return false;
	}//}}}
	function pconnect($host, $user, $pw)
	{//{{{
		$this->connid = mysql_pconnect($host, $user, $pw);
		if ($this->connid)
			{
			return true;
			}
		return false;
	}//}}}

	function charset($charset)
	{//{{{
		if ($this->version() > '4.1')
			{
			$serverset = '';
			if ($charset)
				{
				$serverset = "character_set_connection='{$charset}',character_set_results='{$charset}',character_set_client=binary";
				}
			$serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',')." sql_mode='' ") : '';
			if ($serverset)
				{
				mysql_query("SET {$serverset}", $this->connid);
				}
			}
		return true;
	}//}}}
	function _connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $charset = '')
	{
		// $func = $pconnect == 1 ? 'mysql_pconnect' : 'mysql_connect';
		// if(!$this->connid = @$func($dbhost, $dbuser, $dbpw))
			// {
			// $this->halt('Can not connect to MySQL server');
			// return false;
			// }
		// if($this->version() > '4.1')
			// {
			// $serverset = $charset ? "character_set_connection='$charset',character_set_results='$charset',character_set_client=binary" : '';
			// $serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',')." sql_mode='' ") : '';
			// $serverset && mysql_query("SET $serverset", $this->connid);
			// }
		// if($dbname && !@mysql_select_db($dbname , $this->connid))
			// {
			// $this->halt('Cannot use database '.$dbname);
			// return false;
			// }
		// $this->dbname = $dbname;
		// return $this->connid;
	}

	function select_db($dbname)
	{
		if (!mysql_select_db($dbname , $this->connid))
			{
			return false;
			}
		$this->dbname = $dbname;
		return true;
	}

	function query($sql , $type = '')
	{
		$func = $type == 'UNBUFFERED' ? 'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = @$func($sql , $this->connid)) && $type != 'SILENT')
			{
			$this->halt('MySQL Query Error', $sql);
			return false;
			}
		$this->querynum++;
		return $query;
	}

	function get_primary($table)
	{
		$result = $this->query("SHOW COLUMNS FROM $table");
		while($r = $this->fetch_array($result))
			{
			if($r['Key'] == 'PRI') break;
			}
		$this->free_result($result);
		return $r['Field'];
	}

	function get_fields($table)
	{
		$fields = array();
		$result = $this->query("SHOW COLUMNS FROM $table");
		while($r = $this->fetch_array($result))
			{
			$fields[] = $r['Field'];
			}
		$this->free_result($result);
		return $fields;
	}

	function get_one($sql, $type = '', $expires = 3600, $dbname = '')
	{
		//select 强制加上 limit 子句
		if ('S' == strtoupper($sql[0]) && 'E' == strtoupper($sql[1]) && !stripos($sql, 'limit'))
			{
			$sql .= ' LIMIT 1';
			}
		$tmp = run_time();
		$query = $this->query($sql, $type, $expires, $dbname);
		$tmp = run_time($tmp);
		$this->sql_select_time_total += $tmp;
		gpf::log("{$tmp} {$sql}", gpf::INFO, '', '', __CLASS__.'->'.__FUNCTION__);
		unset($tmp);
		$rs = $this->fetch_array($query);
		$this->free_result($query);
		return $rs ;
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC)
	{
		return mysql_fetch_array($query, $result_type);
	}

	function affected_rows()
	{
		return mysql_affected_rows($this->connid);
	}

	function num_rows($query)
	{
		return mysql_num_rows($query);
	}

	function num_fields($query)
	{
		return mysql_num_fields($query);
	}

	function result($query, $row)
	{
		return @mysql_result($query, $row);
	}

	function free_result(&$query)
	{
		return mysql_free_result($query);
	}

	function insert_id()
	{
		return mysql_insert_id($this->connid);
	}

	function fetch_row($query)
	{
		return mysql_fetch_row($query);
	}

	function escape($string)
	{
		if(!is_array($string)) return str_replace(array('\n', '\r'), array(chr(10), chr(13)), mysql_real_escape_string(preg_replace($this->search, $this->replace, $string), $this->connid));
		foreach($string as $key=>$val) $string[$key] = $this->escape($val);
		return $string;
	}

	function table_status($table)
	{
		return $this->get_one("SHOW TABLE STATUS LIKE '$table'");
	}

	function tables($like = '')
	{
		if ($like)
			{
			$like = " LIKE '{$like}'";
			}
		$tables = array();
		$sql = "SHOW TABLES {$like}";
		//如果用 $this->select() 在 select() 中有一次循环，这里又要一次循环
		$result = $this->query($sql);
		while($r = $this->fetch_array($result))
			{
			$tables[] = array_shift($r);
			}
		$this->free_result($result);
		return $tables;
	}

	function table_exists($table)
	{
		$tables = $this->tables($table);
		return in_array($table, $tables);
	}

	function field_exists($table, $field)
	{
		$fields = $this->get_fields($table);
		return in_array($field, $fields);
	}

	function version()
	{
		return mysql_get_server_info($this->connid);
	}

	function close()
	{
		return mysql_close($this->connid);
	}

	function error()
	{
		return @mysql_error($this->connid);
	}

	function errno()
	{
		return intval(@mysql_errno($this->connid)) ;
	}

	function halt($message = '', $sql = '')
	{
		global $debug;
		$this->error_num++;
		//找到调用本类的位置
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
		gpf::log($this->errormsg, gpf::SQL, $trace['file'], $trace['line'], $trace['func']);
		if($this->debug)
			{
			$msg = (defined('IN_ADMIN') || DEBUG) ? $this->errormsg : "Bad Request. {$LANG['illegal_request_return']}";
			echo '<div style="font-size:12px;text-align:left; border:1px solid #9cc9e0; padding:1px 4px;color:#000000;font-family:Arial, Helvetica,sans-serif;"><span>'.$msg.'</span></div>';
			//ggzhu 2011-1-7 出错也不中断运行。
			//exit;
			}
	}
}

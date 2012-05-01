<?php
class rdb_mysql extends rdb_drive
{
	var $search = array('/union(\s*(\/\*.*\*\/)?\s*)+select/i', '/load_file(\s*(\/\*.*\*\/)?\s*)+\(/i', '/into(\s*(\/\*.*\*\/)?\s*)+outfile/i');
	var $replace = array('union &nbsp; select', 'load_file &nbsp; (', 'into &nbsp; outfile');

	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $charset = '')
	{
		$func = $pconnect == 1 ? 'mysql_pconnect' : 'mysql_connect';
		if(!$this->connid = @$func($dbhost, $dbuser, $dbpw))
		{
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
	}

	function select_db($dbname)
	{
		if(!@mysql_select_db($dbname , $this->connid)) return false;
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

	function select($sql, $keyfield = '')
	{
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
	}

	//2011-03-17 ggzhu 加入 $other 参数，用于输入如 IGNORE 这样的操作符
	//2011-03-24 ggzhu 加入对二维数组的支持
	function insert($table, $data, $other = '')
	{
		$type = is_array(current($data)) ? 2 : 1;	//标记数组的维数
		//一维数组时检查字段合法性
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
		//用if分支,比强制转换为二维数组及 switch 结构快那么一点点
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
	}

	//2011-03-17 ggzhu 废弃，用 insert 及 $other 参数实现
	/*
	function insert_ignore($tablename, $array)
	{
		$fields = $this->get_fields($tablename);
		foreach($array as $key => $value)
		{
			if(!in_array($key,$fields))unset($array[$key]);
		}
		if (!$array)
			{
			return false;
			}
		return $this->query("INSERT IGNORE INTO `$tablename`(`".implode('`,`', array_keys($array))."`) VALUES('".implode("','", $array)."')");
	}
	*/

	//ggzhu 2010-10-22 添加 $is_query 参数，若为false,则返回sql语句,不执行
	function update($tablename, $array, $where = '', $is_query = true)
	{
		$fields = $this->get_fields($tablename);
		foreach($array as $key => $value)
		{
			if(!in_array($key,$fields))
				{
				unset($array[$key]);
				}
		}
		//防止空更新
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
		$tmp = get_time();
		$query = $this->query($sql, $type, $expires, $dbname);
		$tmp = run_time($tmp);
		$this->sql_select_time_total += $tmp;
		log::add("{$tmp} {$sql}", log::INFO, '', '', __CLASS__.'->'.__FUNCTION__);
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
		log::add($this->errormsg, log::SQL, $trace['file'], $trace['line'], $trace['func']);
		if($this->debug)
		{
			$msg = (defined('IN_ADMIN') || DEBUG) ? $this->errormsg : "Bad Request. {$LANG['illegal_request_return']}";
			echo '<div style="font-size:12px;text-align:left; border:1px solid #9cc9e0; padding:1px 4px;color:#000000;font-family:Arial, Helvetica,sans-serif;"><span>'.$msg.'</span></div>';
			//ggzhu 2011-1-7 出错也不中断运行。
			//exit;
		}
	}
}

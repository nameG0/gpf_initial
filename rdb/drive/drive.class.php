<?php
/**
 * 数据库驱动的基类
 * 20120430
 * 
 * @version 20120430
 * @package default
 * @filesource
 */

class rdb_drive
{
	/**
	 * 保存数据库链接
	 */
	var $connid;
	/**
	 * 保存当前使用中的数据库
	 */
	var $dbname;
	/**
	 * SQL 执行次数
	 */
	var $querynum = 0;
	/**
	 * 查询出错次数
	 */
	public $error_num = 0;
	/**
	 * 使用 select() 方法执行的所有 SELECT 语句总用时
	 */
	public $sql_select_time_total = 0;
	/**
	 * 使用 insert() 方法执行的所有 INSERT 语句总用时
	 */
	public $sql_insert_time_total = 0;
	/**
	 * 使用 update() 方法执行的所有 UPDATE 语句总用时
	 */
	public $sql_update_time_total = 0;
	/**
	 * 是否打开 debug 模式.
	 */
	var $debug = 1;

	/**
	 * 执行查询 SQL 并把结果集转换为数组格式返回.
	 * @return array 查询结果记录集.
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
	/**
	 * 执行只返回一条记录的查询.
	 */
	function find($sql, $type = '', $expires = 3600, $dbname = '')
	{//{{{
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
	}//}}}
	/**
	 * 插入记录
	 * <b>hisgory</b>
	 * <pre>
	 * ggzhu@2011-03-17 加入 $other 参数，用于输入如 IGNORE 这样的操作符
	 * ggzhu@2011-03-24 加入对二维数组的支持
	 * </pre>
	 */
	function insert($table, $data, $other = '')
	{//{{{
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
	}//}}}
	/**
	 * 更新记录
	 * <b>hisgory</b>
	 * <pre>
	 * ggzhu@2010-10-22 添加 $is_query 参数，若为false,则返回sql语句,不执行
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
	}//}}}
	/**
	 * 删除记录
	 */
	function delete($table)
	{//{{{
		
	}//}}}
	/**
	 * 处理查询错误
	 */
	function halt($message = '', $sql = '')
	{//{{{
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
	}//}}}

	/**
	 * 建立数据库链接
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
	 * 选择数据库
	 * 子类实现方法
	 * @param string $dbname 数据库名
	 *
	 */
	function select_db($dbname) {}
	/**
	 * 执行 SQL ,返回执行结果.
	 * 子类实现方法
	 * @param string $sql SQL 语句.
	 * @param bool $is_buffer 是否缓存查询结果.
	 * @return PHP 资源类数据类型
	 */
	function query($sql , $is_buffer = true) {}

	/**
	 * 取表主键字段
	 * 子类实现方法
	 * @param string $table 表名
	 * @return string 主键字段名
	 */
	function get_primary($table) {}
	/**
	 * 取表所有字段名
	 * 子类实现方法
	 * @param string $table 表名
	 * @return array 字段名数组. array[] = 字段名
	 */
	function get_fields($table) {}
	/**
	 * 把查询记录集转为变量数据类型(如数组)
	 * 子类实现方法
	 * @param PHP $query 查询记录集
	 * @return mixed 取决于 $result_type 参数值,默认为数组.
	 */
	function fetch_array($query, $result_type = MYSQL_ASSOC) {}
	/**
	 * 返回最近一条 SQL 语句影响的行数
	 * 子类实现方法
	 * @return int 影响的行数
	 */
	function affected_rows() {}
	/**
	 * 返回查询记录集的记录条数
	 * 子类实现方法
	 * @param PHP $query 查询记录集
	 * @return int 记录条数
	 */
	function num_rows($query) {}
	/**
	 * 子类实现方法
	 */
	function num_fields($query) {}
	/**
	 * 释放查询记录集
	 * 子类实现方法
	 * @param PHP $query 查询记录集
	 * @return bool
	 */
	function free_result(& $query) {}
	/**
	 * 返回新插入记录的 id
	 * 子类实现方法
	 * @return int ID
	 */
	function insert_id() {}
	/**
	 * 子类实现方法
	 */
	function fetch_row($query) {}

	/**
	 * 转义特殊字符
	 * 子类实现方法
	 * @param array|string $string
	 * @return array|string
	 */
	function escape($string) {}
	/**
	 * 子类实现方法
	 */
	function table_status($table) {}
	/**
	 * 子类实现方法
	 */
	function tables($like = '') {}
	/**
	 * 检查指定的表是否存在
	 * 子类实现方法
	 * @param string $table
	 * @return bool
	 */
	function table_exists($table) {}
	/**
	 * 检查指定字段是否在指定的表内存在
	 * 子类实现
	 * @param string $table 表名
	 * @param string $field 字段名
	 * @param bool 
	 */
	function field_exists($table, $field) {}
	/**
	 * 返回数据版本号
	 * 子类实现
	 */
	function version() {}
	/**
	 * 关闭数据库链接
	 * 子类实现
	 */
	function close() {}
	/**
	 * 返回最近一次的错误信息
	 * 子类实现
	 */
	function error() {}
	/**
	 * 返回最后一次的错误代号
	 * 子类实现
	 */
	function errno() {}
}

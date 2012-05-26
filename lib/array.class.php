<?php
/**
 * 数组操作助手
 * <pre>
 * 使用连贯操作增加可用性,调用入口为:
 * a::i($array)->...
 * </pre>
 * <b>数据结构</b>
 * <pre>
 * $Fl(field list) array|string 字段列表,字符串时用,号分隔. eg. id,name
 * $Is int 判断条件,类中定义的 Is 选择.
 * </pre>
 * 
 * @package default
 * @filesource
 */
class a
{
	private $data = NULL;
	private $zh = array(); //字段对应的中文名.
	private $error = '';

	static private $obj = NULL;

	//Is 选项
	const ALL = 1; //无条件
	const SET = 2;	//已设置(isset)
	const NSET = 3; //未设置(!isset)
	const EM = 4; //为空(empty)
	const NEM = 5; //不为空(!empty)
	const T = 6; //为true, 比如非空字符串
	const F = 7; //为false, 比如空字符串
	const STR = 8; //为字符串类型(is_string)
	const INT = 9; //为整形(is_int)

	const ERROR = '不合格'; //默认检查失败提示

	//------ 内部使用方法------
	/**
	 * 格式化 $Fl
	 * @param array|string $Fl
	 */
	private function _Fl($Fl)
	{//{{{
		if (!is_array($Fl))
			{
			$Fl = explode(",", $Fl);
			}
		return $Fl;
	}//}}}
	/**
	 * 判断指定的键 Is 判断是否为真
	 * @param string $f 键名
	 * @param Is $Is
	 */
	private function _Is($f, $Is)
	{//{{{
		if (self::ALL == $Is)
			{
			return true;
			}
		if (self::SET == $Is)
			{
			return isset($this->data[$f]);
			}
		if (self::NSET == $Is)
			{
			return !isset($this->data[$f]);
			}
		if (self::EM == $Is)
			{
			return empty($this->data[$f]);
			}
		if (self::NEM == $Is)
			{
			return !empty($this->data[$f]);
			}
		if (self::T == $Is)
			{
			return $this->data[$f];
			}
		if (self::F == $Is)
			{
			return !$this->data[$f];
			}
		if (self::STR == $Is)
			{
			return is_string($this->data[$f]);
			}
		if (self::INT == $Is)
			{
			return is_int($this->data[$f]);
			}
		return false;
	}//}}}

	//------ 辅助方法 ------
	//包括入口方法
	//------
	/**
	 * 调用入口
	 * @param array & $data 需要处理的数组,会直接修改此参数的值.
	 */
	static function i(& $data)
	{//{{{
		if (is_null(self::$obj))
			{
			self::$obj = new a();
			}
		if (!is_array($data))
			{
			$data = array();
			}

		unset(self::$obj->data);
		self::$obj->error = '';

		self::$obj->data = & $data;
		return self::$obj;
	}//}}}
	/**
	 * 设置字段的中文名.方便生成更友好的提示信息.
	 * @param array $zh [field] => 中文
	 */
	function zh($zh)
	{//{{{
		$this->zh = $zh;
		return $this;
	}//}}}
	/**
	 * 返回错误信息
	 * @param string & $error 会直接修改此参数的值
	 */
	function error(& $error)
	{//{{{
		if ($this->error)
			{
			//第一个字符为连接符,号
			$error = substr($this->error, 1);
			}
		return $this;
	}//}}}
	/**
	 * 提取值
	 * @param string $f 键名
	 * @param string & $value 保存值的变量
	 */
	function get($f, & $value)
	{//{{{
		$value = $this->data[$f];
		return $this;
	}//}}}
	/**
	 * 写入值
	 * <pre>
	 * 可以通过 get, set 组合使用函数设置数组值:
	 * ->get('f', $v)->set('f', explode(',', $v)->...
	 * </pre>
	 */
	function set($f, $value)
	{//{{{
		$this->data[$f] = $value;
		return $this;
	}//}}}

	//------ 数组值检查 ------
	//检查方法只检查数组值,不会改变数组值.检查失败的提示信息会保存在 error 中.
	//------
	/**
	 * 进行字段空值检查
	 * @param Fl $Fl 需检查的字段,多个用,号分隔. eg. id,name
	 * @param Is $Is 检查类型
	 * @param string $error 错误提示.字段名会放于提示信息前方.
	 */
	function requ($Fl, $Is = self::EM, $error = self::ERROR)
	{//{{{
		$Fl = $this->_Fl($Fl);
		foreach ($Fl as $k)
			{
			$name = $this->zh[$k] ? $this->zh[$k] : $k;
			if (!$this->_Is($k, $Is))
				{
				$this->error .= ",{$name} {$error}";
				}
			}
		return $this;
	}//}}}
	/**
	 * 正则验证
	 * @param string $reg 正则表达式,可带"/"可不带.
	 */
	function reg($Fl, $reg, $error = self::ERROR)
	{//{{{
		if ('/' != $reg[0])
			{
			$reg = "/{$reg}/";
			}
		$Fl = $this->_Fl($Fl);
		foreach ($Fl as $k)
			{
			$name = $this->zh[$k] ? $this->zh[$k] : $k;
			if (!preg_match($reg, $this->data[$k]))
				{
				$this->error .= ",{$name} {$error}";
				}
			}
		return $this;
	}//}}}
	/**
	 * 是否在某个数组范围之内
	 * @param array $in 允许的值范围
	 */
	function in($Fl, $in, $error = self::ERROR)
	{//{{{
		$Fl = $this->_Fl($Fl);
		foreach ($Fl as $k)
			{
			$name = $this->zh[$k] ? $this->zh[$k] : $k;
			if (!in_array($this->data[$k], $in))
				{
				$this->error .= ",{$name} {$error}";
				}
			}
		return $this;
	}//}}}
	/**
	 * 不允许在指定的范围内
	 * @param array $nin 不允许的范围
	 */
	function nin($Fl, $nin, $error = self::ERROR)
	{//{{{
		$Fl = $this->_Fl($Fl);
		foreach ($Fl as $k)
			{
			$name = $this->zh[$k] ? $this->zh[$k] : $k;
			if (in_array($this->data[$k], $in))
				{
				$this->error .= ",{$name} {$error}";
				}
			}
		return $this;
	}//}}}

	//------ 数组值填充方法 ------
	//填充方法会改写数组值.
	//------
	/**
	 * 按条件写值
	 */
	function d($Fl, $value, $Is = self::NSET)
	{//{{{
		$Fl = $this->_Fl($Fl);
		foreach ($Fl as $f)
			{
			if ($this->_Is($f, $Is))
				{
				$this->data[$f] = $value;
				}
			}
		return $this;
	}//}}}
	/**
	 * 格式化为数字
	 * @param Fl $Fl
	 */
	function int($Fl, $Is = self::ALL)
	{//{{{
		foreach ($Fl as $f)
			{
			if ($this->_Is($f, $Is))
				{
				$this->data[$f] = intval($this->data[$f]);
				}
			}
		return $this;
	}//}}}
	/**
	 * md5 编码
	 */
	function md5($Fl, $Is = self::SET)
	{//{{{
		$Fl = $this->_Fl($Fl);
		foreach ($Fl as $f)
			{
			if ($this->_Is($f, $Is))
				{
				$this->data[$f] = md5($this->data[$f]);
				}
			}
		return $this;
	}//}}}
	/**
	 * 限制字符串最大长度
	 */
	function mlen($Fl, $len, $Is = self::STR)
	{//{{{
		$Fl = $this->_Fl($Fl);
		foreach ($Fl as $f)
			{
			if ($this->_Is($f, $Is) && isset($this->data[$f][$len]))
				{
				$this->data[$f] = substr($this->data[$f], 0, $len);
				}
			}
		return $this;
	}//}}}

	//------ 数组处理 ------
	//类拟于 PHP 的数组函数,如 sort
	//------
	/**
	 * 取得数组的维数(深度),数组需要是规则的，如矩阵
	 * @param int & $depth 深度结果保存变量
	 */
	function depth(& $depth)
	{//{{{
		$depth = 0;
		//用$tmp记录当前数组
		$tmp = $this->data;
		$i = 0;
		while (true)
			{
			//是数组则维数加1
			if (is_array($tmp))
				{
				$depth++;
				$tmp = current($tmp);
				}
			//非数组则退出循环
			else
				{
				break;
				}
			}
		return $this;
	}//}}}

	/**
	 * 用递归求出数组的最大维数(深度)
	 */
	function depth_max(& $depth, $_is_top = true, $_data = NULL)
	{//{{{
		$func_name = __FUNCTION__;
		//递归调用
		if (!$_is_top)
			{
			//参数非数组则已到数组最未，维数为0
			if (!is_array($_data))
				{
				return 0;
				}
			$ret = 1;
			$max = 0;
			foreach ($_data as $v)
				{
				$d = $this->$func_name($tmp, false, $v);
				if ($max < $d)
					{
					$max = $d;
					}
				}
			return $max + 1;
			}
		foreach ($this->data as $v)
			{
			$max = 0;
			$d = $this->$func_name($tmp, false, $v);
			if ($max < $d)
				{
				$max = $d;
				}
			$depth = $max + 1;
			}
		return $this;
	}//}}}
	/**
	 * 把数组强制转换为一维数组，如果数组元素为数组(多维)，则unset掉
	 */
	function depth_one()
	{//{{{
		foreach ($this->data as $key => $value)
			{
			if (is_array($value))
				{
				unset($this->data[$key]);
				}
			}
		return $this;
	}//}}}
}

/**
 * 值唯一
 * ggzhu@20120526 应支持其它模块扩展操作,扩展的接口就是这个函数的定义.比如这个功能应由 rdb 模块来扩展.
 * unique=table[.field]
 * table表名，[.field]表对应字段，如不设此值，则默认同field,如 user.userid
 */
function __o_unique($data, $field, $arg, &$msg)
{//{{{
	$msg = "{$field}已存在";
	if (!$db = obj('db'))
		{
		i('无法读取db类', ierror);
		return false;
		}
	$tmp = explode('.', $arg['unique']);
	$table = $tmp[0];
	$table_field = $tmp[1] ? $tmp[1] : $field;
	if (!isset($data[$field]))
		{
		return false;
		}
	$sql = "SELECT count(*) AS `sum` FROM `{$table}` WHERE `{$table_field}` = '{$data[$field]}'";
	$r = $db->find($sql);
	return $r['sum'] == 0;
}//}}}
//外键验证
//$arg 同__o_unique()
//note 需数据库支持
function __o_from($data, $field, $arg, &$msg)
{//{{{
	$msg = "{$data[$field]}不存在";
	if (!$db = obj('db'))
		{
		i('无法读取db类', ierror);
		return false;
		}
	$tmp = explode('.', $arg['unique']);
	$table = $tmp[0];
	$table_field = $tmp[1] ? $tmp[1] : $field;
	$args = explode('-', $arg);
	if (!isset($data[$field]))
		{
		return false;
		}
	$sql = "SELECT count(*) AS `sum` FROM `{$table}` WHERE `{$table_field}` = '{$data[$field]}'";
	$r = $db->find($sql);
	return $r['sum'] > 0;
}//}}}

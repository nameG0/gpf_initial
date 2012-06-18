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
	private $is_adds = NULL; //表示数组值是否已转义引号, {NULL:未确认, true:已转义, false:未转义}

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
	 * @param array|NULL $data 检查目标数组,为 NULL 使用 this#data
	 */
	private function _Is($f, $Is, $data = NULL)
	{//{{{
		if (!is_array($data))
			{
			$data = $this->data;
			}
		if (self::ALL == $Is)
			{
			return true;
			}
		if (self::SET == $Is)
			{
			return isset($data[$f]);
			}
		if (self::NSET == $Is)
			{
			return !isset($data[$f]);
			}
		if (self::EM == $Is)
			{
			return empty($data[$f]);
			}
		if (self::NEM == $Is)
			{
			return !empty($data[$f]);
			}
		if (self::T == $Is)
			{
			return $data[$f];
			}
		if (self::F == $Is)
			{
			return !$data[$f];
			}
		if (self::STR == $Is)
			{
			return is_string($data[$f]);
			}
		if (self::INT == $Is)
			{
			return is_int($data[$f]);
			}
		return false;
	}//}}}
	/**
	 * 根据 is_adds 的值返回没引号转义的值.
	 */
	private function _no_adds($f)
	{//{{{
		$value = $this->data[$f];
		if ($this->is_adds && is_string($value))
			{
			$value = gstripslashes($value);
			}
		return $value;
	}//}}}
	/**
	 * 把未转义引号的 $value 转为 is_adds 对应的状态
	 */
	private function _if_adds($value)
	{//{{{
		if ($this->is_adds)
			{
			$value = gaddslashes($value);
			}
		return $value;
	}//}}}
	/**
	 * 返回 POST, GET 中的键
	 * @param string $f 若为空字符串则返回整个 POST 或 GET.
	 * @param string $order 从 POST, GET 中取的顺序,{g:只从GET中取, p:只从POST中取, gp:优先从GET中取, pg:优先从POST中取}
	 */
	private function _get_form($f, $order)
	{//{{{
		if (!$f)
			{
			if ('p' == $order[0])
				{
				return $_POST;
				}
			else
				{
				return $_GET;
				}
			}
		$data = 'p' == $order[0] ? $_POST : $_GET;
		if (isset($data[$f]))
			{
			return $data[$f];
			}
		if (!isset($order[1]))
			{
			return NULL;
			}
		$data = 'p' == $order[1] ? $_POST : $_GET;
		return $data[$f];
	}//}}}
	/**
	 * 从POST或GET表单中取一个数据并自动转换引号
	 * @param string $f 若为空字符串则返回整个 POST 或 GET.
	 * @param string $form {p:POST, g:GET}
	 */
	private function _fform($f, $form)
	{//{{{
		$gpf_is_form_adds = gpf::cfg('gpf_is_form_adds');
		$gpf_need_form_adds = gpf::cfg('gpf_need_form_adds');

		$data = $this->_get_form($f, $form);

		if (is_null($this->is_adds))
			{
			//若引号转义未确定,则根据 gpf_need_form_adds 来决定引号转义状态.
			$this->is_adds = $gpf_need_form_adds;
			}
		if ($this->is_adds && !$gpf_is_form_adds)
			{
			//要转义但表单未转义
			$data = gaddslashes($data);
			}
		else if (!$this->is_adds && $gpf_is_form_adds)
			{
			//不转义但表单已转义
			$data = gstripslashes($data);
			}
		return $data;
	}//}}}
	/**
	 * 从POST或GET添加一个或多个元素入数组
	 * @param string|array $f_or_Fl_or_map 表示一个键的 f 或字段列表 Fl 或跟 this#mv() 一样的 map
	 * @param string $form {g:GET, p:POST}
	 * @param NULL|mixed $default_value 当表单没有此值时的默认值
	 */
	private function _aform($f_or_Fl_or_map, $form, $default_value)
	{//{{{
		$map = array(); //把 f_or_Fl_or_map 格式化为 map
		if (!is_array($f_or_Fl_or_map) || is_int(key($f_or_Fl_or_map)))
			{
			$Fl = $this->_Fl($f_or_Fl_or_map);
			foreach ($Fl as $f)
				{
				$map[$f] = $f;
				}
			}
		else
			{
			$map = $f_or_Fl_or_map;
			}
		foreach ($map as $f => $n)
			{
			$n = (array)$n;
			$value = $this->_fform($f, $form);
			if (is_null($value))
				{
				if (is_null($default_value))
					{
					continue;
					}
				$value = $default_value;
				}
			foreach ($n as $v)
				{
				$this->data[$v] = $value;
				}
			}
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
		self::$obj->is_adds = NULL;

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
		$value = $this->_no_adds($f);
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
		$value = $this->_if_adds($value);
		$this->data[$f] = $value;
		return $this;
	}//}}}
	/**
	 * 标记出数据是否已转义引号
	 */
	function is_adds($tf)
	{//{{{
		$this->is_adds = $tf;
		return $this;
	}//}}}
	/**
	 * 从 POST 中取一个数组作为数组,根据 is_adds 及 gpf_is_form_adds, gpf_need_form_adds 的值自动转义引号
	 * @param string $f 若为空则把整个 POST 作为数组.
	 */
	function fpost($f = '')
	{//{{{
		$data = $this->_fform($f, 'p');
		if (!is_array($data))
			{
			$data = array();
			}
		$this->data = $data;
		return $this;
	}//}}}
	/**
	 * 同fpost, 不同为从GET中取数据.
	 */
	function fget($f = '')
	{//{{{
		$data = $this->_fform($f, 'g');
		if (!is_array($data))
			{
			$data = array();
			}
		$this->data = $data;
		return $this;
	}//}}}
	/**
	 * 从 POST 添加一个或多个元素入数组
	 * @param string|array $f_or_Fl_or_map 表示一个键的 f 或字段列表 Fl 或跟 this#mv() 一样的 map
	 * @param NULL|mixed $default_value 当表单没有此值时的默认值
	 */
	function apost($f_or_Fl_or_map, $default_value = NULL)
	{//{{{
		$this->_aform($f_or_Fl_or_map, 'p', $default_value);
		return $this;
	}//}}}
	/**
	 * 同this#apost(),不过是从GET取数据
	 */
	function aget($f_or_Fl_or_map, $default_value = NULL)
	{//{{{
		$this->_aform($f_or_Fl_or_map, 'g', $default_value);
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
	/**
	 * 只允许出现指定的键名, 禁止指定键名出现可以用 requ() 实现
	 */
	function allow($Fl, $error = self::ERROR)
	{//{{{
		$Fl = $this->_Fl($Fl);
		foreach ($this->data as $k => $v)
			{
			$name = $this->zh[$k] ? $this->zh[$k] : $k;
			if (!in_array($k, $Fl))
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
	 * 按条件写值,可用于设置数据的默认值
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
	 */
	function int($Fl, $Is = self::ALL)
	{//{{{
		$Fl = $this->_Fl($Fl);
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
	/**
	 * 复制数组值
	 * @param array $map [键名] => [新键名], 新键名可以是数组
	 */
	function cp($map, $Is = self::ALL)
	{//{{{
		foreach ($map as $k => $v)
			{
			if ($this->_Is($k, $Is))
				{
				if (is_array($v))
					{
					foreach ($v as $n)
						{
						$this->data[$n] = $this->data[$k];
						}
					}
				else
					{
					$this->data[$v] = $this->data[$k];
					}
				}
			}
		return $this;
	}//}}}
	/**
	 * 重命名键名
	 * @param array $map [键名] => [新键名], 新键名可以是数组
	 */
	function mv($map, $Is = self::ALL)
	{//{{{
		foreach ($map as $k => $v)
			{
			if ($this->_Is($k, $Is))
				{
				if (is_array($v))
					{
					foreach ($v as $n)
						{
						$this->data[$n] = $this->data[$k];
						}
					}
				else
					{
					$this->data[$v] = $this->data[$k];
					}
				unset($this->data[$k]);
				}
			}
		return $this;
	}//}}}
	/**
	 * 使用 var_export 字符串化值
	 */
	function vars($Fl, $Is = self::SET)
	{//{{{
		$Fl = $this->_Fl($Fl);
		foreach ($Fl as $f)
			{
			if ($this->_Is($f, $Is))
				{
				$this->data[$f] = $this->_if_adds(var_export($this->_no_adds($f), 1));
				}
			}
		return $this;
	}//}}}
	/**
	 * 重新生成 var_export 字符串化的数据
	 */
	function unvars($Fl, $Is = self::SET)
	{//{{{
		$Fl = $this->_Fl($Fl);
		foreach ($Fl as $f)
			{
			if ($this->_Is($f, $Is))
				{
				$str = $this->_no_adds($f);
				eval("\$tmp = {$str};");
				$this->data[$f] = $this->_if_adds($tmp);
				}
			}
		return $this;
	}//}}}
	/**
	 * 使用 serialize 字符串化值
	 */
	function sers($Fl, $Is = self::SET)
	{//{{{
		$Fl = $this->_Fl($Fl);
		foreach ($Fl as $f)
			{
			if ($this->_Is($f, $Is))
				{
				$this->data[$f] = $this->_if_adds(serialize($this->_no_adds($f)));
				}
			}
		return $this;
	}//}}}
	/**
	 * 重新生成 serialize 字符串化的数据
	 */
	function unsers($Fl, $Is = self::SET)
	{//{{{
		$Fl = $this->_Fl($Fl);
		foreach ($Fl as $f)
			{
			if ($this->_Is($f, $Is))
				{
				//ggzhu@2012-06-11 为空时默认为空数组。
				if (!$this->data[$f])
					{
					$this->data[$f] = array();
					continue;
					}
				$this->data[$f] = $this->_if_adds(unserialize($this->_no_adds($f)));
				}
			}
		return $this;
	}//}}}
	/**
	 * 对数据进行引号转义
	 * @param NULL|Fl $Fl 可对部份键进行转换,但不推荐这样做,因为会导至引号转义混乱.
	 */
	function adds($Fl = NULL)
	{//{{{
		if (is_null($Fl))
			{
			$this->is_adds = true;
			$this->data = gaddslashes($this->data);
			}
		else
			{
			$Fl = $this->_Fl($Fl);
			foreach ($Fl as $f)
				{
				$this->data[$f] = gaddslashes($this->data[$f]);
				}
			}
		return $this;
	}//}}}
	/**
	 * 去除引号转义
	 * @param NULL|Fl $Fl 可对部份键进行转换,但不推荐这样做,因为会导至引号转义混乱.
	 */
	function unadds($Fl = NULL)
	{//{{{
		if (is_null($Fl))
			{
			$this->is_adds = false;
			$this->data = gstripslashes($this->data);
			}
		else
			{
			$Fl = $this->_Fl($Fl);
			foreach ($Fl as $f)
				{
				$this->data[$f] = gstripslashes($this->data[$f]);
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

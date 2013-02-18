<?php
/**
 * 保存(添加或修改)一行数据（整条记录）
 *
 * <pre>
 * save 与 update 的不同之处就在于，save 最小操作单位是一整条记录， update 最小操作单位是一个字段。
 * 在添加及修改整条记录的情况下， save 明显比 insert + update 来得方便。
 * </pre>
 */
class siud_save
{
	private $db = NULL;
	private $obj_table = NULL;

	private $pk = '';
	private $data = array();
	private $error_var = NULL; //保存错误信息的变量的引用
	private $id_var = NULL; //保存所操作记录ID的变量的引用

	public $siud_save = array();
	public $siud_error = NULL;

	/**
	 * 加入错误信息
	 */
	private function _error($error_str)
	{//{{{
		$m = isset($this->error_var[0]) ? ',' : '';
		$this->error_var .= "{$m}{$error_str}";
		log::add($error_str, log::ERROR, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
	}//}}}

	function __construct()
	{//{{{
		$this->db = rdb::obj();
		$this->obj_table = new siud_table();
	}//}}}
	function init($table = NULL)
	{//{{{
		$this->siud_save = array();
		$this->siud_error = NULL;
		$this->table = '';
		$this->pk = '';
		$this->data = array();
		$this->is_bat = false;
		unset($this->error_var, $this->id_var);
		$this->error_var = $this->id_var = NULL;

		$this->obj_table->init();
		if (!is_null($table))
			{
			$this->obj_table->t($table);
			}
	}//}}}
	function pk($field)
	{//{{{
		$this->pk = $field;
		return $this;
	}//}}}
	function data($data)
	{//{{{
		$this->data = $data;
		return $this;
	}//}}}
	/**
	 * 设置保存错误信息的变量
	 */
	function error(& $error)
	{//{{{
		$this->error_var = & $error;
		return $this;
	}//}}}
	/**
	 * 设置保存主键ID值的变量
	 */
	function id(& $id)
	{//{{{
		$this->id_var = & $id;
		return $this;
	}//}}}

	/**
	 * 执行
	 * @param int $id 可以传入一个变量取得操作的 ID 值。
	 */
	function ing()
	{//{{{
		$table = $this->obj_table->get_table();
		if (!$table)
			{
			$this->_error('Require [table]');
			return false;
			}
		if (!$this->pk)
			{
			$this->_error('Require [pk]');
			return false;
			}
		if (!$this->data || !is_array($this->data))
			{
			$this->_error('Require [data]');
			return false;
			}
		//插入数据库
		if (!$this->data[$this->pk])
			{
			//插入数据
			$is_ok = $this->db->insert($table, $this->data);
			$insert_id = $this->db->insert_id();
			}
		else
			{
			//更新数据
			$where = "`{$this->pk}` = '{$this->data[$this->pk]}'";
			log::add($where, log::INFO, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			$is_ok = $this->db->update($table, $this->data, $where);
			$insert_id = $this->data[$this->pk];
			// $func_name = '_after_update_';
			// if (function_exists($func_name))
				// {
				// $func_name(array($siud_save['pk'] => $v[$siud_save['pk']]), $v, $db->affected_rows());
				// }
			}
		$this->id_var = $insert_id;
		return $is_ok;
	}//}}}
	function __call($name, $arg)
	{//{{{
		if ('t' == $name[0])
			{
			call_user_func_array(array($this->obj_table, $name), $arg);
			}
		return $this;
	}//}}}
}

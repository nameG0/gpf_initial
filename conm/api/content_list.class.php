<?php 
/**
 * 前台内容列表查询助手
 * 
 * @package default
 * @filesource
 */

class content_list
{
	public $catid = 0;
	public $offset = 0;
	public $limit = 0;
	public $field = 'contentid, title, url';
	public $is_thumb = false;
	public $thumb_default = '';
	public $order = '';	//排序语句

	function init()
	{//{{{
		$this->catid = 0;
		$this->offset = 0;
		$this->limit = 0;
		$this->field = 'contentid, title, url';
		$this->is_thumb = false;
		$this->thumb_default = '';
		$this->order = '';
	}//}}}
	
	function thumb_default($thumb)
	{//{{{
		$this->is_thumb = true;
		$this->field .= ', thumb';
		$this->thumb_default = $thumb;
		return $this;
	}//}}}

	function order($str)
	{//{{{
		$this->order = "ORDER BY {$str}";
		return $this;
	}//}}}

	function catid($catid)
	{//{{{
		$this->catid = $catid;
		return $this;
	}//}}}

	function field($field)
	{//{{{
		$this->field = $field;
		return $this;
	}//}}}

	function select()
	{//{{{
		global $db;

		$field = $this->field;

		$where = '';
		if ($this->catid)
			{
			$where .= "catid = '{$this->catid}' ";
			}
		if ($where)
			{
			$where = "WHERE {$where}";
			}

		$limit = '';
		if ($this->offset)
			{
			$limit .= $this->offset . ', ';
			}
		if ($this->limit)
			{
			$limit .= $this->limit;
			}
		if ($limit)
			{
			$limit = "LIMIT {$limit}";
			}
		$is_use_get = 1 == $this->limit;
		$is_thumb = $this->is_thumb;

		$sql = "SELECT {$field} FROM " . DB_PRE . "content {$where} {$this->order} {$limit}";
		$result = $db->select($sql);
		if (is_thumb)
			{
			foreach ($result as $k => $r)
				{
				if (!$r['thumb'])
					{
					$result[$k]['thumb'] = $this->thumb_default;
					}
				}
			}
		//在返回结果前重新初始化一次参数，为下一次调用做准备。
		$this->init();

		if ($is_use_get)
			{
			return $result[0];
			}
		return $result;
	}//}}}

	function limit($limit, $offset = 0)
	{//{{{
		$this->limit = $limit;
		$this->offset = $offset;
		return $this;
	}//}}}
}
?>

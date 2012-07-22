<?php 
/**
 * 前台内容列表查询助手
 * 
 * @package callback
 * @filesource
 */

class siudFind_cms__list
{
	private $catid = 0;
	private $offset = 0;
	private $limit = 0;
	private $field = 'contentid, title';
	private $is_thumb = false;
	private $thumb_default = '';
	private $order = '';	//排序语句

	private $format = array();

	function init()
	{//{{{
		$this->catid = 0;
		$this->offset = 0;
		$this->limit = 0;
		$this->field = 'contentid, title';
		$this->is_thumb = false;
		$this->thumb_default = '';
		$this->order = '';
		$this->format = array();
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
		$this->order = $str;
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

	function ing()
	{//{{{
		// global $db;
		$is_use_get = 1 == $this->limit;
		$obj_siud = siud::select();
		$obj_siud->t('c_cms_content');
		$obj_siud->tfield($this->field);

		// $field = $this->field;

		// $where = '';
		if ($this->catid)
			{
			$obj_siud->wis('catid', $this->catid);
			// $where .= "catid = '{$this->catid}' ";
			}
		// if ($where)
			// {
			// $where = "WHERE {$where}";
			// }

		// $limit = '';
		if ($this->offset)
			{
			$obj_siud->limit("{$this->offset}, {$this->limit}");
			// $limit .= $this->offset . ', ';
			}
		else
			{
			$obj_siud->limit($this->limit);
			}
		// if ($this->limit)
			// {
			// $limit .= $this->limit;
			// }
		// if ($limit)
			// {
			// $limit = "LIMIT {$limit}";
			// }
		// $is_use_get = 1 == $this->limit;
		$is_thumb = $this->is_thumb;

		if ($this->order)
			{
			$obj_siud->order($this->order);
			}
		// $sql = "SELECT {$field} FROM " . DB_PRE . "cms_content {$where} {$this->order} {$limit}";
		// $result = $db->select($sql);
		$result = $obj_siud->ing();
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
		if ($this->format['title'])
			{
			$this->_f_title($result);
			}

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

	/**
	 * 方便地在列表页输出文章 title 字段，带 a 标签，自动截取标题长度
	 *
	 * 输出的 html 代码看上去像：
	 * <code>
	 * <a href="content/show.php?contentid=1" title="full title">short title ...<a/>
	 * </code>
	 * 保存在 _title 键中。
	 */
	function f_title($length = 18, $target = '', $title_before = '')
	{//{{{
		$this->format['title'] = array(
			"length" => $length,
			"target" => $target,
			"title_before" => $title_before,
			);
		return $this;
	}//}}}
	function _f_title(& $result)
	{//{{{
		foreach ($result as $k => $r)
			{
			$url = $r['url'] ? $r['url'] : "?a=cms,content,show&contentid={$r['contentid']}";
			// $title = $this->format['title']['length'] ? str_cut($r['title'], $this->format['title']['length']) : $r['title'];
			$title = $r['title'];
			$target = $target ? "target=\"{$target}\"" : '';
			$result[$k]['_title'] = "<a href=\"{$url}\" title=\"{$r['title']}\" {$target} >{$title_before}{$title}</a>";
			}
	}//}}}
}

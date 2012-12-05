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
		$obj_siud->t('content');
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

	/**
	 * 查找上一篇文章
	 * @param int $catid 如果提供文章栏目参数，可减少一次查询。
	 * @return array {int contentid:文章ID, string title:文章标题, string url:文章链接}
	 */
	function prev_content($contentid, $catid = 0)
	{//{{{
		$data = siud::find('content')->tfield("contentid, title")->where("catid={$catid} AND contentid < {$contentid}")->order('contentid DESC')->ing();
		if (!empty($data))
			{
			$data['url'] = gpf::url("cms.content.show.&contentid={$data['contentid']}");
			}
		return $data;
	}//}}}
	/**
	 * 查找下一篇文章
	 * @param int $catid 如果提供文章栏目参数，可减少一次查询。
	 * @return array {int contentid:文章ID, string title:文章标题, string url:文章链接}
	 */
	function next_content($contentid, $catid = 0)
	{//{{{
		$data = siud::find('content')->tfield("contentid, title")->where("catid={$catid} AND contentid > {$contentid}")->order('contentid ASC')->ing();
		if (!empty($data))
			{
			$data['url'] = gpf::url("cms.content.show.&contentid={$data['contentid']}");
			}
		return $data;
	}//}}}
	/**
	 * 相关文章：同栏目其它文章
	 * @param int $contentid 用于排除当前文章
	 * @param int $catid 如果提供文章栏目参数，可减少一次查询。
	 * @return array {int contentid:文章ID, string title:文章标题, string url:文章链接, int inputtime:发布时间}
	 */
	function xiangguan($contentid, $catid = 0, $limit = 6)
	{//{{{
		//显示数为 $limit 条，取出 $limit + 1 条，若发现与当前文章重复则直接 unset 掉
		$result = siud::select('content')->tfield("contentid, title, inputtime")->wis('catid', $catid)->limit($limit + 1)->order("contentid DESC")->ing();
		foreach ($result as $k => $r)
			{
			if ($r['contentid'] == $contentid)
				{
				unset($result[$k]);
				continue;
				}
			$result[$k]['url'] = gpf::url("cms.content.show.&contentid={$r['contentid']}");
			}
		if (count($result) > $limit)
			{
			array_pop($result);
			}
		return $result;
	}//}}}
}

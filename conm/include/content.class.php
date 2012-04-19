<?php
class content
{
	var $db;
	var $table;
	var $table_log;
	var $model_table;
	var $table_category;
	var $ishtml = 0;
	var $userid = 0;
	var $userid_sql = '';
	var $pages;
	var $number;
	var $url;
	var $html;
	var $s;
	var $is_update_related = 1;

	function content()
	{//{{{
		global $db, $log, $MODULE;
		$this->db = &$db;
		$this->table = DB_PRE.'content';
		$this->table_count = DB_PRE.'content_count';
		$this->table_position = DB_PRE.'content_position';
		$this->table_tag = DB_PRE.'content_tag';
		$this->table_category = DB_PRE.'category';
		//$this->log = is_object($log) ? $log : load('log.class.php');
		//$this->url = load('url.class.php');
	}//}}}

	/**
	 * 从栏目中取出对应的文章模型信息
	 */
	function set_catid($catid)
	{//{{{
		global $CATEGORY, $content_ishtml, $MODEL;
		$modelid = $CATEGORY[$catid]['modelid'];
		if(!isset($MODEL[$modelid]))
			{
			log::add("没有此 modelid[{$modelid}]", log::NOTEXI, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			return false;
			}
		$this->modelid = $modelid;
		$this->ishtml = isset($content_ishtml) ? $content_ishtml : $MODEL[$modelid]['ishtml'];
		$this->model_table = DB_PRE.'c_'.$MODEL[$modelid]['tablename'];
		return true;
	}//}}}

	/**
	 * 设置发表文章的 userid
	 */
	function set_userid($userid)
	{//{{{
		$this->userid = intval($userid);
		$this->userid_sql = " AND $this->table.`userid`=$this->userid ";
	}//}}}

	/**
	 * 取出指定的文章数据
	 * @param int $tablecount 需要取的数据，1：只取主表数据；2：为取扩展表数据；3：把统计数据也一并取出。
	 */
	function get($contentid, $tablecount = 2)
	{//{{{
		$contentid = intval($contentid);
		$data = $this->db->get_one("SELECT * FROM `$this->table` WHERE `contentid`=$contentid $this->userid_sql");
		if($data)
		{
			if($tablecount >= 2)
			{
				$this->set_catid($data['catid']);
				if(!$this->model_table) return false;
				$data2 = $this->db->get_one("SELECT * FROM `$this->model_table` WHERE `contentid`=$contentid");
				if($tablecount == 2 && is_array($data) && is_array($data2)) $data = array_merge($data, $data2);
			}
			if($tablecount == 3)
			{
				$data3 = $this->db->get_one("SELECT * FROM `$this->table_count` WHERE `contentid`=$contentid");
				if(is_array($data) && is_array($data2) && is_array($data3)) $data = array_merge($data, $data2, $data3);
			}
		}
		return $data;
	}//}}}

	/**
	 * 添加文章
	 * @todo 需要把一些操作交到字段类型中去。
	 * @param array|int $cat_selected 同步发布到的栏目，0为不同步发布
	 */
	function add($data, $cat_selected = 0, $isimport = 0)
	{//{{{
		global $_userid, $_username,$CATEGORY, $MODEL;
		if(!$this->set_catid($data['catid']))
			{
			log::add("无法设置 catid[{$data['catid']}]", log::INFO, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			return false;
			}

		require_once CONTENT_ROOT . 'include/content_input.class.php';
		require_once CONTENT_ROOT . 'include/content_update.class.php';
		//input 里进行了数据合法化的验证，并用 showmessage() 进行了提示。
		$content_input = new content_input($this->modelid);
		$inputinfo = $content_input->get($data, $isimport);
		//在 input 中对字段进行了分组，分别保存到主表与扩展表。
		//todo:应可以驱动多张主表，主表用配置文件定义，然后主表字段在配置文件中设置。
		$systeminfo = $inputinfo['system'];
		$modelinfo = $inputinfo['model'];
		//todo:username 与 userid 也是字段类型，应是由 member 模块提供的。因为单纯的文章发布，可以不用这两个字段。
		if(!$systeminfo['username']) $systeminfo['username'] = $_username;
		if(!$systeminfo['userid']) $systeminfo['userid'] = $_userid;

		//todo:inputtime 跟 updatetime 一样。
		if($data['inputtime'] && !is_numeric($data['inputtime']))
		{
			$systeminfo['inputtime'] = strtotime($data['inputtime']);
		}
		elseif(!$data['inputtime'])
		{
			$systeminfo['inputtime'] = TIME;
		}
		else
		{
			$systeminfo['inputtime'] = $data['inputtime'];
		}

		//todo:updatetime 也是一个字段类型，这个部份应交给字段类型来完面
		if($data['updatetime'] && !is_numeric($data['updatetime']))
		{
			$systeminfo['updatetime'] = strtotime($data['updatetime']);
		}
		elseif(!$data['updatetime'])
		{
			$systeminfo['updatetime'] = TIME;
		}
		else
		{
			$systeminfo['updatetime'] = $data['updatetime'];
		}

		if(isset($data['paginationtype']))
		{
			$modelinfo['paginationtype'] = $data['paginationtype'];
			$modelinfo['maxcharperpage'] = $data['maxcharperpage'];
		}
		//插入主表
		$this->db->insert($this->table, $systeminfo);
		$contentid = $this->db->insert_id();
		$modelinfo['contentid'] = $contentid;
		//插入扩展表
		$this->db->insert($this->model_table, $modelinfo);
		//计算文章url
		//todo:url 字段，即文章的url，也应该由 urlrule 模块提供字段类型，否则只能用 show.php 显示。
		if($data['islink']==1)
		{
			$url = $data['linkurl'];
		}
		else
		{
			//$info = $this->url->show($contentid, 0, $systeminfo['catid'], $systeminfo['inputtime'], $data['prefix']);
			$info[1] = "content/show.php?contentid={$contentid}";
			$url = $info[1];
			unset($info);
		}
		//更新 url
		$this->db->query("UPDATE `$this->table` SET `url`='$url' WHERE `contentid`=$contentid");
		$this->db->query("UPDATE `$this->table_category` SET `items`=`items`+1 WHERE `catid`='".$data['catid']."'");
		//插入文章统计表
		$this->db->query("INSERT INTO `$this->table_count`(`contentid`) VALUES('$contentid')");

		//同步发布到其它栏目下。
		if(is_array($cat_selected) && !empty($cat_selected))
		{
			$relink_array['title'] = $data['title'];
			$relink_array['url'] = $url;
			$relink_array['islink'] = 1;
			$relink_array['username'] = $_username;
			$relink_array['userid'] = $_userid;
			$relink_array['inputtime'] = $systeminfo['inputtime'];
			$relink_array['updatetime'] = $systeminfo['updatetime'];
			$relink_array['status'] = $data['status'];
			foreach($cat_selected AS $cid)
			{
				$relink_array['catid'] = $cid;
				$relink_modelid = $CATEGORY[$cid]['modelid'];
				$relink_model_table = DB_PRE.'c_'.$MODEL[$relink_modelid]['tablename'];
				$this->db->insert($this->table, $relink_array);
				$relink_contentid = $this->db->insert_id();
				$relinkinfo['contentid'] = $relink_contentid;
				$this->db->insert($relink_model_table, $relinkinfo);
			}
		}
		//进行需要得到 contentid 才能完成的操作。
		$content_update = new content_update($this->modelid, $contentid);
		$content_update->update($data);
		//实际上是发布文章的 hook
		if(!$isimport) $this->log_write($contentid, '', '', $data['islink']);
		return $contentid;
	}//}}}

	/**
	 * 修改文章
	 */
	function edit($contentid, $data)
	{//{{{
		global $MODEL,$old_catid,$PHPCMS;
		if(!$this->set_catid($data['catid']))
			{
			log::add("无法设置 catid[{$data['catid']}]", log::INFO, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			return false;
			}
		//修改了栏目则需要删除静态文件
		if($old_catid && $old_catid!=$data['catid'])
		{
			//$html = load('html.class.php');
			//$html->delete($contentid, $this->model_table);
		}
		$data['contentid'] = $contentid;
		//require_once CONTENT_ROOT . 'include/content_input.class.php';
		require_once CONTENT_ROOT . 'include/content_update.class.php';
		$content_update = new content_update($this->modelid, $contentid);
		list($systeminfo, $modelinfo) = $content_update->get($data);

		if(!$MODEL[$this->modelid]['isrelated']) $this->is_update_related = 0;

		//$content_input = new content_input($this->modelid);
		//$inputinfo = $content_input->get($data);
		// $systeminfo = $inputinfo['system'];
		// $modelinfo = $inputinfo['model'];
		if(isset($data['paginationtype']))
		{
			$modelinfo['paginationtype'] = $data['paginationtype'];
			$modelinfo['maxcharperpage'] = $data['maxcharperpage'];
		}
		if($data['inputtime'])
		{
			$systeminfo['inputtime'] = strtotime($data['inputtime']);
		}
		else
		{
			$systeminfo['inputtime'] = TIME;
		}
		$systeminfo['updatetime'] = TIME;

		if($data['islink']==1)
		{
			$systeminfo['url'] = $data['linkurl'];
		}
		else
		{
			$prefix = $data['prefix'] ? $data['prefix'] : ($PHPCMS['enable_urlencode'] ? hash_string($contentid) : '');
			//$info = $this->url->show($contentid, 0, $data['catid'], $systeminfo['inputtime'], $prefix);
			$info[1] = "content/show.php?contentid={$contentid}";
			$systeminfo['url'] = $info[1];
			unset($info);
		}
		$this->db->update($this->table, $systeminfo, "`contentid`=$contentid $this->userid_sql");
		unset($systeminfo['status']);

		if($modelinfo) $this->db->update($this->model_table, $modelinfo, "`contentid`=$contentid");

		$this->log_write($contentid, '', '', $data['islink']);
		return true;
	}//}}}

	function listinfo($where = '', $order = '`listorder` DESC,`contentid` DESC', $page = 1, $pagesize = 50)
	{//{{{
		if($where) $where = " WHERE $where $this->userid_sql";
		if($order) $order = " ORDER BY $order";
		$page = max(intval($page), 1);
		$offset = $pagesize*($page-1);
		$limit = " LIMIT $offset, $pagesize";
		$number = cache_count("SELECT count(*) AS `count` FROM `$this->table` $where");
		$this->pages = pages($number, $page, $pagesize);
		$array = array();
		$result = $this->db->query("SELECT * FROM `$this->table` $where $order $limit");
		while($r = $this->db->fetch_array($result))
		{
			$r['inputtime'] = $r['inputtime'];
			$r['updatetime'] = $r['updatetime'];
			$array[] = $r;
		}
		$this->number = $this->db->num_rows($result);
		$this->db->free_result($result);
		return $array;
	}//}}}

	function listorder($info)
	{//{{{
		if(!is_array($info)) return false;
		foreach($info as $id=>$listorder)
		{
			$id = intval($id);
			$listorder = intval($listorder);
			$this->db->query("UPDATE `$this->table` SET `listorder`=$listorder WHERE `contentid`=$id $this->userid_sql");
			$this->log_write($id, 'listorder');
		}
		return true;
	}//}}}

	function search($sql, $page = 1, $pagesize = 20, $setting = array())
	{//{{{
		$page = max(intval($page), 1);
		$offset = $number*($page-1);
		$sql_count = preg_replace("/^SELECT([^(]+)FROM(.+)(ORDER BY.+)$/i", "SELECT COUNT(*) AS `count` FROM\\2", $sql);
		$count = cache_count($sql_count);
		$this->pages = pages($count, $page, $number);
		$data = array();
		$result = $db->query("$sql LIMIT $offset, $number");
		while($r = $db->fetch_array($result))
		{
			$data[] = $r;
		}
		$db->free_result($result);
		return $data;
	}//}}}

	/**
	 * 实际上调用 content_output->get()
	 */
	function output($data)
	{//{{{
		require_once CACHE_MODEL_PATH.'content_output.class.php';
		$output = new content_output();
		return $output->get($data);
	}//}}}

	/**
	 * 文章添加/修改/删除的 hook
	 * @param string $handle 操作类型：添加，修改，删除
	 */
	function log_write($contentid, $handle = '', $is_admin = 0, $islink = 99)
	{//{{{
		if($is_admin) $this->ishtml = 1;
		if(!isset($islink)) $islink = 99;

		if($this->ishtml && ($handle == '' || $handle == 99))
		{
			//生成文章静态文件
			if(!is_object($this->html)) $this->html = load('html.class.php');
			//if($islink==99) $this->html->show($contentid, $this->is_update_related);
		}
		//全文搜索
		$this->search_api($contentid);
		//记录日志
		//$this->log->set('contentid', $contentid);
		//return $this->log->add();
		return true;
	}//}}}

	/**
	 * 把文章内容加入到全文搜索模块
	 */
	function search_api($contentid)
	{//{{{
		global $MODULE,$MODEL,$CATEGORY;
		//if(!isset($MODULE['search'])) return false;
		if (!module_init('search'))
			{
			return false;
			}
		if(!is_object($this->s)) $this->s = load('search.class.php', 'search', 'include');
		$r = $this->get($contentid);
		if(!$r)
			{
			log::add("内容不存在[{$contentid}]", log::NOTEXI, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			return false;
			}
		$modelid = $CATEGORY[$r['catid']]['modelid'];
		if(!$MODEL[$modelid]['enablesearch'])
			{
			log::add("此模型不启用全文搜索", log::INFO, __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
			return false;
			}
		$type = $MODEL[$modelid]['tablename'];
		$this->s->set_type($type);
		if ($r['searchid'] && 99 != $r['status'])
			{
			//删除全文搜索记录
			$this->s->delete($r['searchid']);
			return true;
			}
		if (99 == $r['status'])
			{
			$fulltextcontent = '';
			$fulltext_array = cache_read($modelid.'_fields.inc.php',CACHE_MODEL_PATH);
			foreach($fulltext_array AS $key=>$value)
				{
				if($value['isfulltext']) $fulltextcontent .= $r[$key].' ';
				}
			if($r['searchid'])
				{
				//更新全文搜索记录
				$this->s->update($r['searchid'], $r['title'], $fulltextcontent, $r['url']);
				}
			else
				{
				//新插入一条全文搜索记录
				$searchid = $this->s->add($r['title'], $fulltextcontent, $r['url']);
				if(!$searchid) return false;
				$this->db->query("UPDATE `$this->table` SET `searchid`=$searchid WHERE `contentid`=$contentid");
				}
			}
		return true;
	}//}}}

	/**
	 * 提取参数指定的条件中可能存在的contentid
	 */
	function contentid($contentid, $userid = 0, $allow_status = array())
	{//{{{
		$where = "`contentid` IN(".implodeids($contentid).")";
		$where .= $allow_status ? " AND `status` IN(".implodeids($allow_status).")" : '';
		$where .=  $this->userid_sql;
		$array = array();
		$result = $this->db->select("SELECT `contentid` FROM `$this->table` WHERE $where");
		foreach ($result as $k => $r)
			{
			$array[] = $r['contentid'];
			}
		return is_array($contentid) ? $array : $array[0];
	}//}}}

	/**
	 * 删除文章
	 * @todo delete 也应像 output 这样加上字段类型的事件
	 */
	function delete($contentid)
	{//{{{
		global $MODULE,$attachment;
		if(is_array($contentid))
		{
			//实现批量操作
			array_map(array(&$this, 'delete'), $contentid);
		}
		else
		{
			$contentid = intval($contentid);
			$data = $this->db->get_one("SELECT `catid`,`url`,`searchid` FROM `$this->table` WHERE `contentid`=$contentid $this->userid_sql");
			if($data)
			{
				$this->set_catid($data['catid']);
				if($this->ishtml) 
				{
					//删除对应的静态文件
					//$html = load('html.class.php');
					//$html->delete($contentid, $this->model_table);
				}
				$this->db->query("DELETE `$this->table`,`$this->model_table` FROM `$this->table`,`$this->model_table` WHERE $this->table.contentid=$this->model_table.contentid AND $this->table.contentid=$contentid $this->userid_sql");
				$this->db->query("UPDATE `$this->table_category` SET `items`=`items`-1 WHERE `catid`='".$data['catid']."'");
				if($this->db->affected_rows())
				{
					$this->db->query("DELETE FROM `$this->table_count` WHERE `contentid`=$contentid");
					$this->db->query("DELETE FROM `$this->table_position` WHERE `contentid`=$contentid");
					$this->db->query("DELETE FROM `$this->table_tag` WHERE `contentid`=$contentid");
					// if(!is_object($attachment))
					// {
						// require_once 'attachment.class.php';
						// $attachment = new attachment('phpcms', $data['catid']);
					// }
					//删除附件
					// $attachment->delete("`contentid`=$contentid");
					//下面这些都应以 hook 的形式来实现，现在的形式属于硬写代码。
					//删除顶一下
					if(isset($MODULE['digg']))
					{
						$digg = load('digg.class.php', 'digg', 'include');
						$digg->delete($contentid);
					}
					if(isset($MODULE['mood']))
					{
						$this->db->query("DELETE FROM `".DB_PRE."mood_data` WHERE contentid=$contentid");
					}
					//删除评论
					if(isset($MODULE['comment']))
					{
						$this->db->query("DELETE FROM `".DB_PRE."comment` WHERE keyid='phpcms-content-title-$contentid'");
					}
					//删除全文搜索数据
					if(isset($MODULE['search']))
					{
						$search = load('search.class.php', 'search', 'include');
						$search->delete($data['searchid']);
					}
					//采集
					// $this->db->query("DELETE FROM ".DB_PRE."collect WHERE `contentid`='$contentid'");
				}
				$this->log_write($contentid, 'delete');
			}
		}
		return true;
	}//}}}

	/**
	 * 删除所有 status=0 的文章
	 */
	function clear()
	{//{{{
		@set_time_limit(600);
		$result = $this->db->select("SELECT `contentid` FROM `$this->table` WHERE `status`=0");
		foreach ($result as $k => $r)
			{
			$this->delete($r['contentid']);
			}
		return true;
	}//}}}

	/**
	 * 指定文章 status 设为 99
	 */
	function restore($contentid)
	{//{{{
		return $this->status($contentid, 99);
	}//}}}

	/**
	 * 把所有 status=0 的文章 status 设为 99
	 */
	function restoreall()
	{//{{{
		@set_time_limit(600);
		$result = $this->db->select("SELECT `contentid` FROM `$this->table` WHERE `status`=0");
		foreach ($result as $k => $r)
			{
			$this->status($r['contentid'], 99);
			}
		return true;
	}//}}}

	/**
	 * 统计符合条件的文章数量
	 */
	function count($where = '')
	{//{{{
		if($where) $where = " WHERE $where $this->userid_sql";
		return cache_count("SELECT count(*) AS `count` FROM `$this->table` $where");
	}//}}}

	/**
	 * 提取指定文章的统计数据
	 */
	function get_count($contentid)
	{//{{{
		$contentid = intval($contentid);
		return $this->db->get_one("SELECT * FROM `$this->table_count` WHERE `contentid`=$contentid");
	}//}}}

	/**
	 * 为指定文章增加一次点击量
	 */
	function hits($contentid)
	{//{{{
		$contentid = intval($contentid);
		$r = $this->db->get_one("SELECT * FROM `$this->table_count` WHERE `contentid`=$contentid");
		if(!$r) return false;
		$hits = $r['hits'] + 1;
		$hits_day = (date('Ymd', $r['hits_time']) == date('Ymd', TIME)) ? ($r['hits_day'] + 1) : 1;
		$hits_week = (date('YW', $r['hits_time']) == date('YW', TIME)) ? ($r['hits_week'] + 1) : 1;
		$hits_month = (date('Ym', $r['hits_time']) == date('Ym', TIME)) ? ($r['hits_month'] + 1) : 1;
		return $this->db->query("UPDATE `$this->table_count` SET `hits`=$hits,`hits_day`=$hits_day,`hits_week`=$hits_week,`hits_month`=$hits_month,`hits_time`=".TIME." WHERE `contentid`=$contentid");
	}//}}}

	/**
	 * 设置文章的 status 字段值
	 */
	function status($contentid, $status, $is_admin = 0)
	{//{{{
		global $MODULE, $presentpoint,$catid;
		if(!$contentid) return false;
		$status = intval($status);
		$contentids = implodeids($contentid);
		$is_update = $this->db->query("UPDATE `$this->table` SET `status`=$status WHERE `contentid` IN($contentids) $this->userid_sql");
		if(is_array($contentid))
		{
			foreach($contentid as $id)
			{
				if($status==99 && isset($MODULE['pay']))
				{
					$cinfo = $this->get($id);
					$r = $this->db->get_one("SELECT setting FROM `".DB_PRE."category` WHERE `catid`='$cinfo[catid]'");
					$setting = string2array($r['setting']);
					$presentpoint = $setting['presentpoint'];
					$catid = $cinfo['catid'];
					if($catid && $presentpoint)
					{
						$api_msg = $presentpoint > 0 ? '投稿奖励' : '发布信息扣点';
						if(!is_object($pay_api)) $pay_api = load('pay_api.class.php', 'pay', 'api');
						$pay_api->update_exchange('phpcms', 'point', $presentpoint, $api_msg, $cinfo['userid']);
					}
				}
				$this->log_write($id, $status,$is_admin);
			}
		}
		else
		{
			$this->log_write($contentid, $status,$is_admin);
		}
		return $is_update;
	}//}}}

	/**
	 * title > contentid
	 */
	function get_contentid($title)
	{//{{{
		$info = $this->db->get_one("SELECT `contentid` FROM `$this->table` WHERE `title`='$title'");
		if($info['contentid']) return TRUE;
		else return FALSE;
	}//}}}

	/**
	 * 工作流 > status
	 */
	function get_status($processid)
	{//{{{
		$processid = intval($processid);
		$array = array();
		$result = $this->db->query("SELECT `status` FROM `".DB_PRE."process_status` WHERE `processid`=$processid");
		while($r = $this->db->fetch_array($result))
		{
			$array[] = $r['status'];
		}
		$this->db->free_result($result);
		return $array;
	}//}}}

	function move($id = '', $targetcatid = 0, $iscatid = 0)
	{//{{{
		if($iscatid)
		{
			if(!is_array($id)) return false;
			$ids = implode(',',$id);
			$r = $this->db->select("SELECT `contentid` FROM `$this->table` WHERE `catid` IN ($ids)", 'contentid');
			$contentids = array_keys($r);
			array_map(array($this, 'move'), $contentids, array_fill(0, count($contentids), $targetcatid));
		}
		else
		{
			if(strpos($id, ',')!==false)
			{
				$contentids = explode(',', $id);
				array_map(array($this, 'move'), $contentids, array_fill(0, count($contentids), $targetcatid));
			}
			$contentid = intval($id);
			$r = $this->db->get_one("SELECT `catid`, `islink` FROM `$this->table` WHERE `contentid`=$contentid");
			$this->db->query("UPDATE `$this->table_category` SET `items`=`items`-1 WHERE `catid`=$r[catid]");
			$this->set_catid($r['catid']);
			if($this->ishtml && !$r['islink'])
			{
				if(!is_object($html))
				{
					$html = load('html.class.php');
				}
				$html->delete($contentid, $this->model_table);
			}
			$this->db->query("UPDATE `$this->table` SET `catid`='$targetcatid' WHERE `contentid`=$contentid");
			if(!$r['islink'])
			{
				$info = $this->url->show($contentid, 0, $targetcatid);
				$this->db->query("UPDATE `$this->table` SET `url`='$info[1]' WHERE `contentid`=$contentid");
			}
			$this->db->query("UPDATE `$this->table_category` SET `items`=`items`+1 WHERE `catid`=$targetcatid");
			$this->log_write($contentid);
		}
		return true;
	}//}}}

	/**
	 * 推荐位相关
	 */
	function get_posid($contentid = 0, $posid = 0)
	{//{{{
		return $this->db->get_one("SELECT * FROM `".DB_PRE."content_position` WHERE `contentid`=$contentid AND `posid`=$posid");
	}//}}}

	/**
	 * 推荐位相关
	 */
	function add_posid($contentid = 0, $posid = 0)
	{//{{{
		$contentid = intval($contentid);
		$posid = intval($posid);
		if(!$contentid || !$posid) return false;
		$this->db->query("INSERT INTO `".DB_PRE."content_position` (`contentid`, `posid`) VALUES ('$contentid', '$posid')");
		$this->db->query("UPDATE $this->table SET `posids`=1 WHERE `contentid`=$contentid");
		return true;
	}//}}}

	/**
	 * type 相关
	 */
	function add_typeid($contentid = 0, $typeid = 0)
	{//{{{
		$contentid = intval($contentid);
		$typeid = intval($typeid);
		if(!$contentid || !$typeid) return false;
		$this->db->query("UPDATE $this->table SET `typeid`=$typeid WHERE `contentid`=$contentid");
		return true;
	}//}}}

	/**
	 * 更新指定栏目一条全文搜索数据
	 */
	function update_search($catid, $i)
	{//{{{
		$info = $this->db->get_one("SELECT `contentid` FROM `$this->table` WHERE `catid`='$catid' AND `status`=99 ORDER BY `contentid` DESC LIMIT $i, 1");
		$this->search_api($info['contentid']);
		return true;
	}//}}}

	function inspect($contentid, $passname = '')
	{//{{{
		global $MODEL;
		if(!$passname) return false;
		require_once 'admin/process.class.php';
		if(is_array($contentid))
		{
			foreach($contentid as $c)
			{
				$r = $this->get($c);
				$this->set_catid($r['catid']);
				$workflowid = $MODEL[$this->modelid]['workflowid'];
				if(${prco.$workflowid})
				{
					$PROCESS = ${prco.$workflowid};
				}
				else
				{
					${prco.$workflowid} = $PROCESS = cache_read('process_'.$workflowid.'.php');
				}
				$PROCESS = array_keys($PROCESS);
				$re = $this->db->get_one("SELECT `processid` FROM `".DB_PRE."process` WHERE `workflowid`=$workflowid AND `passstatus`=$r[status]");
				if(!$re)
				{
					$processid = array_shift($PROCESS);
				}
				else
				{
					foreach($PROCESS AS $key => $pro)
					{
						if($pro==$re['processid'])
						{
							$key++;
							$processid = $PROCESS[$key];
							break;
						}
					}
				}
				$p = new process($workflowid);
				$allow_status = $p->get_process_status($processid);
				$contentids = $this->contentid($c, 0, $allow_status);
				$process = $p->get($processid, $passname);
				$this->status((array)$contentids, $process['passstatus']);
				unset($p);
			}
		}
		return true;
	}//}}}

	function get_pro_status()
	{//{{{
		global $priv_role;
		$status = array();
		$STATUS = $this->db->select("SELECT `status`, `name` FROM `".DB_PRE."status` WHERE `status`>2 AND `status`!=99");
		foreach($STATUS as $S)
		{
			$process = $this->db->select("SELECT `processid` FROM `".DB_PRE."process_status` WHERE `status`=$S[status]");
			foreach($process as $p)
			{
				if($priv_role->check('processid', $p['processid']))
				{
					$status[] = $S;
					break;
				}
			}
		}
		return $status;
	}//}}}
}
?>

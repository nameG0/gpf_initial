<?php
class category
{
	var $module;
	var $db;
	var $table;
	var $category;
	//var $menu;
	var $u;

	function __construct($module = 'cms')
	{//{{{
		global $CATEGORY;
		$this->db = rdb::obj();
		$this->table = RDB_PRE . 'category';
		$this->category = $CATEGORY;
		$this->module = $module;
		//$this->menu = load('menu.class.php');
		// $this->u = load('url.class.php');
	}//}}}

	function category($module = 'cms')
	{//{{{
		$this->__construct($module);
	}//}}}

	function get($catid)
	{//{{{
		$data = $this->db->get_one("SELECT * FROM `$this->table` WHERE `catid`=$catid");
		if(!$data) return false;
		a::i($data)->unsers('setting');
		// if($data['setting'])
			// {
			// $setting = $data['setting'];
			// eval("\$setting = $setting;");
			// unset($data['setting']);
			// if(is_array($setting)) $data = array_merge($data, $setting);
			// }
		return $data;
	}//}}}

	//$category
	//	type		栏目类型,0=内部栏目,1=单网页,2=外部链接*
	//	parentid	上级栏目*
	//	modelid		绑定模型*
	//	catname		栏目名称*
	//	catdir		栏目目录*
	//	image		栏目图片
	//	description	栏目介绍
	//$setting
	//	workflowid	工作流方案*
	//	meta_title	META Title（栏目标题）
	//	meta_keywords	META Keywords（栏目关键词）
	//	meta_keywords	META Keywords（栏目关键词）
	//	priv_groupid(array1)	会员组权限
	//	priv_roleid(array1)	角色权限
	//	...(其它参数模板)
	//$is_repair	是否调用 $this->repair 方法，若需要多次 add() ，则可设为 false，添加完后再手动调用，可节省些运行时间。
	function add($category, $setting = array(), $is_repair = true)
	{//{{{
		if(!is_array($category)) return FALSE;
		$category['module'] = $this->module;
		$this->db->insert($this->table, $category);
		//ggzhu@2010-08-06 add 对于catid的处理，使其可用于指定catid的新增
		if (!$category['catid'])
			{
			$category['catid'] = $this->db->insert_id();
			}
		$catid = $category['catid'];
		if($category['parentid'])
			{
			$category['arrparentid'] = $this->category[$category['parentid']]['arrparentid'].','.$category['parentid'];
			$category['parentdir'] = $this->category[$category['parentid']]['parentdir'].$this->category[$category['parentid']]['catdir'].'/';
			$parentids = explode(',', $category['arrparentid']);
			foreach($parentids as $parentid)
				{
				if($parentid)
					{
					$arrchildid = $this->category[$parentid]['arrchildid'].','.$catid;
					$url = gpf::url("cms.content.category.&catid={$parentid}");
					$this->db->query("UPDATE `{$this->table}` SET child=1,arrchildid='{$arrchildid}', url='{$url}' WHERE catid='$parentid'");
					}
				}
			}
		else
			{
			$category['arrparentid'] = '0';
			$category['parentdir'] = '';
			}
		$arrparentid = $category['arrparentid'];
		$parentdir = $category['parentdir'];
		$r_update = array(
			"arrchildid" => $catid,
			"listorder" => $catid,
			"arrparentid" => $arrchildid,
			"parentdir" => $parentdir,
			"setting" => $setting,
			);
		if ($category['type'] <= 1)
			{
			$r_update['url'] = gpf::url("cms.content.list.&catid={$catid}");
			}
		a::i($r_update)->sers('setting')->adds();
		$this->db->update($this->table, $r_update, "catid = {$catid}");
		// $this->db->query("UPDATE `$this->table` SET `arrchildid`='$catid',`listorder`=$catid,`arrparentid`='$arrparentid',`parentdir`='$parentdir' WHERE catid=$catid");
		// if($setting) setting_set($this->table, "catid=$catid", $setting);
		if($this->module == 'cms' && $category['type'] < 2)
			{
			$parentid = $category['parentid'];
			//$this->menu->update('catid_'.$catid, array('parentid'=>$this->menu->menuid('catid_'.$parentid), 'name'=>$category['catname'], 'url'=>'?mod=phpcms&file=content&action=manage&catid='.$catid));
			//if($parentid) $this->menu->update('catid_'.$parentid, array('url'=>''));
			}
		// if($category['type'] < 2) $this->url($catid);
		//ggzhu 2010-08-06 add 更新内存中的数据，使其适用于多次调用
		$this->category[$catid] = $category;

		if ($is_repair)
			{
			// $this->repair();
			}
		return $catid;
	}//}}}

	//纯更新数据
	function _edit($where, $data)
	{//{{{
		return sql_edit($this->table, 'catid', $where, $data);
	}//}}}

	function edit($catid, $category, $setting = array())
	{//{{{
		$parentid = $category['parentid'];
		$oldparentid = $this->category[$catid]['parentid'];
		if($parentid != $oldparentid)
			{
			$this->move($catid, $parentid, $oldparentid);
			}

		$category['module'] = $this->module;
		if($setting)
			{
			a::i($category)->is_adds(true)->set('setting', $setting)->sers('setting');
			// setting_set($this->table, "catid=$catid", $setting);
			}
		//todo ggzhu@2012-07-18 硬生成栏目前台URL
		if ($category['type'] < 2)
			{
			if ($this->category[$catid]['child'])
				{
				$category['url'] = gpf::url("cms.content.category.&catid={$catid}");
				}
			else
				{
				$category['url'] = gpf::url("cms.content.list.&catid={$catid}");
				}
			}

		$this->db->update($this->table, $category, "catid=$catid");
		if($this->module == 'cms' && $category['type'] < 2)
			{
			$url = $this->category[$catid]['child'] ? '' : gpf::url("cms.a_content.manage.&catid={$catid}");
			//$this->menu->update('catid_'.$catid, array('parentid'=>$this->menu->menuid('catid_'.$parentid), 'name'=>$category['catname'], 'url'=>$url));
			//if($parentid) $this->menu->update('catid_'.$parentid, array('url'=>''));
			}
		if($category['type'] < 2) $this->url($catid);
		$this->repair();
		_category_cache_catid($catid);
		$this->cache();
		return true;
	}//}}}

	function link($catid, $category)
	{//{{{
		$this->db->update($this->table, $category, "catid=$catid");
		$this->cache();
		return true;
	}//}}}

	function page($catid, $category)
	{//{{{
		$this->db->update($this->table, $category, "catid=$catid");
		$this->cache();
		return true;
	}//}}}

	function delete($catid)
	{//{{{
		global $MODEL,$MODULE;
		if(!array_key_exists($catid, $this->category)) return false;
		@set_time_limit(600);
		$arrparentid = $this->category[$catid]['arrparentid'];
		$arrchildid = $this->category[$catid]['arrchildid'];
		$catids = explode(',', $arrchildid);
		if($this->category[$catid]['type'] == 0)
			{
			if(isset($MODULE['search']) || isset($MODULE['comment']))
				{
				$sids = array();
				$result = $this->db->query("SELECT contentid,searchid FROM ".DB_PRE."content WHERE catid IN($arrchildid)");
				while($r = $this->db->fetch_array($result))
					{
					if(isset($MODULE['comment']))
						{
						$keyid = 'phpcms-content-title-'.$r['contentid'];
						$this->db->query("DELETE FROM ".DB_PRE."comment WHERE keyid='$keyid'");
						}
					$sids[] = $r['searchid'];
					}
				if(isset($MODULE['search']) && $sids)
					{
					$this->search = load('search.class.php', 'search', 'include');
					foreach($sids as $searchid)
						{
						$this->search->delete($searchid, 'searchid');
						}
					}
				}
			if(isset($MODULE['digg']))
				{
				$data = $this->db->select("SELECT `contentid` FROM `".DB_PRE."content` WHERE `catid` IN($arrchildid)", 'contentid');
				if($data)
					{
					$contentids = implode(',', array_keys($data));
					$this->db->query("DELETE `".DB_PRE."digg`,`".DB_PRE."digg_log` FROM `".DB_PRE."digg`,`".DB_PRE."digg_log` WHERE `".DB_PRE."digg`.contentid=`".DB_PRE."digg_log`.contentid AND `".DB_PRE."digg`.contentid IN($contentids)");
					}
				}
			foreach($catids as $id)
				{
				$modelid = $this->category[$id]['modelid'];
				if($this->category[$id]['type']) continue;
				$tablename = $MODEL[$modelid]['tablename'];
				if($tablename && $this->db->table_exists(DB_PRE.'c_'.$tablename))
					{
					$this->db->query("DELETE `".DB_PRE."content`,`".DB_PRE."c_$tablename` FROM `".DB_PRE."content`,`".DB_PRE."c_$tablename` WHERE `".DB_PRE."content`.contentid=`".DB_PRE."c_$tablename`.contentid AND `".DB_PRE."content`.catid='$id'");
					}
				//if($this->module == 'phpcms' && $this->category[$id]['type'] < 2) $this->menu->update('catid_'.$id);
				unset($this->category[$id]);
				}
			}
		else
			{
			//$this->menu->update('catid_'.$catid);
			}
		$this->db->query("DELETE FROM `$this->table` WHERE `catid` IN($arrchildid)");
		if($arrparentid)
			{
			$arrparentids = explode(',', $arrparentid);
			foreach($arrparentids as $id)
				{
				if($id == 0) continue;
				$arrchildid = $this->get_arrchildid($id);
				$child = is_numeric($arrchildid) ? 0 : 1;
				$this->db->query("UPDATE `$this->table` SET `arrchildid`='$arrchildid', `child`='$child' WHERE `catid`='$id'");
				//if($this->module == 'phpcms' && $this->category[$id]['type'] < 2) $this->menu->update('catid_'.$id, array('isfolder'=>$child));
				}
			}
		$this->cache();
		return true;
	}//}}}

	function listorder($listorder)
	{//{{{
		if(!is_array($listorder)) return FALSE;
		foreach($listorder as $catid=>$value)
			{
			$value = intval($value);
			$this->db->query("UPDATE `$this->table` SET listorder=$value WHERE catid=$catid");
			}
		$this->cache();
		return TRUE;
	}//}}}

	function recycle($catid)
	{//{{{
		$modelid = $this->category[$catid]['modelid'];
		$m = cache_read('model_'.$modelid.'.php');
		$this->db->query("DELETE FROM `".DB_PRE."content` ,`".DB_PRE."c_".$m['tablename']."` USING `".DB_PRE."content`,`".DB_PRE."c_".$m['tablename']."` WHERE `".DB_PRE."content`.catid='$catid' AND `".DB_PRE."content`.contentid=`".DB_PRE."c_".$m['tablename']."`.contentid");
		return TRUE;
	}//}}}

	function listinfo($parentid = -1)
	{//{{{
		$categorys = array();
		$where = $parentid > -1 ? " AND parentid='{$parentid}'" : '';
		$result = $this->db->select("SELECT * FROM `{$this->table}` WHERE `module`='{$this->module}' {$where} ORDER BY `listorder`,`catid`");
		foreach ($result as $k => $r)
			{
			a::i($r)->unsers('setting');
			$categorys[$r['catid']] = $r;
			}
		return $categorys;
	}//}}}

	function repair()
	{//{{{
		@set_time_limit(600);
		if(is_array($this->category))
			{
			foreach($this->category as $catid => $cat)
				{
				if($catid == 0) continue;
				$arrparentid = $this->get_arrparentid($catid);
				$parentdir = $this->get_parentdir($catid);
				$arrchildid = $this->get_arrchildid($catid);
				$child = is_numeric($arrchildid) ? 0 : 1;
				$this->db->query("UPDATE `$this->table` SET arrparentid='$arrparentid',parentdir='$parentdir',arrchildid='$arrchildid',child='$child' WHERE catid=$catid");
				if($cat['module']=='phpcms') $this->url($catid);
				}
			}
		$this->cache();
		return TRUE;
	}//}}}

	function join($sourcecatid, $targetcatid)
	{//{{{
		$arrchildid = $this->category[$sourcecatid]['arrchildid'];
		$arrparentid = $this->category[$sourcecatid]['arrparentid'];

		$this->db->query("DELETE FROM `$this->table` WHERE `catid` IN ($arrchildid)");

		$this->db->query("UPDATE ".DB_PRE."content set catid='$targetcatid' WHERE catid IN ($arrchildid)");

		$catids = explode(',', $arrchildid);
		foreach($catids as $id)
			{
			//$this->db->query("DELETE FROM ".DB_PRE."menu WHERE keyid='catid_$id' LIMIT 1");
			unset($this->category[$id]);
			}

		if($arrparentid)
			{
			$arrparentids = explode(',', $arrparentid);
			foreach($arrparentids as $id)
				{
				if($id == 0) continue;
				$arrchildid = $this->get_arrchildid($id);
				$child = is_numeric($arrchildid) ? 0 : 1;
				$this->db->query("UPDATE `$this->table` SET arrchildid='$arrchildid',child=$child WHERE catid='$id'");
				}
			}

		$this->cache();
		return true;
	}//}}}

	function get_arrparentid($catid, $arrparentid = '', $n = 1)
	{//{{{
		if($n > 5 || !is_array($this->category) || !isset($this->category[$catid])) return false;
		$parentid = $this->category[$catid]['parentid'];
		$arrparentid = $arrparentid ? $parentid.','.$arrparentid : $parentid;
		if($parentid)
			{
			$arrparentid = $this->get_arrparentid($parentid, $arrparentid, ++$n);
			}
		else
			{
			$this->category[$catid]['arrparentid'] = $arrparentid;
			}
		return $arrparentid;
	}//}}}

	function get_arrchildid($catid)
	{//{{{
		$arrchildid = $catid;
		if(is_array($this->category))
			{
			foreach($this->category as $id => $cat)
				{
				if($cat['parentid'] && $id != $catid)
					{
					$arrparentids = explode(',', $cat['arrparentid']);
					if(in_array($catid, $arrparentids)) $arrchildid .= ','.$id;
					}
				}
			}
		return $arrchildid;
	}//}}}

	function get_parentdir($catid)
	{//{{{
		if($this->category[$catid]['parentid']==0) return '';
		$arrparentid = $this->category[$catid]['arrparentid'];
		$arrparentid = explode(',', $arrparentid);
		$arrcatdir = array();
		foreach($arrparentid as $id)
			{
			if($id==0) continue;
			$arrcatdir[] = $this->category[$id]['catdir'];
			}
		return implode('/', $arrcatdir).'/';
	}//}}}

	function move($catid, $parentid, $oldparentid)
	{//{{{
		$arrchildid = $this->category[$catid]['arrchildid'];
		$oldarrparentid = $this->category[$catid]['arrparentid'];
		$oldparentdir = $this->category[$catid]['parentdir'];
		$child = $this->category[$catid]['child'];
		$oldarrparentids = explode(',', $this->category[$catid]['arrparentid']);
		$arrchildids = explode(',', $this->category[$catid]['arrchildid']);
		if(in_array($parentid, $arrchildids)) return FALSE;
		$this->category[$catid]['parentid'] = $parentid;
		if($child)
			{
			foreach($arrchildids as $cid)
				{
				if($cid==$catid) continue;
				$newarrparentid = $this->get_arrparentid($cid);
				$this->category[$cid]['arrparentid'] = $newarrparentid;
				$newparentdir = $this->get_parentdir($cid);
				$this->db->query("UPDATE `$this->table` SET arrparentid='$newarrparentid',parentdir='$newparentdir' WHERE catid='$cid'");
				}
			}
		if($parentid)
			{
			$arrparentid = $this->category[$parentid]['arrparentid'].",".$parentid;
			$this->category[$catid]['arrparentid'] = $arrparentid;
			$parentdir = $this->category[$parentid]['parentdir'].$r['catdir']."/";
			$arrparentids = explode(",", $arrparentid);
			foreach($arrparentids as $pid)
				{
				if($pid==0) continue;
				$newarrchildid = $this->get_arrchildid($pid);
				$this->db->query("UPDATE `$this->table` SET arrchildid='$newarrchildid',child=1 WHERE catid=$pid");
				}
			}
		else
			{
			$arrparentid = 0;
			$parentdir = '/';
			$this->category[$catid]['arrparentid'] = $arrparentid;
			}
		$this->db->query("UPDATE `$this->table` SET arrparentid='$arrparentid',parentdir='$parentdir' WHERE catid=$catid");
		if($oldparentid)
			{
			foreach($oldarrparentids as $pid)
				{
				if($pid==0) continue;
				$oldarrchildid = $this->get_arrchildid($pid);
				$child = is_numeric($oldarrchildid) ? 0 : 1;
				$this->db->query("UPDATE `$this->table` SET arrchildid='$oldarrchildid' ,child=$child WHERE catid=$pid");
				}
			}
		return TRUE;
	}//}}}

	function depth($catid)
	{//{{{
		return (substr_count($this->category[$catid]['arrparentid'], ',') + 1);
	}//}}}

	function url($catid, $is_update = 1)
	{//{{{
		//todo 暂时不计算栏目URL
		return '';

		global $MODEL;
		$data = $this->get($catid);
		if(!$data) return false;
		$this->u->CATEGORY[$catid] = $data;
		if($this->category[$catid]['type'] == 2) return false;
		// cache_file_write('category_'.$catid.'.php', $data);
		if($MODEL[$this->category[$catid]['modelid']]['ishtml'])
			{
			if(!preg_match('/:\/\//',$data['url']))
				{
				$url = $this->u->category($catid);
				}
			else
				{
				$url = $data['url'];
				}
			}
		else
			{
			//$url = $this->u->category($catid);
			}
		$url = preg_replace('/index\.[a-z]{3,5}$/', '', $url);
		if($is_update)
			{
			$categorys_c = array();
			$result = $this->db->query("SELECT * FROM `$this->table` WHERE `module`='$this->module'");
			while($r = $this->db->fetch_array($result))
				{
				$categorys_c[$r['catid']] = $r;
				}
			if(!$categorys_c[$catid]['parentid'])
				{
				$this->db->query("UPDATE `$this->table` SET url='$url' WHERE catid=$catid");
				$arrchildid = $data['arrchildid'];
				$arrchild = explode(',',$arrchildid);
				foreach($arrchild AS $k)
					{
					$parentdir = $second_domain = '';
					if($categorys_c[$k]['modelid'])
						{
						if($k == $catid || !$MODEL[$categorys_c[$k]['modelid']]['ishtml'] || $categorys_c[$k]['type'] == 2) continue;
						}
					else
						{
						$child_array_data = $this->get($k);
						if($k == $catid || !$child_array_data['ishtml'] || $categorys_c[$k]['type'] == 2) continue;	
						}
					$arrparentid = $categorys_c[$k]['arrparentid'];
					$arrparentid = explode(',',$arrparentid);
					array_shift($arrparentid);
					if(preg_match('/:\/\//',$categorys_c[$arrparentid[0]]['url']))
						{
						$second_domain = $categorys_c[$arrparentid[0]]['url'];
						array_shift($arrparentid);
						}
					foreach($arrparentid AS $p)
						{
						$parentdir .= $categorys_c[$p]['catdir'].'/';
						}
					$caturl = $second_domain.'/'.$parentdir.$categorys_c[$k]['catdir'].'/';
					$this->db->query("UPDATE `$this->table` SET url='$caturl' WHERE catid=$k");
					}
				}
			else
				{
				$this->db->query("UPDATE `$this->table` SET url='$url' WHERE catid=$catid");
				}
			unset($url);
			}

		return $url;
	}//}}}

	function count($catid, $status = null)
	{//{{{
		if(!isset($this->category[$catid])) return false;
		$where = '';
		$where .= $this->category[$catid]['child'] ? "AND `catid` IN(".$this->category[$catid]['arrchildid'].") " : "AND `catid`=$catid ";
		$where .= $status == null ? '' : "AND status='$status' ";
		if($where) $where = ' WHERE '.substr($where, 3);
		return cache_count("SELECT COUNT(*) AS `count` FROM `".DB_PRE."content` $where");
	}//}}}

	function cache()
	{//{{{
		@set_time_limit(600);
		//cache_category();
		//cache_common();
		_category_cache_all();
	}//}}}
}

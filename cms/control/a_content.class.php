<?php
/**
 * 用于管理内容的控制器
 * 
 * @package default
 * @filesource
 */

class ctrl_a_content
{
	function __construct()
	{//{{{
		admin_check();
		//require_once 'admin/process.class.php';
		require_once CMS_PATH . 'include/content.class.php';
		//require_once 'attachment.class.php';
		$c = new content();

		// if(is_numeric($contentid) && $contentid>0)
			// {
			// $data = $c->get($contentid);
			// $catid = $data['catid'];
			// $modelid = $CATEGORY[$catid]['modelid'];
			// }

		$modelid = intval($modelid);
		$catid = intval($catid);

		// $allow_manage = $priv_role->check('catid', $catid, 'manage');
		// $allow_add = $allow_manage ? true : $priv_role->check('catid', $catid, 'add');
		// $allow_check = $allow_manage ? true : $priv_role->check('catid', $catid, 'check');
		// $allow_view = $allow_manage ? true : $priv_role->check('catid', $catid, 'view');
		$allow_manage = true;
		$allow_add = true;
		$allow_check = true;
		$allow_view = true;

		// $attachment = new attachment($mod, $catid);
		// $p = new process($workflowid);
		// $PROCESS = cache_read('process_'.$workflowid.'.php');
		// //获取该工作流下面的状态
		// $workflow_infos =$p->listinfo("workflowid='$workflowid'");

		$submenu = $allowprocessids = array();
		if($allow_add)
			{
			$submenu[] = array('<font color="red">发布信息</font>', gpf::url("..add.&modelid={$modelid}.catid"));
			//$submenu[] = array('我发布的信息', '?mod='.$mod.'&file='.$file.'&action=my&catid='.$catid);
			}
		if($allow_check)
			{
			// foreach($PROCESS as $pid=>$processname)
			// {
			// if($priv_role->check('processid', $pid))
			// {
			// foreach($workflow_infos AS $_w)
			// {
			// if($pid == $_w['processid'] && $_w['passstatus']==99)
			// {
			// $allow_manage = 1;
			// }
			// else
			// {
			// $allow_manage = 0;
			// }
			// }
			// $allow_processids[] = $pid;
			// if($pid==1) $add_status = '&status=3';
			// $submenu[] = array($processname, '?mod='.$mod.'&file='.$file.'&action=check&catid='.$catid.'&processid='.$pid.$add_status);
			// }
			// }
			}
		if($allow_manage)
			{
			$submenu[] = array('管理', gpf::url("..manage.&modelid={$modelid}.catid"));
			// $submenu[] = array('回收站', '?mod='.$mod.'&file='.$file.'&action=recycle&catid='.$catid);
			// $submenu[] = array('碎片', '?mod='.$mod.'&file='.$file.'&action=block&catid='.$catid);
			// $submenu[] = array('定时发布', '?mod='.$mod.'&file='.$file.'&action=publish&catid='.$catid);
			}
		elseif($allow_view)
			{
			$submenu[] = array('浏览', '?mod='.$mod.'&file='.$file.'&action=browse&catid='.$catid);
			}
		//$submenu[] = array('搜索', '?mod='.$mod.'&file='.$file.'&action=search&catid='.$catid);
		// $menu = admin_menu($CATEGORY[$catid]['catname'].' 栏目管理', $submenu);

		//if(!isset($processid) || !in_array($processid, $allow_processids)) $processid = $allow_processids[0];
	}//}}}
	function action_add()
	{//{{{
		list($modelid, $catid) = i::g()->int('modelid', 'catid')->end();
		$modelid = CMS_MODEL_ID;

		if(!$modelid) showmessage('缺少 modelid 参数!');
		log::add("modelid[{$modelid}]", log::INFO, __FILE__, __LINE__);
		//if(!$priv_role->check('catid', $catid, 'add') && !$allow_manage) showmessage('无发布权限！');

		if (isset($_POST["dosubmit"]))
			{
			a::i($info)->fpost('data');

			//增加判断如果发布时间大于当前时间则设定为定时发布状态98
			// $info['status'] = ($status == 2 || $status == 3) ? $status : ($allow_manage ? ($PHPCMS['publish'] && (strtotime($info['inputtime']) > TIME) ? 98 : 99)  : 3);
			//ggzhu@2012-06-28 暂时直接设置状态
			$info['status'] = $status;

			// if(isset($info['inputtime'])) $info['updatetime'] = $info['inputtime'];
			$info['updatetime'] = $info['inputtime'] = time();
			// $contentid = $c->add($info, $cat_selected);
			//ggzhu@2012-06-28 为求开发速度，直接在控制器中保存数据到数据库
			$CMMR = conm_CMMR($modelid);
			//保存到数据表
			$o_db = rdb::obj();
			// $o_db->insert(RDB_PRE . 'cms_content', array("modelid" => $modelid,));
			$contentid = $o_db->insert_id();
			// $info['contentid'] = $contentid;
			$info = conm_fill($CMMR['CMFL'], $info);
			$o_db->insert(RDB_PRE . $CMMR['tablename'], $info);

			//如果状态为定时发布，文章id作为key，发布时间作为value，写入缓存
			//ggzhu@2012-06-28 暂不支持定时发布
			// if($info['status']==98) {
				// $tmp_publisharr = cache_read('publish.php');
				// $tmp_publisharr[$contentid] = strtotime($info['updatetime']);
				// cache_write('publish.php', $tmp_publisharr);
				// unset($tmp_publisharr);
			// }

			if($contentid) showmessage('发布成功！', gpf::url("..add..catid,modelid"));
			}
		else
			{
			$data['catid'] = $catid;
			$data['template'] = isset($template_show) ? $template_show :$MODEL[$modelid]['template_show'];

			$CMMR = conm_CMMR($modelid);
			// require CONTENT_ROOT . 'include/content_form.class.php';
			// $content_form = new content_form($modelid);
			// $forminfos = $content_form->get($data);
			$forminfos = conm_form($CMMR['CMFL'], $data);
			unset($forminfos['inputtime'], $forminfos['updatetime']);
			// require_once CATEGORY_ROOT . 'include/tree.class.php';
			// foreach($CATEGORY as $cid=>$c)
				// {
				// if($c['module'] != $mod || $c['type'] > 0) continue;
				// $checkbox = $c['child'] ? '' : '<input type="checkbox" name="cat_selected[]" value="'.$cid.'">';
				// $cats[$cid] = array('id'=>$cid, 'parentid'=>$c['parentid'], 'name'=>$c['catname'], 'checkbox'=>$checkbox);
				// }
			// $str = "<tr><td style='height:22px;padding:0 0 0 10px;'>\$spacer\$name</td><td>\$checkbox</td></tr>";
			// $tree = new tree($cats);
			// $categorys = $tree->get_tree(0, $str);
			$pagetitle = $CATEGORY[$catid]['catname'].'-发布';
			@header("Cache-control: private");
			include tpl_admin('content_add');
			}
	}//}}}
	function action_edit()
	{//{{{
		$contentid = i::g()->int('contentid')->end();

		$modelid = CMS_MODEL_ID;
		if (isset($_POST["dosubmit"]))
			{
			a::i($info)->fpost('data');
			a::i($keep)->fpost('keep');
			$info['updatetime'] = time();

			// $r = siud::find('content')->tfield('modelid')->wis('contentid', $contentid)->ing();
			// $modelid = $r['modelid'];
			$info['status'] = ($status == 2 || $status == 3) ? $status : 99;
			$CMMR = conm_CMMR($modelid);
			$data = conm_fill($CMMR['CMFL'], $info, $keep);
			// siud::save('c_' . $CMMR['tablename'])->data($data)->pk('contentid')->ing();
			siud::save('content')->data($data)->pk('contentid')->ing();
			// $c->edit($contentid, $info);
			$forward = gpf::url("..edit..contentid");
			showmessage('修改成功！', $forward);
			}
		else
			{
			// require CONTENT_ROOT . 'include/content_form.class.php';
			// $content_form = new content_form($modelid);
			// $forminfos = $content_form->get($data);
			// $r = siud::find('content')->tfield('modelid')->wis('contentid', $contentid)->ing();
			// $modelid = $r['modelid'];
			$CMMR = conm_CMMR($modelid);
			$data = siud::find($CMMR['tablename'])->wis('contentid', $contentid)->ing();
			$forminfos = conm_form($CMMR['CMFL'], $data);
			unset($forminfos['inputtime'], $forminfos['updatetime']);
			include tpl_admin('content_edit');
			}
	}//}}}
	function action_view()
	{//{{{
		if(!$priv_role->check('catid', $catid, 'view') && !$allow_manage) showmessage('无查看权限！');

		require_once CONTENT_ROOT . 'include/content_output.class.php';
		$coutput = new content_output();
		$info = $coutput->get($data);

		include tpl_admin('content_view');
	}//}}}
	function action_log_list()
	{//{{{
		$ACTION = array('add'=>'发布', 'edit'=>'修改', 'delete'=>'删除');
		$content = $c->get($contentid);
		extract($content);
		$log->set('contentid', $contentid);
		$data = $log->listinfo($where, $page, 20);
		include tpl_admin('content_log');
	}//}}}
	function action_my_contribute()
	{//{{{
		$c->set_userid($_userid);
		$contentid = $c->contentid($contentid, array(0, 1, 2));
		$c->status($contentid, 3);
		showmessage('操作成功！', $forward);
	}//}}}
	function action_my()
	{//{{{
		if(!$allow_add) showmessage('无发布权限！');
		$c->set_userid($_userid);
		$status = isset($status) ? intval($status) : -1;
		$where = "`catid`=$catid ";
		if($status != -1) $where .= " AND `status`='$status'";
		$infos = $c->listinfo($where, 'listorder DESC,contentid DESC', $page, 20);
		$pagetitle = '我的信息-管理';
		include tpl_admin('content_my');
	}//}}}
	function action_my_cancelcontribute()
	{//{{{
		$c->set_userid($_userid);
		$contentid = $c->contentid($contentid, array(3));
		$c->status($contentid, 2);
		showmessage('操作成功！', $forward);
	}//}}}
	function action_my_edit()
	{//{{{
		$c->set_userid($_userid);
		$contentid = $c->contentid($contentid, array(0, 1, 2, 3));

		if($dosubmit)
			{
			$info['status'] = ($status == 2 || $status == 3) ? $status : ($allow_manage ? ($PHPCMS['publish'] && (strtotime($info['inputtime']) > TIME) ? 98 : 99)  : 3);
			$c->edit($contentid, $info);
			showmessage('修改成功！', $forward);
			}
		else
			{
			require CONTENT_ROOT . 'include/content_form.class.php';
			$content_form = new content_form($modelid);
			$forminfos = $content_form->get($data);

			include tpl_admin('content_edit');
			}
	}//}}}
	function action_my_delete()
	{//{{{
		$c->set_userid($_userid);
		$contentid = $c->contentid($contentid, array(0, 1, 2, 3));
		$c->delete($contentid);
		showmessage('操作成功！', $forward);
	}//}}}
	function action_my_view()
	{//{{{
		$c->set_userid($_userid);
		$contentid = $c->contentid($contentid, array(0, 1, 2, 3));

		require_once CONTENT_ROOT . 'include/content_output.class.php';
		$coutput = new content_output();
		$info = $coutput->get($data);

		include tpl_admin('content_view');
	}//}}}
	function action_check()
	{//{{{
		$allow_status = $p->get_process_status($processid);
		if(!isset($status) || !in_array($status, $allow_status)) $status = -1;
		$where = "`catid`=$catid ";
		$where .= $status == -1 ? " AND `status` IN(".implode(',', $allow_status).")" : " AND `status`='$status'";
		$infos = $c->listinfo($where, 'listorder DESC,contentid DESC', $page, 20);
		$process = $p->get($processid, 'passname,passstatus,rejectname,rejectstatus');
		extract($process);

		$pagetitle = $CATEGORY[$catid]['catname'].'-审核';
		include tpl_admin('content_check');
	}//}}}
	function action_publish()
	{//{{{
		if($do) {
			//如果操作定时发布文章，从缓存中移除该文章id。
			if(is_array($contentid)) {
				$tmp_publisharr = cache_read('publish.php');
				foreach($contentid as $v) {
					unset($tmp_publisharr[$v]);
				}
				cache_write('publish.php', $tmp_publisharr);
			}

			if($do == 'publish') {
				if(!$allow_manage) showmessage('无管理权限！');
				$c->status($contentid, 99);
				showmessage('操作成功！', $forward);	
			} elseif($do == 'del') {
				if(!$allow_manage) showmessage('无管理权限！');
				$c->delete($contentid);
				showmessage('操作成功！', $forward);
			} else {}

				$where = "`catid`=$catid ";
			$where .= " AND `status`=98";
			$infos = $c->listinfo($where, 'listorder DESC,contentid DESC', $page, 20);
			$process = $p->get($processid, 'passname,passstatus,rejectname,rejectstatus');
			extract($process);	
		} else {
			$where = "`catid`=$catid ";
			$where .= " AND `status`=98";
			$infos = $c->listinfo($where, 'listorder DESC,contentid DESC', $page, 20);
			$process = $p->get($processid, 'passname,passstatus,rejectname,rejectstatus');
			extract($process);
		}
		include tpl_admin('content_publish');
	}//}}}
	function action_check_title()
	{//{{{
		if(CHARSET=='gbk') $c_title = iconv('utf-8', 'gbk', $c_title);
		if($c->get_contentid($c_title))
			{	
			echo '此标题已存在！';
			}
		else
			{
			echo '标题不存在！';
			}
	}//}}}
	function action_browse()
	{//{{{
		$where = "`catid`=$catid AND `status`=99";
		$infos = $c->listinfo($where, 'listorder DESC,contentid DESC', $page, 20);
		include tpl_admin('content_browse');
	}//}}}
	function action_search()
	{//{{{
		if($dosubmit)
			{
			require CONTENT_ROOT . 'include/content_search.class.php';
			$content_search = new content_search();
			$infos = $content_search->data($page, 20);
			include tpl_admin('content_search_list');
			}
		else
			{
			require CONTENT_ROOT . 'include/content_search_form.class.php';
			$content_search_form = new content_search_form();
			$forminfos = $content_search_form->get_where();
			$orderfields = $content_search_form->get_order();

			$pagetitle = $CATEGORY[$catid]['catname'].'-搜索';
			include tpl_admin('content_search');
			}
	}//}}}
	function action_recycle()
	{//{{{
		if(!$allow_manage) showmessage('无管理权限！');
		$infos = $c->listinfo("catid=$catid AND status=0", 'listorder DESC,contentid DESC', $page, 20);

		$pagetitle = $CATEGORY[$catid]['catname'].'-回收站';
		include tpl_admin('content_recycle');
	}//}}}
	function action_pass()
	{//{{{
		if(!$priv_role->check('catid', $catid, 'check') && !$allow_manage) showmessage('无审核权限！');
		$allow_status = $p->get_process_status($processid);
		if($contentid=='') showmessage('请选择要批准的内容');
		$contentid = $c->contentid($contentid, 0, $allow_status);
		$process = $p->get($processid, 'passstatus');
		$c->status($contentid, $process['passstatus']);
		showmessage('操作成功！', $forward);
	}//}}}
	function action_reject()
	{//{{{
		if(!$priv_role->check('catid', $catid, 'check') && !$allow_manage) showmessage('无审核权限！');
		$allow_status = $p->get_process_status($processid);
		if($contentid=='') showmessage('请选择要批准的内容');
		$contentid = $c->contentid($contentid, 0, $allow_status);
		$process = $p->get($processid, 'rejectstatus');
		$c->status($contentid, $process['rejectstatus']);
		showmessage('操作成功！', $forward);
	}//}}}
	/**
	 * 把 status 设为 0
	 */
	function action_cancel()
	{//{{{
		if(!$allow_manage) showmessage('无管理权限！');
		$c->status($contentid, 0);
		showmessage('操作成功！', $forward);
	}//}}}
	/**
	 * 删除文章
	 */
	function action_delete()
	{//{{{
		$contentid = i::g()->int('contentid')->end();
		//if(!$allow_manage) showmessage('无管理权限！');
		// $c->delete($contentid);
		// siud::delete('content')->wis('contentid', $contentid)->ing();
		siud::delete('content')->wis('contentid', $contentid)->ing();
		showmessage('操作成功！', $forward);
	}//}}}
	function action_clean()
	{//{{{
		if(!$allow_manage) showmessage('无管理权限！');
		$c->clear();
		showmessage('操作成功！', $forward);
	}//}}}
	function action_restore()
	{//{{{
		if(!$allow_manage) showmessage('无管理权限！');
		$c->restore($contentid);
		showmessage('操作成功！', $forward);
	}//}}}
	function action_restoreall()
	{//{{{
		if(!$allow_manage) showmessage('无管理权限！');
		$c->restoreall();
		showmessage('操作成功！', $forward);
	}//}}}
	function action_listorder()
	{//{{{
		$result = $c->listorder($listorders);
		if($result)
			{
			showmessage('操作成功！', $forward);
			}
		else
			{
			showmessage('操作失败！');
			}
	}//}}}
	function action_link()
	{//{{{
		if($dosubmit)
			{
			require_once 'admin/category.class.php';
			$cat = new category($mod);
			$cat->link($catid, $category);

			showmessage('操作成功！', $forward);
			}
		else
			{
			include tpl_admin('content_link');
			}
	}//}}}
	function action_block()
	{//{{{
		if($type == 0)
			{
			$page = max(intval($page), 1);
			if($tpl == 'category')
				{
				if($child == 1)
					{
					$arrchildid = subcat('phpcms', $catid);
					$template = $template_category;
					}
				else
					{
					$template = $template_list;
					}
				}
			elseif($tpl == 'show')
				{
				$template = $MODEL[$modelid]['template_show'];
				}
			else
				{
				$template = $template_list;
				}
			}
		elseif($type == 2)
			{
			header('location:'.$url);
			}

		$catlist = submodelcat($modelid);
		$arrparentid = explode(',', $arrparentid);
		$parentid = $arrparentid[1];

		$head['title'] = $catname;
		$head['keywords'] = $meta_keywords;
		$head['description'] = $meta_description;
		include admin_template('phpcms', $template);
		include tpl_admin('block_ajax', 'phpcms');
	}//}}}
	function action_category()
	{//{{{
		$catid = intval($catid);
		if(!isset($CATEGORY[$catid])) showmessage('访问的栏目不存在！');
		$C = cache_read('category_'.$catid.'.php');
		extract($C);
		if($type == 1)
			{
			$template = $C['template'];
			}
		elseif($type == 2)
			{
			header('location:'.$url);
			}
		else
			{
			$page = max(intval($page), 0);
			if($page == 0)
				{
				$template = $C['template_category'];
				$categorys = $child ? subcat('phpcms', $catid, 0) : array();
				}
			else
				{
				$template = $C['template_list'];
				}
			}
		$head['title'] = $catname;
		$head['keywords'] = $meta_keywords;
		$head['description'] = $meta_description;

		define('BLOCK_EDIT', 1);
		include template('phpcms', $template);
	}//}}}
	function action_posid()
	{//{{{
		if(!$posid) showmessage('不存在此推荐位！');
		if(!$contentid) showmessage('没有被推荐的信息！');
		if(!$priv_role->check('posid', $posid)) showmessage('您没有此推荐位的权限！');
		foreach($contentid as $cid)
			{
			if($c->get_posid($cid, $posid)) continue;
			$c->add_posid($cid, $posid);
			}
		showmessage('批量推荐成功！', '?mod='.$mod.'&file='.$file.'&action=manage&catid='.$catid);
	}//}}}
	function action_typeid()
	{//{{{
		if(!$typeid) showmessage('不存在此类别！');
		if(!$contentid) showmessage('没有信息被选中！');
		foreach($contentid as $cid)
			{
			$c->add_typeid($cid, $typeid);
			}
		showmessage('批量加入类别到成功！', '?mod='.$mod.'&file='.$file.'&action=manage&catid='.$catid);
	}//}}}
	/**
	 * 内容列表
	 */
	function action_manage()
	{//{{{
		list($modelid, $catid) = i::g()->int('modelid', 'catid')->end();

		$where = '1 ';
		if ($catid)
			{
			$where .= "AND `catid`={$catid} ";
			}
		if ($modelid)
			{
			//$where .= "AND modelid={$modelid}";
			}
		// require_once CONTENT_ROOT . 'include/model_field.class.php';
		// $model_field = new model_field($modelid);

		if($typeid) $where .= " AND `typeid`='$typeid' ";
		if($areaid) $where .= " AND `areaid`='$areaid' ";
		if($inputdate_start) $where .= " AND `inputtime`>='".strtotime($inputdate_start.' 00:00:00')."'"; else $inputdate_start = date('Y-m-01');
		if($inputdate_end) $where .= " AND `inputtime`<='".strtotime($inputdate_end.' 23:59:59')."'"; else $inputdate_end = date('Y-m-d');
		if($q)
			{
			if($field == 'title')
				{
				$where .= " AND `title` LIKE '%$q%'";
				}
			elseif($field == 'userid')
				{
				$userid = intval($q);
				if($userid)	$where .= " AND `userid`=$userid";
				}
			elseif($field == 'username')
				{
				$userid = userid($q);
				if($userid)	$where .= " AND `userid`=$userid";
				}
			elseif($field == 'contentid')
				{
				$contentid = intval($q);
				if($contentid) $where .= " AND `contentid`=$contentid";
				}
			}
		// $CMMR = conm_CMMR($modelid);
		// list($result, $pages, $total) = siud::select('c_' . $CMMR['tablename'])->pagesize(20)->ing();
		list($result, $pages, $total) = siud::select('content')->where($where)->pagesize(20)->ing();
		// $infos = $c->listinfo($where, '`listorder` DESC,`contentid` DESC', $page, 20);

		$pagetitle = $CATEGORY[$catid]['catname'].'-管理';
		// foreach($POS AS $key => $p)
		// {
		// if($priv_role->check('posid', $key))
		// {
		// $POSID[$key] = $p;
		// }
		// }
		// $POS = $POSID;
		// $POS[0] = '不限推荐位';

		include tpl_admin('content_manage');
	}//}}}
}

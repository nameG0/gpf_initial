<?php
/**
 * 管理文章栏目
 * 
 * @package default
 * @filesource
 */
class ctrl_a_category
{
	private $o_cate = NULL;

	function __construct()
	{//{{{
		admin_check();

		// defined('IN_PHPCMS') or exit('Access Denied');

		// require_once CATEGORY_ROOT . 'include/cache.func.php';
		// require_once CATEGORY_ROOT . 'include/tree.class.php';
		require_once CMS_PATH . "include/category.class.php";
		// //require_once CATEGORY_ROOT . 'include/admin/content.class.php';

		// //$CATEGORY = category_cache();
		// $tree = new tree;
		$this->o_cate = new category('cms');
		// //$c = new content();

		// $submenu = array(
		// array('添加栏目', admin_url("..add")),
		// array('管理栏目', admin_url("..manage")),
		// array('更新缓存', admin_url("..updatecache")),
		// );

		// $action = $action ? $action : 'manage';
		// if(!$forward) $forward = '?mod='.$mod.'&file='.$file.'&action=manage';
	}//}}}
	function action_add()
	{//{{{
		if (isset($_POST["dosubmit"]))
			{
			list($category, $setting) = i::p()->val('category', 'setting')->end();

			if(!$category['catname']) showmessage($LANG['category_name_not_null']);
			$category['catname'] = trim($category['catname']);
			$category['catdir'] = trim($category['catdir']);
			$catid = $this->o_cate->add($category, $setting);
			//$priv_group->update('catid', $catid, $priv_groupid);
			//$priv_role->update('catid', $catid, $priv_roleid);
			// $forward = admin_url("..add");
			//cache_common();
			cache_category();
			// showmessage('栏目添加成功！待栏目全部添加完成，请修复栏目', $forward);
			?>
<script type="text/javascript">
<!--
if ('function' === typeof(top.__done))
	{
	top.__done(<?=intval($category['parentid'])?>);
	}
//-->
</script>
			<?php
			// echo '栏目添加成功！待栏目全部添加完成，请修复栏目';
			}
		else
			{
			list($type, $modelid, $parentid) = i::gp()->val('type')->int('modelid', 'parentid')->end();
			if('' === $type)
				{
				$modelid = 0;
				if(isset($catid) && isset($CATEGORY[$catid]))
					{
					$modelid = $CATEGORY[$catid]['modelid'];
					}
				}
			/* 如果没有设置是否生成静态选项，那么则按照模型中的初始化 */
			$ishtml = $MODEL[$modelid]['ishtml'];
			$forward = '?mod='.$mod.'&file='.$file.'&action=manage';
			include tpl_admin('category_add');
			}
	}//}}}
	function action_edit()
	{//{{{
		global $CATEGORY;

		list($catid, $parentid) = i::g()->int('catid', 'parentid')->end();

		if(!$catid) showmessage($LANG['illegal_parameters']);
		if($catid == $category['parentid']) showmessage('当前栏目不能与上级栏目相同');
		if (isset($_POST["dosubmit"]))
			{
			list($category, $setting) = i::p()->val('category', 'setting')->end();

			if(!$category['catname']) showmessage($LANG['category_name_not_null']);
			$category['catname'] = trim($category['catname']);
			$category['catdir'] = trim($category['catdir']);
			$this->o_cate->edit($catid, $category, $setting);
			if($createtype_application && $CATEGORY[$catid]['child'])
				{
				$this->o_cate->update_child($catid);
				}
			//$priv_group->update('catid', $catid, $priv_groupid);
			//$priv_role->update('catid', $catid, $priv_roleid);
			// showmessage('操作成功！开始更新网站地图...', gpf::url("..manage"));
			?>
<script type="text/javascript">
<!--
if ('function' === typeof(top.__done))
	{
	top.__done();
	}
//-->
</script>
			<?php
			}
		else
			{
			$category = $this->o_cate->get($catid);
			@extract(hd("html", $category));
			/* 如果没有设置是否生成静态选项，那么则按照模型中的初始化 */
			if(!isset($ishtml))
				{
				$ishtml = $MODEL[$modelid]['ishtml'];
				}
			// if($type == 1)
			// {
			// $priv_roleids = $priv_role->get_roleid('catid', $catid);
			// $priv_roleids = implodeids($priv_roleids);
			// $priv_groupids = $priv_group->get_groupid('catid', $catid);
			// $priv_groupids = implodeids($priv_groupids);
			// }
			include tpl_admin('category_edit');
			}
	}//}}}
	function action_repair()
	{//{{{
		$cat->repair();
		showmessage('更新成功', '?mod='.$mod.'&file='.$file.'&action=manage');
	}//}}}
	function action_delete()
	{//{{{
		log::is_print(false);

		$catid = i::g()->int('catid')->end();
		$LANG['illegal_parameters'] = '不行';
		$LANG['operation_success'] = '操作成功';
		global $CATEGORY;

		if(!array_key_exists($catid, $CATEGORY))
			{
			//输出 2 表示失败
			echo 2;
			// showmessage($LANG['illegal_parameters'], gpf::url("..manage"));
			}
		$this->o_cate->delete($catid);
		//输出 1 表示成功
		echo 1;
		// showmessage($LANG['operation_success'], gpf::url("..updatecache") . '&forward='.urlencode(gpf::url('..manage')));
	}//}}}
	function action_join()
	{//{{{
		if($dosubmit)
			{
			$targetcatid = intval($targetcatid);
			$sourcecatid = intval($sourcecatid);
			if(!$targetcatid || !$sourcecatid) showmessage('源栏目或目标栏目没有选择', $forward);
			if($targetcatid==$sourcecatid) showmessage($LANG['source_not_same_as_distinct_category'],$forward);

			$target = $cat->get($targetcatid);
			if($target['child']==1) showmessage($LANG['distinct_category_has_child_banned_add_information']);

			if($target['arrparentid'])
				{
				$arrparentid = explode(",", $r['arrparentid']);
				if(in_array($sourcecatid,$arrparentid)) showmessage($LANG['distinct_is_the_child_of_source_category_cannot_join']);
				}

			$source = $cat->get($sourcecatid);
			$cat->join($sourcecatid, $targetcatid);

			showmessage($LANG['operation_success'], $forward);
			}
		else
			{
			foreach($CATEGORY AS $catid=>$c)
				{
				if($c['type']!=0) unset($CATEGORY[$catid]);
				}
			include tpl_admin('category_join');
			}
	}//}}}
	function action_listorder()
	{//{{{
		$listorder = i::p()->val('listorder')->end();

		$this->o_cate->listorder($listorder);
		?>
		<script type="text/javascript">
		<!--
		parent.cateTree_listorder();
		//-->
		</script>
		<?php
		// showmessage($LANG['operation_success'], $forward);
	}//}}}
	function action_recycle()
	{//{{{
		$cat->recycle($catid);
		showmessage($LANG['operation_success'], '?mod='.$mod.'&file='.$file.'&action=manage');
	}//}}}
	function action_checkcategory()
	{//{{{
		if($CATEGORY[$targetcatid]['modelid'] != $CATEGORY[$sourcecatid]['modelid'])
			{
			echo -1;
			}
		elseif($CATEGORY[$targetcatid]['child'])
			{
			echo -2;
			}
		elseif($targetcatid == $sourcecatid)
			{
			echo -3;
			}
		elseif(in_array($targetcatid,explode(',',$CATEGORY[$sourcecatid]['arrchildid'])))
			{
			echo -4;
			}
	}//}}}
	/**
	 * 管理栏目数据
	 */
	function action_manage()
	{//{{{
		// $parentid = i::g()->int('catid')->end();

		// $data = $this->o_cate->listinfo($parentid);
		// include tpl_admin('category_manage');
		include tpl_admin('category_tree');
	}//}}}
	function action_urlrule()
	{//{{{
		$ishtml = intval($ishtml);
		$category_urlruleid = intval($category_urlruleid);
		echo form::select_urlrule('phpcms', 'category', $ishtml, 'setting[category_urlruleid]', 'category_urlruleid', $category_urlruleid);
	}//}}}
	function action_show_urlrule()
	{//{{{
		$ishtml = intval($ishtml);
		$show_urlruleid = intval($show_urlruleid);
		echo form::select_urlrule('phpcms', 'show', $ishtml, 'setting[show_urlruleid]', 'show_urlruleid', $show_urlruleid);
	}//}}}
	function action_checkdir()
	{//{{{
		if(!preg_match("/[a-zA-Z0-9_-]+$/i",$value)) exit('栏目目录名称只能为字母、数字、下划线，中划线');
		if($catdir == trim($value)) exit('success');
		foreach($CATEGORY AS $k=>$v)
			{
			if($v['parentid'] == $parentid && $v['catdir'] == trim($value)) exit('栏目目录名称不能重复');
			}
		if($parentid == 0 && isset($MODULE[$value])) exit('栏目目录名称不能重复');
		exit('success');
	}//}}}
	function action_checkname()
	{//{{{
		if($catname == trim($value)) exit('success');
		foreach($CATEGORY AS $k=>$v)
			{
			if($v['parentid'] == $parentid && $v['catname'] == trim($value)) exit('栏目名称不能重复');
			}
		exit('success');
	}//}}}
	function action_more()
	{//{{{
		if($dosubmit)
			{
			$category['catname'] = array_map("trim", $category['catname']);
			$category['catdir'] = array_map("trim",$category['catdir']);
			$c = $s = array();
			$c['type'] = $category['type'];
			$c['parentid'] = $category['parentid'];
			$c['modelid'] = $category['modelid'];

			$s['presentpoint'] = $setting['presentpoint'];
			$s['defaultchargepoint'] = $setting['defaultchargepoint'];
			$s['repeatchargedays'] = $setting['repeatchargedays'];
			$s['template_category'] = $setting['template_category'];
			$s['template_list'] = $setting['template_list'];
			$s['template_show'] = $setting['template_show'];
			$s['template_print'] = $setting['template_print'];
			foreach($category['catname'] AS $key => $value)
				{
				if(!empty($value) && !empty($category['catdir'][$key]))
					{
					$c['catname'] = $category['catname'][$key];
					$c['catdir'] = $category['catdir'][$key];
					$c['ismenu'] = $category['ismenu'][$key];

					$s['workflowid'] = $setting['workflowid'][$key];
					$s['meta_title'] = $setting['meta_title'][$key];
					$s['meta_keywords'] = $setting['meta_keywords'][$key];
					$s['meta_description'] = $setting['meta_description'][$key];
					$catid = $cat->add($c, $s);
					$priv_group->update('catid', $catid, $priv_groupid);
					$priv_role->update('catid', $catid, $priv_roleid);
					cache_common();
					}
				else
					{
					unset($category['catname'][$key]);
					unset($category['catdir'][$key]);
					continue;
					}
				}
			if(!$catid) showmessage('添加失败', '?mod=phpcms&file=category&action=more');
			showmessage('添加成功', '?mod=phpcms&file=category&action=more');
			}
		else
			{
			if(!isset($type))
				{
				$modelid = 0;
				if(isset($catid) && isset($CATEGORY[$catid]))
					{
					$modelid = $CATEGORY[$catid]['modelid'];
					}
				}
			include tpl_admin('category_more');
			}
	}//}}}
	function action_update_search()
	{//{{{
		if($dosubmit)
			{
			if(!$count)
				{
				if(!isset($catids) || $catids[0] == 0) 
					{
					foreach($CATEGORY as $cid=>$v)
						{
						if($v['type'] == 0) $catids[] = $cid;
						}
					}
				foreach($catids as $k=>$id)
					{
					if($CATEGORY[$id]['type'] == 0 && $MODEL[$CATEGORY[$id]['modelid']]['enablesearch'] && $CATEGORY[$id]['child']==0)
						{
						$cids[] = $id;
						}
					}
				if($cids)
					{
					cache_write('search_category_'.$_userid.'.php', $cids);
					$count = count($cids);
					$forward = urlencode($forward);
					showmessage('开始遍历栏目...', "?mod=$mod&file=$file&action=$action&forward=$forward&pagesize=$pagesize&dosubmit=1&count=$count");
					}
				else
					{
					showmessage('更新完成！', "?mod=$mod&file=$file&action=$action");
					}
				}
			else
				{
				$catids = cache_read('search_category_'.$_userid.'.php');
				$page = max(intval($page), 1);
				if($page == 1)
					{
					$catid = array_shift($catids);
					cache_write('search_category_'.$_userid.'.php', $catids);
					}
				$catname = $CATEGORY[$catid]['catname'];

				if($CATEGORY[$catid]['child']==0)
					{
					$offset = $pagesize*($page-1);
					if($page == 1)
						{
						$contents = cache_count("SELECT COUNT(*) AS `count` FROM `".DB_PRE."content` WHERE catid=$catid AND status=99");
						$total = $contents;
						$pages = ceil($total/$pagesize);
						}
					$max = min($offset+$pagesize, $total);
					for($i=$offset; $i<$max; $i++)
						{
						$c->update_search($catid, $i);
						}
					}
				if($pages > $page)
					{
					$page++;
					$percent = round($max/$total, 2)*100;
					$message = "正在更新 <font color='blue'>$catname</font> 栏目中内容的全站搜索，共需更新 <font color='red'>$total</font> 篇内容<br />已更新 <font color='red'>{$max}</font> 篇内容（<font color='red'>{$percent}%</font>）";
					$forward = url_par("catid=$catid&page=$page&pages=$pages&total=$total");
					}
				elseif($catids)
					{
					$message = "<font color='blue'>$catname</font> 栏目更新完成！";
					$forward = url_par("catid=0&page=0&pages=0&total=0");
					}
				else
					{
					cache_delete('search_category_'.$_userid.'.php');
					$message = "更新完成！";
					$forward = '?mod=phpcms&file=category&action=update_search';
					}
				showmessage($message, $forward);
				}
			}
		else
			{
			include tpl_admin('category_search');
			}
	}//}}}
	function action_updatecache()
	{//{{{
		log::is_print(false);
		//cache_common();
		cache_category();
		//输出 1 表示处理成功。
		echo 1;
		// $LANG['category_cache_update_success'] = '缓存更新成功';
		// showmessage($LANG['category_cache_update_success'], gpf::url("..manage"));
	}//}}}
	/**
	 * 更新所有栏目的前台访问URL
	 */
	function action_update_url()
	{//{{{
		log::is_print(false);

		global $CATEGORY;
		$o_rdb = rdb::obj();
		foreach ($CATEGORY as $k => $v)
			{
			if (!('cms' == $v['module'] && $v['type'] < 2))
				{
				continue;
				}
			if ($v['child'])
				{
				$url = gpf::url("cms.content.category.&catid={$k}");
				}
			else
				{
				$url = gpf::url("cms.content.list.&catid={$k}");
				}
			$o_rdb->update(RDB_PRE . 'category', array("url" => $url,), "catid = {$k}");
			// siud::save('category')->pk('catid')->data(array("catid" => , ))->ing();
			}
		//输出 1 表示更新成功
		echo 1;
		// showmessage('URL更新完成');
	}//}}}
	/**
	 * 编辑单网页类栏目的网页内容
	 */
	function action_edit_content()
	{//{{{
		global $CATEGORY;

		$catid = i::g()->int('catid')->end();
		if (!$catid || !$CATEGORY[$catid])
			{
			showmessage('栏目不存在');
			}

		if (isset($_POST["dosubmit"]))
			{
			$content = i::p()->val('content')->end();
			siud::save('category')->pk('catid')->data(array("catid" => $catid, "content" => $content,))->ing();
			showmessage('更新成功');
			}
		$data = siud::find('category')->tfield('content')->wis('catid', $catid)->ing();
		include tpl_admin('category_edit_content');
	}//}}}
	/**
	 * 返回树状管理栏目页子栏目数据。
	 */
	function action_get_child()
	{//{{{
		log::is_print(false);
		global $CATEGORY;

		$pid = i::g()->int('pid')->end();
		$cate_list = array();
		foreach ($CATEGORY as $k => $v)
			{
			if ($pid == $v['parentid'])
				{
				$cate_list[$k] = $v;
				}
			}
		if (!$cate_list)
			{
			?>
		<ul><li>无</li></ul>
			<?php
			exit;
			}
		?>
		<ul >
			<?php
			foreach ($cate_list as $k => $v)
				{
			?>
			<li onclick="cateTree_click(this, event);" onmouseover="cateTree_tool(this, event);" catid="<?=$v['catid']?>">
			<input type="text" name="listorder[<?=$v['catid']?>]" value="<?=$v['listorder']?>" style="width:30px;" />
			<span><?=$v['catname']?></span>
			<span type="tool" style="display:none;">|
				<span onclick="cateTreeTool_refresh_child(this, event);">刷新</span>|
				<a href="<?=gpf::url("..go.&catid={$v['catid']}")?>" target="_blank">访问</a> |
				<span onclick="openwinx('<?=gpf::url("..edit.&catid={$v['catid']}&parentid={$v['parentid']}")?>', 'edit', 600, 600);stopBubble(event);">修改</span>|
				<span onclick="openwinx('<?=gpf::url("..add.&parentid={$v['catid']}")?>', 'add', 600, 600);stopBubble(event);">添加子栏目</span>|
				<span onclick="cateTree_del('<?=$v['catid']?>', event);">删除</span>
			</span>
			<span type="child" is_init="false" is_display="true"></span></li>
			<?php
				}
			?>
		</ul>
		<?php
	}//}}}
	/**
	 * 跳转到栏目对应的前台 URL
	 */
	function action_go()
	{//{{{
		global $CATEGORY;
		$catid = i::g()->int('catid')->end();
		header("location:{$CATEGORY[$catid]['url']}");
	}//}}}
}

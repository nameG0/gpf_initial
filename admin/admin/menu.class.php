<?php
class ctrl_menu
{
	private $m = NULL;

	function __construct()
	{//{{{
		gmod::inc('admin', 'include/menu.class.php');
		// require_once 'menu.inc.php';
		// require_once 'menu.class.php';
		$this->m = new menu();

		// if (!$action) $action = 'manage';
		// if (!$forward) $forward = '?mod='.$mod.'&file='.$file.'&action=manage';
	}//}}}
	function action_index()
	{//{{{
		$parentid = gpf::get('parentid', 'intval', 0);

		if ($parentid)
			{
			$r = $this->m->get($parentid);
			$parentname = $r['name'];
			}
		$forward = URL;
		$where = "`parentid` = '$parentid'";
		$infos = $this->m->listinfo($where, 'listorder, menuid', $page, 20);
		include gpf_tpl('admin', 'admin/menu_manage');
	}//}}}
	function action_add()
	{//{{{
		if ($dosubmit)
			{
			$info['roleids'] = implode(',', $roleids);
			$info['groupids'] = implode(',', $groupids);
			$menuid = $this->m->add($info);
			if ($menuid)
				{
				showmessage('操作成功！', $forward);
				}
			else
				{
				showmessage('操作失败！');
				}
			}
		else
			{
			if (!isset($parentid)) $parentid = 0;
			if (!isset($target)) $target = 'right';
			include gpf_tpl('admin', 'admin/menu_add');
			}
	}//}}}
	function action_edit()
	{//{{{
		$menuid = gpf::get('menuid', 'intval');

		if ($dosubmit)
			{
			$info['roleids'] = implode(',', $roleids);
			$info['groupids'] = implode(',', $groupids);
			$result = $this->m->edit($menuid, $info);
			if ($result)
				{
				showmessage('操作成功！', $forward);
				}
			else
				{
				showmessage('操作失败！');
				}
			}
		else
			{
			$info = $this->m->get($menuid);
			if (!$info)
				{
				gpf::err('指定的菜单不存在！', __FILE__, __LINE__, __CLASS__.'->'.__FUNCTION__);
				}
			extract($info);
			if ($parentid)
				{
				$parent = $this->m->get($parentid);
				$parentname = $parent['name'];
				}
			else
				{
				$parentname = '无';
				}
			include gmod::path('admin', 'include/menu.inc.php');
			include gpf_tpl('admin', 'admin/menu_edit');
			}
	}//}}}
	function action_delete()
	{//{{{
		$result = $this->m->delete($menuid);
		if ($result)
			{
			showmessage('操作成功！', $forward);
			}
		else
			{
			showmessage('操作失败！');
			}
	}//}}}
	function action_listorder()
	{//{{{
		$result = $this->m->listorder($listorder);
		if ($result)
			{
			showmessage('操作成功！', $forward);
			}
		else
			{
			showmessage('操作失败！');
			}
	}//}}}
	function action_disable()
	{//{{{
		$result = $this->m->disable($menuid, $disabled);
		if ($result)
			{
			showmessage('操作成功！', $forward);
			}
		else
			{
			showmessage('操作失败！');
			}
	}//}}}
	function action_getchild()
	{//{{{
		$array = array();
		$infos = $this->m->listinfo("parentid='$parentid'", 'listorder, menuid', $page, 20);
		foreach ($infos as $k=>$v)
			{
			$array[$v['menuid']] = $v['name'];
			}
		if (!$parentid || $array)
			{
			$array[0] = $parentid ? '请选择' : '无';
			ksort($array);
			echo form::select($array, 'setparentid', 'setparentid', $parentid, 1, '', 'onchange="if (this.value>0){getchild(this.value);myform.parentid.value=this.value;this.disabled=true;}"');
			}
	}//}}}
	function action_add_mymenu()
	{//{{{
		if (!isset($target) || empty($target)) $target = 'right';
		if (CHARSET != 'utf-8') $name = iconv('utf-8', DB_CHARSET, $name);
		$info = array('parentid'=>'99', 'name'=>$name, 'url'=>urldecode($url), 'target'=>$target);
		echo $this->m->add($info) ? 1 : 0;
	}//}}}
	function action_get_menu_list()
	{//{{{
		gpf::log_is_print(false);
		$menuid = gpf::get('menuid', 'intval');

		$data = $this->m->get_child($menuid);
		$data = str_charset(CHARSET, 'utf-8', $data);
		$max = array_slice($data, -1);
		$data['max'] = $max[0]['menuid'];
		$data = json_encode($data);
		if (PHP_OS < 5.0) header('Content-type: text/html; charset=utf-8');
		echo $data;
	}//}}}
	/**
	 * 新的取子菜单数据的控制器。
	 */
	function action_child_menu()
	{//{{{
		gpf::log_is_print(false);
		$menuid = gpf::get('menuid', 'intval');
		$depth = gpf::get('depth', 'intval');

		$data = $this->m->get_child($menuid);
		include gpf_tpl('admin', 'admin/_child_menu');
	}//}}}
	function action_menu_pos()
	{//{{{
		gpf::log_is_print(false);
		$menuid = gpf::get('menuid', 'intval');

		$data = $this->m->get_parent($menuid);
		krsort($data);
		$html = '';
		$i=0;
		foreach ($data as $val)
			{
			$target = '';
			if ($val['isfolder'])
				{
				$href = "javascript:get_menu('".$val['menuid']."','tree_".$val['menuid']."',$i)";
				}
			else
				{
				$href = $val['url'];
				$target = " target='".$val['target']."'";
				}
			$html .= "<a href=\"$href\" $target>".$val['name']."</a>";
			$i++;
			}
		echo '<strong>当前位置：</strong>'.$html;
	}//}}}
	function ajax_menu()
	{//{{{
		if (CHARSET != 'utf-8') $menuname = iconv('utf-8', DB_CHARSET, $menuname);
		$data = $this->m->listinfo("`name` like '%$menuname%'");
		foreach ($data as $key=>$val)
			{
			if ($val['name'] && $val['url'] && $val['isfolder']!=1)
				{
				$d[$key]['name'] = iconv(CHARSET, 'utf-8',$val['name']);
				$d[$key]['url'] = iconv(CHARSET, 'utf-8',$val['url']);
				}
			}
		$data = json_encode($d);
		header('Content-type: text/html; charset=utf-8');
		echo $data;
	}//}}}
}

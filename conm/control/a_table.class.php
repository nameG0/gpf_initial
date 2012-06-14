<?php
/**
 * 单表模型类型内容管理
 * 
 * @package default
 * @filesource
 */
class ctrl_a_table
{
	function index()
	{//{{{
		echo 'this is a_table';
	}//}}}
	/**
	 * 管理内容模型
	 * @param int $modelid
	 */
	function manage()
	{//{{{
		$modelid = _g('modelid', 'int');

		$CMMr = conm_model_get($modelid);
		if (!$CMMr)
			{
			showmessage('模型不存在');
			}

		$tablename = $CMMr['tablename'];
		$field = $CMMr['setting']['list_show_field'];
		if (!$field)
			{
			$field = '*';
			}

		list($result, $pages, $total) = siud::select($tablename)->tfield($field)->pagesize(15)->ing();
		?>
		<a href="<?=gpf::url('..add')?>">添加</a>
		<hr />
		<?php
		foreach ($result as $k => $r)
			{
			?>
			<div >
				<?=$r['title']?>
				<a href="<?=gpf::url(".a_model_field.manage.&modelid={$r['modelid']}")?>">管理字段</a>
				<a href="<?=gpf::url("..sync.&modelid={$r['modelid']}")?>">同步</a>
				<a href="<?=gpf::url("..edit.&modelid={$r['modelid']}")?>">修改</a>
				<a href="<?=gpf::url("..delete.&modelid={$r['modelid']}")?>">删除</a>
				|
				<a href="<?=gpf::url(".a_content.manage.&modelid={$r['modelid']}")?>">管理内容</a>
			</div>
			<?=$pages?>
			<?php
			}
		// $infos = $model->listinfo('modeltype=0', 'modelid', 1, 100);
		// include admin_tpl('model_manage');
	}//}}}
	/**
	 * 保存一条内容
	 */
	function save()
	{//{{{
		
	}//}}}
	/**
	 * 显示内容编辑表单
	 */
	function form()
	{//{{{
		if (isset($_POST["dosubmit"]))
			{
			$data = _p('data');

			$db = rdb::obj();
			$db->insert(RDB_PRE . 'model', $data);

			$modelid = $db->insert_id();
			if ($modelid)
				{
				//cache_model();
				// showmessage('操作成功！', admin_url(".model_field.manage.&modelid={$modelid}"));
				echo "操作成功！";
				}
			else
				{
				// showmessage('操作失败！');
				echo '操作失败！';
				}
			}
		else
			{
			include tpl_admin('model_add');
			}
	}//}}}
	function delete()
	{//{{{
		$result = $model->delete($modelid);
		if($result)
			{
			showmessage('操作成功！', $forward);
			}
		else
			{
			showmessage('操作失败！', $forward);
			}
	}//}}}
	function disable()
	{//{{{
		$result = $model->disable($modelid, $disabled);
		if($result)
			{
			showmessage('操作成功！', $forward);
			}
		else
			{
			showmessage('操作失败！');
			}
	}//}}}
	function urlrule()
	{//{{{
		echo $type == 'category' ? form::select_urlrule('phpcms', 'category', $ishtml, 'info[category_urlruleid]', 'category_urlruleid', $category_urlruleid) : form::select_urlrule('phpcms', 'show', $ishtml, 'info[show_urlruleid]', 'show_urlruleid', $show_urlruleid);
	}//}}}
}

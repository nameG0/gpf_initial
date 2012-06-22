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

		$CMMr = conm_CMMR($modelid);
		if (!$CMMr)
			{
			showmessage('模型不存在');
			}

		$tablename = $CMMr['tablename'];
		$pk = $CMMr['setting']['pk'];
		$field = $CMMr['setting']['list_show_field'];
		if (!$field)
			{
			$field = '*';
			}

		list($result, $pages, $total) = siud::select($tablename)->tfield($field)->pagesize(15)->ing();
		?>
		<a href="<?=gpf::url('..form..modelid')?>">添加</a>
		<hr />
		<?php
		foreach ($result as $k => $r)
			{
			?>
			<div >
				<?php
				foreach ($r as $f => $v)
					{
					echo $v, '|';
					}
				?>
				<a href="<?=gpf::url("..form.&id={$r[$pk]}.modelid")?>">修改</a>
				<a href="<?=gpf::url("..delete.&id={$r[$pk]}.modelid")?>">删除</a>
			</div>
			<?=$pages?>
			<?php
			}
		// $infos = $model->listinfo('modeltype=0', 'modelid', 1, 100);
		// include admin_tpl('model_manage');
	}//}}}
	/**
	 * 保存一条内容
	 * @param int $modelid 模型ID
	 * @param array $info 内容数据数组
	 */
	function save()
	{//{{{
		$modelid = _g('modelid', 'int');
		$data = _p('data');
		$keep = _p('keep');

		//todo 应取也模型缓存,缓存内标有模型表主键键名
		$CMMR = conm_CMMR($modelid);
		$data = conm_fill($CMMR['CMFL'], $data, $keep);
		$pk = $CMMR['setting']['pk'];
		siud::save($CMMR['tablename'])->pk($pk)->data($data)->ing();
		$field_use_id = conm_use_id($CMMR['CMFL']);
		if (!$data[$pk] && $field_use_id)
			{
			//第二次填充,填充需要取得主键ID后才能填充的字段。
			$data[$pk] = $insert_id;
			conm_fill($CMMR['CMFL'], $data, $keep);
			$data_update = array($pk => $insert_id,);
			foreach ($field_use_id as $f)
				{
				$data_update[$f] = $dat[$f];
				}
			siud::save($CMMR['tablename'])->pk($pk)->data($data_update)->ing();
			}
		?>
		成功.
		<br />
		<a href="<?=gpf::url('..manage..modelid')?>">管理</a>
		<?php
	}//}}}
	/**
	 * 显示内容编辑表单
	 * @param int $modelid 模型ID
	 * @param int $id 修改内容时传入内容ID
	 */
	function form()
	{//{{{
		$modelid = _g('modelid', 'int');
		$id = _g('id', 'int');

		$CMMR = conm_CMMR($modelid);
		if (!$id)
			{
			$data = array();
			}
		else
			{
			$data = siud::find($CMMR['tablename'])->wis($CMMR['setting']['pk'], $id)->ing();
			}
		?>
		<a href="<?=gpf::url('..manage..modelid')?>">管理</a>
		<hr />
		<form action="<?=gpf::url('..save..modelid')?>" method="POST" enctype="multipart/form-data">
		<?php
		$form = conm_form($CMMR['CMFL'], $data);
		foreach ($form as $f => $html)
			{
			echo $html['name'], ':', $html['form'], "<hr/>\n";
			}
		?>
		<input type="submit" name="dosubmit" value="提交" />
		</form>
		<?php
	}//}}}
	/**
	 * 删除内容
	 * @param int $modelid 模型ID
	 * @param int $id 内容ID
	 */
	function delete()
	{//{{{
		$modelid = _g('modelid', 'int');
		$id = _g('id', 'int');

		$CMMr = conm_model_get($modelid);
		siud::delete($CMMr['tablename'])->wis('id', $id)->ing();
		?>
		成功
		<br />
		<a href="<?=gpf::url('..manage..modelid')?>">管理</a>
		<?php
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

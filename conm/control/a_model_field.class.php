<?php 
/**
 * 字段类型 控制器
 * 2012-05-09
 * 
 * @package default
 * @filesource
 */
// defined('IN_PHPCMS') or exit('Access Denied');
// @set_time_limit(600);

class ctrl_a_model_field
{
	function __construct()
	{//{{{
		// $fields = include CONTENT_ROOT . 'fields/fields.inc.php';
		// require_once CONTENT_ROOT . 'include/model_field.class.php';
		// require_once CONTENT_ROOT . 'include/model.class.php';
		// require_once CONTENT_ROOT . 'include/content.func.php';
		// $modelid = intval($modelid);
		// $field = new model_field($modelid);
		// $model = new model();
		// $modelinfo = $model->get($modelid);
		// $modelname = $modelinfo['name'];
		// $tablename = $field->tablename;

		// $submenu = array(
			// array('修改模型', admin_url(".model.edit..modelid")),
			// array('管理模型', admin_url(".model.manage")),
			// array('添加字段', admin_url("..add..modelid")),
			// array('管理字段', admin_url("..manage..modelid")),
			// array('预览模型', admin_url("..preview..modelid")),
		// );
		// $menu = admin_menu($modelname.'模型字段管理', $submenu);
	}//}}}
	function save()
	{//{{{
		a::i($data)->fpost('data')->apost('setting')->aget('modelid')->sers('setting');

		siud::save('model_field')->pk('field_id')->data($data)->error($error)->ing();
		if ($error)
			{
			echo $error;
			}
		?>
		<a href="<?=gpf::url("..manage..modelid")?>">管理字段</a>
		<a href="<?=gpf::url("..form..modelid")?>">添加字段</a>
		<?php
	}//}}}
	/**
	 * 字段类型编辑表单
	 * @param int $modelid 字段所属模型ID
	 * @param int $field_id 修改字段时传入
	 */
	function form()
	{//{{{
		$field_id = _g('field_id', 'int');

		if ($field_id)
			{
			$data = siud::find('model_field')->wis('field_id', $field_id)->ing();
			a::i($data)->unsers('setting');
			}

		?>
		<a href="<?=gpf::url('..manage..modelid')?>">管理字段</a>
		<hr />
		<?php
		include tpl_admin('field_form');
	}//}}}
	function manage()
	{//{{{
		$modelid = _g('modelid', 'intval');
		$modelid = intval($modelid);
		if (!$modelid)
			{
			echo 'require modelid';
			exit;
			}

		$sql = "SELECT * FROM " . RDB_PRE . "model_field WHERE modelid = {$modelid}";
		$result = rdb::obj()->select($sql);

		?>
		<a href="<?=gpf::url('.a_model.manage')?>">管理模型</a>
		<a href="<?=gpf::url(".a_model.sync..modelid")?>">同步</a>
		<a href="<?=gpf::url('..form..modelid')?>">添加</a>
		<hr />
		<?php
		foreach ($result as $k => $r)
			{
			?>
			<div >
				<?=$r['field']?>(<?=$r['formtype']?>)
				<a href="<?=gpf::url("..form.&field_id={$r['field_id']}.modelid")?>">修改</a>
				<a href="<?=gpf::url("..delete.&field_id={$r['field_id']}.modelid")?>">删除</a>
			</div>
			<?php
			}
		// include tpl_admin('model_field_manage', 'conm');
	}//}}}
	function copy()
	{//{{{
		if($dosubmit)
			{
			$info['modelid'] = $modelid;
			$info['formtype'] = $formtype;
			$info['unsetgroupids'] = isset($unsetgroupids) ? implodeids($unsetgroupids) : '';
			$info['unsetroleids'] = isset($unsetroleids) ? implodeids($unsetroleids) : '';
			$result = $field->add($info, $setting);
			if($result)
				{
				extract($setting);
				extract($info);
				require_once CONTENT_ROOT . 'fields/'.$formtype.'/field_add.inc.php';
				showmessage('操作成功！', $forward);
				}
			else
				{
				showmessage('操作失败！');
				}
			}
		else
			{
			$info = $field->get($fieldid);
			if(!$info) showmessage('指定的字段不存在！');
			extract(new_htmlspecialchars($info));
			$unsetgroups = form::checkbox($GROUP, 'unsetgroupids', 'unsetgroupids', $unsetgroupids, 5);
			$unsetroles = form::checkbox($ROLE, 'unsetroleids', 'unsetroleids', $unsetroleids, 5);
			require_once CONTENT_ROOT . 'fields/patterns.inc.php';
			include tpl_admin('model_field_copy');
			}
	}//}}}
	/**
	 * 删除字段
	 * @param int $field_id 所删除的字段ID
	 * @param int $modelid 字段所属模型ID
	 */
	function delete()
	{//{{{
		$field_id = _g('field_id', 'int');
		$modelid = _g('modelid', 'int');

		siud::delete('model_field')->wis('field_id', $field_id)->ing();
		?>
		<a href="<?=gpf::url("..manage..modelid")?>">管理字段</a>
		<?php
	}//}}}
	function listorder()
	{//{{{
		$result = $field->listorder($info);
		if($result)
			{
			showmessage('操作成功！', $forward);
			}
		else
			{
			showmessage('操作失败！');
			}
	}//}}}
	function disable()
	{//{{{
		$result = $field->disable($fieldid, $disabled);
		if($result)
			{
			showmessage('操作成功！', $forward);
			}
		else
			{
			showmessage('操作失败！');
			}
	}//}}}
	function preview()
	{//{{{
		require CONTENT_ROOT . 'include/content_form.class.php';
		$content_form = new content_form($modelid);
		$forminfos = $content_form->get();
		include tpl_admin('content_add');
	}//}}}
	function checkfield()
	{//{{{
		if(!$field->check($value))
			{
			exit('只能由英文字母、数字和下划线组成，必须以字母开头');
			}
		elseif($field->exists($value))
			{
			exit('字段名已存在');
			}
		else
			{
			exit('success');
			}
	}//}}}
	/**
	 * AJAX 使用,显示指定字段类型的设置表单.
	 * @param string $field_id CMFTid
	 */
	function ajax_setting()
	{//{{{
		log::is_print(false);

		$CMFTid = _g('field_id');

		cm_f_field_load($CMFTid);
		list($mod, $name) = explode("/", $CMFTid);
		$func_name = "cm_ft_{$mod}__{$name}_setting";
		if (!function_exists($func_name))
			{
			exit;
			}

		$func_name(array());
	}//}}}
}

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
require_once GM_PATH_CONM . "include/field.func.php";

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
		// $data = _p('data');
		// $data['setting'] = _p('setting');
		a::i($data)->fpost('data')->apost('setting')->sers('setting');

		siud::save(RDB_PRE . 'model_field')->pk('field_id')->data($data)->error($error)->ing();
		if ($error)
			{
			echo $error;
			}
	}//}}}
	function add()
	{//{{{
		include tpl_admin('field_add');
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
		var_dump($result);exit;

		$where = "modelid={$modelid} ";
		if (!$show_disabled)
			{
			$where .= "AND disabled=0";
			}
		$infos = $field->listinfo($where, 'listorder,fieldid', 1, 100);
		include tpl_admin('model_field_manage', 'conm');
	}//}}}
	function edit()
	{//{{{
		if($dosubmit)
			{
			$info['unsetgroupids'] = isset($unsetgroupids) ? implodeids($unsetgroupids) : '';
			$info['unsetroleids'] = isset($unsetroleids) ? implodeids($unsetroleids) : '';
			$result = $field->edit($fieldid, $info, $setting);
			if($result)
				{
				$formtype = $info['formtype'];
				$path = CONTENT_ROOT . "fields/{$formtype}.inc.php";
				//todo:对修改的字段进行判断，若是主表的字段则不修改表结构。
				//todo:调用自动加载模块字段的函数
				if (is_array($fields[$formtype]))
					{
					require_once PHPCMS_ROOT . "{$fields[$formtype][1]}/fields/{$formtype}.inc.php";
					$func_name = "content_field_{$formtype}_change";
					$func_name($tablename, $info, $setting);
					}
				else if (is_file($path))
					{
					require_once $path;
					$func_name = "content_field_{$formtype}_change";
					$func_name($tablename, $info, $setting);
					}
				else
					{
					extract($setting);
					extract($info);
					if($issystem) $tablename = DB_PRE.'content';
					require_once CONTENT_ROOT . "fields/{$formtype}/field_edit.inc.php";
					}

				/*非系统且作为搜索条件字段，增加索引*/
				// if(!$issystem) {
				// $sql = "SHOW INDEX FROM `$tablename`";
				// $query = $db->query($sql);
				// while($res = $db->fetch_array($query)) {
				// $indexarr[] = $res['Column_name'];
				// }
				// if(is_array($indexarr) && in_array($field, $indexarr)) {
				// if(!$issearch) {
				// $sql = "ALTER TABLE `$tablename` DROP INDEX `$field`";
				// $db->query($sql);
				// }
				// } else {
				// if($issearch) {
				// $sql = "ALTER TABLE `$tablename` ADD INDEX `$field` (`$field`)";
				// $db->query($sql);
				// }
				// }
				// }

				showmessage('操作成功！', $forward);
				}
			else
				{
				showmessage('操作失败！');
				}
			}
		else
			{
			//if(!is_ie()) showmessage('本功能只支持IE浏览器，请用IE浏览器打开。');
			$info = $field->get($fieldid);
			if(!$info)
				{
				showmessage('指定的字段不存在！');
				}
			$setting = array();
			if ($info['setting'])
				{
				eval("\$setting = {$info['setting']};");
				}
			$info = new_htmlspecialchars($info);
			$unsetgroups = form::checkbox($GROUP, 'unsetgroupids', 'unsetgroupids', $unsetgroupids, 4);
			$unsetroles = form::checkbox($ROLE, 'unsetroleids', 'unsetroleids', $unsetroleids, 4);
			require_once CONTENT_ROOT . 'fields/patterns.inc.php';
			//include tpl_admin('model_field_edit');
			include tpl_admin('model_field_add');
			}
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
	function delete()
	{//{{{
		$info = $field->get($fieldid);
		$result = $field->delete($fieldid);
		if($result)
			{
			extract($info);
			@extract(unserialize($setting));
			require_once CONTENT_ROOT . 'fields/'.$formtype.'/field_delete.inc.php';
			showmessage('操作成功！', admin_url("..manage..modelid"));
			}
		else
			{
			showmessage('操作失败！');
			}
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
		$func_name = "cm_ft_{$mod}_{$name}_setting";
		if (!function_exists($func_name))
			{
			echo "字段类型无法加载";
			exit;
			}

		$func_name(array());
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
}

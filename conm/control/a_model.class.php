<?php
/**
 * 2012-05-05
 * 
 * @package default
 * @filesource
 */
class ctrl_a_model
{
	function index()
	{//{{{
		echo 'this is a_model';
	}//}}}
	/**
	 * 管理内容模型.
	 */
	function manage()
	{//{{{
		$sql = "SELECT * FROM " . RDB_PRE . "model";
		$result = rdb::obj()->select($sql);
		var_dump($result);
		// $infos = $model->listinfo('modeltype=0', 'modelid', 1, 100);
		// include admin_tpl('model_manage');
	}//}}}
	function add()
	{//{{{
		if (isset($_POST["dosubmit"]))
			{
			$modelid = $model->add($info);
			if($modelid)
				{
				//cache_model();
				showmessage('操作成功！', admin_url(".model_field.manage.&modelid={$modelid}"));
				}
			else
				{
				showmessage('操作失败！');
				}
			}
		else
			{
			include tpl_admin('model_add');
			}
	}//}}}
	function edit()
	{//{{{
		if($dosubmit)
			{
			$result = $model->edit($modelid, $info);
			if($result)
				{
				require_once 'admin/category.class.php';
				$cat = new category('phpcms');
				$cat->repair();
				cache_model();

				if(is_array($CATEGORY) && $ishtml != $info['ishtml'])
					{
					$forward = '?mod=phpcms&file=url'.$catids.'&forward='.urlencode(URL);
					foreach($CATEGORY AS $k=>$v)
						{
						if($v['modelid'] != $modelid) continue;
						showmessage('内容模型修改成功！请更新对应的栏目URL链接', $forward,'4000');
						}

					}
				showmessage('操作成功！', $forward);
				}
			else
				{
				showmessage('操作失败！');
				}
			}
		else
			{
			$info = $model->get($modelid);
			if(!$info) showmessage('指定的模块不存在！');
			extract($info);
			include admin_tpl('model_edit');
			}
	}//}}}
	function export()
	{//{{{
		$result = $model->export($modelid);
		$filename = $result['arr_model']['tablename'].'.model';
		cache_write($filename, $result, CACHE_MODEL_PATH);
		file_down(CACHE_MODEL_PATH.$filename, $filename);
	}//}}}
	function import()
	{//{{{
		if($dosubmit)
			{
			if(!$info['name']) showmessage('请输入模型名称');
			if(!$info['tablename']) showmessage('请输入表名');
			if(!class_exists('attachment'))
				{
				require 'attachment.class.php';
				}

			$attachment = new attachment('phpcms');
			$aid = $attachment->upload('modelfile', 'model');
			if(!$aid) showmessage($attachment->error(), $forward);
			$filepath = $attachment->get($aid[0], 'filepath');

			$array = include(UPLOAD_ROOT.$filepath['filepath']);
			if(empty($array)) showmessage('上传模型的格式不正确');
			$modelid = $model->import($info);
			if(!$modelid) showmessage($model->msg, $forward);
			if(is_array($array['arr_field']) && !empty($array['arr_field']) && $modelid)
				{
				$tablename = DB_PRE.'c_'.$info['tablename'];
				$arr_model_field = array('contentid', 'catid', 'typeid', 'areaid', 'title', 'style', 'thumb', 'keywords', 'description', 'posids', 'listorder', 'url', 'userid', 'updatetime', 'inputtime', 'status', 'template', 'content', 'islink', 'prefix');
				foreach($array['arr_field'] as $arr_field)
					{
					$arr_field['modelid'] = $modelid;
					$arr_field = new_addslashes($arr_field);
					$db->insert(DB_PRE.'model_field', $arr_field);
					if(in_array($arr_field['field'], $arr_model_field)) continue;
					@extract($arr_field);
					$setting = new_stripslashes($setting);
					eval("\$setting = $setting;");
					@extract($setting);
					$excutefile = file_get_contents(PHPCMS_ROOT.'include/fields/'.$formtype.'/field_add.inc.php');
					$excutefile = str_replace('<?php', '', $excutefile);
					$excutefile = str_replace('?>', '', $excutefile);
					eval($excutefile);
					}
				}
			if(!class_exists('model_field'))
				{
				require 'admin/model_field.class.php';
				}
			$field = new model_field($modelid);
			$field->cache();
			$attachment->delete("aid='$aid[0]'");
			showmessage('操作成功！', $forward);
			}
		else
			{
			include admin_tpl('model_import');
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

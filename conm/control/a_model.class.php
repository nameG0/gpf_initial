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
		?>
		<a href="<?=gpf::url('..add')?>">添加</a>
		<hr />
		<?php
		foreach ($result as $k => $r)
			{
			?>
			<div >
				<?=$r['name']?>
				<a href="<?=gpf::url(".a_model_field.manage.&modelid={$r['modelid']}")?>">管理字段</a>
				<a href="<?=gpf::url("..sync.&modelid={$r['modelid']}")?>">同步</a>
				<a href="<?=gpf::url("..edit.&modelid={$r['modelid']}")?>">修改</a>
				<a href="<?=gpf::url("..delete.&modelid={$r['modelid']}")?>">删除</a>
				|
				<a href="<?=gpf::url(".a_content.manage.&modelid={$r['modelid']}")?>">管理内容</a>
			</div>
			<?php
			}
		// $infos = $model->listinfo('modeltype=0', 'modelid', 1, 100);
		// include admin_tpl('model_manage');
	}//}}}
	function add()
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
	/**
	 * 内容模型数据同步到数据库表。
	 */
	function sync()
	{//{{{
		//todo 加一个参数令 is_sync=1 时可强制同步。
		$modelid = _g('modelid', 'int');

		$CMMr = siud::find('model')->wis('modelid', $modelid)->ing();
		if (!$CMMr)
			{
			exit('模型不存在');
			}
		if (0 == $CMMr['modeltype'])
			{
			$CMMTid = 'conm/table';
			}
		else
			{
			//todo
			exit('未完成其它内容模型的同步功能');
			}
		//todo 在同步之前检查一下模型的 is_sync 字段是否为1,为1一般不同步。
		
		//加载内容模型处理函数
		list($mod, $name) = explode("/", $CMMTid);
		$callback = mod_callback($mod, 'p');
		foreach ($callback as $k => $v)
			{
			$p = "{$v}conm_model/{$name}/function.func.php";
			if (is_file($p))
				{
				include_once $p;
				}
			}
		$func_pre = "cm_mt_{$mod}__{$name}_";
		$func_name = "{$func_pre}is_make";
		if (!function_exists($func_name))
			{
			exit("未定义内容模型处理函数 {$func_name}");
			}

		//查出模型下的字段数据。
		$result = siud::select('model_field')->wis('modelid', $modelid)->ing();
		$CMFl = array();
		foreach ($result as $k => $r)
			{
			//todo 移除没有实际表字段的虚拟字段类型.
			a::i($r)->unsers('setting');
			$CMFl[$r['field']] = $r;
			}
		unset($result);
		//检查是否初始化数据表
		$is_make = $func_name($CMMr);
		if (!$is_make)
			{
			//进行数据表初始化
			$func_name = "{$func_pre}make";
			}
		else
			{
			$func_name = "{$func_pre}sync";
			}
		$sql = $func_name($CMMr, $CMFl);
		if (is_string($sql))
			{
			$sql = (array)$sql;
			}
		$o_db = rdb::obj();
		foreach ($sql as $k => $v)
			{
			log::add($v, log::INFO, __FILE__, __LINE__);
			$o_db->query($v);
			}
		//把 is_sync 改为 1
		// siud::update('model')->wis()->data()->ing();
		?>
		<a href="<?=gpf::url("..manage")?>">管理模型</a>
		<?php
	}//}}}
}

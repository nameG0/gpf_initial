<?php
/**
 * 模型管理
 * 
 * @package default
 * @filesource
 */
class ctrl_a_model
{
	function __construct()
	{//{{{
		admin_check();
	}//}}}
	function action_index()
	{//{{{
		echo 'this is a_model';
	}//}}}
	/**
	 * 管理内容模型.
	 */
	function action_manage()
	{//{{{
		$sql = "SELECT * FROM " . RDB_PRE . "model";
		$result = rdb::obj()->select($sql);
		?>
		<a href="<?=gpf::url('..form')?>">添加</a>
		<hr />
		<?php
		foreach ($result as $k => $r)
			{
			$CMMTid = cm_m_CMMTid($r['modeltype']);
			list($mod, $name) = explode("/", $CMMTid);
			?>
			<div >
				(<?=$r['modelid']?>)
				<?=$r['name']?>
				<a href="<?=gpf::url(".a_model_field.manage.&modelid={$r['modelid']}")?>">管理字段</a>
				<a href="<?=gpf::url("..sync.&modelid={$r['modelid']}")?>">同步</a>
				<a href="<?=gpf::url("..form.&modelid={$r['modelid']}")?>">修改</a>
				<a href="<?=gpf::url("..delete.&modelid={$r['modelid']}")?>">删除</a>
				|
				<a href="<?=gpf::url("{$mod}.a_{$name}.manage.&modelid={$r['modelid']}")?>">管理内容</a>
			</div>
			<?php
			}
		// $infos = $model->listinfo('modeltype=0', 'modelid', 1, 100);
		// include tpl_admin('model_manage');
	}//}}}
	/**
	 * 保存[添加/修改]一条模型数据
	 */
	function action_save()
	{//{{{
		if (!isset($_POST["dosubmit"]))
			{
			showmessage('没有表单数据');
			}
		a::i($CMMr)->fpost('CMMr')->apost('setting')->sers('setting');

		if (!$CMMr['modelid'])
			{
			//添加时检查表是否已存在
			$CMMTid = cm_m_CMMTid($CMMr['modeltype']);
			cm_m_load($CMMTid);
			list($mod, $name) = explode("/", $CMMTid);
			$func_name = "cm_mt_{$mod}__{$name}_is_make";
			if ($func_name($CMMr))
				{
				echo '表已存在';
				exit;
				}
			}
		siud::save('model')->pk('modelid')->data($CMMr)->error($error)->id($modelid)->ing();

		if ($modelid)
			{
			if (!$CMMr['modelid'])
				{
				//添加模型类型默认字段数据。
				$CMMTid = cm_m_CMMTid($CMMr['modeltype']);
				cm_m_load($CMMTid);
				list($mod, $name) = explode("/", $CMMTid);
				$func_name = "cm_mt_{$mod}__{$name}_default_field";
				if (function_exists($func_name))
					{
					$CMFl = $func_name();
					foreach ($CMFl as $f => $r)
						{
						$r['field'] = $f;
						$r['modelid'] = $modelid;
						conm_f_save($r, $error);
						}
					}
				}
			conm_mplug_save_form('model_plug', $modelid);
			//cache_model();
			// showmessage('操作成功！', admin_url(".model_field.manage.&modelid={$modelid}"));
			echo "操作成功！{$modelid}";
			}
		else
			{
			// showmessage('操作失败！');
			echo '操作失败！';
			}
		?>
		<a href="<?=gpf::url('..manage')?>">管理</a>
		<?php
	}//}}}
	/**
	 * 显示模型[添加/修改]表单。
	 * @param int modelid 修改时传入,不传入表示添加。
	 */
	function action_form()
	{//{{{
		$modelid = i::g()->int('modelid')->end();

		$CMMr = array();
		if ($modelid)
			{
			$CMMr = conm_CMMR($modelid, CONM_ONLY_MODEL);
			if (!$CMMr)
				{
				showmessage('指定的模块不存在！');
				}
			}
		$Nt = array("namespace" => 'model', "tag_id" => intval($modelid),);
		$Nt_extend = array();
		$Nt_extend_from = array();
		// $Nt_extend = array("namespace" => 'model', "tag_id" => '1',);
		// $Nt_extend_from = array(
			// array("namespace" => 'model', "tag_id" => 1, "name" => '模型',),
			// array("namespace" => 'catid', "tag_id" => 1, "name" => '上级栏目',),
			// );
		include tpl_admin('model_form');
	}//}}}
	function action_export()
	{//{{{
		$result = $model->export($modelid);
		$filename = $result['arr_model']['tablename'].'.model';
		cache_write($filename, $result, CACHE_MODEL_PATH);
		file_down(CACHE_MODEL_PATH.$filename, $filename);
	}//}}}
	function action_import()
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
			include tpl_admin('model_import');
			}
	}//}}}
	function action_delete()
	{//{{{
		$modelid = _g('modelid', 'int');

		$result = conm_m_delete($modelid, $error);
		if($result)
			{
			echo '操作成功！';
			// showmessage('操作成功！', $forward);
			}
		else
			{
			echo '操作失败！' . $error;
			// showmessage('操作失败！', $forward);
			}
		?>
		<a href="<?=gpf::url('..manage')?>">管理</a>
		<?php
	}//}}}
	function action_disable()
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
	function action_urlrule()
	{//{{{
		echo $type == 'category' ? form::select_urlrule('phpcms', 'category', $ishtml, 'info[category_urlruleid]', 'category_urlruleid', $category_urlruleid) : form::select_urlrule('phpcms', 'show', $ishtml, 'info[show_urlruleid]', 'show_urlruleid', $show_urlruleid);
	}//}}}
	/**
	 * 内容模型数据同步到数据库表。
	 */
	function action_sync()
	{//{{{
		//todo 加一个参数令 is_sync=1 时可强制同步。
		$modelid = i::g()->int('modelid')->end();

		$CMMR = conm_CMMR($modelid);
		if (!$CMMR)
			{
			exit('模型不存在');
			}
		//todo 在同步之前检查一下模型的 is_sync 字段是否为1,为1一般不同步。然后有一个参数可以在 is_sync=1 的情况下强制同步。
		//加载内容模型处理函数
		$CMMTid = cm_m_CMMTid($CMMR['modeltype']);
		cm_m_load($CMMTid);
		list($mod, $name) = explode("/", $CMMTid);
		$func_pre = "cm_mt_{$mod}__{$name}_";
		$func_name = "{$func_pre}is_make";
		if (!function_exists($func_name))
			{
			exit("未定义内容模型处理函数 {$func_name}");
			}

		//检查是否初始化数据表
		$is_make = $func_name($CMMR);
		if (!$is_make)
			{
			//进行数据表初始化
			$func_name = "{$func_pre}make";
			}
		else
			{
			$func_name = "{$func_pre}sync";
			}
		$sql = $func_name($CMMR);
		$o_db = rdb::obj();
		foreach ($sql as $k => $v)
			{
			log::add("model sync:{$v}", log::INFO, __FILE__, __LINE__);
			$o_db->query($v);
			}
		//把 is_sync 改为 1
		// siud::update('model')->wis()->data()->ing();
		?>
		<a href="<?=gpf::url("..manage")?>">管理模型</a>
		<a href="<?=gpf::url(".a_model_field.manage..modelid")?>">管理字段</a>
		<?php
	}//}}}
	/**
	 * 加载模型设置表单
	 * @param int $modeltype
	 */
	function action_ajax_setting_form()
	{//{{{
		$modeltype = i::g()->int('modeltype')->end();

		log::is_print(0);

		$CMMTid = cm_m_CMMTid($modeltype);
		cm_m_setting_form($CMMTid);
	}//}}}
}

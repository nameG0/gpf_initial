<script type="text/javascript" src="static/gencms/jquery/jquery-1.7.2.min.js" ></script>
<script type="text/javascript" src="static/conm/js.js"></script>
<a href="<?=gpf::url('..manage')?>">管理</a>
<hr />
<form action="<?=gpf::url("..save")?>" method="POST" enctype="multipart/form-data">
<input type="hidden" name="CMMr[modelid]" value="<?=intval($CMMr['modelid'])?>" />
模型名：
<input type="text" name="CMMr[name]" value="<?=$CMMr['name']?>" />
<br />
昵称：
<input type="text" name="CMMr[nickname]" value="<?=$CMMr['nickname']?>" />
<br />
模型表名：
<input type="text" name="CMMr[tablename]" value="<?=$CMMr['tablename']?>" />
<br />
模型类型：
<?=hd("select|name=CMMr[modeltype]|id=modeltype|value={$CMMr['modeltype']}", array("option" => array("0" => '单表', "1" => '文章模型', "2" => '树结构模型',),))?>
<br />
<div id="model_setting"><?php cm_m_setting_form('conm/table', $CMMr['setting']); ?></div>
<div id="model_plug"></div>
<br />
<input type="submit" name="dosubmit" value="提交" />
</form>
<script type="text/javascript">
<!--
$("#modeltype").change(function (){
	$("#model_setting").load("<?=gpf::url("..ajax_setting_form")?>&modeltype=" + this.value);
});
conmMPlug_init('model_plug', <?=json_encode($Nt)?>, <?=json_encode($Nt_extend)?>, <?=json_encode($Nt_extend_from)?>);
//-->
</script>

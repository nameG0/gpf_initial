<script type="text/javascript" src="static/gencms/jquery/jquery-1.7.2.min.js" ></script>
<form action="<?=ctrl_url('..save..modelid')?>" method="POST" enctype="multipart/form-data">
<input type="hidden" name="data[field_id]" value="<?=$data['field_id']?>" />
字段名：
<input type="text" name="data[field]" value="<?=$data['field']?>" />
<br />
昵称：
<input type="text" name="data[name]" value="<?=$data['name']?>" />
<br />
模型类型：
<select name="data[formtype]" id="modeltype">
	<option value="">请选择</option>
<?php
$CMFTl = cm_f_field_list();
foreach ($CMFTl as $k => $v)
	{
?>
	<option value="<?=$k?>" <?=$k == $data['formtype'] ? 'selected' : ''?>><?=$v['nn']?></option>
<?php
	}
?>
</select>
<br />
<div id="field_setting"><?php
if ($data['formtype'])
	{
	cm_f_field_load($data['formtype']);
	list($_mod, $_name) = explode("/", $data['formtype']);
	$func_name = "cm_ft_{$_mod}__{$_name}_setting";
	if (function_exists($func_name))
		{
		$func_name($data['setting']);
		}
	}
?></div>
<br />
<input type="submit" name="dosubmit" value="提交" />
</form>
<script type="text/javascript">
<!--
$('#modeltype').change(function (){
	$('#field_setting').load('<?=ctrl_url('..ajax_setting')?>&field_id=' + $('#modeltype').val());
});
//-->
</script>

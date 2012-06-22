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
<?php
$CMFTl = cm_f_field_list();
?>
<select name="data[formtype]" id="modeltype">
	<option value="">请选择</option>
<?php
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
	cm_f_load($data['formtype']);
	list($_mod, $_name) = explode("/", $data['formtype']);
	$func_name = "cm_ft_{$_mod}__{$_name}_setting";
	if (function_exists($func_name))
		{
		$func_name($data['setting']);
		}
	}
?></div>
<?php
echo
hd("text|label=默认值|name=setting[defaultvalue]|value={$data['setting']['defaultvalue']}|size=40|br"),
hd("text|label=字段最小长度|name=setting[minlength]|value={$data['setting']['minlength']}|br"),
hd("text|label=字段最大长度|name=setting[maxlength]|value={$data['setting']['maxlength']}|br"),
hd("text|label=CSS|name=setting[css]|value={$data['setting']['css']}|br"),
hd("text|label=正则验证|name=data[pattern]|value={$data['pattern']}|br")
;
?>
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

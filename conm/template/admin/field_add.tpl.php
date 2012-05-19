<script type="text/javascript" src="static/gencms/jquery/jquery-1.7.2.min.js" ></script>
<form action="" method="POST" enctype="multipart/form-data">
字段名：
<input type="text" name="data[field]" value="" />
<br />
昵称：
<input type="text" name="data[name]" value="" />
<br />
模型类型：[下拉框]
<select name="data[formtype]" id="modeltype">
	<option value="">请选择</option>
<?php
$CMFTl = cm_f_field_list();
foreach ($CMFTl as $k => $v)
	{
?>
	<option value="<?=$k?>"><?=$v['nn']?></option>
<?php
	}
?>
</select>
<br />
<div id="field_setting"></div>
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

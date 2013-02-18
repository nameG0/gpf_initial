<?php
// defined('IN_PHPCMS') or exit('Access Denied');
include tpl_admin('header');
?>
<script type="text/javascript" src="images/js/form.js"></script>
<body>
<?=$menu?>
<form action="<?=gpf::url("....contentid")?>" method="post" name="myform" enctype="multipart/form-data">
<table cellpadding="0" cellspacing="1" class="table_form">
  <caption>修改</caption>
 <?php
 foreach($forminfos as $field=>$info)
 {
 ?>
	<tr>
      <th width="20%"><?php if($info['star']){ ?> <font color="red">*</font><?php } ?> <strong><?=$info['name']?></strong><br />
	  <?=$info['tips']?>
	  </th>
      <td><?=$info['form']?></td>
    </tr>
<?php
}
?>
<tr>
      <th width="20%"><strong>状态</strong><br />
	  </th>
      <td>
	  <?php if($allow_manage){ ?>
	  <label><input type="radio" name="status" value="99" checked/> 发布</label>
	  <?php } ?>
	  <label><input type="radio" name="status" value="3" <?=$allow_manage ? '' : 'checked'?>> 审核</label>
	  <label><input type="radio" name="status" value="2"> 草稿</label>
	  </td>
    </tr>
    <tr>
      <td></td>
      <td>
	  <input type="hidden" name="forward" value="<?=$forward?>">
	  <input type="submit" name="dosubmit" value=" 确定 ">
	  &nbsp; <input type="reset" name="reset" value=" 清除 ">
	  </td>
    </tr>
</table>
</form>
<script LANGUAGE="javascript">
<!--
$().ready(function() {
	  $('form').checkForm(1);
	});
//-->
</script>
</body>
</html>

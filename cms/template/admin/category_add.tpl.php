<?php
// defined('IN_PHPCMS') or exit('Access Denied');
global $CATEGORY;

include tpl_admin('header');
?>
<body >
<iframe name="form_target" style="display:none;"></iframe>
<?php if('' === $type){//{{{ ?>
<form name="myform" method="POST" action="">
<table cellpadding="2" cellspacing="1" class="table_form">
  <caption>添加栏目</caption>
  <tr>
  <th><font color="red">*</font>  <strong>上级栏目</strong></th>
  <td>
<?=hd("cms.select_category|module=cms|parentid=0|name=parentid|id=parentid|alt=无（作为一级栏目）|catid={$parentid}|type=2")?>
  </td>
  </tr>
     <tr>
      <th><strong>栏目类型</strong></th>
      <td>
	  <label>
	  <input type="radio" name="type" value="0" checked >内部栏目（可绑定内容模型，并支持在栏目下建立子栏目或发布信息）
	  </label>
	  <br/>
	  <label>
	  <input type="radio" name="type" value="1" >单网页（可更新单网页内容，但是不能在栏目下建立子栏目或发布信息）
	  </label>
	  <br/>
	  <label>
	  <input type="radio" name="type" value="2" >外部链接（可建立一个链接并指向任意网址）
	  </label>
	  <br/>
	  </td>
    </tr>
	<tr>
	 <th></th>
	 <td><input type="submit" name="next" value=" 下一步 ">&nbsp;&nbsp;&nbsp;&nbsp;<input type="reset" name="reset" value=" 重置 "></td>
	</tr>
</table>
</form>

<?php }//}}}
elseif($type == 0) {//{{{ ?>
<form name="myform" method="POST" target="form_target" action="">
<input type="hidden" name="category[type]" value="<?=$type?>">
<input name="forward" type="hidden" value="<?=$forward?>">
<table cellpadding="2" cellspacing="1" class="table_form">
  <tbody id='Tabs0' style='display:'>
  <tr>
  <th width='30%'><strong>上级栏目</strong></th>
  <td>
  <input type="hidden" name="category[parentid]" value="<?=$parentid?>"> <?=$parentid ? $CATEGORY[$parentid]['catname'] : '无'?>
  </td>
  </tr>
    <tr>
      <th><font color="red">*</font> <strong>栏目名称</strong></th>
      <td>
      <input name='category[catname]' type='text' id='catname' size='40' maxlength='50' require="true" datatype="limit|ajax" min="1" max="50" url="<?=gpf::url("..checkname.&parentid={$parentid}")?>" msg="字符长度范围必须为1到50位|" msgid="msgid1">
      <span id="msgid1"></span>
      </td>
    </tr>
    <tr>
      <th><font color="red">*</font> <strong>栏目目录</strong></th>
      <td><input name='category[catdir]' type='text' id='catdir' size='20' maxlength='50' require="true" datatype="limit|ajax" min="1" max="50" url="<?=gpf::url("..checkdir.&parentid={$parentid}")?>" msg="字符长度范围必须为1到50位|"></td>
    </tr>
  </tbody>
</table>
<table width="100%" height="25" border="0" cellpadding="0" cellspacing="0">
  <tr>
     <th width='30%'></th>
     <td><input type="submit" name="dosubmit" value=" 确定 ">&nbsp;&nbsp;&nbsp;&nbsp;<input type="reset" name="reset" value=" 重置 "></td>
  </tr>
</table>
</form>

<?php }//}}}
elseif($type == 1){//{{{ ?>
<form name="myform" method="POST" target="form_target" action="" onSubmit='return CheckForm();'>
<input name="forward" type="hidden" value="<?=$forward?>">
<input type="hidden" name="category[type]" value="<?=$type?>">
<table cellpadding="2" cellspacing="1" class="table_form">
  <caption>添加单网页</caption>
  <tr>
  <th width='25%'><strong>上级栏目</strong></th>
  <td>
  <input type="hidden" name="category[parentid]" value="<?=$parentid?>"> <?=$parentid ? $CATEGORY[$parentid]['catname'] : '无'?>
  </td>
  </tr>
    <tr>
      <th><strong>栏目名称</strong></th>
      <td><input name='category[catname]' type='text' id='catname' size='40' maxlength='50'  require="true" datatype="require" msg="单网页名称不能为空"> <font color="red">*</font></td>
    </tr>
    <tr>
      <th><strong>栏目目录</strong></th>
      <td><input name='category[catdir]' type='text' id='catdir' size='20' maxlength='50'  require="true" datatype="require" msg="单网页英文名不能为空">  <font color="red">*</font></td>
    </tr>
    <tr>
      <th><strong>栏目模板</strong></th>
	<td>
	<input type="text" name="setting[template]" value="" />(mod/name), eg. main/index
	</td>
    </tr>
  <tr>
     <th></th>
     <td><input type="submit" name="dosubmit" value=" 确定 ">&nbsp;&nbsp;&nbsp;&nbsp;<input type="reset" name="reset" value=" 重置 "></td>
  </tr>
</table>
</form>

<?php }//}}}
elseif($type == 2){//{{{ ?>
<script language='JavaScript' type='text/JavaScript'>
function CheckForm(){
	if(document.myform.catname.value==''){
		alert('请输入链接名称！');
		document.myform.catname.focus();
		return false;
	}
	if(document.myform.url.value==''){
		alert('请输入链接地址！');
		document.myform.url.focus();
		return false;
	}
}
</script>
<form name="myform" method="POST" target="form_target" action="" onSubmit='return CheckForm();'>
<input name="forward" type="hidden" value="<?=$forward?>">
<input type="hidden" name="category[type]" value="<?=$type?>">
<table cellpadding="2" cellspacing="1" class="table_form">
  <caption>添加外部链接</caption>
  <tr>
  <th width='25%'><strong>上级栏目</strong></th>
  <td>
  <input type="hidden" name="category[parentid]" value="<?=$parentid?>"> <?=$parentid ? $CATEGORY[$parentid]['catname'] : '无'?>
  </td>
  </tr>
    <tr>
      <th><strong>栏目名称</strong></th>
      <td><input name='category[catname]' type='text' id='catname' size='40' maxlength='50'> <font color="red">*</font></td>
    </tr>
    <tr>
      <th><strong>栏目目录</strong></th>
      <td><input name='category[catdir]' type='text' id='catdir' size='20' maxlength='50'  require="true" datatype="require" msg="单网页英文名不能为空">  <font color="red">*</font></td>
    </tr>
    <tr>
      <th><strong>链接地址</strong></th>
      <td><input name='category[url]' type='text' id='url' size='60' maxlength='100' require="true" datatype="require" msg="链接地址不能为空">  <font color="red">*</font></td>
    </tr>
  <tr>
     <th width='30%'></th>
     <td><input type="submit" name="dosubmit" value=" 确定 ">&nbsp;&nbsp;&nbsp;&nbsp;<input type="reset" name="reset" value=" 重置 "></td>
  </tr>
</table>
</form>
<?php }//}}} ?>
</body>
</html>
<script LANGUAGE="javascript">
<!--
$().ready(function() {
	  $('form').checkForm(1);
	});
function __done(catid)
{//{{{
	window.opener.cateTree_refresh_child_by_catid(catid);
	window.close();
}//}}}
//-->
</script>

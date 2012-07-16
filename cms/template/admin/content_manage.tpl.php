<?php 
// defined('IN_PHPCMS') or exit('Access Denied');
include tpl_admin('header');
?>
<body>
<?=$menu?>
<a href="<?=gpf::url("..add..catid")?>" target="main">发布内容</a>
<form name="myform" method="post" action="<?=gpf::url("..delete")?>">
<table cellpadding="0" cellspacing="1" class="table_list">
  <caption>信息管理</caption>
<tr>
<th width="50">选中</th>
<th width="40">ID</th>
<th>标题</th>
<th width="165">更新时间</th>
<th width="165">管理操作</th>
</tr>
<?php 
if(is_array($result)){
	foreach($result as $info){
		// $r = $c->get_count($info['contentid']);
?>
<tr>
<td><input type="checkbox" name="contentid[]" value="<?=$info['contentid']?>" id="content_<?=$info['contentid']?>" /></td>
<!--
<td class="align_c"><input type="text" name="listorders[<?=$info['contentid']?>]" value="<?=$info['listorder']?>" size="4" /></td>
-->
<td><?=$info['contentid']?></td>
<td>
<!--
<a href="<?=$info['url']?>" target="_blank">
-->
<?=$info['title']//output::style($info['title'], $info['style'])?><!--</a>--> <?=$info['thumb'] ? '<font color="red">图</font>' : ''?>&nbsp;<?=$info['posids']?'<font color="green">荐</font>': ''?>&nbsp;<?=$info['typeid']?'<font color="blue">类</font>': ''?></td>
<td class="align_c"><?=date('Y-m-d H:i', $info['updatetime'])?></td>
<td class="align_c">
<!--
<a href="?mod=<?=$mod?>&file=<?=$file?>&action=view&catid=<?=$catid?>&contentid=<?=$info['contentid']?>">查看</a> | 
-->
<a href="<?=gpf::url("..edit.&contentid={$info['contentid']}&catid={$info['catid']}")?>">修改</a> |
<a href="<?=gpf::url("..delete.&contentid={$info['contentid']}&catid={$info['catid']}")?>" onclick="if(!confirm('真的要删除《<?=$info['title']?>》吗？')){return false;}">删除</a>
<!--
<a href="?mod=<?=$mod?>&file=<?=$file?>&action=log_list&catid=<?=$catid?>&contentid=<?=$info['contentid']?>">日志</a>
<?php if(isset($MODULE['comment'])){ ?> | <a href="?mod=comment&file=comment&keyid=phpcms-content-title-<?=$info['contentid']?>">评论</a> <?php } ?>
-->
</td>
</tr>
<?php 
	}
}
?>
</table>
<div class="button_box">
<span style="width:60px"><a href="###" onclick="javascript:$('input[type=checkbox]').attr('checked', true)">全选</a>/<a href="###" onclick="javascript:$('input[type=checkbox]').attr('checked', false)">取消</a></span>
		<!--
		<input type="button" name="listorder" value=" 排序 " onclick="myform.action='?mod=<?=$mod?>&file=<?=$file?>&action=listorder&catid=<?=$catid?>&processid=<?=$processid?>&forward=<?=urlencode(URL)?>';myform.submit();"> 
		-->
		<input type="button" name="delete" value=" 删除 " onclick="myform.action='<?=gpf::url("..delete..catid")?>';myform.submit();"> 
		<!--
		<input type="button" name="move" value=" 批量移动 " onclick="myform.action='?mod=<?=$mod?>&file=content_all&action=move&catid=<?=$catid?>&processid=<?=$processid?>&forward=<?=urlencode(URL)?>';myform.submit();">
		-->
		<?php if(array_key_exists('posids', array()) && !check_in($_roleid, $model_field->fields['posids']['unsetroleids'])) {?>批量添加至推荐位：<?=form::select($POS, 'posid', 'posid', '', '', '', "onchange=\"myform.action='?mod={$mod}&file={$file}&action=posid&catid={$catid}&processid={$processid}';myform.submit();\"")?> <?php } ?>
		<?php if(array_key_exists('typeid', array()) && !check_in($_roleid, $model_field->fields['typeid']['unsetroleids'])) {?>批量添加至类别：<?=form::select_type('phpcms', 'typeid', '', '请选择', '', "onchange=\"myform.action='?mod={$mod}&file={$file}&action=typeid&catid={$catid}&processid={$processid}';myform.submit();\"", $modelid)?> <?php }?>
</div>
<div id="pages"><?=$c->pages?></div>
</form>
</body>
</html>

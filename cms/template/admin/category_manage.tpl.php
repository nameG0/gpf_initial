<?php 
// defined('IN_PHPCMS') or exit('Access Denied');
include tpl_admin('header', 'main');
?>
<body>
<?php if($catid){ ?>
<div class="pos"><strong>当前栏目</strong>：<a href="<?=gpf::url("...")?>>栏目管理</a><?=1//catpos($catid, '?mod=phpcms&file=category&action=manage&catid=$catid')?></div>
<?php } ?>
<a href="<?=gpf::url('..add')?>">添加栏目</a>
<form method="post" action="<?=gpf::url("..listorder")?>">
<table cellpadding="0" cellspacing="1" class="table_list">
    <caption><?=$catid ? $CATEGORY[$catid]['catname'].'子' : ''?>栏目管理</caption>
	<tr>
		<th width="5%">排序</th>
		<th width="5%">ID</th>
		<th>栏目名称</th>
		<th width="8%">栏目类型</th>
		<th width="10%">绑定模型</th>
		<th width="5%">访问</th>
		<th width="320">管理操作</th>
	</tr>
<?php
foreach($data as $k=>$r)
{
	$CMMR = conm_CMMR($r['modelid'], CONM_ONLY_MODEL);
?>
<tr>
	<td class="align_c"><input name='listorder[<?=$r['catid']?>]' type='text' size='3' value='<?=$r['listorder']?>'></td>
	<td class="align_c"><?=$r['catid']?></td>
	<td><a href="<?=gpf::url("..edit.&catid={$r['catid']}&parentid={$r['parentid']}")?>"><span class='<?=$r['style']?>'><?=$r['catname']?></span></a></td>
	<td class="align_c"><?=$r['type'] == 0 ? '内部栏目' : ($r['type'] == 1 ? '<font color="blue">单网页</font>' : '<font color="red">外部链接</font>')?></td>
	<td class="align_c"><?php if($r['type'] == 0) { ?><a href="<?=gpf::url("conm.a_model_field.manage.&modelid={$r['modelid']}")?>"><?=$CMMR['name']?></a><?php } ?></td>
	<td class="align_c"><a href='<?=$r['url']?>' target='_blank'>访问</a></td>
	<td class="align_c">
	<?php if($r['type']>1){ ?>
	<font color="#CCCCCC">添加子栏目</font> | 
	<font color="#CCCCCC">子栏目</font> | 
	<a href="<?=gpf::url("..edit.&catid={$r['catid']}&parentid={$r['parentid']}")?>">修改</a> | 
	<!--<font color="#CCCCCC">移动</font> | <font color="#CCCCCC">清空</font> | -->
	<a href="javascript:confirmurl('<?=gpf::url("..delete.&catid={$r['catid']}")?>', '确认删除“<?=$r['catname']?>”栏目吗？')">删除</a>
    <?php }else{ ?>
	<a href='<?=gpf::url("..add.&catid={$r['catid']}")?>'>添加子栏目</a> | 
	<a href="<?=gpf::url("..manage.&catid={$r['catid']}")?>">子栏目</a> | 
	<a href="<?=gpf::url("..edit.&catid={$r['catid']}&parentid={$r['parentid']}")?>">修改</a> | 
	<!--
	<?php if($r['type']==1) { ?>
	<font color="#CCCCCC">移动</font> | <font color="#CCCCCC">清空</font> | 
	<?php }else{ ?>
	<a href='?mod=<?=$mod?>&file=content_all&action=move&catid=<?=$r['catid']?>'>移动</a> | 
	<a href="javascript:confirmurl('?mod=<?=$mod?>&file=<?=$file?>&action=recycle&catid=<?=$r['catid']?>', '确认清空“<?=$r['catname']?>”栏目吗？')" >清空</a> | 
	-->
	<?php } ?>
	<a href="<?=gpf::url("..delete.&catid={$r['catid']}")?>)" onclick="if(!confirm('确认删除“<?=$r['catname']?>”栏目吗？')){return false;}">删除</a>
	<?php } ?>
	</td>
</tr>
<?php 
}	
?>
</table>
<div class="button_box">
<input name="dosubmit" type="submit" value=" 排序 ">
</form>
</body>
</html>

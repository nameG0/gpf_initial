<script type="text/javascript">
<!--
<?php
if ($Nt_extend_from)
	{
	foreach ($Nt_extend_from as $k => $v)
		{
		echo "conmMPlug_data['{$dom_id}']['Nt_{$k}'] = {namespace: '{$v['namespace']}', tag_id: '{$v['tag_id']}'};";
		}
	}
?>
//-->
</script>
<input type="hidden" name="<?=$dom_id?>[namespace]" value="<?=$namespace?>" />
<input type="hidden" name="<?=$dom_id?>[tag_id]" value="<?=$tag_id?>" />
<input type="hidden" name="<?=$dom_id?>[namespace_extend]" id="<?=$dom_id?>_namespace_extend" value="" />
<input type="hidden" name="<?=$dom_id?>[tag_id_extend]" id="<?=$dom_id?>_tag_id_extend" value="" />
<div >
<span onclick="conmMPlugTigger_enable('<?=$dom_id?>');"> [使用的插件] </span>
<span onclick="conmMPlugTigger_disable('<?=$dom_id?>');"> [禁用的插件] </span>
</div>
<div >
	继承自：
	<input type="button" value="无" onclick="conmMPlug_show('<?=$dom_id?>', 'Nt_null');" />
<?php
foreach ($Nt_extend_from as $k => $v)
	{
	?>
	<input type="button" value="<?=$v['name']?>" onclick="conmMPlug_show('<?=$dom_id?>', 'Nt_<?=$k?>');" />
	<?php
	}
?>
</div>
<div >
	<?=hd("conm.mplug_select|id={$dom_id}_mplugid")?>
	<input type="button" value="添加新插件" onclick="conmMPlug_new_plug('<?=$dom_id?>');" />
</div>
<div id="<?=$dom_id?>_setting"></div>

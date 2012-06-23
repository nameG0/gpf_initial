<?php
a::i($setting)->d('pk', 'catid')->d('pid', 'parentid');
?>
<pre>
树结构模型配置表单
主ID字段：<input type="text" name="setting[pk]" value="<?=$setting['pk']?>" />
父ID字段：<input type="text" name="setting[pk]" value="<?=$setting['pid']?>" />
列表页显示字段：<input type="text" name="setting[list_show_field]" value="<?=$setting['list_show_field']?>" />
</pre>

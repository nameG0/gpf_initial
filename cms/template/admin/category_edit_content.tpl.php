<?php 
// defined('IN_PHPCMS') or exit('Access Denied');
include tpl_admin('header');
?>
<body>
<form action="" method="POST" enctype="multipart/form-data">
	<?=hd("editor.fck|name=content", array("value" => $data['content'],))?>
	<input type="submit" name="dosubmit" value="保存" />
</form>
</body>
</html>

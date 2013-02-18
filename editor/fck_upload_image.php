<?php 
/*
2011-10-16
供 fckeditor 上传图片
*/
require dirname(__FILE__) . '/include/header.inc.php';
module_init('attachment');
log::is_print(false);

$upload = atta_upload_init('uploadfile');
//todo:应只限制图片上传格式
atta_upload_filter($upload, UPLOAD_ALLOWEXT);
atta_upload_move($upload);
?>
<script language='javascript'>
<?php
if ($upload[0]['_is_waring'])
	{
	?>
window.parent.alert('<?=$upload[0]['_error']?>');
	<?php
	}
else
	{
	?>
window.parent.SetUrl('<?=UPLOAD_URL , $upload[0]['_path']?>', '', '', '<?=$upload[0]['name']?>');
	<?php
	}
?>
</script>

<?php 
/*
2011-10-15
*/
$_tmp = array(
	'UPLOAD_FRONT' => 1, //是否允许前台上传附件
	'UPLOAD_ROOT' => G_PATH_UPLOADFILE, //附件保存物理路径
	'UPLOAD_URL' => 'uploadfile/', //附件目录访问路径
	'UPLOAD_ALLOWEXT' => 'doc,xls,ppt,wps,zip,rar,txt,jpg,jpeg,gif,bmp,png', //允许上传的文件后缀，多个后缀用“,”分隔
	'UPLOAD_DENYEXT' => '', //禁止上传的文件后缀，多个后缀用“,”分隔
	'UPLOAD_MINSIZE' => 0, //允许上传的附件最小值
	'UPLOAD_MAXSIZE' => 5242880, //允许上传的附件最大值
	'UPLOAD_MAXUPLOADS' => 100, //前台同一IP 24小时内允许上传附件的最大个数
	'UPLOAD_FUNC' => 'move_uploaded_file', //文件上传函数（copy, move_uploaded_file）
	);
foreach ($_tmp as $k => $v)
	{
	if (!defined($k))
		{
		define($k, $v);
		}
	}
unset($_tmp);
?>

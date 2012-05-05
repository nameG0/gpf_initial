<?php 
$_tmp = array(
	"GM_PATH_TPL_INST" => G_PATH_INST . 'template' . DS, //项目模板存放目录
	);
foreach ($_tmp as $k => $v)
	{
	if (!defined($k))
		{
		define($k, $v);
		}
	}
unset($_tmp);

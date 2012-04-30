<?php 
/**
 * rdb 模块默认配置
 *
 * 2011-10-19
 * 
 * @version 20120430
 * @package default
 * @filesource
 */
$_tmp = array(
	//数据库配置信息
	'RDB_HOST' => 'localhost', //数据库服务器主机地址
	'RDB_USER' => 'root', //数据库帐号
	'RDB_PW' => '', //数据库密码
	'RDB_NAME' => 'phpcms', //数据库名
	'RDB_PRE' => 'phpcms_', //数据库表前缀，同一数据库安装多套Phpcms时，请修改表前缀
	'RDB_CHARSET' => 'utf8', //数据库字符集
	'RDB_PCONNECT' => 0, //0 或1，是否使用持久连接
	'RDB_DATABASE' => 'mysql', //数据库类型
	);
foreach ($_tmp as $k => $v)
	{
	if (!defined($k))
		{
		define($k, $v);
		}
	}
unset($_tmp);

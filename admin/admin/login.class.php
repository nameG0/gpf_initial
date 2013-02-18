<?php
/**
 * 后台登录与注销控制器
 * 
 * @package default
 * @filesource
 */
log::is_print(false);

class ctrl_login
{
	function action_index()
	{//{{{
		if (is_file(GM_ADMIN_PATH_PASSWORD))
			{
			$tpl = 'login';
			}
		else
			{
			$tpl = 'init';
			}
		include tpl_admin($tpl);
	}//}}}
	/**
	 * 首次登录时设置登录密码
	 */
	function action_init()
	{//{{{
		list($sQ_password, $sQ_password_again) = i::p()->val('password', 'password_again')->end();

		if (is_file(GM_ADMIN_PATH_PASSWORD))
			{
			showmessage('密码已存在', gpf::url("..index"));
			}
		if (!$sQ_password)
			{
			showmessage('请输入登录密码', gpf::url("..index"));
			}
		if ($sQ_password != $sQ_password_again)
			{
			showmessage('两次输入的密码不一致,请重新输入！', gpf::url('..index'));
			}
		mkdiri(dirname(GM_ADMIN_PATH_PASSWORD));
		$size = file_put_contents(GM_ADMIN_PATH_PASSWORD, md5($sQ_password));
		if ($size)
			{
			showmessage("密码初始化成功，后台登录密码为：{$sQ_password}", gpf::url(".index.index"));
			}
		showmessage("密码初始化失败", gpf::url("..index"));
	}//}}}
	function action_login()
	{//{{{
		$password = i::p()->val('password')->end();

		if (!is_file(GM_ADMIN_PATH_PASSWORD))
			{
			showmessage('没有密码文件，请初始化登录密码', gpf::url("..index"));
			}
		if (!$password)
			{
			showmessage('请输入登录密码', gpf::url('..index'));
			}
		$password_md5 = file_get_contents(GM_ADMIN_PATH_PASSWORD);
		if (md5($password) != $password_md5)
			{
			showmessage('密码错误', gpf::url("..index"));
			}
		$_SESSION[GM_ADMIN_SESSION_KEY] = true;
		showmessage('登录成功', gpf::url(".index.index"));
	}//}}}
	function action_logout()
	{//{{{
		unset($_SESSION[GM_ADMIN_SESSION_KEY]);
		showmessage("登出成功", gpf::url("main.index.index"));
	}//}}}
}

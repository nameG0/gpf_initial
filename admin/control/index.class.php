<?php
/**
 * 默认控制器
 * 
 * @package default
 * @filesource
 */
class ctrl_index
{
	function __construct()
	{//{{{
		admin_check();
		log::is_print(false);
	}//}}}
	function index()
	{//{{{
		include tpl_admin('index');
	}//}}}
	function top()
	{//{{{
		include tpl_admin('top');
	}//}}}
	function left()
	{//{{{
		echo 'this is left';
	}//}}}
	function main()
	{//{{{
		echo 'this is main';
	}//}}}
	/**
	 * 修改密码
	 */
	function repassword()
	{//{{{
		if (isset($_POST["dosubmit"]))
			{
			list($sQ_password_old, $sQ_password_new, $sQ_password_new_again) = i::p()->val('password_old', 'password_new', 'password_new_again')->end();
			$password_md5 = file_get_contents(GM_ADMIN_PATH_PASSWORD);
			if (md5($sQ_password_old) != $password_md5)
				{
				showmessage('旧密码不正确', gpf::url('...'));
				}
			if (!$sQ_password_new)
				{
				showmessage('新密码不能为空，请重新输入！', gpf::url('...'));
				}
			if ($sQ_password_new != $sQ_password_new_again)
				{
				showmessage('两新输入的新密码不一致，请重新输入！', gpf::url('...'));
				}
			file_put_contents(GM_ADMIN_PATH_PASSWORD, md5($sQ_password_new));
			showmessage('密码修改成功', gpf::url('admin.index.main'));
			}
		include tpl_admin('repassword');
	}//}}}
}

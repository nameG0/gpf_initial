<?php
/**
 * 后台登录验证
 * 
 * @package default
 * @filesource
 */

function admin_check()
{//{{{
	if (!isset($_SESSION[GM_ADMIN_SESSION_KEY]) || !$_SESSION[GM_ADMIN_SESSION_KEY])
		{
		showmessage('还未登录', gpf::url("admin.login.index"));
		}
}//}}}

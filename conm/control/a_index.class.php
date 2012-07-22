<?php
/**
 * 后台管理初始控制器
 * 
 * @package default
 * @filesource
 */
class ctrl_a_index
{
	function __construct()
	{//{{{
		admin_check();
	}//}}}
	function action_left()
	{//{{{
		log::is_print(false);
		include tpl_admin('a_left');
	}//}}}
}

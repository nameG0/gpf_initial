<?php
/**
 * 后台初始入口
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
	function left()
	{//{{{
		global $CATEGORY;
		log::is_print(false);
		include tpl_admin('a_left');
	}//}}}
}

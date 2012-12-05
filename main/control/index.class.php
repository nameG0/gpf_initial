<?php
/**
 * 显示一些模板
 * 
 * @package default
 * @filesource
 */

// require_once dirname(__FILE__) . "/../include/common.inc.php";
// module_init('category');

class ctrl_index
{
	function action_index()
	{//{{{
		include tpl('index', 'main');
	}//}}}

	/**
	 * 联系我们页
	 */
	function action_lxwm()
	{//{{{
		echo '联系我们';
		// include template('main', 'lianxifangshi');
	}//}}}
}

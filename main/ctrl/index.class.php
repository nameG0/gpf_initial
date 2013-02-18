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
		$this->tpl_index();
		// include tpl('index', 'main');
	}//}}}
	function tpl_index()
	{//{{{
		?>
<p ><a href="?a=pc8gb">留言模块</a></p>
		<?php
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

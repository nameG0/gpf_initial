<?php
/**
 * 模块间通讯函数
 * 
 * @version 2012-04-04
 * @package default
 * @filesource
 */

/**
 * 加载模块 include/init.inc.php 文件。
 */
function mod_init($mod)
{//{{{
	
}//}}}

/**
 * 读取指定模块信息。
 * @param string $mod 模块名。
 * @param NULL|string $key 信息名，NULL 表示返回所有信息，此时会返回数组。
 * @return mixed|false
 */
function mod_info($mod, $key = NULL)
{//{{{
	if ('main' == $mod && 'path_full' == $key)
		{
		return G_PATH_MOD_RUN . 'main' . DS;
		}
}//}}}

/**
 * 进行模块的 callback 操作。包括，注册，提取。
 */
//mod_callback

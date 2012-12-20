<?php
/**
 * 模块初始化
 * 
 * 哪些模块需要使用 SIUD ，直接 include 此文件即可。
 * @package default
 * @filesource
 */
if (defined('SIUD_PATH'))
	{
	return ;
	}
define('SIUD_PATH', dirname(dirname(__FILE__)) . '/');
//define('SIUD', SIUD_PATH . 'api/siud.inc.php');
/**
 * 查询器（SELECT）
 * @see select.inc.php
 */
define('SIUD_SELECT', SIUD_PATH . 'include/select.inc.php');
/**
 * 记录保存器（SAVE）
 * @see save.inc.php
 */
define('SIUD_SAVE', SIUD_PATH . 'include/save.inc.php');
/**
 * 插入器（INSERT）
 * @see insert.inc.php
 */
define('SIUD_INSERT', SIUD_PATH . 'include/insert.inc.php');
/**
 * 更新器（UPDATE）
 * @see update.inc.php
 */
define('SIUD_UPDATE', SIUD_PATH . 'include/update.inc.php');
/**
 * 删除器（DELETE）
 * @see delete.inc.php
 */
define('SIUD_DELETE', SIUD_PATH . 'include/delete.inc.php');

require_once SIUD_PATH . 'api/global.func.php';
require_once SIUD_PATH . 'api/paging.func.php';
require_once SIUD_PATH . 'api/html_dom.func.php';
require_once SIUD_PATH . 'api/siud.class.php';
require SIUD_PATH . "api/find.class.php";

require_once SIUD_PATH . 'include/siud.func.php';

/**
 * 使用 template 目录下的模板文件
 * @return string 模板路径，直接 include 便可
 */
function a_siud_tpl($tpl)
{//{{{
	return SIUD_PATH . "template/{$tpl}.tpl.php";
}//}}}

<?php
/**
 * ģ���ʼ��
 * 
 * ��Щģ����Ҫʹ�� SIUD ��ֱ�� include ���ļ����ɡ�
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
 * ��ѯ����SELECT��
 * @see select.inc.php
 */
define('SIUD_SELECT', SIUD_PATH . 'include/select.inc.php');
/**
 * ��¼��������SAVE��
 * @see save.inc.php
 */
define('SIUD_SAVE', SIUD_PATH . 'include/save.inc.php');
/**
 * ��������INSERT��
 * @see insert.inc.php
 */
define('SIUD_INSERT', SIUD_PATH . 'include/insert.inc.php');
/**
 * ��������UPDATE��
 * @see update.inc.php
 */
define('SIUD_UPDATE', SIUD_PATH . 'include/update.inc.php');
/**
 * ɾ������DELETE��
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
 * ʹ�� template Ŀ¼�µ�ģ���ļ�
 * @return string ģ��·����ֱ�� include ���
 */
function a_siud_tpl($tpl)
{//{{{
	return SIUD_PATH . "template/{$tpl}.tpl.php";
}//}}}

<?php
echo 'this is gpf.cls.php';
/**
 * GPF 主类。
 * 
 * 在类方法中使用中断式错误提示(showmessage)方案。
 * 在类中直接进行错误提示主要是为方便使用，无需调用者每次都要手动处理错误信息。
 * 需要做错误提示的类直接调用 gpf::err()。
 *
 * @version 2012-05-05
 * @package default
 * @filesource
 */
class gpf
{
	/**
	 * 构造方法声明为private，防止直接创建对象
	 */
	private function __construct() {
	}

}

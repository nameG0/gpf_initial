<?php
/**
 * 空查询器。
 * 
 * @package callback
 * @filesource
 */

class siudFind_siud__example
{
	/**
	 * 查询器必须有的初始化查询参数方法。
	 */
	function init()
	{//{{{
		
	}//}}}

	/**
	 * 执行查询方法。返回查询结果。
	 */
	function ing()
	{//{{{
		return array();
	}//}}}

	/**
	 * 空方法
	 */
	function __call($name, $args)
	{//{{{
		return $this;
	}//}}}
}

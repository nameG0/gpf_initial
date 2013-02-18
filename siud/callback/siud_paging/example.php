<?php
/**
 * 分页样式示例
 * <pre>
 * <b>传入参数</b>
 * $page_count int 总页数
 * $page_current int 当前页码
 * $urlrule string 分页链接。其中 {page} 表示页数应放的位置。
 * <b>输出</b>
 * $pages 分页HTML代码
 * 注：在此文件中不应产生直接输出。
 * <b>功能函数</b>
 * _paging_url($urlrule, $i) 生成 $i 页的分页链接
 * _paging_style($style) 取指定样式绝对路径，可以通过此函数在其它分页样式基础上建立样式。
 * </pre>
 * 
 * @package default
 * @filesource
 */

<?php 
/**
 * hd 扩展
 * 
 * @package default
 * @filesource
 */

/**
 * 输出显示成树状结构的栏目下拉框
 * @param string $module 提取栏目所属模块
 * @param int $parentid 所提取栏目的父ID
 * @param string $name 表单项 name
 * @param string $id 表单项id
 * @param string $alt
 * @param int $catid
 * @param string $property
 * @param int $type 排序的栏目分类，0表示不排除。
 * @param int $optgroup
 */
function hd_cms__select_category($attr)
{//{{{
	a::i($attr)->d('module', 'cms')->d('parentid', 0)->d('name', 'catid')->d('id', '')->d('alt', '')->d('catid', 0)->d('property', '')->d('type', 0)->d('optgroup', 0);
	extract($attr);

	global $CATEGORY;
	mod_init('tree');
	require_once TREE_PATH . 'include/tree.class.php';
	$tree = new tree;
	if(!$id) $id = $name;
	if($optgroup) $optgroup_str = "<optgroup label='\$name'></optgroup>";
	$data = "<select name='$name' id='$id' $property>\n<option value='0'>$alt</option>\n";
	if(is_array($CATEGORY))
		{
		$categorys = array();
		foreach($CATEGORY as $id=>$cat)
			{
			if(($type == 2 && $cat['type'] ==2) || ($type == 1 && $cat['type'])) continue;
			if($cat['module'] == $module) $categorys[$id] = array('id'=>$id, 'parentid'=>$cat['parentid'], 'name'=>$cat['catname']);
			}
		$tree->tree($categorys);
		$data .= $tree->get_tree($parentid, "<option value='\$id' \$selected>\$spacer\$name</option>\n", $catid, '' , $optgroup_str);
		}
	$data .= '</select>';
	return $data;
}//}}}

//级联式栏目下拉框
//$name	$id 表单项属性
//$catid	默认选择的栏目
function hd_cms__select_categoryi($catid = 0, $name = 'catid', $id = '')
{//{{{
	$id = $id ? $id : str_replace(array('[', ']'), array('', ''), $name);
	return "<input type=\"hidden\" name=\"{$name}\" id=\"{$id}\" value=\"{$catid}\"><span id=\"load_{$id}\"></span><script type=\"text/javascript\">category_load_simple('{$catid}','{$id}');</script>";
}//}}}

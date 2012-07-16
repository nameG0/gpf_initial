<!--
<a href="<?=gpf::url(".a_category.manage")?>" target="main">管理栏目</a>
<br />
-->
<div id="content_manage">
	<?php
	mod_init('tree');
	require_once TREE_PATH . 'include/tree.class.php';
	$tree = new tree;
	if(is_array($CATEGORY))
		{
		$categorys = array();
		foreach($CATEGORY as $id=>$cat)
			{
			if(($type == 2 && $cat['type'] ==2) || ($type == 1 && $cat['type'])) continue;
			if($cat['module'] == 'cms') $categorys[$id] = array('id'=>$id, 'parentid'=>$cat['parentid'], 'name'=>$cat['catname']);
			}
		$tree->tree($categorys);
		echo $tree->get_tree(0, "<div>\$spacer<a href=\\\"" . gpf::url("cms.a_content.manage") . "&catid=\$id\\\" target=\\\"main\\\">\$name</a></div>\n");
		}
	?>
</div>

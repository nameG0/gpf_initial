<a href="<?=gpf::url(".a_category.manage")?>" target="main">管理栏目</a>
<hr />
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
			if($cat['module'] == 'cms' && $cat['type'] <= 1)
				{
				if (0 == $cat['type'])
					{
					$url = gpf::url("cms.a_content.manage.&catid={$cat['catid']}");
					}
				else
					{
					$url = gpf::url("cms.a_category.edit_content.&catid={$cat['catid']}");
					}
				$categorys[$id] = array('id'=>$id, 'parentid'=>$cat['parentid'], 'name'=>$cat['catname'], "url" => $url,);
				}
			}
		$tree->tree($categorys);
		echo $tree->get_tree(0, "<div>\$spacer<a href=\\\"\$url\\\" target=\\\"main\\\">\$name</a></div>\n");
		}
	?>
</div>

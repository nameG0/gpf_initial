<?php
$_url = gpf::url("@f") . '?a=admin.menu.child_menu&menuid=';
$url_pos = gpf::url("@f") . '?a=admin.menu.menu_pos&menuid=';
$_site_url = gpf::url('@s') . '/pc8/skin_admin/';
$max = count($data) - 1;
$auto_open = array(); //保存需自动展开的menuid
foreach ($data as $k => $r)
	{
	if ($r['isopen'])
		{
		$auto_open[] = $r['menuid'];
		}
	if ($r['isfolder'])
		{
		$touimg = 'elbow-plus.gif';
		$isend = 'tree_line';
		if ($k === $max)
			{
			$touimg = 'elbow-end-plus.gif';
			$isend = 'end';
			}
	?>
	<div class="tree_div" id="menu_<?=$r['menuid']?>" id_child="menu_child_<?=$r['menuid']?>" is_folder="1" is_open="0" url_child="<?=$_url . $r['menuid']?>" url_pos="<?=$url_pos . $r['menuid']?>" onclick="menu_click(this);">
		<span class="tree_img"><img src="<?=$_site_url?>images/<?=$touimg?>" id="touimg_<?=$r['menuid']?>" width="16" height="18" border="0" /><img src="<?=$_site_url?>images/folder.gif" id="img_<?=$r['menuid']?>" width="16" height="16" border="0" /></span><span class="tree_text"><?=$r['name']?></span></div>
	<div id="menu_child_<?=$r['menuid']?>" class="<?=$isend?>" style="display:none;"></div>
	<?php
		}
	else
		{
		$touimg = $k === $max ? 'elbow-end.gif' : 'elbow.gif';
		?>
	<div class="tree_div" is_folder="0" url_pos="">
		<span class="tree_img"><img src="<?=$_site_url?>images/<?=$touimg?>" id="touimg_<?=$r['menuid']?>" width="16" height="18" border="0" /><img src="<?=$_site_url?>images/leaf.gif" id="img_<?=$r['menuid']?>" width="16" height="16" border="0" /></span>
		<a href="<?=$r['url']?>" target="right" class="tree_text"><?=$r['name']?></a>
	</div>
		<?php
		}
	}
if ($auto_open)
	{
	?><script type="text/javascript"><?php
	foreach ($auto_open as $id)
		{
		?>$("#menu_<?=$id?>").click();<?php
		}
	?></script><?php
	}

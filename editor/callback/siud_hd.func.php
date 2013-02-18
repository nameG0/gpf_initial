<?php
/**
 * 输出fckeditor编辑器表单
 * <pre>
 * textareaid string 关联的文本框ID
 * toolbar string 工具栏类型
 * width string 编辑器宽度
 * height string 编辑器高度
 * isshowext bool 未知
 * </pre>
 * @param array $set
 * @return string HTML代码
 */
function hd_editor__fck($set)
{//{{{
	$str = '';
	a::i($set)->d('textareaid', 'content')->d('toolbar', 'standard')->d('width', '100%')->d('height', 400)->d('isshowext', false);
	//ggzhu@2012-07-24 如果设置了 $set['value'] 则自动输出文本框
	if (isset($set['value']))
		{
		$set['value'] = htmlspecialchars($set['value']);
		$str .= "<textarea name=\"{$set['name']}\" id=\"{$set['textareaid']}\" style=\"display:none;\">{$set['value']}</textarea>\n";
		}
	$SITE_URL = '/gz/';
	$str .= "<script type=\"text/javascript\" src=\"{$SITE_URL}static/editor/fckeditor/fckeditor.js\"></script>\n<script language=\"JavaScript\" type=\"text/JavaScript\">var SiteUrl = \"{$SITE_URL}\"; var Module = \"{$mod}\"; var sBasePath = \"{$SITE_URL}\" + 'static/editor/fckeditor/'; var oFCKeditor = new FCKeditor( '".$set['textareaid']."' ) ; oFCKeditor.BasePath = sBasePath ; oFCKeditor.Height = '".$set['height']."'; oFCKeditor.Width	= '".$set['width']."' ; oFCKeditor.ToolbarSet	= '".$set['toolbar']."' ;oFCKeditor.ReplaceTextarea();";
	if($_userid && $isshowext)
	{
		$str .= "editor_data_id += '".$textareaid."|';if(typeof(MM_time)=='undefined'){MM_time = setInterval(update_editor_data,".($PHPCMS['editor_interval_data']*1000).");}";
	}
	$str .= "</script>";
	if($set['isshowext'])
	{
		$str .= "<div style='width:$width;text-align:left'>";
		if($_userid)
		{
			$str .= "<span style='float:right;height:22px'>";
			if(defined('IN_ADMIN') && $mod == 'phpcms' && $file == 'content')
			{
				$str .= "<span style='padding:1px;margin-right:10px;background-color: #fefe;border:#006699 solid 1px;'><a href='javascript:insert_page(\"$textareaid\")' title='在光标处插入分页标记'>分页</a></span>";
				$str .= "<span style='padding:1px;margin-right:10px;background-color: #fefe;border:#006699 solid 1px;'><a href='javascript:insert_page_title(\"$textareaid\")' title='在光标处插入带子标题的分页标记'>子标题</a></span>";
			}
			$str .= "<span style='padding:1px;margin-right:10px;background-color: #fefe;border:#006699 solid 1px;'><div id='page_title_div' style='background-color: #fff;border:#006699 solid 1px;position:absolute;z-index:10;padding:1px;display:none;right:80px;'>
			<table cellpadding='0' cellspacing='1' border='0'><tr><td >请输入子标题名称：<span id='msg_page_title_value'></span></td><td><span style='cursor:pointer;float:right;' onclick='javascript:$(\"#page_title_div\").hide()'>×</span></td>
			<tr><td colspan='2'><input name='page_title_value' id='page_title_value' value='' size='40'>&nbsp;<input type='button' value=' 确定 ' onclick=insert_page_title(\"$textareaid\",1)></td></tr>
			</table></span></div>";

			$str .= "<span style='padding:1px;margin-right:10px;background-color: #fefe;border:#006699 solid 1px;'><div id='".$textareaid."_div' style='background-color: #fff;border:#006699 solid 1px;position:absolute;z-index:10;padding:5px;display:none;right:60px;'>
					<table cellpadding='0' cellspacing='1' border='0'><tr><td>		
					<div>";
			for($i=1; $i<=$PHPCMS['editor_max_data_hour']; $i++)
			{
				$bold = $i==1 ? "font-weight: bold;" : '';
				$str .= "<a href='javascript:get_editor_data_list(\"".$textareaid."\",$i)' class='hour' style='border:#cccccc solid 1px;margin:2px;padding-left:4px;padding-right:4px;$bold' title='$i 小时'>$i</a>";	
			}
			$str .= "</div></td><td><span style='cursor:pointer;' onclick='javascript:$(\"#".$textareaid."_div\").hide()'>×</span></td></tr></table><ul id='".$textareaid."_lists' style='height:200px;width:140px;overflow:auto;'></ul></div><a href='javascript:get_editor_data_list(\"".$textareaid."\",1)' title='点击恢复数据'>恢复数据</a></span></span>";
		}
		$str .= "<img src=\"".SITE_URL."images/phpcms/editor_add.jpg\" title='增加编辑器高度' tag='1' fck=\"".$textareaid."\"/>&nbsp;  <img src=\"".SITE_URL."images/phpcms/editor_diff.jpg\" title='减少编辑器高度' tag='0' fck=\"".$textareaid."\"/></div>";
	}
	$str .= "<div id=\"MM_file_list_".$textareaid."\" style=\"text-align:left\"></div><div id='FilePreview' style='Z-INDEX: 1000; LEFT: 0px; WIDTH: 10px; POSITION: absolute; TOP: 0px; HEIGHT: 10px; display: none;'></div><div id='".$textareaid."_save'></div>";
	return $str;
}//}}}

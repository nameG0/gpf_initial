<?php 
/*
2011-10-17
栏目 字段
*/

function content_field_catid_add($tablename, $info, $setting)
{//{{{
	global $db;
	if(!$maxlength) $maxlength = 255;
	$maxlength = min($maxlength, 255);
	$sql = "ALTER TABLE `$tablename` ADD `$field` VARCHAR( $maxlength ) NOT NULL DEFAULT '$defaultvalue'";
	$db->query($sql);
}//}}}

function content_field_catid_change()
{//{{{
	
}//}}}

function content_field_catid_drop($tablename, $info, $setting)
{//{{{
	global $db;
	$db->query("ALTER TABLE `$tablename` DROP `$field`");
}//}}}

function content_field_catid_setting($info, $setting)
{//{{{
	?>
<table cellpadding="2" cellspacing="1">
	<tr> 
      <td>默认值</td>
      <td><input type="text" name="setting[defaultvalue]" value="<?=$defaultvalue?>" size="5"></td>
    </tr>
</table>
	<?php
}//}}}

function content_field_catid_form($field, $value, $fieldinfo)
{//{{{
		global $CATEGORY,$action,$priv_role;
		extract($fieldinfo);
		if(defined('IN_ADMIN'))
		{
			$data = "<select name=\"info[$field]\" id=\"$field\">";
			//$modelid = $CATEGORY[$value]['modelid'];
			$modelid = $fieldinfo['modelid'];
			$role_num = 0;
			foreach($CATEGORY as $C)
			{
				if($C['modelid']==$modelid && $C['child']==0)
				{
					// if($priv_role->check('catid', $value, $action))
					// {
						if($C['catid']==$value) $tag = 'selected';
						else $tag = '';
						$data .= "<option value=\"".$C['catid']."\" ".$tag.">".$C['catname']."</option>";
						$role_num++;
					// }
				}
			}
			$data .= '</select>';
			if($role_num)
			{
				$data .= "<input type=\"hidden\" name=\"old_{$field}\" value=\"$value\">";
			}
			else
			{
				$catname = $CATEGORY[$value]['catname'];
				$data .= "<input type=\"hidden\" name=\"info[$field]\" id=\"$field\" value=\"$value\"> $catname";
			}
		}
		else
		{
			$catname = $CATEGORY[$value]['catname'];
			$data = "<input type=\"hidden\" name=\"info[$field]\" id=\"$field\" value=\"$value\"> $catname";
		}
		$publishCats = '';
		//if(defined('IN_ADMIN') && $action=='add') $publishCats = "<a href='' class=\"jqModal\" onclick=\"$('.jqmWindow').show();\"/> [同时发布到其他栏目]</a>";
		return $data.' '.$publishCats;
}//}}}

function content_field_catid_output($field, $value)
{//{{{
	return $value;
}//}}}

function content_field_catid_tag_form($field, $value, $fieldinfo)
{
	global $CATEGORY;
	extract($fieldinfo);
	$js = "<script type=\"text/javascript\">
				function category_load(id)
				{
					\$.get('load.php', { field: 'catid', id: id },
						  function(data){
							\$('#load_$field').append(data);
						  });
				}
				function category_reload()
				{
					\$('#load_$field').html('');
					category_load(0);
				}
				category_load(0);
		</script>";
	if($value)
	{
		$catname = $CATEGORY[$value]['catname'];
		return "<input type=\"text\" name=\"info[$field]\" id=\"$field\" value=\"$value\" size=\"10\">
		<span onclick=\"this.style.display='none';\$('#reselect_$field').show();\" style=\"cursor:pointer;\">$catname <font color=\"red\">点击重选</font></span>
		<span id=\"reselect_$field\" style=\"display:none;\">
		<span id=\"load_$field\"></span> 
		<a href=\"javascript:category_reload();\">重选</a>
		</span>$js";
	}
	else
	{
		return "<input type=\"text\" name=\"info[$field]\" id=\"$field\" value=\"$value\" size=\"10\">
		<span id=\"load_$field\"></span>
		<a href=\"javascript:category_reload();\">重选</a>$js";
	}
}

function content_field_catid_tag($field, $value)
{
     return $value === '' ? '' : '".get_sql_catid('.$value.')."'; 
}

function content_field_catid_search($field, $value)
{
	$value = get_sql_catid($value);
	$value = str_replace('AND','',$value);
	return $value === '' ? '' : " $value "; 
}

function content_field_catid_search_form($field, $value, $fieldinfo)
{
	global $CATEGORY;
	extract($fieldinfo);
	$js = "<script type=\"text/javascript\">
				function category_load(id)
				{
					\$.get('load.php', { field: 'catid', id: id },
						  function(data){
							\$('#load_$field').append(data);
						  });
				}
				function category_reload()
				{
					\$('#load_$field').html('');
					category_load(0);
				}
				category_load(0);
		</script>";
	if($value)
	{
		$catname = $CATEGORY[$value]['catname'];
		return "<input type=\"hidden\" name=\"$field\" id=\"$field\" value=\"$value\">
		<span onclick=\"this.style.display='none';\$('#reselect_$field').show();\" style=\"cursor:pointer;\">$catname <font color=\"red\">点击重选</font></span>
		<span id=\"reselect_$field\" style=\"display:none;\">
		<span id=\"load_$field\"></span> 
		<a href=\"javascript:category_reload();\">重选</a>
		</span>$js";
	}
	else
	{
		return "<input type=\"hidden\" name=\"$field\" id=\"$field\" value=\"$value\">
		<span id=\"load_$field\"></span>
		<a href=\"javascript:category_reload();\">重选</a>$js";
	}
}
?>

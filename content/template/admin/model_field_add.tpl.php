<?php 
defined('IN_PHPCMS') or exit('Access Denied');
include admin_tpl('header');
$url_setting_add = admin_url("..setting_add..modelid,fieldid");
?>
<body >
<?=$menu?>
<table cellpadding="0" cellspacing="1" class="table_form">
<form action="<?=admin_url("....modelid,fieldid")?>" method="post" name="myform">
    <caption>添加字段</caption>
	<tr> 
      <th width="25%"><font color="red">*</font> <strong>字段名</strong><br />
	  只能由英文字母、数字和下划线组成，并且仅能字母开头，不以下划线结尾
	  </th>
      <td>
      <?php
if ('edit' == $action)
      	{
	?>
	<input type="hidden" name="info[field]" value="<?=$info['field']?>" />
	<?php
      	echo $info['field'];
      	}
else
	{
	?>
	<input type="text" name="info[field]" size="20" require="true" datatype="limit|ajax" min="1" max="20" url="?mod=<?=$mod?>&file=<?=$file?>&action=checkfield&modelid=<?=$modelid?>" msg="字符长度范围必须为1到10位|"></td>
	<?php
	}
      ?>
    </tr>
	<tr> 
      <th><font color="red">*</font> <strong>字段别名</strong><br />例如：文章标题</th>
      <td><input type="text" name="info[name]" value="<?=$info['name']?>" size="30" require="true" datatype="limit" min="1" max="50" msg="字符长度范围必须为1到50位"></td>
    </tr>
	<tr> 
      <th><strong>字段提示</strong><br />显示在字段别名下方作为表单输入提示</th>
      <td><textarea name="info[tips]" value="<?=$info["tips"]?>" rows="2" cols="20" id="tips" style="height:60px;width:250px;"></textarea></td>
    </tr>
	<tr> 
      <th><strong>字段类型</strong><br /></th>
      <td>
<?php
$formtype = $info['formtype'];
if ('edit' == $action)
	{
	?>
	<input type="hidden" name="info[formtype]" value="<?=$formtype?>" />
	<?php
	echo is_array($fields[$formtype]) ? $fields[$formtype][0] : $fields[$formtype];
	}
else
	{
?>
      <select name="info[formtype]" id="formtype" <?='edit' == $action ? 'disabled' : 'onchange="javascript:$(\'#setting\').load(\'' . $url_setting_add . '&formtype=\'+this.value);"'?> >
<?php 
foreach($fields as $type=>$name)
{
	$selected = $type == $formtype ? 'selected' : '';
	$name = is_array($name) ? $name[0] : $name;
	echo "<option value=\"{$type}\" {$selected}>{$name}</option>\n";
} 
?>
	  </select>
<?php
	}
?>
	  </td>
    </tr>
	<tr>
      <th><strong>相关参数</strong><br />设置表单相关属性</th>
      <td>
<?php
if ('edit' == $action)
	{
	if (is_array($fields[$formtype]))
		{
		$path = PHPCMS_ROOT . "{$fields[$formtype][1]}/fields/{$formtype}.inc.php";
		if (is_file($path))
			{
			require_once $path;
			$func_name = "content_field_{$formtype}_setting";
			$func_name($info, $setting);
			}
		}
	else
		{
		require_once CONTENT_ROOT . 'fields/'.$formtype.'/field_add_form.inc.php';
		}
	}
else
	{
?>
<div id="setting"></div>
<script type="text/javascript">
$('#setting').load('<?=$url_setting_add?>&formtype=' + myform.formtype.value);
</script>
<?php	
	}
?>
      </td>
    </tr>
	<tr> 
      <th><strong>表单附加属性</strong><br />可以通过此处加入javascript事件</th>
      <td><input type="text" name="info[formattribute]" value="<?=$info['formattribute']?>" size="50"></td>
    </tr>
	<tr> 
      <th><strong>表单样式名</strong><br />定义表单的CSS样式名</th>
      <td><input type="text" name="info[css]" value="<?=$info['css']?>" size="10"></td>
    </tr>
	<tr> 
      <th><strong>字符长度取值范围</strong><br />系统将在表单提交时检测数据长度范围是否符合要求，如果不想限制长度请留空</th>
      <td>
      最小值：<input type="text" name="info[minlength]" id="minlength" value="<?=intval($info['minlength'])?>" size="5">
      最大值：<input type="text" name="info[maxlength]" id="maxlength" value="<?=$info['maxlength']?>" size="5">
      </td>
    </tr>
	<tr> 
      <th><strong>数据校验正则</strong><br />系统将通过此正则校验表单提交的数据合法性，如果不想校验数据请留空</th>
      <td><input type="text" name="info[pattern]" id="pattern" value="<?=$info['pattern']?>" size="40"> 
<select name="pattern_select" onchange="javascript:$('#pattern').val(this.value)">
<option value="">常用正则</option>
<?php 
foreach($patterns as $p)
{
	echo "<option value=\"{$p['pattern']}\">{$p['name']}</option>\n";
}
?>
</select>
	  </td>
    </tr>
	<tr> 
      <th><strong>数据校验未通过的提示信息</strong><br />当表单数据不满足正在校验时的系统提示信息</th>
      <td><input type="text" name="info[errortips]" value="<?=$info['errortips']?>" size="50"></td>
    </tr>
	<tr> 
      <th><strong>值唯一</strong></th>
      <td>
      <label>
      <input type="radio" name="info[isunique]" value="1" <?=($info['isunique'] ? 'checked' : '')?> /> 是
      </label>
      <label>
      <input type="radio" name="info[isunique]" value="0" <?=($info['isunique'] ? '' : 'checked')?> /> 否
      </label>
      </td>
    </tr>
	<tr> 
      <th><strong>作为基本信息</strong><br />本选项只在添加、修改信息时起作用，选“是”则在信息的基本选项中显示，否则在高级选项中显示</th>
      <td>
      <label>
      <input type="radio" name="info[isbase]" value="1" <?=($info['isbase'] ? 'checked' : '')?> /> 是
      </label>
      <label>
      <input type="radio" name="info[isbase]" value="0" <?=($info['isbase'] ? '' : 'checked')?> /> 否
      </label>
      </td>
    </tr>
	<tr> 
      <th><strong>作为搜索条件</strong></th>
      <td>
      <label>
      <input type="radio" name="info[issearch]" value="1" <?=($info['issearch'] ? 'checked' : '')?> /> 是
      </label>
      <label>
      <input type="radio" name="info[issearch]" value="0" <?=($info['issearch'] ? '' : 'checked')?> /> 否
      </label>
      </td>
    </tr>
	<tr> 
      <th><strong>作为标签默认读取字段</strong></th>
      <td>
      <label>
      <input type="radio" name="info[isselect]" value="1" <?=($info['isselect'] ? 'checked' : '')?> /> 是
      </label>
      <label>
      <input type="radio" name="info[isselect]" value="0" <?=($info['isselect'] ? '' : 'checked')?> /> 否
      </label>
      </td>
    </tr>
	<tr> 
      <th><strong>作为标签调用条件</strong></th>
      <td>
      <label>
      <input type="radio" name="info[iswhere]" value="1" <?=($info['iswhere'] ? 'checked' : '')?> /> 是
      </label>
      <label>
      <input type="radio" name="info[iswhere]" value="0" <?=($info['iswhere'] ? '' : 'checked')?> /> 否
      </label>
      </td>
    </tr>
	<tr> 
      <th><strong>作为标签调用排序字段</strong></th>
      <td>
      <label>
      <input type="radio" name="info[isorder]" value="1" <?=($info['isorder'] ? 'checked' : '')?> /> 是
      </label>
      <label>
      <input type="radio" name="info[isorder]" value="0" <?=($info['isorder'] ? '' : 'checked')?> /> 否
      </label>
      </td>
    </tr>
	<tr> 
      <th><strong>在前台投稿中显示</strong></th>
      <td>
      <label>
      <input type="radio" name="info[isadd]" value="1" <?=($info['isadd'] ? 'checked' : '')?> /> 是
      </label>
      <label>
      <input type="radio" name="info[isadd]" value="0" <?=($info['isadd'] ? '' : 'checked')?> /> 否
      </label>
      </td>
    </tr>
	<tr> 
      <th><strong>作为全站搜索信息</strong></th>
      <td>
      <label>
      <input type="radio" name="info[isfulltext]" value="1" <?=($info['isfulltext'] ? 'checked' : '')?> /> 是
      </label>
      <label>
      <input type="radio" name="info[isfulltext]" value="0" <?=($info['isfulltext'] ? '' : 'checked')?> /> 否
      </label>
      </td>
    </tr>
	<tr> 
      <th><strong>禁止设置字段值的会员组</strong></th>
      <td><?=$unsetgroups?></td>
    </tr>
	<tr> 
      <th><strong>禁止设置字段值的角色</strong></th>
      <td><?=$unsetroles?></td>
    </tr>
    <tr> 
      <th></th>
      <td> 
	  <input type="hidden" name="forward" value="<?=admin_url("..manage..modelid")?>"> 
	  <input type="submit" name="dosubmit" value=" 确定 "> 
      &nbsp; <input type="reset" name="reset" value=" 清除 ">
	  </td>
    </tr>
	</form>
</table>
</body>
</html>
<script LANGUAGE="javascript">
<!--
$().ready(function() {
	  $('form').checkForm(1);
	});
//-->
</script>

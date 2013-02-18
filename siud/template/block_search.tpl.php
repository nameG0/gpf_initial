<?php					
/**
 * 后台搜索表单组件,一般配合 t.func.php > make_search() 函数一起使用
 * 
 * 2011-05-19<br/>
 * [...]	表示括号内的参数是可选的<br/>
 * {field} 搜索项名<br/>
 * phpstring 表示将用 eval() 执行的 php 双引号字符串<br/>
 * phpcode	表示将用 eval() 执行的 php 代码<br/>
 * 
 * @package template
 * @filesource
 */
//参数及其默认值,参数不能为原以下划线起头的全局变量，如 userid。片段参数都以下划线起头，如 $_is_form
$__block_search = array(
	"table_attr_class" => 'table_list',	//表格的 class 属性值
	"caption" => '搜索',	//表格的 caption 属性值
	"action" => '',		//表单的提交地址
	"method" => 'GET',	//表单的提交方式
	"reset_url" => admin_url("...&" . filter_get(array('mod', 'file', 'action', 'w'))),	//重置搜索参数的跳转链接
	"manage" => array(),	//其它链接，将出现在“搜索”钮按右边，二维数组，第一维键名为链接名称，第二维 url 为链接(其实可以直接就为 url,即 [名称] => url ，也就是变为一维数组了
	//搜索项,以二维数组的格式表示各搜索项的位置，如：
		//array(
		//array(第一行第一个搜索项，第一行第二个搜索项),
		//array(第二行第一个搜索项，第二行第二个搜索项),
		//)
	//一般用英文，每个搜索项名称是唯一的，名称同时也是表单项的 name 属性值。
	"display" => array(),
	//表单项默认值, {field} => 值
	"display_default" => array(),
	//即点即搜：
	"is_click_search" => true,	//是否启用“即点即搜”
	"click_search_default" => true,	//“即点即搜”默认状态（开启/关闭）
	"is_click_search_change" => true,	//是否允许用户更改“即点即搜”状态

	//搜索项中文含意：{field} => 中文含意
	"field" => array(),
	//搜索表单项的值：{field} => 值
	"value" => $_GET['w'],
	//搜索项提示, {field} => 提示文字
	"comment" => array(),
	//包含搜索表单项的数组名，以“search”为例，则搜索项单项的 name 属性为 search[搜索项名]
	"array_name" => 'w',

	//搜索类型之 输入(默认搜索类型,所以无需 input 参数)
	//与 select_compare 格式一致，支持的比较符有 like, =, !=, in, not in, between, not between 等
	"input_compare" => array(),
	//直接定义搜索项表单的 html 代码，若无指定 html 或 replace_html，则默认为输入框，{field} => html 代码
	"input_html" => array(),
	//需要对表单名及值进行自动替换的 html 代码，其中 _name_ 将替换为表单名，_value_ 将替换为表单值，主要是方便用简单统一的参数调用 form 类生成 html 代码，{field} => html 代码
	"input_replace_html" => array(),

	//搜索类型之 选择
	"select" => array(),	//选择类的字段：{field} => [可选值], [可选值]为数组，eg. array(1 => 已审, 0 => 未审)
	//选择类字段的比较符：{field} => [比较符参数]，[比较符参数]包括 "=","!="，数据类型可为数组或字符串，若为数组且数组超过1个元素，则表示比较符允许用户自定义,数组的第一个值为默认的比较符，eg. array('=', '!=') 中 '=' 是默认的比较符，默认的比较符为"="
	"select_compare" => array(),
	//表单项类型：{field} => [类型参数]，[类型参数]包括 "radio"(单选), "checkbox"(多选), 其它同 [比较符参数],默认的类型为 "radio"
	"select_form" => array(),
	//空间压缩，决定把所有选项完全显示还是显示为下拉框，{field} => [参数]，参数有三个可选值：true=总是压缩,false=不压缩, 为某数字=超过指定数字则压缩,默认为 false
	"select_compress" => array(),
	//不显示“全部/清空”功能按钮，array(field, ...), 默认全部字段都显示“全部/清空”按钮（只针对多选框）
	"select_not_clean" => array(),
	);
//设置参数变量, 模板片段参数通过数组传递，变量名为模板片段名，如 $block_quick_userid，在 include 模板片段之前设置即可。
if (is_array($block_search))
	{
	foreach ($block_search as $k => $v)
		{
		$__block_search[$k] = $v;
		}
	}
unset($block_search);

$__block_search['_click_search_js'] = '';	//存放“即点即搜”JS调用代码
if ($__block_search['is_click_search'])
	{
	$__block_search['_click_search_js'] = 'onclick="click_search(this.form)"';
	?>
<script type="text/javascript">
var is_click_search = <?=$__block_search['click_search_default'] ? 1 : 0?>;
function click_search(form)
{
	if (is_click_search)
		{
		form.submit();
		}
}
</script>
	<?php
	}
?>
<form action="<?=$__block_search["action"]?>" method="<?=$__block_search["method"]?>" enctype="multipart/form-data">
<?php
$new_get = filter_get(array('w'), 'array');
foreach ($new_get as $k => $v)
	{
?>
<input type="hidden" name="<?=$k?>" value="<?=$v?>" />
<?php
	}
unset($new_get);
?>
<table class="<?=$__block_search["table_attr_class"]?>" cellpadding="0" cellspacing="1" >
<?php
if ($__block_search['caption'])
	{
	?>
	<caption><?=$__block_search["caption"]?></caption>
	<?php
	}
?>
	<?php
	foreach ($__block_search['display'] as $line)
		{
	?>
	<tr>
		<td>
		<?php
		$line = is_array($line) ? $line : array($line);
		foreach ($line as $key)
			{
			$name = $__block_search['array_name'] ? "{$__block_search['array_name']}[{$key}]" : $key;
			echo $__block_search["field"][$key] ? $__block_search['field'][$key] : $key;
			echo ':';
			//搜索类型之 选择
			if ($__block_search['in'][$key])
				{
				//表单类型
				$_select_form = 'radio';
				if ($__block_search['select_form'][$key])
					{
					$_select_form = $__block_search['select_form'][$key];
					}
				//分析当前所选值
				$_value = '';
				if (isset($__block_search['value'][$key]))
					{
					$_value = $__block_search['value'][$key];
					}
				else if (isset($__block_search['display_default'][$key]))
					{
					$_value = $__block_search['display_default'][$key];
					}
					//多选时把值转为数组
				if ('checkbox' == $_select_form)
					{
					$_value = '' === $_value ? array() : (array)$_value;
					$name = $name . '[]';
					}
				//“全部/清空”按钮
				if (!in_array($key, $__block_search['select_not_clean']))
					{
					if ('radio' == $_select_form)
						{
				?>
				<label><input name="<?=$name?>" type="<?=$_select_form?>" value="" <?='' === $_value ? 'checked' : ''?> <?=$__block_search['_click_search_js']?> />全部</label>
				<?php
						}
				else
						{
				?>
				<label><input name="<?=$name?>" type="<?=$_select_form?>" value="" onclick="$('input[name=\'' + this.name + '\']').attr('checked', false);click_search(this.form);" />清空</label>
				<?php
						}
					}
				foreach ($__block_search['in'][$key] as $k => $v)
					{
					$_is_checked = false;
					if ('radio' == $_select_form && '' !== $_value && $k == $_value)
						{
						$_is_checked = true;
						}
					else if ('checkbox' == $_select_form && in_array($k, $_value))
						{
						$_is_checked = true;
						}
				?>
				<label><input name="<?=$name?>" type="<?=$_select_form?>" value="<?=$k?>" <?=$_is_checked ? 'checked' : ''?> <?=$__block_search['_click_search_js']?> /><?=$v?></label>
				<?php
					}
				}
			//搜索类型之 输入
			else
				{
				//当前比较符
				$_compare = '=';
				if ($__block_search['value']["c_{$key}"])
					{
					$_compare = $__block_search['value']["c_{$key}"];
					}
				else if ($__block_search['input_compare'][$key])
					{
					if (is_array($__block_search['input_compare'][$key]))
						{
						$_compare = $__block_search['input_compare'][$key][0];
						}
					else
						{
						$_compare = $__block_search['input_compare'][$key];
						}
					}
				//允许用户选择比较符
				if (is_array($__block_search['input_compare'][$key]) && count($__block_search['input_compare'][$key]) > 1)
					{
					//用 c_{$key} 表示此字段的比较符
					?>
					<select name="s[c_<?=$key?>]">
					<?php
					foreach ($__block_search['input_compare'][$key] as $v)
						{
						?>
						<option value="<?=$v?>" <?=$v == $_compare ? 'selected' : ''?>><?=$v?></option>
						<?php
						}
					?>
					</select>
					<?php
					}
				$_value = '';
				if (isset($__block_search['value'][$key]) && '' !== $__block_search['value'][$key])
					{
					$_value = $__block_search['value'][$key];
					}
				else if ($__block_search['display_default'][$key])
					{
					$_value = $__block_search['display_default'][$key];
					}
				$_html = "<input type=\"text\" name=\"{$name}\" value=\"{$_value}\" />";
				if ($__block_search['input_html'][$key])
					{
					$_html = $__block_search['input_html'][$key];
					}
				else if ($__block_search['input_replace_html'][$key])
					{
					$_html = str_replace(array('_name_', '_value_'), array($name, $_value), $__block_search['input_replace_html'][$key]);
					}
				//根据比较符决定是否显示两个输入框
				if ('between' == $_compare)
					{
					$_name_start = $__block_search['array_name'] . "[{$key}_start]";
					$_name_end = $__block_search['array_name'] . "[{$key}_end]";
					$_value_start = $_value_end = $_value;
					if ($__block_search['value']["{$key}_start"])
						{
						$_value_start = $__block_search['value']["{$key}_start"];
						}
					if ($__block_search['value']["{$key}_end"])
						{
						$_value_end = $__block_search['value']["{$key}_end"];
						}
					$_html = str_replace(array($name, $_value), array($_name_start, $_value_start), $_html) . ' - ' . str_replace(array($name, $_value), array($_name_end, $_value_end), $_html);
					}
				echo $_html;
				}
			if ($__block_search['comment'][$key])
				{
				echo $__block_search['comment'][$key];
				}
			echo '&nbsp;';
			}
		?>
		</td>
	</tr>
	<?php
		}
	?>
	<tr>
		<td>
		<a href="<?=$__block_search['reset_url']?>">重置</a>
		<input type="submit" value="搜索" />
		<?php
		if ($__block_search['manage'])
			{
			$middle = '';
			foreach ($__block_search['manage'] as $k => $v)
				{
				if (is_string($v))
					{
					$v = array("url" => $v,);
					}
				?>
				<a href="<?=$v['url']?>"><?=$k?></a><?=$middle?>
				<?php
				$middle = '|';
				}
			unset($middle);
			}
		?>
		</td>
	</tr>
</table>
</form>
<?php
unset($__block_search);
?>

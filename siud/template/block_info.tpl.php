<?php
/**
 * 用于添加及修改数据的模板片段
 * 
 * <b>*display:显示字段</b>
 * <code>
 * "display" => array(
 * 	'id',
 * 	"name" => array(
 * 		"form" => 'span',
 * </code>
 * <b>zh:字段中文</b>
 * <code>
 * "zh" => array(
 * 	"field" => '中文',
 * </code>
 * <b>value:数据</b>
 * <code>
 * "value" => array(
 * 	"field" => 1,
 * </code>
 * <b>comment:字段附加说明</b>
 * <code>
 * "comment" => array(
 * 	"field" => '注释',
 * </code>
 * <b>html:直接设置表单项 HTML 代码</b>
 * <code>
 * 'html' => array(
 * 	'name' => "<input ....>",
 * </code>
 * <b>caption:表格标题</b><br/>
 * <code>
 * "caption" => '添加表格',
 * </code>
 * <b>form:表单设置</b>
 * <code>
 * "form" => array(
 * 	"id" => 'form_id',
 * </code>
 * <b>table:表格属性</b>
 * <code>
 * "table" => array(
 * 	"name" => 'table_name',
 * </code>
 * <b>form_name_pre:表单 name 属性前序</b>
 * <code>
 * "form_name_pre" => 'data', //默认为 data > data[field]
 * </code>
 * <b>func:字段显示函数</b>
 * <code>
 * "func" => array("field" => 'func_name', ...), //能自动绑定 _print_{$field} 函数。
 * //调用参数：
 * $func_name(array 已格式化后的 display 中对应的字段配置);
 * </code>
 * <b>name:表单项名</b>
 * <code>
 * "display" => array(
 * 	"field" => array("name" => 'abc',),
 * </code>
 * <b>form#span:直接输出内容</b>
 * <code>
 * "form" => 'span',
 * </code>
 * <b>form#radio:单选框</b>
 * <code>
 * "form" => 'radio',
 * "in" => array('选项值' => '选项名', ...),
 * </code>
 *
 * @version 2012-04-12
 * @package template
 * @filesource
 */
require_once SIUD_PATH . "include/form.func.php";

$__block_info = array(
	"caption" => '添加',	//表格标题
	//表格与表单设置：
	"form" => array(),
	// "is_form_tag" => true,	//是否输出表单标签头，即 <form>
	// "is_form_close" => true,	//是否输出表单标签尾，即 </form>
	// "is_table_tag" => true,	//是否输出表格标签头，即 <table>
	// "is_table_close" => true,	//是否显示表格标签尾，即 </table>，若此项为 false ，则“提交”按钮也一并隐藏
	// "action" => '',		//表单的提交地址
	// "method" => 'POST',
	// "form_attr" => array(),	//form 标签属性
	"table" => array(),
	// "table_attr" => array(),	//table 标签属性
	"is_form_check" => true,	//是否用 JQuery 验证表单
	"reset_button_name" => ' 清除 ',//重置 按钮的名称
	"form_name_pre" => 'data',	//表单项前序，如 data 则为 data[XX]
	"value" => array(),	//数据

	"display" => array(),	//显示的字段
	//字段配置：
	"zh" => array(),	//字段中文名称
	"comment" => array(),	//字段的说明
	"require" => array(),	//表单验证——必填项
	//"th" => array(),		//th 的示例
	//调用指定函数输出字段表单的字段：{field} => 函数名，调用参数： func($r, $form_name), $form_name 为表单项名
	"func" => array(),
	"html" => array(), //直接设置 HTML 代码。
	
	//表单项类型：
	// "f_hide" => array(),	//以隐藏域显示的字段:array({field}, {field}, )
	// "f_textarea" => array(),	//文本框
	// "f_select" => array(),	//下拉框：{field} => array(下拉框值 => 显示内容,,,)
	// "f_img_file" => array(),
	// "f_file" => array(),
	// "f_password" => array(),
	// "f_checkbox" => array(),
	);
//设置参数变量, 模板片段参数通过数组传递，变量名为模板片段名，如 $block_quick_userid，在 include 模板片段之前设置即可。
if (is_array($block_info))
	{
	foreach ($block_info as $k => $v)
		{
		$__block_info[$k] = $v;
		}
	}
unset($block_info);
//格式化 display. 格式化后 display 格式变为 'field' => array(配置)
$__block_info['display'] = $__block_info['display'] ? $__block_info['display'] : array_keys($__block_info['data']);
$_display = array();
foreach ($__block_info['display'] as $k => $v)
	{
	//转换数字索引的元素为键索引
	if (is_int($k))
		{
		$k = $v;
		$v = array();
		}
	//转换数字索引的配置项，比如 array('require') > ['require'] = 'require'
	foreach ($v as $_k => $_v)
		{
		if (is_int($_k))
			{
			$v[$_v] = $_v;
			unset($v[$_k]);
			}
		}
	//分解键名及数据类型
	$ks = explode(",", $k);
	$v['type'] = array_pop($ks);
	//必须要定义数据类型，没定义的设为 str
	if ('int' != $v['type'] && 'str' != $v['type'])
		{
		$ks[] = $v['type'];
		$v['type'] = 'str';
		}
	$v['form_name_pre'] = $__block_info['form_name_pre'];
	foreach ($ks as $_f)
		{
		$_display[$_f] = $v;
		}
	}
$__block_info['display'] = $_display;
unset($_display, $ks, $_f);
//进行助手类设置合并
$__block_info['_value'] = $__block_info['value']; //字段值在调用字段显示函数时需要用到。
$_set = array('zh', 'require', 'comment', 'value', 'func', 'html');
foreach ($_set as $_key)
	{
	if (!$__block_info[$_key])
		{
		continue;
		}
	foreach ($__block_info[$_key] as $k => $v)
		{
		if ($__block_info['display'][$k])
			{
			$__block_info['display'][$k][$_key] = $v;
			}
		}
	unset($__block_info[$_key]);
	}
unset($_set);
//绑定 func
foreach ($__block_info['display'] as $k => $_set)
	{
	$func_name = "_print_{$k}";
	if (function_exists($func_name) && !$_set['func'])
		{
		$__block_info['display'][$k]['func'] = $func_name;
		}
	}
unset($_set, $func_name);
//格式化所有 display 字段
foreach ($__block_info['display'] as $k => $_set)
	{
	if (!isset($_set['name']))
		{
		$_set['name'] = "{$__block_info['form_name_pre']}[{$k}]";
		}
	if (!isset($_set['id']))
		{
		$_set['id'] = "{$__block_info['form_name_pre']}_{$k}";
		}
	if (!$_set['zh'])
		{
		$_set['zh'] = $k;
		}
	if (!$_set['form'])
		{
		$_set['form'] = 'text';
		}
	if ('int' == $_set['type'] && 'text' == $_set['form'])
		{
		$_set['onkeyup'] = "this.value=this.value.replace(/^0+|[^\d+\.\d+|\d+]/g, '')";
		}
	//表单 JS 验证
	if (isset($_set['require']))
		{
		$_set['is_require'] = true;
		$is_check = true;
		if (!$_set['len'][0])
			{
			$_set['len'][0] = 1;
			}
		}
	if ($_set['len'])
		{
		$is_check = true;
		if ($_set['len'][0])
			{
			$_set['min'] = $_set['len'][0];
			}
		if ($_set['len'][1])
			{
			$_set['max'] = $_set['maxlength'] = $_set['len'][1];
			}
		$_set['datatype'][] = "limit";
		$_set['msg'][] = $_set['_len'] ? $_set['_len'] : "请输入{$_set['zh']}";
		unset($_set['len'], $_set['_len']);
		}
	if ($_set['email'])
		{
		$_set['datatype'][] = 'email';
		$_set['msg'][] = $_set['email'];
		unset($_set['email']);
		}
	if ($_set['mobile'])
		{
		$_set['datatype'][] = 'mobile';
		$_set['msg'][] = $_set['mobile'];
		unset($_set['mobile']);
		}
	if ($_set['number'])
		{
		$_set['datatype'][] = 'number';
		$_set['msg'][] = $_set['number'];
		unset($_set['mobile']);
		}
	if ($_set['datatype'])
		{
		$_set['require'] = 'true';
		$_set['datatype'] = join("|", $_set['datatype']);
		}
	if ($_set['msg'])
		{
		$_set['msg'] = join("|", $_set['msg']);
		}
	$__block_info['display'][$k] = $_set;
	}
//form 设置的默认值
$_form = array(
	"method" => 'POST',
	"action" => '',
	"enctype" => 'multipart/form-data',
	"name" => 'myform',
	);
foreach ($_form as $k => $v)
	{
	if (!isset($__block_info['form'][$k]))
		{
		$__block_info['form'][$k] = $v;
		}
	}
unset($_form);
//table 设置默认值
$_table = array(
	"class" => 'table_form',
	"cellpadding" => 0,
	"cellspacing" => 1,
	);
foreach ($_table as $k => $v)
	{
	if (!isset($__block_info['table'][$k]))
		{
		$__block_info['table'][$k] = $v;
		}
	}
unset($_table);
?>
<form <?=_form_make_attr($__block_info['form'])?> >
<?php
//输出隐藏域形式的字段
foreach ($__block_info['display'] as $k => $_set)
	{
	if ('hide' == $_set['form'] || $_set['hide'])
		{
?>
<input type="hidden" name="<?=$_set['name']?>" value="<?=$_set['value']?>" id="<?=$_set['id']?>" />
<?php
		if ('hide' == $_set['form'])
			{
			unset($__block_info['display'][$k]);
			}
		}
	}
?>
<table <?=_form_make_attr($__block_info['table'])?> >
	<caption><?=$__block_info['caption']?></caption>
	<?php
	foreach ($__block_info['display'] as $k => $_set)
		{
	?>
	<tr>
		<th width="20%"><strong title="<?=$k?>"><?=$_set['zh']?><?=$_set['is_require'] ? "<span style='color:red;background-color:'>*</span>" : ''?>:</strong></th>
		<td>
		<?php
		if ($_set['func'])
			{
			$_set['func']($_set, $__block_info['_value']);
			}
		else if ($_set['html'])
			{
			echo $_set['html'];
			}
		else
			{
			$_form = $_set['form'];
			unset($_set['form'], $_set['type'], $_set['zh'], $_set['is_require'], $_set['hide'], $_set['form_name_pre']);
			switch ($_form)
				{
				case "span":
					echo $_set['value'];
					break;
				case "img_file":
					echo form_img_file($_set);
					break;
				case "file":
					echo form_file($_set);
					break;
				case "date":
					echo form_date_phpcms($_set);
					break;
				case "select":
					$_set['option'] = $_set['in'];
					unset($_set['in']);
					echo form_select($_set);
					break;
				case "radio":
					$_set['_data'] = $_set['in'];
					unset($_set['in']);
					echo form_radio($_set);
					break;
				case "checkbox":
					$_set['_data'] = $_set['in'];
					unset($_set['in']);
					echo form_checkbox($_set);
					break;
				case "textarea":
				case "texts":
					$_set['cols'] = 65;
					$_set['rows'] = 10;
					echo form_textarea($_set);
					break;
				case "password":
					echo form_password($_set);
					break;
				case "text":
				default:
					echo form_text($_set);
					break;
				}
			}
		echo $_set['comment'];
		if ($_quick[$k])
			{
			foreach ($_quick[$k] as $_v)
				{
			?>
			<input type="button" value="<?=$_v?>" onClick="$('#<?=$form_id?>').val('<?=$_v?>')" />
			<?php
				}
			echo '(点击按钮快速输入)';
			}
		?>
		</td>
	</tr>
	<?php
		}
	?>
	<tr> 
		<th>&nbsp;</th>
		<td>
			<input type="submit" name="dosubmit" value=" 确定 "> &nbsp; <input type="reset" name="reset" value="<?=$__block_info['reset_button_name']?>">
		</td>
	</tr>
</table>
</form>
<?php
if ($__block_info['is_form_check'])
	{
?>
<script type="text/javascript">
$('form').checkForm(1);
</script>
<?php
	}
?>

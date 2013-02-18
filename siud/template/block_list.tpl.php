<?php					
/**
 * 后台列表模板片段
 * 
 * <b>*display:设置显示的列</b>
 * <b>*result:查询记录集</b>
 * <code>
 * "result" => $result,	//默认使用 $result 变量的值。
 * </code>
 * <b>name:列中文含意</b>
 * <code>
 * "name" => array(
 * 	"field" => '中文',
 * </code>
 * <b>row_func:Row处理函数</b>
 * <pre>
 * 可以对整条记录进行处理。
 * 可自动绑定 _format_data() 函数
 * 调用参数： func($r, $_print)
 * 可以把函数定义成 func(& $r, & $_print) 以改写 $r, $_print 的值。
 * 可以改 $_print 的值，同时也要以输出需要的东西。
 * </pre>
 * <code>
 * "row_func" => 'func_name',
 * </code>
 * <b>field_print_func:列值显示函数</b>
 * <pre>
 * //调用参数：
 * func_name($r, $_print);
 * //自动绑定:
 * _print_{field}()
 * </pre>
 * <code>
 * "field_print_func" => array(
 * 	"field" => 'func_name',
 * </code>
 * <b>特别说明</b>
 * <pre>
 * [...]	表示括号内的参数是可选的
 * {field} 表示字段名
 * </pre>
 *
 * @version 2012-04-11
 * @package template
 * @filesource
 */
//参数及其默认值,参数不能为原以下划线起头的全局变量，如 userid。片段参数都以下划线起头，如 $_is_form
$__block_list = array(
	//表单与表格设置：
	"is_form" => false,	//表格是否用表单标签（form）包围
	"form_attr" => array(),	//form 标签属性：属性名 => 属性值
	"form_end" => '',
	//同 form_end ，只不过是调用指定的函数,可自动绑定 _print_bottom_() 函数
	"form_end_func" => '',
	//提交表单的按钮: html 字符串
	"caption" => '管理',
	"table_attr" => array(),	//table 标签属性
	"width" => array(),	//列宽

	//输出设置：
	//neededit csv应可以保存到文件（路径），也可以发送下载。
	"csv" => false,		//是否以csv格式输出，若导出csv，将exit程序

	//字段设置：
	"result" => array(),	//结果集数组
	"pages" => '',		//分页代码（html格式）
	'display' => array(),	//显示的字段
	//每条数据显示前调用的函数，调用参数： func($r, $_print), 可自动绑定 _format_data() 函数
	"row_func" => '',
	//显示完一行后调用的函数，调用参数：func($r, $_print, 当前行数，表格总列数),可自动绑定 _print_after_() 函数
	"row_after_func" => '',
	//字段列名: {field} => 显示的列名
	"name" => array(),
	//字段值链接，某个字段的值就是另一个某字段的值，{field} => {field}
		//eg. 'typeid' => 'name'
	"field_copy" => array(),
	//输出字段值调用的函数:{field} => 文件路径，调用格式同 row_func, _print_{field} 的函数可以自动绑定。
	"field_print_func" => array(),
	//单元格作为表单输出,助手性质,完全可通过 php 参数实现: {field} => 参数格式
		//多选框: array('checkbox', {field}),多选框 name 属性为 {field}[], value 属性为 {field} 的值。
		//输入框: 'text', array('input', name, value, html), "name" 为 name 属性组成规则, "value" 为 value 属性组成规则，都为phpstring, html 为直接输出的 html。
		//文本框：'texts', 'textarea', array('textarea', 长, 宽) 自动对字段值执行 htmlspecialchars 函数
	"form" => array(),
	"pk" => '',		//主键字段名
	//管理操作列 内容,多维数组，可以为链接(默认)，可以为 html 代码，可以为需替换的 html 代码
	"manage" => array(),
	"comment" => array(),	//字段的说明
	"map" => array(),	//数据值显示映射，比如把1表示为启用
	);
//设置参数变量, 模板片段参数通过数组传递，变量名为模板片段名，如 $block_quick_userid，在 include 模板片段之前设置即可。
if (is_array($block_list))
	{
	foreach ($block_list as $k => $v)
		{
		${'_' . $k} = $v;
		$__block_list[$k] = $v;
		}
	}
unset($block_list);

if (empty($__block_list['result']) && !$__block_list['csv'])
	{
?>
<table cellpadding="0" cellspacing="1" class="<?=$__block_list['table_attr_class']?>">
	<caption><?=$__block_list['caption']?></caption>
	<tr>
		<td style="text-align:center;"><strong >暂时没有数据</strong></td>
	</tr>
</table>
<?php
	return ;
	}

if (!$__block_list['display'])
	{
	$__block_list['display'] = array_keys(current($__block_list['result']));
	}

foreach ($__block_list['display'] as $k => $v)
	{
	$func_name = "_print_{$v}";
	if (function_exists($func_name) && !$__block_list['field_print_func'][$v])
		{
		$__block_list['field_print_func'][$v] = $func_name;
		}
	}
$func_name = '_print_after_';
if (function_exists($func_name) && !$__block_list['row_after_func'])
	{
	$__block_list['row_after_func'] = $func_name;
	}
$func_name = '_format_data';
if (function_exists($func_name) && !$__block_list['row_func'])
	{
	$__block_list['row_func'] = $func_name;
	}
$func_name = '_print_bottom_';
if (function_exists($func_name) && !$__block_list['form_end_func'])
	{
	$__block_list['form_end_func'] = $func_name;
	}
unset($func_name);

//先计算记录集的所有记录值，然后根据是否输出csv分开处理，计算后的结果保存到 print 键中。
$__block_list['print'] = array();
foreach ($__block_list['result'] as $k => $r)
	{
	$row = array();
	foreach ($__block_list['display'] as $key)
		{
		$_print = $__block_list['field_copy'][$key] ? $r[$__block_list['field_copy'][$key]] : $r[$key];	//显示的字段值
		//计算值映射
		if (isset($__block_list['map'][$key][$r[$key]]))
			{
			$_print = $__block_list['map'][$key][$r[$key]];
			}
		//表单输出
		if ($__block_list['form'][$key])
			{
			if (!is_array($__block_list['form'][$key]))
				{
				$__block_list['form'][$key] = array($__block_list['form'][$key]);
				}
			$_arg = $__block_list['form'][$key];
			switch ($_arg[0])
				{
				case "checkbox":
					$_print = "<input name=\"{$_arg[1]}[]\" type=\"checkbox\" value=\"{$r[$_arg[1]]}\" />";
					break;
				case "input":
				case "text":
					if ($_arg[1])
						{
						eval('$_name = "' . $_arg[1] . '";');
						}
					else
						{
						$_name = "data[{$key}]";
						}
					if ($_arg[2])
						{
						eval('$_value = "' . $_arg[2] . '";');
						}
					else
						{
						$_value = $r[$key];
						}
					$_print = "<input type=\"text\" name=\"{$_name}\" value=\"{$_value}\" {$_arg[3]} />";
					break;
				case "texts":
				case "textarea":
					$_print = "<textarea name=\"data[{$key}]\" rows=\"{$_arg[1]}\" cols=\"{$_arg[2]}\">" . htmlspecialchars($r[$key])  . "</textarea>";
					break;
				case "hide":
					if ($_arg[1])
						{
						eval('$_name = "' . $_arg[1] . '";');
						}
					else
						{
						$_name = "data[{$key}]";
						}
					if ($_arg[2])
						{
						eval('$_value = "' . $_arg[2] . '";');
						}
					else
						{
						$_value = $r[$key];
						}
					$_print = "<input type=\"hidden\" name=\"{$_name}\" value=\"{$_value}\" />{$r[$key]}";
					break;
				}
			}
		$row[$key] = $_print;
		}
	$__block_list['print'][$k] = $row;
	}

//输出为csv格式
if ($__block_list['csv'])
	{
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename={$__block_list['caption']}.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	$fp = fopen("php://output", 'a');	//输出到浏览器的句柄
	//输出文件标题
	if ($__block_list['caption'])
		{
		$r = array_fill(0, count($__block_list['display']), '');
		$r[0] = $__block_list['caption'];
		fputcsv($fp, $r);
		}
	$r = array();
	//输出列名
	foreach ($__block_list['display'] as $k)
		{
		$r[] = $__block_list['name'][$k] ? $__block_list['name'][$k] : $k;
		}
	fputcsv($fp, $r);
	//输出数据
	foreach ($__block_list['print'] as $k => $r)
		{
		$r = array_map('strip_tags', $r);	//去除html代码
		fputcsv($fp, $r);
		}
	fclose($fp);
	exit;
	}

if ($__block_list['is_form'])
	{
	if (!$__block_list['form_attr']['method'])
		{
		$__block_list['form_attr']['method'] = $__block_list['method'];
		}
	if (!$__block_list['form_attr']['action'])
		{
		$__block_list['form_attr']['action'] = $__block_list['action'];
		}
	if (!$__block_list['form_attr']['method'])
		{
		$__block_list['form_attr']['method'] = 'POST';
		}
?>
<form <?php
foreach ($__block_list['form_attr'] as $k => $v)
	{
	echo "{$k}=\"{$v}\" ";
	}
?> >
<?php
	}
if (!$__block_list['table_attr']['class'])
	{
	$__block_list['table_attr']['class'] = 'table_list';
	}
$__block_list['table_attr']['cellpadding'] = 0;
$__block_list['table_attr']['cellspacing'] = 1;
?>
<table <?php
foreach ($__block_list['table_attr'] as $k => $v)
	{
	echo "{$k}=\"{$v}\" ";
	}
?> >
	<caption id="caption_list"><?=$__block_list['caption']?></caption>
	<tr>
		<?php
		foreach ($__block_list['display'] as $k)
			{
		?>
		<th width="<?=$__block_list['width'][$k]?>"><strong title="<?=$k?>"><?=isset($__block_list['name'][$k]) ? $__block_list['name'][$k] : $k?></strong></th>
		<?php
			}
		if ($__block_list['manage'])
			{
		?>
		<th>管理操作</th>
		<?php
			}
		?>
	</tr>
	<?php
	$line = 1;	//当前行数
	foreach ($__block_list['result'] as $k => $r)
		{
		$line++;
	?>
	<tr>
		<?php
		//row_func
		$_print = $__block_list['print'][$k];
		if ($__block_list['row_func'])
			{
			$__block_list['row_func']($r, $_print);
			}
		foreach ($__block_list['display'] as $key)
			{
			echo '<td>';
			//field_print_func
			if ($__block_list['field_print_func'][$key])
				{
				$__block_list['field_print_func'][$key]($r, $_print);
				}
			else
				{
				echo $_print[$key];
				}
			echo '</td>';
			}
		if ($__block_list['manage'])
			{
		?>
		<td>
		<?php
			foreach ($__block_list['manage'] as $key => $value)
				{
				$_html = '';
				//默认显示为超链接格式
				if (!is_array($value))
					{
					$value = array("url" => $value,);
					}
				//兼容旧版，旧版键名0表示超链接
				if (isset($value[0]))
					{
					$value['url'] = $value[0];
					unset($value[0]);
					}
				// _p(print) ，显示条件
				if ($value['_p'])
					{
					eval("\$_p = {$value['_p']};");
					if (!$_p)
						{
						continue;
						}
					}
				//超链接
				if ($value['url'])
					{
					// _o，链接是否有效？无效则只输出文字
					$_tmp = true;
					if ($value['_o'])
						{
						eval("\$_tmp = {$value['_o']};");
						}
					if (!$_tmp)
						{
						$_html = $key . '| ';
						}
					else
						{
						eval("\$_url = \"{$value['url']}\";");
						//1，直接输出的 html 代码
						$_html = "<a href=\"{$_url}\" {$value[1]}>{$key}</a>|";
						}
					}
				//html 代码
				if ($value['html'])
					{
					$_html = $value['html'];
					}
				//需替换的 html 代码，在 html 代码中用 {字段名} 表示此字段的值
				else if ($value['html_replace'])
					{
					//把字段名组成 {字段名} 的格式
					$_key = array_keys($r);
					foreach ($_key as $_k => $_v)
						{
						$_key[$_k] = '{' . $_v . '}';
						}
					$_html = str_replace($_key, array_values($r), $value['html_replace']);
					}
				echo $_html;
				}
		?>
		</td>
		<?php
			}
		?>
	</tr>
	<?php
		if ($__block_list['row_after_func'])
			{
			$__block_list['row_after_func']($r, $_print, $line, count($__block_list['display']));
			}
		}
	?>
</table>
<?php
if ($__block_list['form_end'] || $__block_list['form_end_func'])
	{
?>
<div style="text-align:left;margin-left:50px;">
<?php
	echo $__block_list['form_end'];
	if ($__block_list['form_end_func'])
		{
		$__block_list['form_end_func']();
		}
?>
</div>
<?php
	}
if ($__block_list['is_form'])
	{
?>
</form>
<?php
	}
if ($__block_list['pages'])
	{
	?>
<div id="pages"><?=$__block_list['pages']?></div>
	<?php
	}
unset($__block_list);
?>

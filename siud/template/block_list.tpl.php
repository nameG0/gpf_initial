<?php					
/**
 * ��̨�б�ģ��Ƭ��
 * 
 * <b>*display:������ʾ����</b>
 * <b>*result:��ѯ��¼��</b>
 * <code>
 * "result" => $result,	//Ĭ��ʹ�� $result ������ֵ��
 * </code>
 * <b>name:�����ĺ���</b>
 * <code>
 * "name" => array(
 * 	"field" => '����',
 * </code>
 * <b>row_func:Row������</b>
 * <pre>
 * ���Զ�������¼���д���
 * ���Զ��� _format_data() ����
 * ���ò����� func($r, $_print)
 * ���԰Ѻ�������� func(& $r, & $_print) �Ը�д $r, $_print ��ֵ��
 * ���Ը� $_print ��ֵ��ͬʱҲҪ�������Ҫ�Ķ�����
 * </pre>
 * <code>
 * "row_func" => 'func_name',
 * </code>
 * <b>field_print_func:��ֵ��ʾ����</b>
 * <pre>
 * //���ò�����
 * func_name($r, $_print);
 * //�Զ���:
 * _print_{field}()
 * </pre>
 * <code>
 * "field_print_func" => array(
 * 	"field" => 'func_name',
 * </code>
 * <b>�ر�˵��</b>
 * <pre>
 * [...]	��ʾ�����ڵĲ����ǿ�ѡ��
 * {field} ��ʾ�ֶ���
 * </pre>
 *
 * @version 2012-04-11
 * @package template
 * @filesource
 */
//��������Ĭ��ֵ,��������Ϊԭ���»�����ͷ��ȫ�ֱ������� userid��Ƭ�β��������»�����ͷ���� $_is_form
$__block_list = array(
	//���������ã�
	"is_form" => false,	//����Ƿ��ñ���ǩ��form����Χ
	"form_attr" => array(),	//form ��ǩ���ԣ������� => ����ֵ
	"form_end" => '',
	//ͬ form_end ��ֻ�����ǵ���ָ���ĺ���,���Զ��� _print_bottom_() ����
	"form_end_func" => '',
	//�ύ���İ�ť: html �ַ���
	"caption" => '����',
	"table_attr" => array(),	//table ��ǩ����
	"width" => array(),	//�п�

	//������ã�
	//neededit csvӦ���Ա��浽�ļ���·������Ҳ���Է������ء�
	"csv" => false,		//�Ƿ���csv��ʽ�����������csv����exit����

	//�ֶ����ã�
	"result" => array(),	//���������
	"pages" => '',		//��ҳ���루html��ʽ��
	'display' => array(),	//��ʾ���ֶ�
	//ÿ��������ʾǰ���õĺ��������ò����� func($r, $_print), ���Զ��� _format_data() ����
	"row_func" => '',
	//��ʾ��һ�к���õĺ��������ò�����func($r, $_print, ��ǰ���������������),���Զ��� _print_after_() ����
	"row_after_func" => '',
	//�ֶ�����: {field} => ��ʾ������
	"name" => array(),
	//�ֶ�ֵ���ӣ�ĳ���ֶε�ֵ������һ��ĳ�ֶε�ֵ��{field} => {field}
		//eg. 'typeid' => 'name'
	"field_copy" => array(),
	//����ֶ�ֵ���õĺ���:{field} => �ļ�·�������ø�ʽͬ row_func, _print_{field} �ĺ��������Զ��󶨡�
	"field_print_func" => array(),
	//��Ԫ����Ϊ�����,��������,��ȫ��ͨ�� php ����ʵ��: {field} => ������ʽ
		//��ѡ��: array('checkbox', {field}),��ѡ�� name ����Ϊ {field}[], value ����Ϊ {field} ��ֵ��
		//�����: 'text', array('input', name, value, html), "name" Ϊ name ������ɹ���, "value" Ϊ value ������ɹ��򣬶�Ϊphpstring, html Ϊֱ������� html��
		//�ı���'texts', 'textarea', array('textarea', ��, ��) �Զ����ֶ�ִֵ�� htmlspecialchars ����
	"form" => array(),
	"pk" => '',		//�����ֶ���
	//��������� ����,��ά���飬����Ϊ����(Ĭ��)������Ϊ html ���룬����Ϊ���滻�� html ����
	"manage" => array(),
	"comment" => array(),	//�ֶε�˵��
	"map" => array(),	//����ֵ��ʾӳ�䣬�����1��ʾΪ����
	);
//���ò�������, ģ��Ƭ�β���ͨ�����鴫�ݣ�������Ϊģ��Ƭ�������� $block_quick_userid���� include ģ��Ƭ��֮ǰ���ü��ɡ�
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
		<td style="text-align:center;"><strong >��ʱû������</strong></td>
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

//�ȼ����¼�������м�¼ֵ��Ȼ������Ƿ����csv�ֿ����������Ľ�����浽 print ���С�
$__block_list['print'] = array();
foreach ($__block_list['result'] as $k => $r)
	{
	$row = array();
	foreach ($__block_list['display'] as $key)
		{
		$_print = $__block_list['field_copy'][$key] ? $r[$__block_list['field_copy'][$key]] : $r[$key];	//��ʾ���ֶ�ֵ
		//����ֵӳ��
		if (isset($__block_list['map'][$key][$r[$key]]))
			{
			$_print = $__block_list['map'][$key][$r[$key]];
			}
		//�����
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

//���Ϊcsv��ʽ
if ($__block_list['csv'])
	{
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename={$__block_list['caption']}.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	$fp = fopen("php://output", 'a');	//�����������ľ��
	//����ļ�����
	if ($__block_list['caption'])
		{
		$r = array_fill(0, count($__block_list['display']), '');
		$r[0] = $__block_list['caption'];
		fputcsv($fp, $r);
		}
	$r = array();
	//�������
	foreach ($__block_list['display'] as $k)
		{
		$r[] = $__block_list['name'][$k] ? $__block_list['name'][$k] : $k;
		}
	fputcsv($fp, $r);
	//�������
	foreach ($__block_list['print'] as $k => $r)
		{
		$r = array_map('strip_tags', $r);	//ȥ��html����
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
		<th>�������</th>
		<?php
			}
		?>
	</tr>
	<?php
	$line = 1;	//��ǰ����
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
				//Ĭ����ʾΪ�����Ӹ�ʽ
				if (!is_array($value))
					{
					$value = array("url" => $value,);
					}
				//���ݾɰ棬�ɰ����0��ʾ������
				if (isset($value[0]))
					{
					$value['url'] = $value[0];
					unset($value[0]);
					}
				// _p(print) ����ʾ����
				if ($value['_p'])
					{
					eval("\$_p = {$value['_p']};");
					if (!$_p)
						{
						continue;
						}
					}
				//������
				if ($value['url'])
					{
					// _o�������Ƿ���Ч����Ч��ֻ�������
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
						//1��ֱ������� html ����
						$_html = "<a href=\"{$_url}\" {$value[1]}>{$key}</a>|";
						}
					}
				//html ����
				if ($value['html'])
					{
					$_html = $value['html'];
					}
				//���滻�� html ���룬�� html �������� {�ֶ���} ��ʾ���ֶε�ֵ
				else if ($value['html_replace'])
					{
					//���ֶ������ {�ֶ���} �ĸ�ʽ
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

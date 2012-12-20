<?php
/**
 * ������Ӽ��޸����ݵ�ģ��Ƭ��
 * 
 * <b>*display:��ʾ�ֶ�</b>
 * <code>
 * "display" => array(
 * 	'id',
 * 	"name" => array(
 * 		"form" => 'span',
 * </code>
 * <b>zh:�ֶ�����</b>
 * <code>
 * "zh" => array(
 * 	"field" => '����',
 * </code>
 * <b>value:����</b>
 * <code>
 * "value" => array(
 * 	"field" => 1,
 * </code>
 * <b>comment:�ֶθ���˵��</b>
 * <code>
 * "comment" => array(
 * 	"field" => 'ע��',
 * </code>
 * <b>html:ֱ�����ñ��� HTML ����</b>
 * <code>
 * 'html' => array(
 * 	'name' => "<input ....>",
 * </code>
 * <b>caption:������</b><br/>
 * <code>
 * "caption" => '��ӱ��',
 * </code>
 * <b>form:������</b>
 * <code>
 * "form" => array(
 * 	"id" => 'form_id',
 * </code>
 * <b>table:�������</b>
 * <code>
 * "table" => array(
 * 	"name" => 'table_name',
 * </code>
 * <b>form_name_pre:�� name ����ǰ��</b>
 * <code>
 * "form_name_pre" => 'data', //Ĭ��Ϊ data > data[field]
 * </code>
 * <b>func:�ֶ���ʾ����</b>
 * <code>
 * "func" => array("field" => 'func_name', ...), //���Զ��� _print_{$field} ������
 * //���ò�����
 * $func_name(array �Ѹ�ʽ����� display �ж�Ӧ���ֶ�����);
 * </code>
 * <b>name:������</b>
 * <code>
 * "display" => array(
 * 	"field" => array("name" => 'abc',),
 * </code>
 * <b>form#span:ֱ���������</b>
 * <code>
 * "form" => 'span',
 * </code>
 * <b>form#radio:��ѡ��</b>
 * <code>
 * "form" => 'radio',
 * "in" => array('ѡ��ֵ' => 'ѡ����', ...),
 * </code>
 *
 * @version 2012-04-12
 * @package template
 * @filesource
 */
require_once SIUD_PATH . "include/form.func.php";

$__block_info = array(
	"caption" => '���',	//������
	//���������ã�
	"form" => array(),
	// "is_form_tag" => true,	//�Ƿ��������ǩͷ���� <form>
	// "is_form_close" => true,	//�Ƿ��������ǩβ���� </form>
	// "is_table_tag" => true,	//�Ƿ��������ǩͷ���� <table>
	// "is_table_close" => true,	//�Ƿ���ʾ����ǩβ���� </table>��������Ϊ false �����ύ����ťҲһ������
	// "action" => '',		//�����ύ��ַ
	// "method" => 'POST',
	// "form_attr" => array(),	//form ��ǩ����
	"table" => array(),
	// "table_attr" => array(),	//table ��ǩ����
	"is_form_check" => true,	//�Ƿ��� JQuery ��֤��
	"reset_button_name" => ' ��� ',//���� ��ť������
	"form_name_pre" => 'data',	//����ǰ���� data ��Ϊ data[XX]
	"value" => array(),	//����

	"display" => array(),	//��ʾ���ֶ�
	//�ֶ����ã�
	"zh" => array(),	//�ֶ���������
	"comment" => array(),	//�ֶε�˵��
	"require" => array(),	//����֤����������
	//"th" => array(),		//th ��ʾ��
	//����ָ����������ֶα����ֶΣ�{field} => �����������ò����� func($r, $form_name), $form_name Ϊ������
	"func" => array(),
	"html" => array(), //ֱ������ HTML ���롣
	
	//�������ͣ�
	// "f_hide" => array(),	//����������ʾ���ֶ�:array({field}, {field}, )
	// "f_textarea" => array(),	//�ı���
	// "f_select" => array(),	//������{field} => array(������ֵ => ��ʾ����,,,)
	// "f_img_file" => array(),
	// "f_file" => array(),
	// "f_password" => array(),
	// "f_checkbox" => array(),
	);
//���ò�������, ģ��Ƭ�β���ͨ�����鴫�ݣ�������Ϊģ��Ƭ�������� $block_quick_userid���� include ģ��Ƭ��֮ǰ���ü��ɡ�
if (is_array($block_info))
	{
	foreach ($block_info as $k => $v)
		{
		$__block_info[$k] = $v;
		}
	}
unset($block_info);
//��ʽ�� display. ��ʽ���� display ��ʽ��Ϊ 'field' => array(����)
$__block_info['display'] = $__block_info['display'] ? $__block_info['display'] : array_keys($__block_info['data']);
$_display = array();
foreach ($__block_info['display'] as $k => $v)
	{
	//ת������������Ԫ��Ϊ������
	if (is_int($k))
		{
		$k = $v;
		$v = array();
		}
	//ת��������������������� array('require') > ['require'] = 'require'
	foreach ($v as $_k => $_v)
		{
		if (is_int($_k))
			{
			$v[$_v] = $_v;
			unset($v[$_k]);
			}
		}
	//�ֽ��������������
	$ks = explode(",", $k);
	$v['type'] = array_pop($ks);
	//����Ҫ�����������ͣ�û�������Ϊ str
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
//�������������úϲ�
$__block_info['_value'] = $__block_info['value']; //�ֶ�ֵ�ڵ����ֶ���ʾ����ʱ��Ҫ�õ���
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
//�� func
foreach ($__block_info['display'] as $k => $_set)
	{
	$func_name = "_print_{$k}";
	if (function_exists($func_name) && !$_set['func'])
		{
		$__block_info['display'][$k]['func'] = $func_name;
		}
	}
unset($_set, $func_name);
//��ʽ������ display �ֶ�
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
	//�� JS ��֤
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
		$_set['msg'][] = $_set['_len'] ? $_set['_len'] : "������{$_set['zh']}";
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
//form ���õ�Ĭ��ֵ
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
//table ����Ĭ��ֵ
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
//�����������ʽ���ֶ�
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
			echo '(�����ť��������)';
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
			<input type="submit" name="dosubmit" value=" ȷ�� "> &nbsp; <input type="reset" name="reset" value="<?=$__block_info['reset_button_name']?>">
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

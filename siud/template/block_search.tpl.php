<?php					
/**
 * ��̨���������,һ����� t.func.php > make_search() ����һ��ʹ��
 * 
 * 2011-05-19<br/>
 * [...]	��ʾ�����ڵĲ����ǿ�ѡ��<br/>
 * {field} ��������<br/>
 * phpstring ��ʾ���� eval() ִ�е� php ˫�����ַ���<br/>
 * phpcode	��ʾ���� eval() ִ�е� php ����<br/>
 * 
 * @package template
 * @filesource
 */
//��������Ĭ��ֵ,��������Ϊԭ���»�����ͷ��ȫ�ֱ������� userid��Ƭ�β��������»�����ͷ���� $_is_form
$__block_search = array(
	"table_attr_class" => 'table_list',	//���� class ����ֵ
	"caption" => '����',	//���� caption ����ֵ
	"action" => '',		//�����ύ��ַ
	"method" => 'GET',	//�����ύ��ʽ
	"reset_url" => admin_url("...&" . filter_get(array('mod', 'file', 'action', 'w'))),	//����������������ת����
	"manage" => array(),	//�������ӣ��������ڡ�������ť���ұߣ���ά���飬��һά����Ϊ�������ƣ��ڶ�ά url Ϊ����(��ʵ����ֱ�Ӿ�Ϊ url,�� [����] => url ��Ҳ���Ǳ�Ϊһά������
	//������,�Զ�ά����ĸ�ʽ��ʾ���������λ�ã��磺
		//array(
		//array(��һ�е�һ���������һ�еڶ���������),
		//array(�ڶ��е�һ��������ڶ��еڶ���������),
		//)
	//һ����Ӣ�ģ�ÿ��������������Ψһ�ģ�����ͬʱҲ�Ǳ���� name ����ֵ��
	"display" => array(),
	//����Ĭ��ֵ, {field} => ֵ
	"display_default" => array(),
	//���㼴�ѣ�
	"is_click_search" => true,	//�Ƿ����á����㼴�ѡ�
	"click_search_default" => true,	//�����㼴�ѡ�Ĭ��״̬������/�رգ�
	"is_click_search_change" => true,	//�Ƿ������û����ġ����㼴�ѡ�״̬

	//���������ĺ��⣺{field} => ���ĺ���
	"field" => array(),
	//���������ֵ��{field} => ֵ
	"value" => $_GET['w'],
	//��������ʾ, {field} => ��ʾ����
	"comment" => array(),
	//����������������������ԡ�search��Ϊ�������������� name ����Ϊ search[��������]
	"array_name" => 'w',

	//��������֮ ����(Ĭ����������,�������� input ����)
	//�� select_compare ��ʽһ�£�֧�ֵıȽϷ��� like, =, !=, in, not in, between, not between ��
	"input_compare" => array(),
	//ֱ�Ӷ������������ html ���룬����ָ�� html �� replace_html����Ĭ��Ϊ�����{field} => html ����
	"input_html" => array(),
	//��Ҫ�Ա�����ֵ�����Զ��滻�� html ���룬���� _name_ ���滻Ϊ������_value_ ���滻Ϊ��ֵ����Ҫ�Ƿ����ü�ͳһ�Ĳ������� form ������ html ���룬{field} => html ����
	"input_replace_html" => array(),

	//��������֮ ѡ��
	"select" => array(),	//ѡ������ֶΣ�{field} => [��ѡֵ], [��ѡֵ]Ϊ���飬eg. array(1 => ����, 0 => δ��)
	//ѡ�����ֶεıȽϷ���{field} => [�ȽϷ�����]��[�ȽϷ�����]���� "=","!="���������Ϳ�Ϊ������ַ�������Ϊ���������鳬��1��Ԫ�أ����ʾ�ȽϷ������û��Զ���,����ĵ�һ��ֵΪĬ�ϵıȽϷ���eg. array('=', '!=') �� '=' ��Ĭ�ϵıȽϷ���Ĭ�ϵıȽϷ�Ϊ"="
	"select_compare" => array(),
	//�������ͣ�{field} => [���Ͳ���]��[���Ͳ���]���� "radio"(��ѡ), "checkbox"(��ѡ), ����ͬ [�ȽϷ�����],Ĭ�ϵ�����Ϊ "radio"
	"select_form" => array(),
	//�ռ�ѹ��������������ѡ����ȫ��ʾ������ʾΪ������{field} => [����]��������������ѡֵ��true=����ѹ��,false=��ѹ��, Ϊĳ����=����ָ��������ѹ��,Ĭ��Ϊ false
	"select_compress" => array(),
	//����ʾ��ȫ��/��ա����ܰ�ť��array(field, ...), Ĭ��ȫ���ֶζ���ʾ��ȫ��/��ա���ť��ֻ��Զ�ѡ��
	"select_not_clean" => array(),
	);
//���ò�������, ģ��Ƭ�β���ͨ�����鴫�ݣ�������Ϊģ��Ƭ�������� $block_quick_userid���� include ģ��Ƭ��֮ǰ���ü��ɡ�
if (is_array($block_search))
	{
	foreach ($block_search as $k => $v)
		{
		$__block_search[$k] = $v;
		}
	}
unset($block_search);

$__block_search['_click_search_js'] = '';	//��š����㼴�ѡ�JS���ô���
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
			//��������֮ ѡ��
			if ($__block_search['in'][$key])
				{
				//������
				$_select_form = 'radio';
				if ($__block_search['select_form'][$key])
					{
					$_select_form = $__block_search['select_form'][$key];
					}
				//������ǰ��ѡֵ
				$_value = '';
				if (isset($__block_search['value'][$key]))
					{
					$_value = $__block_search['value'][$key];
					}
				else if (isset($__block_search['display_default'][$key]))
					{
					$_value = $__block_search['display_default'][$key];
					}
					//��ѡʱ��ֵתΪ����
				if ('checkbox' == $_select_form)
					{
					$_value = '' === $_value ? array() : (array)$_value;
					$name = $name . '[]';
					}
				//��ȫ��/��ա���ť
				if (!in_array($key, $__block_search['select_not_clean']))
					{
					if ('radio' == $_select_form)
						{
				?>
				<label><input name="<?=$name?>" type="<?=$_select_form?>" value="" <?='' === $_value ? 'checked' : ''?> <?=$__block_search['_click_search_js']?> />ȫ��</label>
				<?php
						}
				else
						{
				?>
				<label><input name="<?=$name?>" type="<?=$_select_form?>" value="" onclick="$('input[name=\'' + this.name + '\']').attr('checked', false);click_search(this.form);" />���</label>
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
			//��������֮ ����
			else
				{
				//��ǰ�ȽϷ�
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
				//�����û�ѡ��ȽϷ�
				if (is_array($__block_search['input_compare'][$key]) && count($__block_search['input_compare'][$key]) > 1)
					{
					//�� c_{$key} ��ʾ���ֶεıȽϷ�
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
				//���ݱȽϷ������Ƿ���ʾ���������
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
		<a href="<?=$__block_search['reset_url']?>">����</a>
		<input type="submit" value="����" />
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

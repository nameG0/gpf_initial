<?php
/**
 * 内容模型插件
 * 
 * @package include
 * @filesource
 */
define('GM_CONM_MPLUG_EDIT', 1);
define('GM_CONM_MPLUG_NEW', 2);

/**
 * 取一个命名空间的模型插件数据。
 */
//继承关系需要使用数据表保存。分完全继承与部份继承，完全继承就是设置一模一样。
//表结构(conm_mplug_extend)：eid, namespace, tag_id, pid(继承自)
//不做缓存。因为改动设置会所有下层的缓存都需要重建。直接查数据库更划算。直接查数据查询的次数为层数，而层数一般就4级，也就是查4次。而且插件这个东西不是经常读取的功能。
//表结构(conm_mplug_quote):qid, pid(继承自,记录的是起始点，可与qid值相同，表示这条记录就是起始点), eid, status, mplugid, setting
//继承自其它插件的记录保存所继承的那个插件的ID。比如一个全局插件，所有继承这个插件的配置都会记录这个插件的ID。
//返回值：
//array(MPlugs，$change:指示哪些参数是自定义的数组, namespace_extend, tag_id_extend)
//namespace_extend 用于指定继承关系，namespace_extend = NULL 表示使用 extend 表的继承关系。
function conm_mplugs($namespace, $tag_id, $namespace_extend = NULL, $tag_id_extend = NULL)
{//{{{
	$t_extend = 'conm_mplug_extend';
	$t_quote = 'conm_mplug_quote';
	$r_extend = siud::find($t_extend)->tfield('eid, pid')->wis('namespace', $namespace)->wis('tag_id', $tag_id)->ing();
	if (!$r_extend)
		{
		return array(array());
		}
	if (is_null($namespace_extend))
		{
		$pid = intval($r_extend['pid']);
		$p_extend = siud::find($t_extend)->tfield('namespace, tag_id')->wis('eid', $pid)->ing();
		$namespace_extend = $p_extend['namespace'];
		$tag_id = $p_extend['tag_id'];
		unset($p_extend);
		}
	else
		{
		//todo ... ->wis(array("namespace" => $namespace_extend, "tag_id" => $tag_id_extend,)) ...
		$p_extend = siud::find($t_extend)->tfield('eid')->wis('namespace', $namespace_extend)->wis('tag_id', $tag_id)->ing();
		$pid = intval($p_extend['eid']);
		unset($p_extend);
		}
	$extend_link = array($r_extend['eid']); //记录所有上层对象的 Eid, 按[下层, 上层]排序：层4, 层3, 层2, 顶层。 eg. array(4,3,2,1)
	while ($pid)
		{
		$p_extend = siud::find($t_extend)->tfield('pid')->wis('eid', $pid)->ing();
		$pid = intval($p_extend['pid']);
		if ($pid)
			{
			$extend_link[] = $pid;
			}
		}
	unset($p_extend);
	//------
	//由顶层开始组合参数，这样可以过滤掉插件引用设置在继承点之外的记录。
	//------
	array_reverse($extend_link);

	$MPlugs = array(); //保存插件配置, [pid] [setting][key] => value, [status], [mplugid]
	$change_quote = array(); //保存自己自定义过的插件配置参数项
	foreach ($extend_link as $_eid)
		{
		$result_quote = siud::select($t_quote)->tfield("qid, pid, status, mplugid, setting")->wis('eid', $_eid)->ing();
		foreach ($result_quote as $_r_quote)
			{
			$MPlugs[$_r_quote['pid']]['qid'] = $_r_quote['qid'];
			$MPlugs[$_r_quote['pid']]['MPlugID'] = $_r_quote['mplugid'];
			$MPlugs[$_r_quote['pid']]['status'] = $_r_quote['status'];
			$MPlugs[$_r_quote['pid']]['pid'] = $_r_quote['pid'];
			a::i($_r_quote)->unsers('setting');
			if ($_r_quote['setting'])
				{
				if ($r_extend['eid'] == $_eid)
					{
					//------
					//避免因为继承点没有对应的插件令插件配置不完整：
					//设原本继承自 A ，有 A 有 A1 插件，现在改为从 B 继承，B 没有 A1 插件。
					//此时会导至 A1 插件的配置不完整（无法继承 A 的插件配置）。
					//所以合并本层的插件配置时，先检查一下插件 pid 是否已设置过。
					//没置设表示新的继承点不存在此插件，跳过。
					//------
					foreach ($_r_quote['setting'] as $k => $v)
						{
						if (!isset($MPlugs[$_r_quote['pid']]))
							{
							continue;
							}
						$MPlugs[$_r_quote['pid']]['setting'][$k] = $v;
						$change_quote[$_r_quote['pid']][$k] = true;
						}
					}
				else
					{
					foreach ($_r_quote['setting'] as $k => $v)
						{
						$MPlugs[$_r_quote['pid']]['setting'][$k] = $v;
						}
					}
				}
			}
		}
	
	return array($MPlugs, $change_quote, $namespace_extend, $tag_id_extend);
}//}}}

/**
 * 运行插件
 * @param array $data 如 url 这样的字段是需要更改 $data 的吧。
 * @return array 被修改过的数据，需更新回数据库
 */
function conm_mplug($data, $MPlugs, & $error)
{//{{{
	$data_bak = $data;
	$load_ed = array();
	foreach ($MPlugs as $k => $v)
		{
		if (isset($load_ed[$v['MPlugID']]))
			{
			if (!$load_ed[$v['MPlugID']])
				{
				unset($MPlugs[$k]);
				}
			continue;
			}
		list($mod, $name) = explode("/", $v['MPlugID']);
		$callback = mod_callback($mod, 'p');
		foreach ($callback as $p)
			{
			$path = "{$p}conm_mplug/{$name}/function.func.php";
			if (is_file($path))
				{
				include $path;
				$load_ed[$v['MPlugID']] = true;
				break;
				}
			}
		if (!$load_ed[$v['MPlugID']])
			{
			unset($MPlugs[$k]);
			}
		$MPlugs[$k]['_mod'] = $mod;
		$MPlugs[$k]['_name'] = $name;
		}

	$base = array();
	foreach ($MPlugs as $k => $v)
		{
		$func_name = "cm_mp_{$v['_mod']}__{$v['_name']}_base";
		if (!function_exists($func_name))
			{
			continue;
			}
		$_base = $func_name($data, $v['setting']);
		if (is_array($_base))
			{
			$base = $_base + $base;
			}
		}

	foreach ($MPlugs as $k => $v)
		{
		$func_name = "cm_mp_{$v['_mod']}__{$v['_name']}_proc";
		if (!function_exists($func_name))
			{
			continue;
			}
		$func_name($data, $base, $v['setting'], $_error);
		}

	return array_diff_assoc($data, $data_bak);
}//}}}

/**
 * 检查指定继承点是否存在
 * @return bool {true:存在, false:不存在}
 */
function conmMPlug_is_extend_exists($namespace, $tag_id)
{//{{{
	$r_extend = siud::find($t_extend)->tfield('eid')->wis('namespace', $namespace)->wis('tag_id', $tag_id)->ing();
	return intval($r_extend['eid']) > 0;
}//}}}

/**
 * 把信息保存入数据库。
 * 自动分析哪些属于单独设置，哪些是继承。单独设置的才写入 quote 表。
 * 查出上级的设置信息，只要状态或设置有单独设置，都写入 quote 表。通过多选框钩选确认哪些是单独设置。
 * 属于这级自己建立的，都写入 quote 表中。
 */
function conm_mplug_form($dom_id, $MPlugr, $change_quote, $event = GM_CONM_MPLUG_EDIT)
{//{{{
	if (!$MPlugr['MPlugID'])
		{
		return ;
		}
	//$change_quote = array('*' => true) 时表示所有配置都是自定义，在添加时使用。
	//添加时 $MPlugr['qid'] 随便给一个不重复的数字就可以。pid 设为与 qid 相同的值。
	if (GM_CONM_MPLUG_EDIT === $event)
		{
		$form_name = 'mplug_edit';
		}
	else
		{
		$form_name = 'mplug_new';
		}
	$id = "{$dom_id}_{$form_name}_{$MPlugr['pid']}";
	$form_name = "{$dom_id}[{$form_name}][{$MPlugr['pid']}]";
	?>
	<div id="<?=$id?>">
		<input type="button" value="<?=$MPlugr['status'] ? 'Ena' : 'Dis'?>" id="<?=$id?>_button" onclick="conmMPlugTigger_status('<?=$dom_id?>', '<?=$id?>');" />
		<?php
		if ($MPlugr['qid'] == $MPlugr['pid'])
			{
			?>
			<input type="button" value="删除" onclick="$(this).parent().remove();" />
			<?php
			}
		?>
		<input type="hidden" id="<?=$id?>_status" name="<?=$form_name?>[status]" value="<?=$MPlugr['status']?>" />
		<input type="hidden" name="<?=$form_name?>[MPlugID]" value="<?=$MPlugr['MPlugID']?>" />
		<br />
	<?php
	list($mod, $name) = explode("/", $MPlugr['MPlugID']);
	$callback = mod_callback($mod, 'p');
	foreach ($callback as $p)
		{
		$path = "{$p}conm_mplug/{$name}/setting.inc.php";
		if (is_file($path))
			{
			$setting = $MPlugr['setting'];
			include $path;
			break;
			}
		}
	?>
	</div>
	<?php
}//}}}

/**
 * 保存模型插件数据。
 */
function conm_mplug_save($namespace, $tag_id, $namespace_extend, $tag_id_extend, $sQ_mplug_edit, $sQ_mplug_new)
{//{{{
	$t_extend = 'conm_mplug_extend';
	$t_quote = 'conm_mplug_quote';
	if ($namespace_extend)
		{
		$p_extend = siud::find($t_extend)->tfield('eid')->wis('namespace', $namespace_extend)->wis('tag_id', $tag_id_extend)->ing();
		if (!$p_extend)
			{
			return false;
			}
		}
	else
		{
		$p_extend = array("eid" => 0,);
		}
	$r_extend = siud::find($t_extend)->tfield('eid')->wis('namespace', $namespace)->wis('tag_id', $tag_id)->ing();
	if (!$r_extend)
		{
		$r_extend = array("namespace" => $namespace, "tag_id" => $tag_id);
		}
	$r_extend['pid'] = $p_extend['eid'];
	siud::save($t_extend)->pk('eid')->data($r_extend)->id($r_extend['eid'])->ing();

	//用于对比 status 是否不同。
	list($p_MPlugs) = conm_mplugs($namespace_extend, $tag_id_extend);

	//用于更新及删除插件引用配置
	$result_quote = siud::select($t_quote)->tfield('qid, pid')->wis('eid', $r_extend['eid'])->ing();
	$pid2qid = array();
	foreach ($result_quote as $k => $r)
		{
		$pid2qid[$r['pid']] = $r['qid'];
		}
	unset($result_quote);
//表单提交的格式为：$quote[pid][mplugid] => MPlugID。
//表单提交的格式为：$quote[pid][setting][配置项] => 配置值。
//指示哪些属于自定义的多选框数据格式为：$quote[pid][change][配置项] => true
//指示插件状态的格式为：$quote[pid][status] = true/false
//新添加的插件格式为：$new[编号] = 同 $quote 的格式
	foreach ($sQ_mplug_edit as $_pid => $_quote)
		{
		$_quote['mplugid'] = $_quote['MPlugID'];
		if (is_array($_quote['change']))
			{
			foreach ($_quote['setting'] as $k => $v)
				{
				if (!$_quote['change'][$k])
					{
					unset($_quote['setting'][$k]);
					}
				}
			}
		else if ($_quote['status'] != $p_MPlugs[$_pid])
			{
			$_quote['setting'] = array();
			}
		unset($_quote['change']);
		a::i($_quote)->is_adds(1)->sers('setting');
		if ($pid2qid[$_pid])
			{
			$_quote['qid'] = $pid2qid[$_pid];
			unset($pid2qid[$_pid]);
			}
		siud::save($t_quote)->pk('qid')->data($_quote)->ing();
		}
	foreach ($pid2qid as $_pid => $_qid)
		{
		siud::delete($t_quote)->wis('qid', $qid)->ing();
		}
	$o_db = rdb::obj();
	foreach ($sQ_mplug_new as $_quote)
		{
		$_quote['eid'] = $r_extend['eid'];
		$_quote['mplugid'] = $_quote['MPlugID'];
		unset($_quote['change']);
		a::i($_quote)->is_adds(1)->sers('setting');
		siud::save($t_quote)->pk('qid')->data($_quote)->id($qid)->ing();
		$o_db->update(RDB_PRE . $t_quote, array("pid" => $qid,), "qid = {$qid}");
		}
	return true;
}//}}}

/**
 * 保存表单提交的插件引用数据。
 * @param string 包含插件编辑界面的DIV ID
 * @param int $tag_id 设置 tag_id, 在类拟添加模型的场合使用，在表单提交后才能生成 insert_id 。
 */
function conm_mplug_save_form($dom_id, $tag_id = 0)
{//{{{
	$sQ_data = i::pg()->val($dom_id)->end();
	if (!is_array($sQ_data['mplug_edit']))
		{
		$sQ_data['mplug_edit'] = array();
		}
	if (!is_array($sQ_data['mplug_new']))
		{
		$sQ_data['mplug_new'] = array();
		}
	if ($tag_id)
		{
		$sQ_data['tagid'] = $tag_id;
		}
	conm_mplug_save($sQ_data['namespace'], $sQ_data['tag_id'], $sQ_data['namespace_extend'], $sQ_data['tag_id_extend'], $sQ_data['mplug_edit'], $sQ_data['mplug_new']);
}//}}}

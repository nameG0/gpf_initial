//参数
var conmMPlug_argv = {
	url_init: '?a=conm,a_model_plug,ajax_form_init',
	url_plug: '?a=conm,a_model_plug,ajax_form_plug',
	url_new: '/gz/index.php?a=conm,a_model_plug,ajax_new_form',
	};
var conmMPlug_data = {}; //全局数据变量
//Nt, Nt_extend json {namespace:string, tag_id:any, name: 昵称}
//Nt_extend_from 继承范围，多个 Nt 结构。[Nt, Nt]
//Nt_extend 为 {} 时表示使用数据库中保存的继承关系。
function conmMPlug_init(dom_id, Nt, Nt_extend, Nt_extend_from)
{//{{{
	conmMPlug_data[dom_id] = {new_id: 0, Nt: Nt, Nt_extend: Nt_extend, 'Nt_null': {namespace:'', tag_id:''}};
	var Nt_url = Nt.namespace + ',' + Nt.tag_id;
	var Nt_extend_from_url = '';
	$.each(Nt_extend_from, function (k, v){
		Nt_extend_from_url = Nt_extend_from_url + '&Nt_extend_from[]=' + v.namespace + ',' + v.tag_id + ',' + v.name;
	});
	$("#" + dom_id).load(conmMPlug_argv.url_init + Nt_extend_from_url, {dom_id: dom_id, Nt: Nt_url}, function () {
		conmMPlug_show(dom_id, 'Nt_extend');
	});
}//}}}
/**
 * 显示各插件引用设置表单
 * @param string Nt_extend_str 继承点标记
 */
function conmMPlug_show(dom_id, Nt_extend_str)
{//{{{
	var Nt = conmMPlug_data[dom_id].Nt;
	var Nt_extend = conmMPlug_data[dom_id][Nt_extend_str];

	var Nt_url = Nt.namespace + ',' + Nt.tag_id;
	var Nt_extend_url = '';
	if (2 === Nt_extend.length)
		{
		Nt_extend_url = Nt_extend.namespace + ',' + Nt_extend.tag_id;
		}
	$("#" + dom_id + '_setting').load(conmMPlug_argv.url_plug, {dom_id: dom_id, Nt: Nt_url, Nt_extend: Nt_extend_url});
}//}}}
/**
 * 添加一个插件引用。
 */
function conmMPlug_new_plug(dom_id)
{//{{{
	var mplugid = $('#' + dom_id + '_mplugid').val();
	var new_id = conmMPlug_data[dom_id].new_id;
	conmMPlug_data[dom_id].new_id = new_id + 1;
	$.get(conmMPlug_argv.url_new, {dom_id: dom_id, mplugid: mplugid, qid: new_id}, function (html){
		$("#" + dom_id + "_enable").append(html);
	});
}//}}}
/**
 * 切换到启用插件列表
 */
function conmMPlugTigger_enable(dom_id)
{//{{{
	$('#' + dom_id + '_enable').show();
	$('#' + dom_id + '_disable').hide();
}//}}}
/**
 * 切换到禁用插件列表
 */
function conmMPlugTigger_disable(dom_id)
{//{{{
	$('#' + dom_id + '_disable').show();
	$('#' + dom_id + '_enable').hide();
}//}}}
/**
 * 启用/禁用插件
 * @param string dom_id_plug 包围目标插件引用设置表单的DIV ID
 */
function conmMPlugTigger_status(dom_id, dom_id_plug)
{//{{{
	var status_current = Number($('#' + dom_id_plug + '_status').val());
	//0=正常，1=禁用
	if (0 === status_current)
		{
		$('#' + dom_id_plug + '_status').val(1);
		$('#' + dom_id_plug + '_button').val('Ena');
		$('#' + dom_id + '_disable').append($('#' + dom_id_plug));
		}
	else
		{
		$('#' + dom_id_plug + '_status').val(0);
		$('#' + dom_id_plug + '_button').val('Dis');
		$('#' + dom_id + '_enable').append($('#' + dom_id_plug));
		}
}//}}}

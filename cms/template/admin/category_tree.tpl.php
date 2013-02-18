<?php 
// defined('IN_PHPCMS') or exit('Access Denied');
include tpl_admin('header', 'main');
?>
<body>
<?php if($catid){ ?>
<div class="pos"><strong>当前栏目</strong>：<a href="<?=gpf::url("...")?>>栏目管理</a><?=1//catpos($catid, '?mod=phpcms&file=category&action=manage&catid=$catid')?></div>
<?php } ?>
<a href="javascript:void();" onclick="openwinx('<?=gpf::url("..add.&catid=0")?>', 'add', 600, 600);">添加栏目</a>
<a href="javascript:void();" onclick="action_updatecache(this);" >更新缓存</a><span></span>
<a href="javascript:void();" onclick="action_upadte_url(this);">更新URL</a><span></span>
<a href="javascript:void();" onclick="cateTree_init('#top');">刷新</a>
<script type="text/javascript">
<!--
/**
 * 对栏目进行从小到大排序
 */
function cateTree_listorder()
{//{{{
	_cateTree_listorder($('#top').children('ul'));
}//}}}
function _cateTree_listorder(jQ_dom)
{//{{{
	//函数对 jQ_dom 的子元素进行从小到大的排序。 eg. 1, 2, 3
	//先取 jQ_dom 子元素排序号存入数组，然后使用数组的 sort() 方法排序。
	//清空 JQ_dom , 按顺序重新放入子元素。
	//遇到子元素里还有子元素的元素，递归调用本身。
	if (0 === jQ_dom.children().length)
		{
		return ;
		}
	var l = [];
	var Num2Dom = {};
	jQ_dom.children().each(function (i) {
		var l_num = $(this).children('input').val();
		l.push(l_num);
		_cateTree_listorder($(this).children('span[type=child]').children('ul'));
		Num2Dom[l_num] = $(this);
	});
	l.sort(function (a, b) { return a - b; });
	jQ_dom.empty();
	$.each(l, function (i, n) {
		jQ_dom.append(Num2Dom[n]);
	});
}//}}}
/**
 * 更新缓存
 */
function action_updatecache(dom)
{//{{{
	$(dom).next().html('(正在更新缓存...)');
	$.get('<?=gpf::url('..updatecache')?>', function (html) {
		if ('1' === html)
			{
			$(dom).next().html('(成功)');
			}
		else
			{
			$(dom).next().html('(失败)');
			}
	});
}//}}}
/**
 * 更新URL
 */
function action_upadte_url(dom)
{//{{{
	$(dom).next().html('(正在更新URL...)');
	$.get('<?=gpf::url('..update_url')?>', function (html) {
		if ('1' === html)
			{
			$(dom).next().html('(成功)');
			}
		else
			{
			$(dom).next().html('(失败)');
			}
	});
}//}}}
//-->
</script>
<style type="text/css">
/*ul{list-style-type:none;}*/
li{margin-top:10px;}
</style>
<form method="POST" action="<?=gpf::url("..listorder")?>" target="form_target">
<div id="top" catid="0"></div>
<div class="button_box">
<input name="dosubmit" type="submit" value=" 排序 " />
</form>
<iframe name="form_target" style="display:none;"></iframe>

<script type="text/javascript">
<!--
/**
 * 打开新窗口
 */
function openwinx(url, name, w, h)
{//{{{
	window.open(url, name, "top=100,left=400,width=" + w + ",height=" + h + ",toolbar=no,menubar=no,scrollbars=yes,resizable=no,location=no,status=no");
}//}}}
/**
 * 阻止事件冒泡
 */
function stopBubble(event)
{//{{{
	if (window.event)
		{
		window.event.cancelBubble = true; 
		}
	else if (undefined !== event)
		{
		event.stopPropagation(); 
		}
}//}}}
/**
 * 栏目项的单击事件。
 */
function cateTree_click(dom, event)
{//{{{
	var jQ_child = $(dom).children('span[type=child]');
	if ('false' === jQ_child.attr('is_init'))
		{
		cateTree_get_child(dom);
		}
	else if ('true' === jQ_child.attr('is_display'))
		{
		jQ_child.attr('is_display', 'false').hide();
		}
	else
		{
		jQ_child.attr('is_display', 'true').show();
		}
	stopBubble(event);
}//}}}
function cateTree_get_child(dom_li)
{//{{{
	var jQ_child = $(dom_li).children('span[type=child]');
	jQ_child.html('<ul><li>加载中...<li><ul>');
	$.get('<?=gpf::url("..get_child")?>&pid=' + $(dom_li).attr('catid'), function (html) {
		jQ_child.attr('is_init', 'true').html(html);
	});
}//}}}
function cateTree_init(dom_id)
{//{{{
	$.get('<?=gpf::url("..get_child")?>&pid=' + $(dom_id).attr('catid'), function (html) {
		$(dom_id).html(html);
	});
}//}}}
var CateTreePrevLi = undefined;
/**
 * 显示工具条
 * @param event event 用于阻止事件冒泡
 */
function cateTree_tool(dom, event)
{//{{{
	//------------
	// 通过用 onmouseover, onmouseout 两个事件在 <li> 标签中显示隐藏工具条
	// 出现问题：当鼠标移到其子标签的子标签时工具条被反复显示隐藏，造成一闪一闪的问题。标签结构如下：
	// <li onmouseover=显示 onmouseout=隐藏><span type="tool"><span>操作1</span><span><li>
	// 当鼠标移到“操作1”上时，工具条（<span type="tool">）会被隐藏又显示又隐藏，一闪一闪的。
	//
	// 通过用 onmouseover 事件显示工具条，同时使用一个全局变量记录上一次显示工具条的 li, 如果与当前 li 不一样，则擦除上次 li 中的工具条，把当前 li 写入全局变量中，代替原来的 li。
	// 出现问题：显示出来的工具条 <span onclick="alert(1);"> 事件无法生效。
	//
	// 通过输出栏目数据的 AJAX 接口页面直接输出每个栏目的菜单，通过 onmouseover, onmouseout 两个事件进行显示及隐藏。
	//------------
	if (undefined !== CateTreePrevLi && $(dom).attr('catid') !== $(CateTreePrevLi).attr('catid'))
		{
		$(CateTreePrevLi).children('span[type=tool]').hide();
		}
	$(dom).children('span[type=tool]').show();
	CateTreePrevLi = dom;
	stopBubble(event);
}//}}}
/**
 * 重取子栏目数据。
 */
function cateTreeTool_refresh_child(dom, event)
{//{{{
	cateTree_get_child($(dom).parent().parent());
	stopBubble(event);
}//}}}
/**
 * 刷新指定 catid 的子栏目数据。
 */
function cateTree_refresh_child_by_catid(catid)
{//{{{
	if (0 === Number(catid))
		{
		cateTree_init('#top');
		}
	else
		{
		cateTree_get_child($('li[catid=' + catid + ']'));
		}
}//}}}

cateTree_init('#top');

/**
 * 删除栏目
 */
function cateTree_del(catid, event)
{//{{{
	if (!confirm('确定删除？'))
		{
		return ;
		}
	$.get('<?=gpf::url("..delete")?>&catid=' + catid, function (html) {
		if ('1' === html)
			{
			$("li[catid=" + catid + "]").remove();
			}
		else
			{
			alert('删除失败');
			}
	});
	stopBubble(event);
}//}}}
//-->
</script>
</body>
</html>

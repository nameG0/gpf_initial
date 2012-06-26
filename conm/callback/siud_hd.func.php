<?php 
/**
 * hd 扩展
 * 
 * @package default
 * @filesource
 */
mod_init('conm');

/**
 * 输出模型下拉选择框。
 * @param string $CMMTid 显示的模型类型。
 */
function hd_conm__model_select($attr)
{//{{{
	$modeltype = cm_m_modeltype($attr['CMMTid']);
	echo $modeltype;
	?>
	<a href="<?=gpf::url("content.model.manage")?>">管理模型</a>
	<?php
}//}}}

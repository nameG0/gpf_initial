<div id="<?=$dom_id?>_enable" >
	<?php
	foreach ($MPlugs as $_pid => $_MPlugr)
		{
		if (1 == $_MPlugr['status'])
			{
			continue;
			}
		conm_mplug_form($dom_id, $_MPlugr, $change[$_pid], GM_CONM_MPLUG_EDIT);
		unset($MPlugs[$_pid]);
		}
	?>
</div>
<div id="<?=$dom_id?>_disable" style="display:none;">
	<?php
	foreach ($MPlugs as $_pid => $_MPlugr)
		{
		conm_mplug_form($_MPlugr, $change[$_pid], GM_CONM_MPLUG_EDIT);
		}
	?>
</div>

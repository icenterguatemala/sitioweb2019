<?php
$mkd_sidebar = servicemaster_mikado_get_sidebar();
?>
<div class="mkd-column-inner">
	<aside class="mkd-sidebar">
		<?php
		if(is_active_sidebar($mkd_sidebar)) {
			dynamic_sidebar($mkd_sidebar);
		}
		?>
	</aside>
</div>

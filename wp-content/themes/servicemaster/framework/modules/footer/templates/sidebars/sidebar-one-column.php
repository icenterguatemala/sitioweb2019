<?php
$page_id = servicemaster_mikado_get_page_id();
$custom_footer_top_area = get_post_meta($page_id, 'mkd_footer_top_meta_1', true);
?>
<div class="mkd-grid-row">
	<div class="mkd-grid-col-12">
		<?php
			if($custom_footer_top_area !== ''){
				dynamic_sidebar($custom_footer_top_area);
			}
			elseif(is_active_sidebar('footer_column_1')) {
				dynamic_sidebar('footer_column_1');
			}
		?>
	</div>
</div>
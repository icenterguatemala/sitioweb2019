<?php
$page_id = servicemaster_mikado_get_page_id();
$custom_footer_bottom_area = get_post_meta($page_id, 'mkd_footer_text_meta', true);
?>

<div class="mkd-grid-row mkd-footer-bottom-one-col">
	<div class="mkd-grid-col-12">

		<?php
		if($custom_footer_bottom_area !== ''){
			dynamic_sidebar($custom_footer_bottom_area);
		}
		elseif(is_active_sidebar('footer_text')) {
			dynamic_sidebar('footer_text');
		}
		?>

	</div>
</div>
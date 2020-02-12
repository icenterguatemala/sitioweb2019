<?php
$page_id = servicemaster_mikado_get_page_id();
$custom_footer_bottom = get_post_meta($page_id, 'mkd_footer_text_meta', true);
$custom_footer_bottom_left = get_post_meta($page_id, 'mkd_footer_bottom_left_meta', true);
$custom_footer_bottom_right = get_post_meta($page_id, 'mkd_footer_bottom_right_meta', true);
?>

<div class="mkd-grid-row mkd-footer-bottom-three-cols">

	<div class="mkd-grid-col-3 mkd-left">
		<?php
		if($custom_footer_bottom_left !== ''){
			dynamic_sidebar($custom_footer_bottom_left);
		}
		elseif(is_active_sidebar('footer_bottom_left')) {
			dynamic_sidebar('footer_bottom_left');
		}?>
	</div>

	<div class="mkd-grid-col-6 mkd-left">
		<?php
		if($custom_footer_bottom !== ''){
			dynamic_sidebar($custom_footer_bottom);
		}
		elseif(is_active_sidebar('footer_text')) {
			dynamic_sidebar('footer_text');
		}?>
	</div>

	<div class="mkd-grid-col-3 mkd-left">
		<?php
		if($custom_footer_bottom_right !== ''){
			dynamic_sidebar($custom_footer_bottom_right);
		}
		elseif(is_active_sidebar('footer_bottom_right')) {
			dynamic_sidebar('footer_bottom_right');
		}?>
	</div>

</div>
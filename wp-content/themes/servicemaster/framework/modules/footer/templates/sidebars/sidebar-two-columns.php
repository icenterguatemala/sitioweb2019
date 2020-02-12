<?php
$cols = 2;
$page_id = servicemaster_mikado_get_page_id();
?>

<div class="mkd-grid-row">
	<?php for($i = 1; $i <= $cols; $i++) : ?>
		<div class="mkd-grid-col-6">
			<?php
				$custom_footer_top_area = get_post_meta($page_id, 'mkd_footer_top_meta_'.$i, true);
				if($custom_footer_top_area !== ''){
					dynamic_sidebar($custom_footer_top_area);
				}elseif(is_active_sidebar('footer_column_'.$i)) {
					dynamic_sidebar('footer_column_'.$i);
				}
			?>
		</div>
	<?php endfor; ?>
</div>
<?php
/*
Template Name: WooCommerce
*/
?>
<?php

global $woocommerce;

$mkd_id      = get_option('woocommerce_shop_page_id');
$mkd_shop    = get_post($mkd_id);
$mkd_sidebar = servicemaster_mikado_sidebar_layout();

if(get_post_meta($mkd_id, 'mkd_page_background_color', true) != '') {
	$mkd_background_color = 'background-color: '.esc_attr(get_post_meta($mkd_id, 'mkd_page_background_color', true));
} else {
	$mkd_background_color = '';
}

$mkd_content_style = '';
if(get_post_meta($mkd_id, 'mkd_content-top-padding', true) != '') {
	if(get_post_meta($mkd_id, 'mkd_content-top-padding-mobile', true) == 'yes') {
		$mkd_content_style = 'padding-top:'.esc_attr(get_post_meta($mkd_id, 'mkd_content-top-padding', true)).'px !important';
	} else {
		$mkd_content_style = 'padding-top:'.esc_attr(get_post_meta($mkd_id, 'mkd_content-top-padding', true)).'px';
	}
}

if(get_query_var('paged')) {
	$mkd_paged = get_query_var('paged');
} elseif(get_query_var('page')) {
	$mkd_paged = get_query_var('page');
} else {
	$mkd_paged = 1;
}

get_header();

servicemaster_mikado_get_title();
get_template_part('slider');

$mkd_full_width = false;

if(servicemaster_mikado_options()->getOptionValue('mkd_woo_products_list_full_width') == 'yes' && !is_singular('product')) {
	$mkd_full_width = true;
}

if($mkd_full_width) { ?>
<div class="mkd-full-width" <?php servicemaster_mikado_inline_style($mkd_background_color); ?>>
	<?php } else { ?>
	<div class="mkd-container" <?php servicemaster_mikado_inline_style($mkd_background_color); ?>>
		<?php }
		if($mkd_full_width) { ?>
		<div class="mkd-full-width-inner" <?php servicemaster_mikado_inline_style($mkd_content_style); ?>>
			<?php } else { ?>
			<div class="mkd-container-inner clearfix" <?php servicemaster_mikado_inline_style($mkd_content_style); ?>>
				<?php }

				//Woocommerce content
				if(!is_singular('product')) {

					switch($mkd_sidebar) {

						case 'sidebar-33-right': ?>
							<div class="mkd-two-columns-66-33 grid2 mkd-woocommerce-with-sidebar clearfix">
								<div class="mkd-column1">
									<div class="mkd-column-inner">
										<?php servicemaster_mikado_woocommerce_content(); ?>
									</div>
								</div>
								<div class="mkd-column2">
									<?php

									get_sidebar(); ?>
								</div>
							</div>
							<?php
							break;
						case 'sidebar-25-right': ?>
							<div class="mkd-two-columns-75-25 grid2 mkd-woocommerce-with-sidebar clearfix">
								<div class="mkd-column1 mkd-content-left-from-sidebar">
									<div class="mkd-column-inner">
										<?php servicemaster_mikado_woocommerce_content(); ?>
									</div>
								</div>
								<div class="mkd-column2">
									<?php
									get_sidebar(); ?>
								</div>
							</div>
							<?php
							break;
						case 'sidebar-33-left': ?>
							<div class="mkd-two-columns-33-66 grid2 mkd-woocommerce-with-sidebar clearfix">
								<div class="mkd-column1">
									<?php get_sidebar(); ?>
								</div>
								<div class="mkd-column2">
									<div class="mkd-column-inner">
										<?php servicemaster_mikado_woocommerce_content(); ?>
									</div>
								</div>
							</div>
							<?php
							break;
						case 'sidebar-25-left': ?>
							<div class="mkd-two-columns-25-75 grid2 mkd-woocommerce-with-sidebar clearfix">
								<div class="mkd-column1">
									<?php get_sidebar(); ?>
								</div>
								<div class="mkd-column2 mkd-content-right-from-sidebar">
									<div class="mkd-column-inner">
										<?php servicemaster_mikado_woocommerce_content(); ?>
									</div>
								</div>
							</div>
							<?php
							break;
						default:
							servicemaster_mikado_woocommerce_content();
					}

				} else {
					servicemaster_mikado_woocommerce_content();
				} ?>

			</div>
		</div>
		<?php get_footer(); ?>

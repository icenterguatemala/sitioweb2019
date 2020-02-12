<?php

if ( ! function_exists( 'servicemaster_mikado_woo_single_product_thumb_position_class' ) ) {
    function servicemaster_mikado_woo_single_product_thumb_position_class( $classes ) {
        $product_thumbnail_position = servicemaster_mikado_get_meta_field_intersect( 'woo_set_thumb_images_position' );

        if ( ! empty( $product_thumbnail_position ) ) {
            $classes[] = 'mkd-woo-single-thumb-' . $product_thumbnail_position;
        }

        return $classes;
    }

    add_filter( 'body_class', 'servicemaster_mikado_woo_single_product_thumb_position_class' );
}

if ( ! function_exists( 'servicemaster_mikado_woo_single_product_has_zoom_class' ) ) {
    function servicemaster_mikado_woo_single_product_has_zoom_class( $classes ) {
        $zoom_maginifier = servicemaster_mikado_get_meta_field_intersect( 'woo_enable_single_product_zoom_image' );

        if ( $zoom_maginifier === 'yes' ) {
            $classes[] = 'mkd-woo-single-has-zoom';
        }

        return $classes;
    }

    add_filter( 'body_class', 'servicemaster_mikado_woo_single_product_has_zoom_class' );
}

if ( ! function_exists( 'servicemaster_mikado_woo_single_product_has_zoom_support' ) ) {
    function servicemaster_mikado_woo_single_product_has_zoom_support() {
        $zoom_maginifier = servicemaster_mikado_get_meta_field_intersect( 'woo_enable_single_product_zoom_image' );

        if ( $zoom_maginifier === 'yes' ) {
            add_theme_support( 'wc-product-gallery-zoom' );
        }
    }

    add_action( 'init', 'servicemaster_mikado_woo_single_product_has_zoom_support' );
}

if ( ! function_exists( 'servicemaster_mikado_woo_single_product_image_behavior_class' ) ) {
    function servicemaster_mikado_woo_single_product_image_behavior_class( $classes ) {
        $image_behavior = servicemaster_mikado_get_meta_field_intersect( 'woo_set_single_images_behavior' );

        if ( ! empty( $image_behavior ) ) {
            $classes[] = 'mkd-woo-single-has-' . $image_behavior;
        }

        return $classes;
    }

    add_filter( 'body_class', 'servicemaster_mikado_woo_single_product_image_behavior_class' );
}

if ( ! function_exists( 'servicemaster_mikado_woo_single_product_photo_swipe_support' ) ) {
    function servicemaster_mikado_woo_single_product_photo_swipe_support() {
        $image_behavior = servicemaster_mikado_get_meta_field_intersect( 'woo_set_single_images_behavior' );

        if ( $image_behavior === 'photo-swipe' ) {
            add_theme_support( 'wc-product-gallery-lightbox' );
        }
    }

    add_action( 'init', 'servicemaster_mikado_woo_single_product_photo_swipe_support' );
}

if (!function_exists('servicemaster_mikado_woocommerce_products_per_page')) {
    /**
     * Function that sets number of products per page. Default is 12
     * @return int number of products to be shown per page
     */
    function servicemaster_mikado_woocommerce_products_per_page() {

        $products_per_page_meta = servicemaster_mikado_options()->getOptionValue( 'mkd_woo_products_per_page' );
        $products_per_page      = ! empty( $products_per_page_meta ) ? intval( $products_per_page_meta ) : 12;

        if ( isset( $_GET['woo-products-count'] ) && $_GET['woo-products-count'] === 'view-all' ) {
            $products_per_page = 9999;
        }

        return $products_per_page;
    }
}

if (!function_exists('servicemaster_mikado_woocommerce_related_products_args')) {
	/**
	 * Function that sets number of displayed related products. Hooks to woocommerce_output_related_products_args filter
	 * @param $args array array of args for the query
	 * @return mixed array of changed args
	 */
	function servicemaster_mikado_woocommerce_related_products_args($args) {

		if (servicemaster_mikado_options()->getOptionValue('mkd_woo_product_list_columns')) {

			$related = servicemaster_mikado_options()->getOptionValue('mkd_woo_product_list_columns');
			switch ($related) {
				case 'mkd-woocommerce-columns-4':
					$args['posts_per_page'] = 4;
					break;
				case 'mkd-woocommerce-columns-3':
					$args['posts_per_page'] = 3;
					break;
				default:
					$args['posts_per_page'] = 3;
			}

		} else {
			$args['posts_per_page'] = 3;
		}

		return $args;
	}
}

if (!function_exists('servicemaster_mikado_woocommerce_template_loop_product_title')) {
	/**
	 * Function for overriding product title template in Product List Loop
	 */
	function servicemaster_mikado_woocommerce_template_loop_product_title() {

		$tag = servicemaster_mikado_options()->getOptionValue('mkd_products_list_title_tag');
		if ($tag === '') {
			$tag = 'h5';
		}
		the_title('<' . $tag . ' class="mkd-product-list-title"><a href="' . get_the_permalink() . '">', '</a></' . $tag . '>');
	}
}

if (!function_exists('servicemaster_mikado_woocommerce_template_single_title')) {
	/**
	 * Function for overriding product title template in Single Product template
	 */
	function servicemaster_mikado_woocommerce_template_single_title() {

		$tag = servicemaster_mikado_options()->getOptionValue('mkd_single_product_title_tag');
		if ($tag === '') {
			$tag = 'h1';
		}
		the_title('<' . $tag . '  itemprop="name" class="mkd-single-product-title">', '</' . $tag . '>');
	}
}

if (!function_exists('servicemaster_mikado_woocommerce_sale_flash')) {
	/**
	 * Function for overriding Sale Flash Template
	 *
	 * @return string
	 */
	function servicemaster_mikado_woocommerce_sale_flash() {
		if (!is_single()) {
			return '<span class="mkd-on-sale mkd-product-mark">' . esc_html__('SALE', 'servicemaster') . '</span>';
		}
	}
}

if (!function_exists('servicemaster_mikado_woocommerce_sale_flash_single')) {
	/**
	 * Function for overriding Sale Flash Template on Single product page
	 * @return string
	 */
	function servicemaster_mikado_woocommerce_sale_flash_single() {
		global $product;
		if ($product->is_on_sale()) {
			print '<span class="mkd-on-sale mkd-product-mark">' . esc_html__('SALE', 'servicemaster') . '</span>';
		}
	}
}

if (!function_exists('servicemaster_mikado_woocommerce_product_out_of_stock')) {
	/**
	 * Function for adding Out Of Stock Template
	 *
	 * @return string
	 */
	function servicemaster_mikado_woocommerce_product_out_of_stock() {

		global $product;

		if (!$product->is_in_stock()) {
			print '<span class="mkd-out-of-stock mkd-product-mark">' . esc_html__('SOLD OUT', 'servicemaster') . '</span>';
		}
	}
}

if (!function_exists('servicemaster_mikado_woo_view_all_pagination_additional_tag_before')) {
	function servicemaster_mikado_woo_view_all_pagination_additional_tag_before() {

		print '<div class="mkd-woo-pagination-holder"><div class="mkd-woo-pagination-inner">';
	}
}

if (!function_exists('servicemaster_mikado_woo_view_all_pagination_additional_tag_after')) {
	function servicemaster_mikado_woo_view_all_pagination_additional_tag_after() {

		print '</div></div>';
	}
}

if (!function_exists('servicemaster_mikado_single_product_content_additional_tag_before')) {
	function servicemaster_mikado_single_product_content_additional_tag_before() {

		print '<div class="mkd-single-product-content">';
	}
}

if (!function_exists('servicemaster_mikado_single_product_content_additional_tag_after')) {
	function servicemaster_mikado_single_product_content_additional_tag_after() {

		print '</div>';
	}
}

if (!function_exists('servicemaster_mikado_single_product_summary_additional_tag_before')) {
	function servicemaster_mikado_single_product_summary_additional_tag_before() {

		print '<div class="mkd-single-product-summary">';
	}
}

if (!function_exists('servicemaster_mikado_single_product_summary_additional_tag_after')) {
	function servicemaster_mikado_single_product_summary_additional_tag_after() {

		print '</div>';
	}
}

if (!function_exists('servicemaster_mikado_pl_holder_additional_tag_before')) {
	function servicemaster_mikado_pl_holder_additional_tag_before() {

		print '<div class="mkd-pl-main-holder">';
	}
}

if (!function_exists('servicemaster_mikado_pl_holder_additional_tag_after')) {
	function servicemaster_mikado_pl_holder_additional_tag_after() {

		print '</div>';
	}
}

if (!function_exists('servicemaster_mikado_pl_outer_additional_tag_before')) {
	function servicemaster_mikado_pl_outer_additional_tag_before() {
		global $product;

		$rating = $product->get_average_rating();

		if ($rating > 0) {
			print '<div class="mkd-pl-outer rating">';
		} else {
			print '<div class="mkd-pl-outer">';
		}
	}
}

if (!function_exists('servicemaster_mikado_pl_inner_additional_tag_before')) {
	function servicemaster_mikado_pl_inner_additional_tag_before() {

		print '<div class="mkd-pl-inner">';
	}
}

if (!function_exists('servicemaster_mikado_pl_inner_additional_tag_after')) {
	function servicemaster_mikado_pl_inner_additional_tag_after() {

		print '</div>';
	}
}

if (!function_exists('servicemaster_mikado_pl_outer_additional_tag_after')) {
	function servicemaster_mikado_pl_outer_additional_tag_after() {

		print '</div>';
	}
}

if (!function_exists('servicemaster_mikado_pl_image_additional_tag_before')) {
	function servicemaster_mikado_pl_image_additional_tag_before() {

		print '<div class="mkd-pl-image">';
	}
}

if (!function_exists('servicemaster_mikado_pl_image_additional_tag_after')) {
	function servicemaster_mikado_pl_image_additional_tag_after() {

		print '</div>';
	}
}

if (!function_exists('servicemaster_mikado_pl_inner_text_additional_tag_before')) {
	function servicemaster_mikado_pl_inner_text_additional_tag_before() {

		print '<div class="mkd-pl-cart">';
	}
}

if (!function_exists('servicemaster_mikado_pl_inner_text_additional_tag_after')) {
	function servicemaster_mikado_pl_inner_text_additional_tag_after() {

		print '</div>';
	}
}

if (!function_exists('servicemaster_mikado_pl_text_wrapper_additional_tag_before')) {
	function servicemaster_mikado_pl_text_wrapper_additional_tag_before() {

		print '<div class="mkd-pl-text-wrapper"><div class="mkd-pl-text-wrapper-inner">';
	}
}

if (!function_exists('servicemaster_mikado_pl_text_wrapper_additional_tag_after')) {
	function servicemaster_mikado_pl_text_wrapper_additional_tag_after() {

		print '</div></div>';
	}
}

if (!function_exists('servicemaster_mikado_pl_rating_additional_tag_before')) {
	function servicemaster_mikado_pl_rating_additional_tag_before() {
		global $product;

		if (get_option('woocommerce_enable_review_rating') !== 'no') {

			// this condition is only for woocommerce 3.0, because get_rating_html function is deprecated, when they release new update remove else
			if (function_exists('wc_get_rating_html')) {
				$rating_html = wc_get_rating_html($product->get_average_rating());
			} else {
				$rating_html = $product->get_rating_html();
			}

			if ($rating_html !== '') {
				print '<div class="mkd-pl-rating-holder">';
			}
		}
	}
}

if (!function_exists('servicemaster_mikado_pl_rating_additional_tag_after')) {
	function servicemaster_mikado_pl_rating_additional_tag_after() {
		global $product;

		if (get_option('woocommerce_enable_review_rating') !== 'no') {

			// this condition is only for woocommerce 3.0, because get_rating_html function is deprecated, when they release new update remove else
			if (function_exists('wc_get_rating_html')) {
				$rating_html = wc_get_rating_html($product->get_average_rating());
			} else {
				$rating_html = $product->get_rating_html();
			}

			if ($rating_html !== '') {
				print '</div>';
			}
		}
	}
}

if (!function_exists('servicemaster_mikado_woocommerce__new_product_mark')) {
	/**
	 * Function for adding New Product Template
	 *
	 * @return string
	 */
	function servicemaster_mikado_woocommerce__new_product_mark() {
		global $product;

		if (get_post_meta($product->get_id(), 'mkd_single_product_new_meta', true) === 'yes') {
			print '<span class="mkd-new-product mkd-product-mark">' . esc_html__('NEW', 'servicemaster') . '</span>';
		}
	}
}


if (!function_exists('servicemaster_mikado_share_like_tag_before')) {
	/**
	 * Function that adds tag before share and like section
	 */
	function servicemaster_mikado_share_like_tag_before() {
		print '<div class="mkd-single-product-share-like">';
	}
}

if (!function_exists('servicemaster_mikado_share_like_tag_after')) {
	/**
	 * Function that adds tag before share and like section
	 */
	function servicemaster_mikado_share_like_tag_after() {
		print '</div>';
	}
}

if (!function_exists('servicemaster_mikado_woocommerce_get_stock_html')) {
	function servicemaster_mikado_woocommerce_get_stock_html($availability_html, $product = null) {
		global $product;

		$availability = $product->get_availability();

		return empty($availability['availability']) ? '' : '</td><td class="stock">' . $availability_html;
	}
}
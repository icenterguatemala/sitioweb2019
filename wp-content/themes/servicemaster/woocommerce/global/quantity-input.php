<?php
/**
 * Product quantity inputs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/global/quantity-input.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ($max_value && $min_value === $max_value) {
	?>
	<div class="mkd-quantity-buttons quantity hidden">
		<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" class="qty" name="<?php echo esc_attr($input_name); ?>"
			   value="<?php echo esc_attr($min_value); ?>"/>
	</div>
<?php
} else {
	?>
	<div class="quantity mkd-quantity-buttons">
		<span class="mkd-quantity-plus lnr lnr-chevron-up"></span>
		<input type="text" data-step="<?php echo esc_attr($step); ?>" data-min="<?php echo esc_attr($min_value); ?>"
			   data-max="<?php echo esc_attr($max_value); ?>" name="<?php echo esc_attr($input_name); ?>"
			   value="<?php echo esc_attr($input_value); ?>"
			   title="<?php echo esc_attr_x('Qty', 'Product quantity input tooltip', 'servicemaster') ?>"
			   class="input-text qty text mkd-quantity-input" size="4" pattern="<?php echo esc_attr($pattern); ?>"
			   inputmode="<?php echo esc_attr($inputmode); ?>" aria-labelledby="<?php echo ! empty( $args['product_name'] ) ? sprintf( esc_attr__( '%s quantity', 'woocommerce' ), $args['product_name'] ) : ''; ?>" />
		<span class="mkd-quantity-minus lnr lnr-chevron-down"></span>
	</div>
<?php
}
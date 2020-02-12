(function ($) {
	'use strict';

	var woocommerce = {};
	mkd.modules.woocommerce = woocommerce;

	woocommerce.mkdInitQuantityButtons = mkdInitQuantityButtons;
	woocommerce.mkdInitSelect2 = mkdInitSelect2;

	woocommerce.mkdOnDocumentReady = mkdOnDocumentReady;
	woocommerce.mkdOnWindowLoad = mkdOnWindowLoad;
	woocommerce.mkdOnWindowResize = mkdOnWindowResize;

	woocommerce.mkdProductImagesMinHeight = mkdProductImagesMinHeight;

	$(document).ready(mkdOnDocumentReady);
	$(window).load(mkdOnWindowLoad);
	$(window).resize(mkdOnWindowResize);

	/*
	 All functions to be called on $(document).ready() should be in this function
	 */
	function mkdOnDocumentReady() {
		mkdInitQuantityButtons();
        mkdInitSingleProductLightbox();
		mkdInitSelect2();
		mkdProductImagesMinHeight();
	}

	/*
	 All functions to be called on $(window).load() should be in this function
	 */
	function mkdOnWindowLoad() {

	}

	/*
	 All functions to be called on $(window).resize() should be in this function
	 */
	function mkdOnWindowResize() {
		mkdProductImagesMinHeight();
	}

	function mkdInitQuantityButtons() {
		$(document).on('click', '.mkd-quantity-minus, .mkd-quantity-plus', function (e) {
			e.stopPropagation();

			var button = $(this),
				inputField = button.siblings('.mkd-quantity-input'),
				step = parseFloat(inputField.data('step')),
				max = parseFloat(inputField.data('max')),
				minus = false,
				inputValue = parseFloat(inputField.val()),
				newInputValue;

			if (button.hasClass('mkd-quantity-minus')) {
				minus = true;
			}

			if (minus) {
				newInputValue = inputValue - step;
				if (newInputValue >= 1) {
					inputField.val(newInputValue);
				} else {
					inputField.val(0);
				}
			} else {
				newInputValue = inputValue + step;
				if (max === undefined) {
					inputField.val(newInputValue);
				} else {
					if (newInputValue >= max) {
						inputField.val(max);
					} else {
						inputField.val(newInputValue);
					}
				}
			}

			inputField.trigger('change');
		});
	}

    /*
     ** Init Product Single Pretty Photo attributes
     */
    function mkdInitSingleProductLightbox() {
        var item = $('.mkd-woo-single-page.mkd-woo-single-has-pretty-photo .images .woocommerce-product-gallery__image');

        if(item.length) {
            item.children('a').attr('data-rel', 'prettyPhoto[woo_single_pretty_photo]');

            if (typeof mkd.modules.common.mkdPrettyPhoto === "function") {
                mkd.modules.common.mkdPrettyPhoto();
            }
        }
    }

	/*
	 ** Init select2 script for select html dropdowns
	 */
	function mkdInitSelect2() {
		var orderByDropDown = $('.woocommerce-ordering .orderby');
		if (orderByDropDown.length) {
			orderByDropDown.select2({
				minimumResultsForSearch: Infinity
			});
		}

		var shippingCountryCalc = $('#calc_shipping_country');
		if (shippingCountryCalc.length) {
			shippingCountryCalc.select2();
		}

		var shippingStateCalc = $('.cart-collaterals .shipping select#calc_shipping_state');
		if (shippingStateCalc.length) {
			shippingStateCalc.select2();
		}

		var variableProducts = $('.mkd-single-product-content .variations select');
		if (variableProducts.length) {
			variableProducts.select2();
		}
	}

	/* calculate product images section min height because of absolute position */
	function mkdProductImagesMinHeight() {
		var hh = $('.mkd-woo-single-page .product .images .woocommerce-product-gallery__image:first-child').height();
		$('.mkd-woo-single-page .product .images').css({'min-height': hh});

		//woocommerce 3.0
		$('.mkd-woo-single-page .product .images figure').css({'min-height': hh});
	}

})(jQuery);
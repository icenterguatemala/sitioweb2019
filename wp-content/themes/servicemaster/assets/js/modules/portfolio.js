(function ($) {
	'use strict';

	var portfolio = {};
	mkd.modules.portfolio = portfolio;

	portfolio.mkdOnDocumentReady = mkdOnDocumentReady;
	portfolio.mkdOnWindowLoad = mkdOnWindowLoad;
	portfolio.mkdOnWindowResize = mkdOnWindowResize;
	portfolio.mkdOnWindowScroll = mkdOnWindowScroll;

	$(document).ready(mkdOnDocumentReady);
	$(window).load(mkdOnWindowLoad);
	$(window).resize(mkdOnWindowResize);
	$(window).scroll(mkdOnWindowScroll);

	/*
	 All functions to be called on $(document).ready() should be in this function
	 */
	function mkdOnDocumentReady() {
		portfolio.mkdPortfolioSlider();
	}

	/*
	 All functions to be called on $(window).load() should be in this function
	 */
	function mkdOnWindowLoad() {
		mkdPortfolioSingleFollow().init();
	}

	/*
	 All functions to be called on $(window).resize() should be in this function
	 */
	function mkdOnWindowResize() {

	}

	/*
	 All functions to be called on $(window).scroll() should be in this function
	 */
	function mkdOnWindowScroll() {

	}

	portfolio.mkdPortfolioSlider = function () {
		var sliders = $('.mkd-portfolio-slider-holder');

		var setPortfolioResponsiveOptions = function (slider, options) {
			var columns = slider.data('columns');
			if (typeof columns === 'number') {
				options.slidesToShow = columns;
				options.slidesToScroll = columns;
				options.arrows = false;
			}
		};

		if (sliders.length) {
			sliders.each(function () {
				var sliderHolder = $(this).find('.mkd-portfolio-slider-list');
				var options = {};

				options.dots = typeof sliderHolder.data('dots') !== 'undefined' && sliderHolder.data('dots') === 'yes';

				if(options.dots){
					sliderHolder.addClass('slick-dots');
				}

				options.infinite = true;
				options.autoplay = true;

				setPortfolioResponsiveOptions(sliderHolder, options);
                options.responsive = [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3,
							slidesToScroll: 3
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2,
							slidesToScroll: 2
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
							slidesToScroll: 1
                        }
                    }
                ];

                sliderHolder.on('init', function(){
                	$(this).css('visibility','visible');
                });

				sliderHolder.slick(options);
			});
		}
	};


	var mkdPortfolioSingleFollow = function () {

		var info = $('.mkd-follow-portfolio-info .small-images.mkd-portfolio-single-holder .mkd-portfolio-info-holder, ' +
		'.mkd-follow-portfolio-info .small-slider.mkd-portfolio-single-holder .mkd-portfolio-info-holder');

		if (info.length) {
			var infoHolder = info.parent(),
				infoHolderOffset = infoHolder.offset().top,
				infoHolderHeight = infoHolder.height(),
				mediaHolder = $('.mkd-portfolio-media'),
				mediaHolderHeight = mediaHolder.height(),
				header = $('.header-appear, .mkd-fixed-wrapper'),
				headerHeight = (header.length) ? header.height() : 0;
		}

		var infoHolderPosition = function () {

			if (info.length) {

				if (mediaHolderHeight > infoHolderHeight) {
					if (mkd.scroll > infoHolderOffset) {
						info.animate({
							marginTop: (mkd.scroll - (infoHolderOffset) + mkdGlobalVars.vars.mkdAddForAdminBar + headerHeight + 20) //20 px is for styling, spacing between header and info holder
						});
					}
				}

			}
		};

		var recalculateInfoHolderPosition = function () {

			if (info.length) {
				if (mediaHolderHeight > infoHolderHeight) {
					if (mkd.scroll > infoHolderOffset) {

						if (mkd.scroll + headerHeight + mkdGlobalVars.vars.mkdAddForAdminBar + infoHolderHeight + 20 < infoHolderOffset + mediaHolderHeight) {    //20 px is for styling, spacing between header and info holder

							//Calculate header height if header appears
							if ($('.header-appear, .mkd-fixed-wrapper').length) {
								headerHeight = $('.header-appear, .mkd-fixed-wrapper').height();
							}
							info.stop().animate({
								marginTop: (mkd.scroll - (infoHolderOffset) + mkdGlobalVars.vars.mkdAddForAdminBar + headerHeight + 20) //20 px is for styling, spacing between header and info holder
							});
							//Reset header height
							headerHeight = 0;
						}
						else {
							info.stop().animate({
								marginTop: mediaHolderHeight - infoHolderHeight
							});
						}
					} else {
						info.stop().animate({
							marginTop: 0
						});
					}
				}
			}
		};

		return {

			init: function () {
				if (!mkd.modules.common.mkdIsTouchDevice()) {
					infoHolderPosition();
					$(window).scroll(function () {
						recalculateInfoHolderPosition();
					});
				}
			}

		};

	};

})(jQuery);
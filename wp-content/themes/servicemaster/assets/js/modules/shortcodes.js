(function ($) {
    'use strict';

    var shortcodes = {};

    mkd.modules.shortcodes = shortcodes;

    shortcodes.mkdInitCounter = mkdInitCounter;
    shortcodes.mkdInitProgressBars = mkdInitProgressBars;
    shortcodes.mkdInitCountdown = mkdInitCountdown;
    shortcodes.mkdInitMessages = mkdInitMessages;
    shortcodes.mkdInitMessageHeight = mkdInitMessageHeight;
    shortcodes.mkdInitTestimonials = mkdInitTestimonials;
    shortcodes.mkdInitCarousels = mkdInitCarousels;
    shortcodes.mkdInitPieChart = mkdInitPieChart;
    shortcodes.mkdInitPieChartDoughnut = mkdInitPieChartDoughnut;
    shortcodes.mkdInitTabs = mkdInitTabs;
    shortcodes.mkdInitTabIcons = mkdInitTabIcons;
    shortcodes.mkdInitBlogListMasonry = mkdInitBlogListMasonry;
    shortcodes.mkdCustomFontResize = mkdCustomFontResize;
    shortcodes.mkdInitImageGallery = mkdInitImageGallery;
    shortcodes.mkdInitAccordions = mkdInitAccordions;
    shortcodes.mkdShowGoogleMap = mkdShowGoogleMap;
    shortcodes.mkdInitPortfolioListMasonry = mkdInitPortfolioListMasonry;
    shortcodes.mkdInitProductListMasonry = mkdInitProductListMasonry;
    shortcodes.mkdInitProductListLookbookMasonry = mkdInitProductListLookbookMasonry;
    shortcodes.mkdInitPortfolioListPinterest = mkdInitPortfolioListPinterest;
    shortcodes.mkdInitPortfolio = mkdInitPortfolio;
    shortcodes.mkdInitPortfolioMasonryFilter = mkdInitPortfolioMasonryFilter;
    shortcodes.mkdInitPortfolioLoadMore = mkdInitPortfolioLoadMore;
    shortcodes.mkdPortfolioAppearEffect = mkdPortfolioAppearEffect;
    shortcodes.mkdCheckSliderForHeaderStyle = mkdCheckSliderForHeaderStyle;
    shortcodes.mkdInfoBox = mkdInfoBox;
    shortcodes.mkdProcess = mkdProcess;
    shortcodes.mkdTwitterSlider = mkdTwitterSlider;
    shortcodes.mkdComparisonPricingTables = mkdComparisonPricingTables;
    shortcodes.mkdProgressBarVertical = mkdProgressBarVertical;
    shortcodes.mkdIconProgressBar = mkdIconProgressBar;
    shortcodes.mkdBlogSlider = mkdBlogSlider;
    shortcodes.mkdBlogLatestPosts = mkdBlogLatestPosts;
    shortcodes.mkdResizeBlogSlider = mkdResizeBlogSlider;
    shortcodes.mkdTeamSlider = mkdTeamSlider;
    shortcodes.mkdCardSlider = mkdCardSlider;
    shortcodes.mkdCenteredSlider = mkdCenteredSlider;
    shortcodes.mkdOnDocumentReady = mkdOnDocumentReady;
    shortcodes.mkdOnWindowLoad = mkdOnWindowLoad;
    shortcodes.mkdOnWindowResize = mkdOnWindowResize;
    shortcodes.mkdOnWindowScroll = mkdOnWindowScroll;
    shortcodes.emptySpaceResponsive = emptySpaceResponsive;
    shortcodes.horizontalTimeline = horizontalTimeline;
    shortcodes.mkdInitVerticalSplitSlider = mkdInitVerticalSplitSlider;
    shortcodes.mkdDeviceSlider = mkdDeviceSlider;
    shortcodes.mkdInitMobileSlide = mkdInitMobileSlider;
    shortcodes.mkdTiltHoverEffect = mkdTiltHoverEffect;
    shortcodes.mkdParentChildFilter = mkdParentChildFilter;
    shortcodes.mkdInitBackgroundSlider = mkdInitBackgroundSlider;
    shortcodes.mkdInitItemShowcase = mkdInitItemShowcase;
    shortcodes.mkdInitUnorderedList = mkdInitUnorderedList;
    shortcodes.mkdInitTableShortcode = mkdInitTableShortcode;
    shortcodes.mkdInitIWTShortcode = mkdInitIWTShortcode;
    shortcodes.mkdInitImageWithTextOver = mkdInitImageWithTextOver;
    shortcodes.mkdInitTextMarquee = mkdInitTextMarquee;

    $(document).ready(mkdOnDocumentReady);
    $(window).load(mkdOnWindowLoad);
    $(window).resize(mkdOnWindowResize);
    $(window).scroll(mkdOnWindowScroll);

    /*
     All functions to be called on $(document).ready() should be in this function
     */
    function mkdOnDocumentReady() {
        mkdInitItemShowcase();
        mkdInitCounter();
        mkdInitProgressBars();
        mkdInitCountdown();
        mkdInitBackgroundSlider();
        mkdIcon().init();
        mkdInitMessages();
        mkdInitMessageHeight();
        mkdInitPieChart();
        mkdInitPieChartDoughnut();
        mkdInitTabs();
        mkdInitTabIcons();
        mkdButton().init();
        mkdPricingTableWithIcon().init();
        mkdInteractiveBox().init();
        mkdInitBlogListMasonry();
        mkdInitProductListMasonry();
        mkdInitProductListLookbookMasonry();
        mkdCustomFontResize();
        mkdInitImageGallery();
        mkdTwitterSlider();
        mkdBlogSlider();
        mkdBlogLatestPosts();
        mkdResizeBlogSlider();
        mkdTeamSlider();
        mkdCardSlider();
        mkdCenteredSlider();
        mkdInitAccordions();
        mkdShowGoogleMap();
        mkdInitPortfolioListMasonry();
        mkdInitPortfolioListPinterest();
        mkdInitPortfolio();
        mkdInitPortfolioMasonryFilter();
        mkdInitPortfolioLoadMore();
        mkdSlider().init();
        mkdSocialIconWidget().init();
        mkdProcess().init();
        mkdComparisonPricingTables().init();
        mkdProgressBarVertical().init();
        mkdIconProgressBar().init();
        emptySpaceResponsive().init();
        horizontalTimeline().init();
        mkdInitVerticalSplitSlider();
        mkdInitTestimonials();
        mkdInitCarousels();
        mkdInitMiniTextSlider();
        mkdTabSlider();
        mkdPlaylist();
        mkdDeviceSlider();
        mkdInitMobileSlider();
        mkdAdvancedSlider().init();
        mkdReservationFormDatePicker();
        mkdTiltHoverEffect();
        mkdParentChildFilter();
        mkdInitUnorderedList();
        mkdInitTableShortcode();
        mkdInitIWTShortcode();
        mkdInitImageWithTextOver();
        mkdInitTextMarquee();
    }

    /*
     All functions to be called on $(window).load() should be in this function
     */
    function mkdOnWindowLoad() {
        mkdCardsGallery();
        mkdInfoBox();
        mkdAdvancedSlider().load();
        mkdPortfolioAppearEffect();
        mkdInitElementsHolderResponsiveStyle();
        mkd.modules.common.mkdInitParallax();
    }

    /*
     All functions to be called on $(window).resize() should be in this function
     */
    function mkdOnWindowResize() {
        mkdCustomFontResize();
        mkdInitPortfolioListPinterest();
        mkd.modules.common.mkdInitParallax();
    }

    /*
     All functions to be called on $(window).scroll() should be in this function
     */
    function mkdOnWindowScroll() {

    }

    /**
     * Init item showcase shortcode
     */
    function mkdInitItemShowcase() {
        var itemShowcase = $('.mkd-item-showcase-holder');

        if (itemShowcase.length) {
            itemShowcase.each(function () {
                var thisItemShowcase = $(this),
                    leftItems = thisItemShowcase.find('.mkd-is-left'),
                    rightItems = thisItemShowcase.find('.mkd-is-right'),
                    itemImage = thisItemShowcase.find('.mkd-is-image');

                //logic
                leftItems.wrapAll("<div class='mkd-is-item-holder mkd-is-left-holder' />");
                rightItems.wrapAll("<div class='mkd-is-item-holder mkd-is-right-holder' />");
                thisItemShowcase.animate({opacity: 1}, 200);

                var leftHolder = thisItemShowcase.find('.mkd-is-left-holder');
                var rightHolder = thisItemShowcase.find('.mkd-is-right-holder');

                leftHolder.css({
                    'background-color': thisItemShowcase.data('left-holder-background'),
                    'padding': thisItemShowcase.data('holder-padding')
                });
                rightHolder.css({
                    'background-color': thisItemShowcase.data('right-holder-background'),
                    'padding': thisItemShowcase.data('holder-padding')
                });
                setTimeout(function () {
                    thisItemShowcase.appear(function () {
                        itemImage.addClass('mkd-appeared');
                        if (mkd.windowWidth > 1200) {
                            itemAppear('.mkd-is-left-holder .mkd-is-item');
                            itemAppear('.mkd-is-right-holder .mkd-is-item');
                        } else {
                            itemAppear('.mkd-is-item');
                        }
                    }, {accX: 0, accY: mkdGlobalVars.vars.mkdElementAppearAmount});
                }, 100);

                //appear animation trigger
                function itemAppear(itemCSSClass) {
                    thisItemShowcase.find(itemCSSClass).each(function (i) {
                        var thisListItem = $(this);
                        setTimeout(function () {
                            thisListItem.addClass('mkd-appeared');
                        }, i * 150);
                    });
                }
            });
        }
    }


    /**
     * Counter Shortcode
     */
    function mkdInitCounter() {

        var counters = $('.mkd-counter');


        if (counters.length) {
            counters.each(function () {
                var counter = $(this);
                counter.appear(function () {
                    counter.parent().css({'opacity': 1});

                    //Counter zero type
                    if (counter.hasClass('zero')) {
                        var max = parseFloat(counter.text());
                        counter.countTo({
                            from: 0,
                            to: max,
                            speed: 1500,
                            refreshInterval: 100
                        });
                    } else {
                        counter.absoluteCounter({
                            speed: 2000,
                            fadeInDelay: 1000
                        });
                    }

                }, {accX: 0, accY: mkdGlobalVars.vars.mkdElementAppearAmount});
            });
        }

    }

    /*
     **	Horizontal progress bars shortcode
     */
    function mkdInitProgressBars() {

        var progressBar = $('.mkd-progress-bar');

        if (progressBar.length) {

            progressBar.each(function () {

                var thisBar = $(this);

                thisBar.appear(function () {
                    mkdInitToCounterProgressBar(thisBar);
                    if (thisBar.find('.mkd-floating.mkd-floating-inside') !== 0) {
                        var floatingInsideMargin = thisBar.find('.mkd-progress-content').height();
                        floatingInsideMargin += parseFloat(thisBar.find('.mkd-progress-title-holder').css('padding-bottom'));
                        floatingInsideMargin += parseFloat(thisBar.find('.mkd-progress-title-holder').css('margin-bottom'));
                        thisBar.find('.mkd-floating-inside').css('margin-bottom', -(floatingInsideMargin) + 'px');
                    }
                    var percentage = thisBar.find('.mkd-progress-content').data('percentage'),
                        progressContent = thisBar.find('.mkd-progress-content'),
                        progressNumber = thisBar.find('.mkd-progress-number');

                    progressContent.css('width', '0%');
                    progressContent.animate({'width': percentage + '%'}, 1500);
                    progressNumber.css('left', '0%');
                    progressNumber.animate({'left': percentage + '%'}, 1500);

                });
            });
        }
    }

    /*
     **	Counter for horizontal progress bars percent from zero to defined percent
     */
    function mkdInitToCounterProgressBar(progressBar) {
        var percentage = parseFloat(progressBar.find('.mkd-progress-content').data('percentage'));
        var percent = progressBar.find('.mkd-progress-number .mkd-percent');
        if (percent.length) {
            percent.each(function () {
                var thisPercent = $(this);
                thisPercent.parents('.mkd-progress-number-wrapper').css('opacity', '1');
                thisPercent.countTo({
                    from: 0,
                    to: percentage,
                    speed: 1500,
                    refreshInterval: 50
                });
            });
        }
    }

    /*
     **	Function to close message shortcode
     */
    function mkdInitMessages() {
        var message = $('.mkd-message');
        if (message.length) {
            message.each(function () {
                var thisMessage = $(this);
                thisMessage.find('.mkd-close').click(function (e) {
                    e.preventDefault();
                    $(this).parent().parent().fadeOut(500);
                });
            });
        }
    }

    /*
     **	Init message height
     */
    function mkdInitMessageHeight() {
        var message = $('.mkd-message.mkd-with-icon');
        if (message.length) {
            message.each(function () {
                var thisMessage = $(this);
                var textHolderHeight = thisMessage.find('.mkd-message-text-holder').height();
                var iconHolderHeight = thisMessage.find('.mkd-message-icon-holder').height();

                if (textHolderHeight > iconHolderHeight) {
                    thisMessage.find('.mkd-message-icon-holder').height(textHolderHeight);
                } else {
                    thisMessage.find('.mkd-message-text-holder').height(iconHolderHeight);
                }
            });
        }
    }

    /**
     * Countdown Shortcode
     */
    function mkdInitCountdown() {

        var countdowns = $('.mkd-countdown'),
            year,
            month,
            day,
            hour,
            minute,
            timezone,
            monthLabel,
            dayLabel,
            hourLabel,
            minuteLabel,
            secondLabel;

        if (countdowns.length) {

            countdowns.each(function () {

                //Find countdown elements by id-s
                var countdownId = $(this).attr('id'),
                    countdown = $('#' + countdownId),
                    digitFontSize,
                    digitColor,
                    labelFontSize,
                    labelColor;

                //Get data for countdown
                year = countdown.data('year');
                month = countdown.data('month');
                day = countdown.data('day');
                hour = countdown.data('hour');
                minute = countdown.data('minute');
                timezone = countdown.data('timezone');
                monthLabel = countdown.data('month-label');
                dayLabel = countdown.data('day-label');
                hourLabel = countdown.data('hour-label');
                minuteLabel = countdown.data('minute-label');
                secondLabel = countdown.data('second-label');
                digitFontSize = countdown.data('digit-size');
                digitColor = countdown.data('digit-color');
                labelFontSize = countdown.data('label-size');
                labelColor = countdown.data('label-color');

                var padZeros = countdown.hasClass('type-two');

                //Initialize countdown
                countdown.countdown({
                    until: new Date(year, month - 1, day, hour, minute, 44),
                    labels: ['Years', monthLabel, 'Weeks', dayLabel, hourLabel, minuteLabel, secondLabel],
                    format: 'yodHMS',
                    timezone: timezone,
                    padZeroes: !padZeros,
                    onTick: setCountdownStyle
                });

                function setCountdownStyle() {
                    countdown.find('.countdown-amount').css({
                        'font-size': digitFontSize + 'px',
                        'line-height': digitFontSize + 'px',
                        'color': digitColor
                    });
                    countdown.find('.countdown-period').css({
                        'font-size': labelFontSize + 'px',
                        'color': labelColor
                    });
                }

            });

        }

    }

    /**
     * Object that represents icon shortcode
     * @returns {{init: Function}} function that initializes icon's functionality
     */
    var mkdIcon = mkd.modules.shortcodes.mkdIcon = function () {
        //get all icons on page
        var icons = $('.mkd-icon-shortcode');

        /**
         * Function that triggers icon animation and icon animation delay
         */
        var iconAnimation = function (icon) {
            if (icon.hasClass('mkd-icon-animation')) {
                icon.appear(function () {
                    icon.parent('.mkd-icon-animation-holder').addClass('mkd-icon-animation-show');
                }, {accX: 0, accY: mkdGlobalVars.vars.mkdElementAppearAmount});
            }
        };

        /**
         * Function that triggers icon hover color functionality
         */
        var iconHoverColor = function (icon) {
            if (typeof icon.data('hover-color') !== 'undefined') {
                var changeIconColor = function (event) {
                    event.data.icon.css('color', event.data.color);
                };

                var iconElement = icon.find('.mkd-icon-element');
                var hoverColor = icon.data('hover-color');
                var originalColor = iconElement.css('color');

                if (hoverColor !== '') {
                    icon.on('mouseenter', {icon: iconElement, color: hoverColor}, changeIconColor);
                    icon.on('mouseleave', {icon: iconElement, color: originalColor}, changeIconColor);
                }
            }
        };

        /**
         * Function that triggers icon holder background color hover functionality
         */
        var iconHolderBackgroundHover = function (icon) {
            if (typeof icon.data('hover-background-color') !== 'undefined') {
                var changeIconBgColor = function (event) {
                    event.data.icon.css('background-color', event.data.color);
                };

                var hoverBackgroundColor = icon.data('hover-background-color');
                var originalBackgroundColor = icon.css('background-color');

                if (hoverBackgroundColor !== '') {
                    icon.on('mouseenter', {icon: icon, color: hoverBackgroundColor}, changeIconBgColor);
                    icon.on('mouseleave', {icon: icon, color: originalBackgroundColor}, changeIconBgColor);
                }
            }
        };

        /**
         * Function that initializes icon holder border hover functionality
         */
        var iconHolderBorderHover = function (icon) {
            if (typeof icon.data('hover-border-color') !== 'undefined') {
                var changeIconBorder = function (event) {
                    event.data.icon.css('border-color', event.data.color);
                };

                var hoverBorderColor = icon.data('hover-border-color');
                var originalBorderColor = icon.css('border-color');

                if (hoverBorderColor !== '') {
                    icon.on('mouseenter', {icon: icon, color: hoverBorderColor}, changeIconBorder);
                    icon.on('mouseleave', {icon: icon, color: originalBorderColor}, changeIconBorder);
                }
            }
        };

        return {
            init: function () {
                if (icons.length) {
                    icons.each(function () {
                        iconAnimation($(this));
                        iconHoverColor($(this));
                        iconHolderBackgroundHover($(this));
                        iconHolderBorderHover($(this));
                    });

                }
            }
        };
    };

    /**
     * Object that represents social icon widget
     * @returns {{init: Function}} function that initializes icon's functionality
     */
    var mkdSocialIconWidget = mkd.modules.shortcodes.mkdSocialIconWidget = function () {
        //get all social icons on page
        var icons = $('.mkd-social-icon-widget-holder');

        /**
         * Function that triggers icon hover color functionality
         */
        var socialIconHoverColor = function (icon) {
            if (typeof icon.data('hover-color') !== 'undefined') {
                var changeIconColor = function (event) {
                    event.data.icon.css('color', event.data.color);
                };

                var iconElement = icon;
                var hoverColor = icon.data('hover-color');
                var originalColor = iconElement.css('color');

                if (hoverColor !== '') {
                    icon.on('mouseenter', {icon: iconElement, color: hoverColor}, changeIconColor);
                    icon.on('mouseleave', {icon: iconElement, color: originalColor}, changeIconColor);
                }
            }
        };

        return {
            init: function () {
                if (icons.length) {
                    icons.each(function () {
                        socialIconHoverColor($(this));
                    });

                }
            }
        };
    };

    /**
     * Init testimonials shortcode
     */
    function mkdInitTestimonials() {

        var testimonial_sliders = $('.mkd-testimonials.testimonials-slider');
        if (testimonial_sliders.length) {
            testimonial_sliders.each(function () {

                var thisTestimonial = $(this);

                thisTestimonial.appear(function () {
                    thisTestimonial.css('visibility', 'visible');
                }, {accX: 0, accY: mkdGlobalVars.vars.mkdElementAppearAmount});

                var fadeSlides = function () {
                    var slides = thisTestimonial.find('.slick-slide');

                    slides.removeClass('mkd-fade-in mkd-fade-out');

                    slides.each(function () {
                        var currentSlide = $(this),
                            sliderWindowOffsetLeft = thisTestimonial.find('.slick-list').offset().left,
                            sliderWindowWidth = thisTestimonial.find('.slick-list').outerWidth(),
                            currentSlideOffsetLeft = currentSlide.offset().left,
                            currentSlideWidth = currentSlide.outerWidth();


                        if (currentSlideOffsetLeft >= sliderWindowOffsetLeft && currentSlideOffsetLeft + currentSlideWidth <= sliderWindowOffsetLeft + sliderWindowWidth) {
                            currentSlide.addClass('mkd-fade-out');
                        }

                        if (currentSlideOffsetLeft < sliderWindowOffsetLeft && currentSlideOffsetLeft + currentSlideWidth > 0) {
                            currentSlide.addClass('mkd-fade-in');
                        }

                        if (currentSlideOffsetLeft > sliderWindowOffsetLeft && currentSlideOffsetLeft < mkd.windowWidth) {
                            currentSlide.addClass('mkd-fade-in');
                        }
                    });
                };

                thisTestimonial.on('beforeChange', function () {
                    fadeSlides();
                });


                thisTestimonial.on('init', function () {
                    // change default opacity on init
                    thisTestimonial.css({'opacity': '1'});
                    thisTestimonial.find('.slick-active').addClass('mkd-fade-in');

                }).slick({
                    infinite: true,
                    autoplay: true,
                    autoplaySpeed: 3000,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    fade: false,
                    cssEase: 'cubic-bezier(.38,.76,0,.87)',
                    arrows: true,
                    dots: false,
                    speed: 800
                });
            });

        }

        var testimonial_sliders_boxed = $('.mkd-testimonials.testimonials-slider-boxed');

        if (testimonial_sliders_boxed.length) {
            testimonial_sliders_boxed.each(function () {

                var thisTestimonial = $(this);

                thisTestimonial.addClass('slick-dots');

                var itemsToShow = thisTestimonial.data('items-to-show');

                if (!(typeof itemsToShow !== 'undefined' && itemsToShow !== false)) {
                    itemsToShow = 3;
                }

                thisTestimonial.waitForImages(function () {
                    thisTestimonial.css('visibility', 'visible');
                });
                thisTestimonial.on('init', function () {
                    // change default opacity on init
                    thisTestimonial.css({'opacity': '1'});
                }).slick({
                    dots: true,
                    arrows: false,
                    autoplay: true,
                    autoplaySpeed: 3000,
                    slidesToScroll: itemsToShow,
                    slidesToShow: itemsToShow,
                    speed: 800,
                    cssEase: 'cubic-bezier(.38,.76,0,.87)',
                    responsive: [
                        {
                            breakpoint: 1281,
                            settings: {
                                slidesToShow: itemsToShow > 3 ? 3 : itemsToShow,
                                slidesToScroll: itemsToShow > 3 ? 3 : itemsToShow
                            }
                        },
                        {
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 2
                            }
                        },
                        {
                            breakpoint: 769,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 2
                            }
                        },
                        {
                            breakpoint: 481,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        }
                    ]
                });
            });
        }

    }

    /**
     * Init Carousel shortcode
     */
    function mkdInitCarousels() {

        var carouselHolders = $('.mkd-carousel-holder'),
            carousel,
            numberOfItems;

        if (carouselHolders.length) {
            carouselHolders.each(function () {
                carousel = $(this).children('.mkd-carousel');
                numberOfItems = carousel.data('items');

                var showNav = carousel.data('navigation');

                if (typeof showNav !== 'undefined') {
                    showNav = showNav === 'yes';
                } else {
                    showNav = false;
                }

                carousel.on('init', function () {
                    // change default opacity on init
                    carousel.addClass('appeared');

                }).slick({
                    slidesToScroll: 1,
                    slidesToShow: numberOfItems,
                    autoplay: true,
                    autoplaySpeed: 2000,
                    arrows: showNav,
                    speed: 600,
                    responsive: [
                        {
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: 4
                            }
                        },
                        {
                            breakpoint: 769,
                            settings: {
                                slidesToShow: 3
                            }
                        },
                        {
                            breakpoint: 481,
                            settings: {
                                slidesToShow: 1
                            }
                        }
                    ]
                });
            });
        }
    }

    function mkdTwitterSlider() {
        var twitterSliders = $('.mkd-twitter-slider-inner');

        if (twitterSliders.length) {
            twitterSliders.each(function () {
                var twitterSlider = $(this);
                twitterSlider.on('init', function () {

                    // change default opacity on init
                    twitterSlider.css({'opacity': '1'});
                }).slick({
                    slidesToShow: 1,
                    arrows: false,
                    dots: false,
                    speed: 750,
                    cssEase: 'cubic-bezier(.19,1,.22,1)',
                    swipeToSlide: true,
                    autoplay: true,
                    autoplaySpeed: 3000,
                });
            });
        }
    }

    /**
     * Init Pie Chart and Pie Chart With Icon shortcode
     */
    function mkdInitPieChart() {

        var pieCharts = $('.mkd-pie-chart-holder, .mkd-pie-chart-with-icon-holder');

        if (pieCharts.length) {

            pieCharts.each(function () {

                var pieChart = $(this),
                    percentageHolder = pieChart.children('.mkd-percentage, .mkd-percentage-with-icon'),
                    barColor,
                    trackColor,
                    lineWidth = 8,
                    size = 194;

                if (typeof percentageHolder.data('size') !== 'undefined' && percentageHolder.data('size') !== '') {
                    size = percentageHolder.data('size');
                }

                if (typeof pieChart.data('bar-color') !== 'undefined' && pieChart.data('bar-color') !== '') {
                    barColor = pieChart.data('bar-color');
                }

                if (typeof pieChart.data('track-color') !== 'undefined' && pieChart.data('track-color') !== '') {
                    trackColor = pieChart.data('track-color');
                }

                percentageHolder.appear(function () {
                    initToCounterPieChart(pieChart);
                    percentageHolder.css('opacity', '1');

                    percentageHolder.easyPieChart({
                        barColor: barColor,
                        trackColor: trackColor,
                        scaleColor: false,
                        lineCap: 'butt',
                        lineWidth: lineWidth,
                        animate: 1500,
                        size: size
                    });
                }, {accX: 0, accY: mkdGlobalVars.vars.mkdElementAppearAmount});

            });

        }

    }

    /*
     **	Counter for pie chart number from zero to defined number
     */
    function initToCounterPieChart(pieChart) {

        pieChart.css('opacity', '1');
        var counter = pieChart.find('.mkd-to-counter'),
            max = parseFloat(counter.text());
        counter.countTo({
            from: 0,
            to: max,
            speed: 1500,
            refreshInterval: 50
        });

    }

    /**
     * Init Pie Chart shortcode
     */
    function mkdInitPieChartDoughnut() {

        var pieCharts = $('.mkd-pie-chart-doughnut-holder, .mkd-pie-chart-pie-holder');

        pieCharts.each(function () {

            var pieChart = $(this),
                canvas = pieChart.find('canvas'),
                chartID = canvas.attr('id'),
                chart = document.getElementById(chartID).getContext('2d'),
                data = [],
                jqChart = $(chart.canvas); //Convert canvas to JQuery object and get data parameters

            for (var i = 1; i <= 10; i++) {

                var chartItem,
                    value = jqChart.data('value-' + i),
                    color = jqChart.data('color-' + i);

                if (typeof value !== 'undefined' && typeof color !== 'undefined') {
                    chartItem = {
                        value: value,
                        color: color
                    };
                    data.push(chartItem);
                }

            }

            if (canvas.hasClass('mkd-pie')) {
                new Chart(chart).Pie(data,
                    {segmentStrokeColor: 'transparent'}
                );
            } else {
                new Chart(chart).Doughnut(data,
                    {segmentStrokeColor: 'transparent'}
                );
            }

        });

    }

    /*
     **	Init tabs shortcode
     */
    function mkdInitTabs() {

        var tabs = $('.mkd-tabs');
        if (tabs.length) {
            tabs.each(function () {
                var thisTabs = $(this);

                thisTabs.children('.mkd-tab-container').each(function (index) {
                    index = index + 1;
                    var that = $(this),
                        link = that.attr('id'),
                        navItem = that.parent().find('.mkd-tabs-nav li:nth-child(' + index + ') a'),
                        navLink = navItem.attr('href');

                    link = '#' + link;

                    if (link.indexOf(navLink) > -1) {
                        navItem.attr('href', link);
                    }
                });

                if (thisTabs.hasClass('mkd-horizontal')) {
                    thisTabs.tabs();
                }
                else if (thisTabs.hasClass('mkd-vertical')) {
                    thisTabs.tabs().addClass('ui-tabs-vertical ui-helper-clearfix');
                    thisTabs.find('.mkd-tabs-nav > ul >li').removeClass('ui-corner-top').addClass('ui-corner-left');
                }
            });
        }
    }

    /*
     **	Generate icons in tabs navigation
     */
    function mkdInitTabIcons() {

        var tabContent = $('.mkd-tab-container');
        if (tabContent.length) {

            tabContent.each(function () {
                var thisTabContent = $(this);

                var id = thisTabContent.attr('id');
                var icon = '';
                if (typeof thisTabContent.data('icon-html') !== 'undefined' || thisTabContent.data('icon-html') !== 'false') {
                    icon = thisTabContent.data('icon-html');
                }

                var tabNav = thisTabContent.parents('.mkd-tabs').find('.mkd-tabs-nav > li > a[href="#' + id + '"]');

                if (typeof(tabNav) !== 'undefined') {
                    tabNav.children('.mkd-icon-frame').append(icon);
                }
            });
        }
    }

    /**
     * Button object that initializes whole button functionality
     * @type {Function}
     */
    var mkdButton = mkd.modules.shortcodes.mkdButton = function () {
        //all buttons on the page
        var buttons = $('.mkd-btn');

        /**
         * Initializes button hover color
         * @param button current button
         */
        var buttonHoverColor = function (button) {
            if (typeof button.data('hover-color') !== 'undefined') {
                var changeButtonColor = function (event) {
                    event.data.button.css('color', event.data.color);
                };

                var originalColor = button.css('color');
                var hoverColor = button.data('hover-color');

                button.on('mouseenter', {button: button, color: hoverColor}, changeButtonColor);
                button.on('mouseleave', {button: button, color: originalColor}, changeButtonColor);
            }
        };


        /**
         * Initializes button hover background color
         * @param button current button
         */
        var buttonHoverBgColor = function (button) {
            if (typeof button.data('hover-bg-color') !== 'undefined') {
                var changeButtonBg = function (event) {
                    event.data.button.css('background-color', event.data.color);
                };

                var originalBgColor = button.css('background-color');
                var hoverBgColor = button.data('hover-bg-color');

                button.on('mouseenter', {button: button, color: hoverBgColor}, changeButtonBg);
                button.on('mouseleave', {button: button, color: originalBgColor}, changeButtonBg);
            }
        };

        /**
         * Initializes button border color
         * @param button
         */
        var buttonHoverBorderColor = function (button) {
            if (typeof button.data('hover-border-color') !== 'undefined') {
                var changeBorderColor = function (event) {
                    event.data.button.css('border-color', event.data.color);
                };

                var originalBorderColor = button.css('borderTopColor'); //take one of the four sides
                var hoverBorderColor = button.data('hover-border-color');

                button.on('mouseenter', {button: button, color: hoverBorderColor}, changeBorderColor);
                button.on('mouseleave', {button: button, color: originalBorderColor}, changeBorderColor);
            }
        };

        return {
            init: function () {
                if (buttons.length) {
                    buttons.each(function () {
                        buttonHoverColor($(this));
                        buttonHoverBgColor($(this));
                        buttonHoverBorderColor($(this));
                    });
                }
            }
        };
    };

    /**
     * PricingTable object that initializes whole pricing table functionality
     * @type {Function}
     */
    var mkdPricingTableWithIcon = mkd.modules.shortcodes.mkdPricingTableWithIcon = function () {
        var pricingTables = $('.mkd-pricing-table-wi');

        /**
         * Initializes button hover background color
         * @param button current button
         */
        var pricingTableBgColor = function (pricingTable) {
            if (typeof pricingTable.data('hover-bg-color') !== 'undefined') {
                var changePricingTableBg = function (event) {
                    event.data.pricingTable.css('background-color', event.data.color);
                };

                var originalBgColor = pricingTable.css('background-color');
                var hoverBgColor = pricingTable.data('hover-bg-color');

                pricingTable.on('mouseenter', {pricingTable: pricingTable, color: hoverBgColor}, changePricingTableBg);
                pricingTable.on('mouseleave', {
                    pricingTable: pricingTable,
                    color: originalBgColor
                }, changePricingTableBg);
            }
        };

        return {
            init: function () {
                if (pricingTables.length) {
                    pricingTables.each(function () {
                        pricingTableBgColor($(this));
                    });
                }
            }
        };
    };

    /**
     * PricingTable object that initializes whole pricing table functionality
     * @type {Function}
     */
    var mkdInteractiveBox = mkd.modules.shortcodes.mkdInteractiveBox = function () {
        var mkdInteractiveBoxes = $('.mkd-ib-content');

        /**
         * Initializes button hover background color
         * @param button current button
         */
        var mkdInteractiveBoxStyles = function (interactiveBox) {
            if (typeof interactiveBox.data('icon_hover_color') !== 'undefined' || typeof interactiveBox.data('title_hover_color') !== 'undefined') {

                //icon style
                var changeIconColor = function (event) {
                    event.data.interactiveBox.find('.mkd-ib-icon').css('color', event.data.color);
                };

                var originalIconColor = interactiveBox.find('.mkd-ib-icon').css('color');
                var hoverIconColor = interactiveBox.data('icon_hover_color');

                interactiveBox.on('mouseenter', {
                    interactiveBox: interactiveBox,
                    color: hoverIconColor
                }, changeIconColor);

                interactiveBox.on('mouseleave', {
                    interactiveBox: interactiveBox,
                    color: originalIconColor
                }, changeIconColor);


                //title style
                var changeTitleColor = function (event) {
                    event.data.interactiveBox.find('.mkd-ib-title').css('color', event.data.color);
                };

                var originalTitleColor = interactiveBox.find('.mkd-ib-title').css('color');
                var hoverTitleColor = interactiveBox.data('title_hover_color');

                interactiveBox.on('mouseenter', {
                    interactiveBox: interactiveBox,
                    color: hoverTitleColor
                }, changeTitleColor);

                interactiveBox.on('mouseleave', {
                    interactiveBox: interactiveBox,
                    color: originalTitleColor
                }, changeTitleColor);

                //separator style
                var changeSeparatorColor = function (event) {
                    event.data.interactiveBox.find('.mkd-separator').css('border-color', event.data.color);
                };

                var originalSeparatorColor = interactiveBox.find('.mkd-separator').css('border-color');
                var hoverSeparatorColor = interactiveBox.data('separator_hover_color');

                interactiveBox.on('mouseenter', {
                    interactiveBox: interactiveBox,
                    color: hoverSeparatorColor
                }, changeSeparatorColor);

                interactiveBox.on('mouseleave', {
                    interactiveBox: interactiveBox,
                    color: originalSeparatorColor
                }, changeSeparatorColor);
            }
        };

        return {
            init: function () {
                if (mkdInteractiveBoxes.length) {
                    mkdInteractiveBoxes.each(function () {
                        mkdInteractiveBoxStyles($(this));
                    });
                }
            }
        };

    };

    /*
     **	Init blog list masonry type
     */
    function mkdInitBlogListMasonry() {
        var blogList = $('.mkd-blog-list-holder.mkd-masonry');
        if (blogList.length) {
            blogList.each(function () {
                var container = $(this);

                container.waitForImages(function () {
                    container.css('visibility', 'visible');

                    container.isotope({
                        itemSelector: 'article',
                        masonry: {
                            columnWidth: '.mkd-blog-masonry-grid-sizer'
                        }
                    });
                });
            });
        }
    }

    /*
     **	Custom Font resizing
     */
    function mkdCustomFontResize() {
        var customFont = $('.mkd-custom-font-holder');
        if (customFont.length) {
            customFont.each(function () {
                var thisCustomFont = $(this);
                var fontSize;
                var lineHeight;
                var coef1 = 1;
                var coef2 = 1;

                if (mkd.windowWidth < 1200) {
                    coef1 = 0.8;
                }

                if (mkd.windowWidth < 1000) {
                    coef1 = 0.7;
                }

                if (mkd.windowWidth < 768) {
                    coef1 = 0.6;
                    coef2 = 0.7;
                }

                if (mkd.windowWidth < 600) {
                    coef1 = 0.5;
                    coef2 = 0.6;
                }

                if (mkd.windowWidth < 480) {
                    coef1 = 0.4;
                    coef2 = 0.5;
                }

                if (typeof thisCustomFont.data('font-size') !== 'undefined' && thisCustomFont.data('font-size') !== false) {
                    fontSize = parseInt(thisCustomFont.data('font-size'));

                    if (fontSize > 70) {
                        fontSize = Math.round(fontSize * coef1);
                    }
                    else if (fontSize > 35) {
                        fontSize = Math.round(fontSize * coef2);
                    }

                    thisCustomFont.css('font-size', fontSize + 'px');
                }

                if (typeof thisCustomFont.data('line-height') !== 'undefined' && thisCustomFont.data('line-height') !== false) {
                    lineHeight = parseInt(thisCustomFont.data('line-height'));

                    if (lineHeight > 70 && mkd.windowWidth < 1200) {
                        lineHeight = '1.2em';
                    }
                    else if (lineHeight > 35 && mkd.windowWidth < 768) {
                        lineHeight = '1.2em';
                    }
                    else {
                        lineHeight += 'px';
                    }

                    thisCustomFont.css('line-height', lineHeight);
                }
            });
        }
    }

    /*
     **	Show Google Map
     */
    function mkdShowGoogleMap() {

        if ($('.mkd-google-map').length) {
            $('.mkd-google-map').each(function () {

                var element = $(this);

                var customMapStyle;
                if (typeof element.data('custom-map-style') !== 'undefined') {
                    customMapStyle = element.data('custom-map-style');
                }

                var colorOverlay;
                if (typeof element.data('color-overlay') !== 'undefined' && element.data('color-overlay') !== false) {
                    colorOverlay = element.data('color-overlay');
                }

                var saturation;
                if (typeof element.data('saturation') !== 'undefined' && element.data('saturation') !== false) {
                    saturation = element.data('saturation');
                }

                var lightness;
                if (typeof element.data('lightness') !== 'undefined' && element.data('lightness') !== false) {
                    lightness = element.data('lightness');
                }

                var zoom;
                if (typeof element.data('zoom') !== 'undefined' && element.data('zoom') !== false) {
                    zoom = element.data('zoom');
                }

                var pin;
                if (typeof element.data('pin') !== 'undefined' && element.data('pin') !== false) {
                    pin = element.data('pin');
                }

                var mapHeight;
                if (typeof element.data('height') !== 'undefined' && element.data('height') !== false) {
                    mapHeight = element.data('height');
                }

                var uniqueId;
                if (typeof element.data('unique-id') !== 'undefined' && element.data('unique-id') !== false) {
                    uniqueId = element.data('unique-id');
                }

                var scrollWheel;
                if (typeof element.data('scroll-wheel') !== 'undefined') {
                    scrollWheel = element.data('scroll-wheel');
                }
                var addresses;
                if (typeof element.data('addresses') !== 'undefined' && element.data('addresses') !== false) {
                    addresses = element.data('addresses');
                }

                var map = "map_" + uniqueId;
                var geocoder = "geocoder_" + uniqueId;
                var holderId = "mkd-map-" + uniqueId;

                mkdInitializeGoogleMap(customMapStyle, colorOverlay, saturation, lightness, scrollWheel, zoom, holderId, mapHeight, pin, map, geocoder, addresses);
            });
        }

    }

    /*
     **	Init Google Map
     */
    function mkdInitializeGoogleMap(customMapStyle, color, saturation, lightness, wheel, zoom, holderId, height, pin, map, geocoder, data) {

        var mapStyles = [
            {
                stylers: [
                    {hue: color},
                    {saturation: saturation},
                    {lightness: lightness},
                    {gamma: 1}
                ]
            }
        ];

        var googleMapStyleId;

        if (customMapStyle) {
            googleMapStyleId = 'mkd-style';
        } else {
            googleMapStyleId = google.maps.MapTypeId.ROADMAP;
        }

        var qoogleMapType = new google.maps.StyledMapType(mapStyles,
            {name: "Mikado Google Map"});

        geocoder = new google.maps.Geocoder();
        var latlng = new google.maps.LatLng(-34.397, 150.644);

        if (!isNaN(height)) {
            height = height + 'px';
        }

        var myOptions = {

            zoom: zoom,
            scrollwheel: wheel,
            center: latlng,
            zoomControl: true,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle.SMALL,
                position: google.maps.ControlPosition.RIGHT_CENTER
            },
            scaleControl: false,
            scaleControlOptions: {
                position: google.maps.ControlPosition.LEFT_CENTER
            },
            streetViewControl: false,
            streetViewControlOptions: {
                position: google.maps.ControlPosition.LEFT_CENTER
            },
            panControl: false,
            panControlOptions: {
                position: google.maps.ControlPosition.LEFT_CENTER
            },
            mapTypeControl: false,
            mapTypeControlOptions: {
                mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'mkd-style'],
                style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                position: google.maps.ControlPosition.LEFT_CENTER
            },
            mapTypeId: googleMapStyleId
        };

        map = new google.maps.Map(document.getElementById(holderId), myOptions);
        map.mapTypes.set('mkd-style', qoogleMapType);

        var index;

        for (index = 0; index < data.length; ++index) {
            mkdInitializeGoogleAddress(data[index], pin, map, geocoder);
        }

        var holderElement = document.getElementById(holderId);
        holderElement.style.height = height;
    }

    /*
     **	Init Google Map Addresses
     */
    function mkdInitializeGoogleAddress(data, pin, map, geocoder) {
        if (data === '')
            return;
        var contentString = '<div id="content">' +
            '<div id="siteNotice">' +
            '</div>' +
            '<div id="bodyContent">' +
            '<p>' + data + '</p>' +
            '</div>' +
            '</div>';
        var infowindow = new google.maps.InfoWindow({
            content: contentString
        });
        geocoder.geocode({'address': data}, function (results, status) {
            if (status === google.maps.GeocoderStatus.OK) {
                map.setCenter(results[0].geometry.location);
                var marker = new google.maps.Marker({
                    map: map,
                    position: results[0].geometry.location,
                    icon: pin,
                    title: data['store_title']
                });
                google.maps.event.addListener(marker, 'click', function () {
                    infowindow.open(map, marker);
                });

                google.maps.event.addDomListener(window, 'resize', function () {
                    map.setCenter(results[0].geometry.location);
                });

            }
        });
    }

    function mkdInitAccordions() {
        var accordion = $('.mkd-accordion-holder');
        if (accordion.length) {
            accordion.each(function () {

                var thisAccordion = $(this);

                if (thisAccordion.hasClass('mkd-accordion')) {

                    thisAccordion.accordion({
                        animate: "swing",
                        collapsible: false,
                        active: 0,
                        icons: "",
                        heightStyle: "content"
                    });
                }

                if (thisAccordion.hasClass('mkd-toggle')) {

                    var toggleAccordion = $(this);
                    var toggleAccordionTitle = toggleAccordion.find('.mkd-title-holder');
                    var toggleAccordionContent = toggleAccordionTitle.next();

                    toggleAccordion.addClass("accordion ui-accordion ui-accordion-icons ui-widget ui-helper-reset");
                    toggleAccordionTitle.addClass("ui-accordion-header ui-helper-reset ui-state-default ui-corner-top ui-corner-bottom");
                    toggleAccordionContent.addClass("ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom").hide();

                    toggleAccordionTitle.each(function () {
                        var thisTitle = $(this);
                        thisTitle.hover(function () {
                            thisTitle.toggleClass("ui-state-hover");
                        });

                        thisTitle.on('click', function () {
                            thisTitle.toggleClass('ui-accordion-header-active ui-state-active ui-state-default ui-corner-bottom');
                            thisTitle.next().toggleClass('ui-accordion-content-active').slideToggle(400);
                        });
                    });
                }
            });
        }
    }

    function mkdInitImageGallery() {

        var galleries = $('.mkd-image-gallery');

        if (galleries.length) {
            galleries.each(function () {
                var gallery = $(this).children('.mkd-image-gallery-slider'),
                    autoplay = gallery.data('autoplay'),
                    arrows = (gallery.data('arrows') == 'yes'),
                    dots = (gallery.data('dots') == 'yes');
                if (dots) {
                    gallery.addClass('slick-dots');
                }
                gallery.on('init', function () {

                    // change default opacity on init
                    gallery.css({'opacity': '1'});
                }).slick({
                    singleItem: true,
                    autoplay: autoplay !== 'disable',
                    autoplaySpeed: autoplay * 1000,
                    arrows: arrows,
                    dots: dots,
                    slideSpeed: 600
                });
            });
        }

    }

    /**
     * Initializes portfolio list
     */
    function mkdInitPortfolio() {
        var portList = $('.mkd-portfolio-list-holder-outer.mkd-ptf-standard, .mkd-portfolio-list-holder-outer.mkd-ptf-gallery, .mkd-portfolio-list-holder-outer.mkd-ptf-simple');
        if (portList.length) {
            portList.each(function () {
                var thisPortList = $(this);
                mkdInitPortMixItUp(thisPortList);
            });
        }
    }

    /**
     * Initializes mixItUp function for specific container
     */
    function mkdInitPortMixItUp(container) {
        var filterClass = '';
        if (container.hasClass('mkd-ptf-has-filter')) {
            filterClass = container.find('.mkd-portfolio-filter-holder-inner ul li').data('class');
            filterClass = '.' + filterClass;
        }

        var holderInner = container.find('.mkd-portfolio-list-holder');
        holderInner.mixItUp({
            callbacks: {
                onMixLoad: function () {
                    holderInner.find('article').css('visibility', 'visible');
                    mkd.modules.common.mkdInitParallax(); //since there is no height of portoflio holder before loading animation
                },
                onMixStart: function () {
                    holderInner.find('article').css('visibility', 'visible');
                    container.find('.mkd-ptf-list-load-more').css('visibility', 'hidden');
                },
                onMixBusy: function () {
                    holderInner.find('article').css('visibility', 'visible');
                },
                onMixEnd: function () {
                    mkdTiltHoverEffect();
                    mkdPortfolioAppearEffect();
                    container.find('.mkd-ptf-list-load-more').css('visibility', 'visible');
                }
            },
            selectors: {
                filter: filterClass
            },
            animation: {
                duration: 600,
                effects: 'fade',
            }
        });

    }

    /*
     **	Init portfolio list masonry type
     */
    function mkdInitPortfolioListMasonry() {
        var portList = $('.mkd-portfolio-list-holder-outer.mkd-ptf-masonry');
        if (portList.length) {
            portList.each(function () {
                var thisPortList = $(this).children('.mkd-portfolio-list-holder');

                mkdResizeMasonry(thisPortList);

                mkdInitMasonry(thisPortList);
                $(window).resize(function () {
                    mkdResizeMasonry(thisPortList);
                    mkdInitMasonry(thisPortList);
                });
            });
        }
    }

    function mkdInitMasonry(container) {
        container.animate({opacity: 1});
        container.waitForImages(function () {
            container.isotope({
                itemSelector: '.mkd-portfolio-item',
                resizable: false,
                layoutMode: 'packery',
                packery: {
                    columnWidth: '.mkd-portfolio-list-masonry-grid-sizer'
                }
            });
            container.parent().find('.mkd-ptf-list-load-more').css('visibility', 'visible');
            container.addClass('mkd-appeared');
        });
    }

    function mkdResizeMasonry(container) {

        var size = container.find('.mkd-portfolio-list-masonry-grid-sizer').width() * 0.75;

        var defaultMasonryItem = container.find('.mkd-default-masonry-item');
        var largeWidthMasonryItem = container.find('.mkd-large-width-masonry-item');
        var largeHeightMasonryItem = container.find('.mkd-large-height-masonry-item');
        var largeWidthHeightMasonryItem = container.find('.mkd-large-width-height-masonry-item');

        defaultMasonryItem.css('height', size);
        largeHeightMasonryItem.css('height', Math.round(2 * size));

        if (mkd.windowWidth > 768) {
            largeWidthHeightMasonryItem.css('height', Math.round(2 * size));
            largeHeightMasonryItem.css('height', Math.round(2 * size));
            largeWidthMasonryItem.css('height', size);
        } else {
            largeWidthHeightMasonryItem.css('height', size);
            largeHeightMasonryItem.css('height', size);
        }
    }

    /**
     * Initializes portfolio pinterest
     */
    function mkdInitPortfolioListPinterest() {

        var portList = $('.mkd-portfolio-list-holder-outer.mkd-ptf-pinterest');
        if (portList.length) {
            portList.each(function () {
                var thisPortList = $(this).children('.mkd-portfolio-list-holder');
                mkdInitPinterest(thisPortList);
                $(window).resize(function () {
                    mkdInitPinterest(thisPortList);
                });
            });

        }
    }

    function mkdInitPinterest(container) {
        container.animate({opacity: 1});
        container.waitForImages(function () {
            container.isotope({
                layoutMode: 'packery',
                itemSelector: '.mkd-portfolio-item',
                packery: {
                    columnWidth: '.mkd-portfolio-list-masonry-grid-sizer'
                }
            });
            container.parent().find('.mkd-ptf-list-load-more').css('visibility', 'visible');
        });

    }

    /**
     * Initializes portfolio masonry filter
     */
    function mkdInitPortfolioMasonryFilter() {

        var filterHolder = $('.mkd-portfolio-filter-holder.mkd-masonry-filter');

        if (filterHolder.length) {
            filterHolder.each(function () {

                var thisFilterHolder = $(this);

                var portfolioIsotopeAnimation = null;

                var filter = thisFilterHolder.find('ul li').data('class');

                thisFilterHolder.find('.filter:first').addClass('current');

                thisFilterHolder.find('.filter').click(function () {

                    var currentFilter = $(this);
                    clearTimeout(portfolioIsotopeAnimation);

                    $('.isotope, .isotope .isotope-item').css('transition-duration', '0.8s');

                    portfolioIsotopeAnimation = setTimeout(function () {
                        $('.isotope, .isotope .isotope-item').css('transition-duration', '0s');
                    }, 700);

                    var selector = $(this).attr('data-filter');
                    thisFilterHolder.siblings('.mkd-portfolio-list-holder-outer').find('.mkd-portfolio-list-holder').isotope({filter: selector});

                    thisFilterHolder.find('.filter').removeClass('current');
                    currentFilter.addClass('current');

                    return false;

                });
            });
        }
    }

    function mkdParentChildFilter() {

        var filterHolder = $('.mkd-portfolio-filter-holder');

        if (filterHolder.length) {
            filterHolder.each(function () {

                var thisFilterHolder = $(this);
                //parent child filter
                var parentCategories = thisFilterHolder.find('.parent-filter');

                parentCategories.click(function () {
                    var activeParent = $(this);

                    parentCategories.each(function () {
                        $(this).removeClass('active');
                    });
                    activeParent.addClass('active');

                    var parentId = activeParent.data('group-id');

                    var children = $(this).siblings('ul');

                    children.each(function () {
                        if ($(this).data('parent-id') == parentId) {
                            var thisItem = $(this);
                            setTimeout(function () {
                                thisItem.fadeIn();
                            }, 500);
                        }
                        else {
                            $(this).fadeOut();
                        }
                    });
                });
            });
        }
    }

    /**
     * Initializes portfolio load more function
     */
    function mkdInitPortfolioLoadMore() {
        var portList = $('.mkd-portfolio-list-holder-outer.mkd-ptf-load-more');
        if (portList.length) {
            portList.each(function () {

                var thisPortList = $(this);
                var thisPortListInner = thisPortList.find('.mkd-portfolio-list-holder');
                var nextPage;
                var maxNumPages;
                var loadMoreButton = thisPortList.find('.mkd-ptf-list-load-more a');
                var loadMoreInitialText = loadMoreButton.text();
                var loadMoreLoadingText = mkdGlobalVars.vars.mkdPtfLoadMoreMessage;

                if (typeof thisPortList.data('max-num-pages') !== 'undefined' && thisPortList.data('max-num-pages') !== false) {
                    maxNumPages = thisPortList.data('max-num-pages');
                }

                loadMoreButton.on('click', function (e) {
                    var loadMoreDatta = mkdGetPortfolioAjaxData(thisPortList);
                    nextPage = loadMoreDatta.nextPage;
                    loadMoreButton.text(loadMoreLoadingText);
                    e.preventDefault();
                    e.stopPropagation();
                    if (nextPage <= maxNumPages) {
                        var ajaxData = mkdSetPortfolioAjaxData(loadMoreDatta);
                        $.ajax({
                            type: 'POST',
                            data: ajaxData,
                            url: mkdCoreAjaxUrl,
                            success: function (data) {
                                nextPage++;
                                thisPortList.data('next-page', nextPage);
                                var response = $.parseJSON(data);
                                var responseHtml = mkdConvertHTML(response.html); //convert response html into jQuery collection that Mixitup can work with
                                thisPortList.waitForImages(function () {
                                    setTimeout(function () {
                                        if (thisPortList.hasClass('mkd-ptf-masonry') || thisPortList.hasClass('mkd-ptf-pinterest')) {
                                            thisPortListInner.isotope().append(responseHtml).isotope('appended', responseHtml).isotope('reloadItems');
                                            if (thisPortList.hasClass('mkd-ptf-masonry')) {
                                                mkdResizeMasonry(thisPortList);
                                                mkdTiltHoverEffect();
                                            }
                                        } else {
                                            thisPortListInner.mixItUp('append', responseHtml);
                                        }
                                        loadMoreButton.text(loadMoreInitialText);
                                    }, 400);
                                });
                            }
                        });
                    }
                    if (nextPage === maxNumPages) {
                        loadMoreButton.hide();
                    }
                });

            });
        }
    }

    function mkdConvertHTML(html) {
        var newHtml = $.trim(html),
            $html = $(newHtml),
            $empty = $();

        $html.each(function (index, value) {
            if (value.nodeType === 1) {
                $empty = $empty.add(this);
            }
        });

        return $empty;
    }

    /**
     * Initializes portfolio load more data params
     * @param portfolio list container with defined data params
     * return array
     */
    function mkdGetPortfolioAjaxData(container) {
        var returnValue = {};

        returnValue.type = '';
        returnValue.columns = '';
        returnValue.gridSize = '';
        returnValue.orderBy = '';
        returnValue.order = '';
        returnValue.number = '';
        returnValue.imageSize = '';
        returnValue.customImageDimensions = '';
        returnValue.filter = '';
        returnValue.filterOrderBy = '';
        returnValue.category = '';
        returnValue.selectedProjects = '';
        returnValue.showLoadMore = '';
        returnValue.titleTag = '';
        returnValue.showCategories = '';
        returnValue.nextPage = '';
        returnValue.maxNumPages = '';
        returnValue.showExcerpt = '';
        returnValue.shaderBackgroundStyle = '';
        returnValue.shaderBackgroundColor = '';

        if (typeof container.data('type') !== 'undefined' && container.data('type') !== false) {
            returnValue.type = container.data('type');
        }

        if (typeof container.data('grid-size') !== 'undefined' && container.data('grid-size') !== false) {
            returnValue.gridSize = container.data('grid-size');
        }

        if (typeof container.data('columns') !== 'undefined' && container.data('columns') !== false) {
            returnValue.columns = container.data('columns');
        }

        if (typeof container.data('order-by') !== 'undefined' && container.data('order-by') !== false) {
            returnValue.orderBy = container.data('order-by');
        }

        if (typeof container.data('order') !== 'undefined' && container.data('order') !== false) {
            returnValue.order = container.data('order');
        }

        if (typeof container.data('number') !== 'undefined' && container.data('number') !== false) {
            returnValue.number = container.data('number');
        }

        if (typeof container.data('image-size') !== 'undefined' && container.data('image-size') !== false) {
            returnValue.imageSize = container.data('image-size');
        }

        if (typeof container.data('custom-image-dimensions') !== 'undefined' && container.data('custom-image-dimensions') !== false) {
            returnValue.customImageDimensions = container.data('custom-image-dimensions');
        }

        if (typeof container.data('filter') !== 'undefined' && container.data('filter') !== false) {
            returnValue.filter = container.data('filter');
        }

        if (typeof container.data('filter-order-by') !== 'undefined' && container.data('filter-order-by') !== false) {
            returnValue.filterOrderBy = container.data('filter-order-by');
        }

        if (typeof container.data('category') !== 'undefined' && container.data('category') !== false) {
            returnValue.category = container.data('category');
        }

        if (typeof container.data('selected-projects') !== 'undefined' && container.data('selected-projects') !== false) {
            returnValue.selectedProjects = container.data('selected-projects');
        }

        if (typeof container.data('show-load-more') !== 'undefined' && container.data('show-load-more') !== false) {
            returnValue.showLoadMore = container.data('show-load-more');
        }

        if (typeof container.data('title-tag') !== 'undefined' && container.data('title-tag') !== false) {
            returnValue.titleTag = container.data('title-tag');
        }

        if (typeof container.data('show-categories') !== 'undefined' && container.data('show-categories') !== false) {
            returnValue.showCategories = container.data('show-categories');
        }

        if (typeof container.data('next-page') !== 'undefined' && container.data('next-page') !== false) {
            returnValue.nextPage = container.data('next-page');
        }

        if (typeof container.data('max-num-pages') !== 'undefined' && container.data('max-num-pages') !== false) {
            returnValue.maxNumPages = container.data('max-num-pages');
        }

        if (typeof container.data('show-excerpt') !== 'undefined' && container.data('show-excerpt') !== false) {
            returnValue.showExcerpt = container.data('show-excerpt');
        }

        if (typeof container.data('shader-background-style') !== 'undefined' && container.data('shader-background-style') !== false) {
            returnValue.shaderBackgroundStyle = container.data('shader-background-style');
        }

        if (typeof container.data('shader-background-color') !== 'undefined' && container.data('shader-background-color') !== false) {
            returnValue.shaderBackgroundColor = container.data('shader-background-color');
        }

        return returnValue;
    }

    /**
     * Sets portfolio load more data params for ajax function
     * @param portfolio list container with defined data params
     * return array
     */
    function mkdSetPortfolioAjaxData(container) {
        var returnValue = {
            action: 'mkd_core_portfolio_ajax_load_more',
            type: container.type,
            columns: container.columns,
            gridSize: container.gridSize,
            orderBy: container.orderBy,
            order: container.order,
            number: container.number,
            imageSize: container.imageSize,
            customImageDimensions: container.customImageDimensions,
            filter: container.filter,
            filterOrderBy: container.filterOrderBy,
            category: container.category,
            selectedProjectes: container.selectedProjectes,
            showLoadMore: container.showLoadMore,
            titleTag: container.titleTag,
            showCategories: container.showCategories,
            nextPage: container.nextPage,
            showExcerpt: container.showExcerpt,
            shaderBackgroundStyle: container.shaderBackgroundStyle,
            shaderBackgroundColor: container.shaderBackgroundColor,
        };
        return returnValue;
    }

    /*
     * Portfolio Appear effect
     */
    function mkdPortfolioAppearEffect() {
        var ptfLists = $('.mkd-portfolio-list-holder-outer.mkd-appear-effect');

        if (ptfLists.length && !mkd.htmlEl.hasClass('touch')) {
            ptfLists.each(function () {
                var ptfList = $(this),
                    article = ptfList.find('article');

                if (ptfList.hasClass('mkd-one-by-one')) {
                    var animateCycle = 5, // rewind delay
                        animateCycleCounter = 0;

                    article.not('mkd-appeared').each(function () {
                        var currentArticle = $(this);

                        setTimeout(function () {
                            currentArticle.appear(function () {
                                animateCycleCounter++;

                                if (animateCycleCounter == animateCycle) {
                                    animateCycleCounter = 0;
                                }

                                setTimeout(function () {
                                    currentArticle.addClass('mkd-appeared');
                                }, animateCycleCounter * 250);
                            }, {accX: 0, accY: 0});
                        }, 30);
                    });
                }

                if (ptfList.hasClass('mkd-random')) {
                    var randomize = function (n) {
                        var queue = [];

                        for (var i = 0; i < numberOfItems; i++) {
                            var queueElement = Math.floor(Math.random() * numberOfItems);

                            if (jQuery.inArray(queueElement, queue) > 0) {
                                --i;
                            } else {
                                queue.push(queueElement);
                            }
                        }

                        return queue;
                    };

                    var numberOfItems = article.length,
                        r = randomize(numberOfItems);

                    article.not('mkd-appeared').each(function (i) {
                        var currentArticle = $(this);

                        currentArticle.appear(function () {
                            setTimeout(function () {
                                currentArticle.addClass('mkd-appeared');
                            }, r[i] * 80);
                        });
                    });
                }

            });
        }
    }

    /**
     * Slider object that initializes whole slider functionality
     * @type {Function}
     */
    var mkdSlider = mkd.modules.shortcodes.mkdSlider = function () {

        //all sliders on the page
        var sliders = $('.mkd-slider .carousel');
        //image regex used to extract img source
        var imageRegex = /url\(["']?([^'")]+)['"]?\)/;
        //default responsive breakpoints set
        var responsiveBreakpointSet = [1600, 1200, 900, 650, 500, 320];
        //var init for coefficiens array
        var coefficientsGraphicArray;
        var coefficientsTitleArray;
        var coefficientsSubtitleArray;
        var coefficientsTextArray;
        var coefficientsButtonArray;
        //var init for slider elements responsive coefficients
        var sliderGraphicCoefficient;
        var sliderTitleCoefficient;
        var sliderSubtitleCoefficient;
        var sliderTextCoefficient;
        var sliderButtonCoefficient;
        var sliderTitleCoefficientLetterSpacing;
        var sliderSubtitleCoefficientLetterSpacing;
        var sliderTextCoefficientLetterSpacing;

        /*** Functionality for translating image in slide - START ***/

        var matrixArray = {
            zoom_center: '1.2, 0, 0, 1.2, 0, 0',
            zoom_top_left: '1.2, 0, 0, 1.2, -150, -150',
            zoom_top_right: '1.2, 0, 0, 1.2, 150, -150',
            zoom_bottom_left: '1.2, 0, 0, 1.2, -150, 150',
            zoom_bottom_right: '1.2, 0, 0, 1.2, 150, 150'
        };

        // regular expression for parsing out the matrix components from the matrix string
        var matrixRE = /\([0-9epx\.\, \t\-]+/gi;

        // parses a matrix string of the form "matrix(n1,n2,n3,n4,n5,n6)" and
        // returns an array with the matrix components
        var parseMatrix = function (val) {
            return val.match(matrixRE)[0].substr(1).split(",").map(function (s) {
                return parseFloat(s);
            });
        };

        // transform css property names with vendor prefixes;
        // the plugin will check for values in the order the names are listed here and return as soon as there
        // is a value; so listing the W3 std name for the transform results in that being used if its available
        var transformPropNames = [
            "transform",
            "-webkit-transform"
        ];

        var getTransformMatrix = function (el) {
            // iterate through the css3 identifiers till we hit one that yields a value
            var matrix = null;
            transformPropNames.some(function (prop) {
                matrix = el.css(prop);
                return (matrix !== null && matrix !== "");
            });

            // if "none" then we supplant it with an identity matrix so that our parsing code below doesn't break
            matrix = (!matrix || matrix === "none") ?
                "matrix(1,0,0,1,0,0)" : matrix;
            return parseMatrix(matrix);
        };

        // set the given matrix transform on the element; note that we apply the css transforms in reverse order of how its given
        // in "transformPropName" to ensure that the std compliant prop name shows up last
        var setTransformMatrix = function (el, matrix) {
            var m = "matrix(" + matrix.join(",") + ")";
            for (var i = transformPropNames.length - 1; i >= 0; --i) {
                el.css(transformPropNames[i], m + ' rotate(0.01deg)');
            }
        };

        // interpolates a value between a range given a percent
        var interpolate = function (from, to, percent) {
            return from + ((to - from) * (percent / 100));
        };

        $.fn.transformAnimate = function (opt) {
            // extend the options passed in by caller
            var options = {
                transform: "matrix(1,0,0,1,0,0)"
            };
            $.extend(options, opt);

            // initialize our custom property on the element to track animation progress
            this.css("percentAnim", 0);

            // supplant "options.step" if it exists with our own routine
            var sourceTransform = getTransformMatrix(this);
            var targetTransform = parseMatrix(options.transform);
            options.step = function (percentAnim, fx) {
                // compute the interpolated transform matrix for the current animation progress
                var $this = $(this);
                var matrix = sourceTransform.map(function (c, i) {
                    return interpolate(c, targetTransform[i],
                        percentAnim);
                });

                // apply the new matrix
                setTransformMatrix($this, matrix);

                // invoke caller's version of "step" if one was supplied;
                if (opt.step) {
                    opt.step.apply(this, [matrix, fx]);
                }
            };

            // animate!
            return this.stop().animate({percentAnim: 100}, options);
        };

        /*** Functionality for translating image in slide - END ***/


        /**
         * Calculate heights for slider holder and slide item, depending on window width, but only if slider is set to be responsive
         * @param slider, current slider
         * @param defaultHeight, default height of slider, set in shortcode
         * @param responsive_breakpoint_set, breakpoints set for slider responsiveness
         * @param reset, boolean for reseting heights
         */
        var setSliderHeight = function (slider, defaultHeight, responsive_breakpoint_set, reset) {
            var sliderHeight = defaultHeight;
            if (!reset) {
                if (mkd.windowWidth > responsive_breakpoint_set[0]) {
                    sliderHeight = defaultHeight;
                } else if (mkd.windowWidth > responsive_breakpoint_set[1]) {
                    sliderHeight = defaultHeight * 0.75;
                } else if (mkd.windowWidth > responsive_breakpoint_set[2]) {
                    sliderHeight = defaultHeight * 0.6;
                } else if (mkd.windowWidth > responsive_breakpoint_set[3]) {
                    sliderHeight = defaultHeight * 0.55;
                } else if (mkd.windowWidth <= responsive_breakpoint_set[3]) {
                    sliderHeight = defaultHeight * 0.45;
                }
            }

            slider.css({'height': (sliderHeight) + 'px'});
            slider.find('.mkd-slider-preloader').css({'height': (sliderHeight) + 'px'});
            slider.find('.mkd-slider-preloader .mkd-ajax-loader').css({'display': 'block'});
            slider.find('.item').css({'height': (sliderHeight) + 'px'});
        };

        /**
         * Calculate heights for slider holder and slide item, depending on window size, but only if slider is set to be full height
         * @param slider, current slider
         */
        var setSliderFullHeight = function (slider) {
            var mobileHeaderHeight = mkd.windowWidth < 1000 ? mkdGlobalVars.vars.mkdMobileHeaderHeight + $('.mkd-top-bar').height() : 0;
            slider.css({'height': (mkd.windowHeight - mobileHeaderHeight) + 'px'});
            slider.find('.mkd-slider-preloader').css({'height': (mkd.windowHeight) + 'px'});
            slider.find('.mkd-slider-preloader .mkd-ajax-loader').css({'display': 'block'});
            slider.find('.item').css({'height': (mkd.windowHeight) + 'px'});
        };

        /**
         * Set initial sizes for slider elements and put them in global variables
         * @param slideItem, each slide
         * @param index, index od slide item
         */
        var setSizeGlobalVariablesForSlideElements = function (slideItem, index) {
            window["slider_graphic_width_" + index] = [];
            window["slider_graphic_height_" + index] = [];
            window["slider_title_" + index] = [];
            window["slider_subtitle_" + index] = [];
            window["slider_text_" + index] = [];
            window["slider_button1_" + index] = [];
            window["slider_button2_" + index] = [];

            //graphic size
            window["slider_graphic_width_" + index].push(parseFloat(slideItem.find('.mkd-thumb img').data("width")));
            window["slider_graphic_height_" + index].push(parseFloat(slideItem.find('.mkd-thumb img').data("height")));

            // font-size (0)
            window["slider_title_" + index].push(parseFloat(slideItem.find('.mkd-slide-title').css("font-size")));
            window["slider_subtitle_" + index].push(parseFloat(slideItem.find('.mkd-slide-subtitle').css("font-size")));
            window["slider_text_" + index].push(parseFloat(slideItem.find('.mkd-slide-text').css("font-size")));
            window["slider_button1_" + index].push(parseFloat(slideItem.find('.mkd-btn:eq(0)').css("font-size")));
            window["slider_button2_" + index].push(parseFloat(slideItem.find('.mkd-btn:eq(1)').css("font-size")));

            // line-height (1)
            window["slider_title_" + index].push(parseFloat(slideItem.find('.mkd-slide-title').css("line-height")));
            window["slider_subtitle_" + index].push(parseFloat(slideItem.find('.mkd-slide-subtitle').css("line-height")));
            window["slider_text_" + index].push(parseFloat(slideItem.find('.mkd-slide-text').css("line-height")));
            window["slider_button1_" + index].push(parseFloat(slideItem.find('.mkd-btn:eq(0)').css("line-height")));
            window["slider_button2_" + index].push(parseFloat(slideItem.find('.mkd-btn:eq(1)').css("line-height")));

            // letter-spacing (2)
            window["slider_title_" + index].push(parseFloat(slideItem.find('.mkd-slide-title').css("letter-spacing")));
            window["slider_subtitle_" + index].push(parseFloat(slideItem.find('.mkd-slide-subtitle').css("letter-spacing")));
            window["slider_text_" + index].push(parseFloat(slideItem.find('.mkd-slide-text').css("letter-spacing")));
            window["slider_button1_" + index].push(parseFloat(slideItem.find('.mkd-btn:eq(0)').css("letter-spacing")));
            window["slider_button2_" + index].push(parseFloat(slideItem.find('.mkd-btn:eq(1)').css("letter-spacing")));

            // margin-bottom (3)
            window["slider_title_" + index].push(parseFloat(slideItem.find('.mkd-slide-title').css("margin-bottom")));
            window["slider_subtitle_" + index].push(parseFloat(slideItem.find('.mkd-slide-subtitle').css("margin-bottom")));


            // slider_button padding top/bottom(3), padding left/right(4)
            window["slider_button1_" + index].push(parseFloat(slideItem.find('.mkd-btn:eq(0)').css("padding-top")));
            window["slider_button2_" + index].push(parseFloat(slideItem.find('.mkd-btn:eq(1)').css("padding-top")));

            window["slider_button1_" + index].push(parseFloat(slideItem.find('.mkd-btn:eq(0)').css("padding-left")));
            window["slider_button2_" + index].push(parseFloat(slideItem.find('.mkd-btn:eq(1)').css("padding-left")));
        };

        /**
         * Set responsive coefficients for slider elements
         * @param responsiveBreakpointSet, responsive breakpoints
         * @param coefficientsGraphicArray, responsive coeaficcients for graphic
         * @param coefficientsTitleArray, responsive coeaficcients for title
         * @param coefficientsSubtitleArray, responsive coeaficcients for subtitle
         * @param coefficientsTextArray, responsive coeaficcients for text
         * @param coefficientsButtonArray, responsive coeaficcients for button
         */
        var setSliderElementsResponsiveCoeffeicients = function (responsiveBreakpointSet, coefficientsGraphicArray, coefficientsTitleArray, coefficientsSubtitleArray, coefficientsTextArray, coefficientsButtonArray) {

            function coefficientsSetter(graphicArray, titleArray, subtitleArray, textArray, buttonArray) {
                sliderGraphicCoefficient = graphicArray;
                sliderTitleCoefficient = titleArray;
                sliderSubtitleCoefficient = subtitleArray;
                sliderTextCoefficient = textArray;
                sliderButtonCoefficient = buttonArray;
            }

            if (mkd.windowWidth > responsiveBreakpointSet[0]) {
                coefficientsSetter(coefficientsGraphicArray[0], coefficientsTitleArray[0], coefficientsSubtitleArray[0], coefficientsTextArray[0], coefficientsButtonArray[0]);
            } else if (mkd.windowWidth > responsiveBreakpointSet[1]) {
                coefficientsSetter(coefficientsGraphicArray[1], coefficientsTitleArray[1], coefficientsSubtitleArray[1], coefficientsTextArray[1], coefficientsButtonArray[1]);
            } else if (mkd.windowWidth > responsiveBreakpointSet[2]) {
                coefficientsSetter(coefficientsGraphicArray[2], coefficientsTitleArray[2], coefficientsSubtitleArray[2], coefficientsTextArray[2], coefficientsButtonArray[2]);
            } else if (mkd.windowWidth > responsiveBreakpointSet[3]) {
                coefficientsSetter(coefficientsGraphicArray[3], coefficientsTitleArray[3], coefficientsSubtitleArray[3], coefficientsTextArray[3], coefficientsButtonArray[3]);
            } else if (mkd.windowWidth > responsiveBreakpointSet[4]) {
                coefficientsSetter(coefficientsGraphicArray[4], coefficientsTitleArray[4], coefficientsSubtitleArray[4], coefficientsTextArray[4], coefficientsButtonArray[4]);
            } else if (mkd.windowWidth > responsiveBreakpointSet[5]) {
                coefficientsSetter(coefficientsGraphicArray[5], coefficientsTitleArray[5], coefficientsSubtitleArray[5], coefficientsTextArray[5], coefficientsButtonArray[5]);
            } else {
                coefficientsSetter(coefficientsGraphicArray[6], coefficientsTitleArray[6], coefficientsSubtitleArray[6], coefficientsTextArray[6], coefficientsButtonArray[6]);
            }

            // letter-spacing decrease quicker
            sliderTitleCoefficientLetterSpacing = sliderTitleCoefficient;
            sliderSubtitleCoefficientLetterSpacing = sliderSubtitleCoefficient;
            sliderTextCoefficientLetterSpacing = sliderTextCoefficient;
            if (mkd.windowWidth <= responsiveBreakpointSet[0]) {
                sliderTitleCoefficientLetterSpacing = sliderTitleCoefficient / 2;
                sliderSubtitleCoefficientLetterSpacing = sliderSubtitleCoefficient / 2;
                sliderTextCoefficientLetterSpacing = sliderTextCoefficient / 2;
            }
        };

        /**
         * Set sizes for slider elements
         * @param slideItem, each slide
         * @param index, index od slide item
         * @param reset, boolean for reseting sizes
         */
        var setSliderElementsSize = function (slideItem, index, reset) {

            if (reset) {
                sliderGraphicCoefficient = sliderTitleCoefficient = sliderSubtitleCoefficient = sliderTextCoefficient = sliderButtonCoefficient = sliderTitleCoefficientLetterSpacing = sliderSubtitleCoefficientLetterSpacing = sliderTextCoefficientLetterSpacing = 1;
            }

            slideItem.find('.mkd-thumb').css({
                "width": Math.round(window["slider_graphic_width_" + index][0] * sliderGraphicCoefficient) + 'px',
                "height": Math.round(window["slider_graphic_height_" + index][0] * sliderGraphicCoefficient) + 'px'
            });

            slideItem.find('.mkd-slide-title').css({
                "font-size": Math.round(window["slider_title_" + index][0] * sliderTitleCoefficient) + 'px',
                "line-height": Math.round(window["slider_title_" + index][1] * sliderTitleCoefficient) + 'px',
                "letter-spacing": Math.round(window["slider_title_" + index][2] * sliderTitleCoefficient) + 'px',
                "margin-bottom": Math.round(window["slider_title_" + index][3] * sliderTitleCoefficient) + 'px'
            });

            slideItem.find('.mkd-slide-subtitle').css({
                "font-size": Math.round(window["slider_subtitle_" + index][0] * sliderSubtitleCoefficient) + 'px',
                "line-height": Math.round(window["slider_subtitle_" + index][1] * sliderSubtitleCoefficient) + 'px',
                "margin-bottom": Math.round(window["slider_subtitle_" + index][3] * sliderSubtitleCoefficient) + 'px',
                "letter-spacing": Math.round(window["slider_subtitle_" + index][2] * sliderSubtitleCoefficientLetterSpacing) + 'px'
            });

            slideItem.find('.mkd-slide-text').css({
                "font-size": Math.round(window["slider_text_" + index][0] * sliderTextCoefficient) + 'px',
                "line-height": Math.round(window["slider_text_" + index][1] * sliderTextCoefficient) + 'px',
                "letter-spacing": Math.round(window["slider_text_" + index][2] * sliderTextCoefficientLetterSpacing) + 'px'
            });

            slideItem.find('.mkd-btn:eq(0)').css({
                "font-size": Math.round(window["slider_button1_" + index][0] * sliderButtonCoefficient) + 'px',
                "line-height": Math.round(window["slider_button1_" + index][1] * sliderButtonCoefficient) + 'px',
                "letter-spacing": Math.round(window["slider_button1_" + index][2] * sliderButtonCoefficient) + 'px',
                "padding-top": Math.round(window["slider_button1_" + index][3] * sliderButtonCoefficient) + 'px',
                "padding-bottom": Math.round(window["slider_button1_" + index][3] * sliderButtonCoefficient) + 'px',
                "padding-left": Math.round(window["slider_button1_" + index][4] * sliderButtonCoefficient) + 'px',
                "padding-right": Math.round(window["slider_button1_" + index][4] * sliderButtonCoefficient) + 'px'
            });
            slideItem.find('.mkd-btn:eq(1)').css({
                "font-size": Math.round(window["slider_button2_" + index][0] * sliderButtonCoefficient) + 'px',
                "line-height": Math.round(window["slider_button2_" + index][1] * sliderButtonCoefficient) + 'px',
                "letter-spacing": Math.round(window["slider_button2_" + index][2] * sliderButtonCoefficient) + 'px',
                "padding-top": Math.round(window["slider_button2_" + index][3] * sliderButtonCoefficient) + 'px',
                "padding-bottom": Math.round(window["slider_button2_" + index][3] * sliderButtonCoefficient) + 'px',
                "padding-left": Math.round(window["slider_button2_" + index][4] * sliderButtonCoefficient) + 'px',
                "padding-right": Math.round(window["slider_button2_" + index][4] * sliderButtonCoefficient) + 'px'
            });
        };

        /**
         * Set heights for slider and elemnts depending on slider settings (full height, responsive height od set height)
         * @param slider, current slider
         */
        var setHeights = function (slider) {

            slider.find('.item').each(function (i) {
                setSizeGlobalVariablesForSlideElements($(this), i);
                setSliderElementsSize($(this), i, false);
            });

            if (slider.hasClass('mkd-full-screen')) {

                setSliderFullHeight(slider);

                $(window).resize(function () {
                    setSliderElementsResponsiveCoeffeicients(responsiveBreakpointSet, coefficientsGraphicArray, coefficientsTitleArray, coefficientsSubtitleArray, coefficientsTextArray, coefficientsButtonArray);
                    setSliderFullHeight(slider);
                    slider.find('.item').each(function (i) {
                        setSliderElementsSize($(this), i, false);
                    });
                });

            } else if (slider.hasClass('mkd-responsive-height')) {

                var defaultHeight = slider.data('height');
                setSliderHeight(slider, defaultHeight, responsiveBreakpointSet, false);

                $(window).resize(function () {
                    setSliderElementsResponsiveCoeffeicients(responsiveBreakpointSet, coefficientsGraphicArray, coefficientsTitleArray, coefficientsSubtitleArray, coefficientsTextArray, coefficientsButtonArray);
                    setSliderHeight(slider, defaultHeight, responsiveBreakpointSet, false);
                    slider.find('.item').each(function (i) {
                        setSliderElementsSize($(this), i, false);
                    });
                });

            } else {
                var defaultHeight = slider.data('height');

                slider.find('.mkd-slider-preloader').css({'height': (slider.height()) + 'px'});
                slider.find('.mkd-slider-preloader .mkd-ajax-loader').css({'display': 'block'});

                mkd.windowWidth < 1000 ? setSliderHeight(slider, defaultHeight, responsiveBreakpointSet, false) : setSliderHeight(slider, defaultHeight, responsiveBreakpointSet, true);

                $(window).resize(function () {
                    setSliderElementsResponsiveCoeffeicients(responsiveBreakpointSet, coefficientsGraphicArray, coefficientsTitleArray, coefficientsSubtitleArray, coefficientsTextArray, coefficientsButtonArray);
                    if (mkd.windowWidth < 1000) {
                        setSliderHeight(slider, defaultHeight, responsiveBreakpointSet, false);
                        slider.find('.item').each(function (i) {
                            setSliderElementsSize($(this), i, false);
                        });
                    } else {
                        setSliderHeight(slider, defaultHeight, responsiveBreakpointSet, true);
                        slider.find('.item').each(function (i) {
                            setSliderElementsSize($(this), i, true);
                        });
                    }
                });
            }
        };

        /**
         * Set prev/next numbers on navigation arrows
         * @param slider, current slider
         * @param currentItem, current slide item index
         * @param totalItemCount, total number of slide items
         */
        var setPrevNextNumbers = function (slider, currentItem, totalItemCount) {
            if (currentItem == 1) {
                slider.find('.left.carousel-control .prev').html(totalItemCount);
                slider.find('.right.carousel-control .next').html(currentItem + 1);
            } else if (currentItem == totalItemCount) {
                slider.find('.left.carousel-control .prev').html(currentItem - 1);
                slider.find('.right.carousel-control .next').html(1);
            } else {
                slider.find('.left.carousel-control .prev').html(currentItem - 1);
                slider.find('.right.carousel-control .next').html(currentItem + 1);
            }
        };

        /**
         * Set video background size
         * @param slider, current slider
         */
        var initVideoBackgroundSize = function (slider) {
            var min_w = 1500; // minimum video width allowed
            var video_width_original = 1920;  // original video dimensions
            var video_height_original = 1080;
            var vid_ratio = 1920 / 1080;

            slider.find('.item .mkd-video .mkd-video-wrap').each(function () {

                var slideWidth = mkd.windowWidth;
                var slideHeight = $(this).closest('.carousel').height();

                $(this).width(slideWidth);

                min_w = vid_ratio * (slideHeight + 20);
                $(this).height(slideHeight);

                var scale_h = slideWidth / video_width_original;
                var scale_v = (slideHeight - mkdGlobalVars.vars.mkdMenuAreaHeight) / video_height_original;
                var scale = scale_v;
                if (scale_h > scale_v)
                    scale = scale_h;
                if (scale * video_width_original < min_w) {
                    scale = min_w / video_width_original;
                }

                $(this).find('video, .mejs-overlay, .mejs-poster').width(Math.ceil(scale * video_width_original + 2));
                $(this).find('video, .mejs-overlay, .mejs-poster').height(Math.ceil(scale * video_height_original + 2));
                $(this).scrollLeft(($(this).find('video').width() - slideWidth) / 2);
                $(this).find('.mejs-overlay, .mejs-poster').scrollTop(($(this).find('video').height() - slideHeight) / 2);
                $(this).scrollTop(($(this).find('video').height() - slideHeight) / 2);
            });
        };

        /**
         * Init video background
         * @param slider, current slider
         */
        var initVideoBackground = function (slider) {
            $('.item .mkd-video-wrap .video').mediaelementplayer({
                enableKeyboard: false,
                iPadUseNativeControls: false,
                pauseOtherPlayers: false,
                // force iPhone's native controls
                iPhoneUseNativeControls: false,
                // force Android's native controls
                AndroidUseNativeControls: false
            });

            $(window).resize(function () {
                initVideoBackgroundSize(slider);
            });

            //mobile check
            if (navigator.userAgent.match(/(Android|iPod|iPhone|iPad|IEMobile|Opera Mini)/)) {
                $('.mkd-slider .mkd-mobile-video-image').show();
                $('.mkd-slider .mkd-video-wrap').remove();
            }
        };

        /**
         * initiate slider
         * @param slider, current slider
         * @param currentItem, current slide item index
         * @param totalItemCount, total number of slide items
         * @param slideAnimationTimeout, timeout for slide change
         */
        var initiateSlider = function (slider, totalItemCount, slideAnimationTimeout) {

            //set active class on first item
            slider.find('.carousel-inner .item:first-child').addClass('active');
            //check for header style
            mkdCheckSliderForHeaderStyle($('.carousel .active'), slider.hasClass('mkd-header-effect'));
            // setting numbers on carousel controls
            if (slider.hasClass('mkd-slider-numbers')) {
                setPrevNextNumbers(slider, 1, totalItemCount);
            }
            // set video background if there is video slide
            if (slider.find('.item video').length) {
                initVideoBackgroundSize(slider);
                initVideoBackground(slider);
            }

            //init slider
            if (slider.hasClass('mkd-auto-start')) {
                slider.carousel({
                    interval: slideAnimationTimeout,
                    pause: false
                });

                //pause slider when hover slider button
                slider.find('.slide_buttons_holder .qbutton')
                    .mouseenter(function () {
                        slider.carousel('pause');
                    })
                    .mouseleave(function () {
                        slider.carousel('cycle');
                    });
            } else {
                slider.carousel({
                    interval: 0,
                    pause: false
                });
            }


            //initiate image animation
            if ($('.carousel-inner .item:first-child').hasClass('mkd-animate-image') && mkd.windowWidth > 1000) {
                slider.find('.carousel-inner .item.mkd-animate-image:first-child .mkd-image').transformAnimate({
                    transform: "matrix(" + matrixArray[$('.carousel-inner .item:first-child').data('mkd_animate_image')] + ")",
                    duration: 30000
                });
            }
        };

        return {
            init: function () {
                if (sliders.length) {
                    sliders.each(function () {
                        var $this = $(this);
                        var src;
                        var slideAnimationTimeout = $this.data('slide_animation_timeout');
                        var totalItemCount = $this.find('.item').length;
                        if ($this.data('mkd_responsive_breakpoints')) {
                            if ($this.data('mkd_responsive_breakpoints') == 'set2') {
                                responsiveBreakpointSet = [1600, 1300, 1000, 768, 567, 320];
                            }
                        }
                        coefficientsGraphicArray = $this.data('mkd_responsive_graphic_coefficients').split(',');
                        coefficientsTitleArray = $this.data('mkd_responsive_title_coefficients').split(',');
                        coefficientsSubtitleArray = $this.data('mkd_responsive_subtitle_coefficients').split(',');
                        coefficientsTextArray = $this.data('mkd_responsive_text_coefficients').split(',');
                        coefficientsButtonArray = $this.data('mkd_responsive_button_coefficients').split(',');

                        setSliderElementsResponsiveCoeffeicients(responsiveBreakpointSet, coefficientsGraphicArray, coefficientsTitleArray, coefficientsSubtitleArray, coefficientsTextArray, coefficientsButtonArray);

                        setHeights($this);

                        /*** wait until first video or image is loaded and than initiate slider - start ***/
                        var backImg;

                        if (mkd.htmlEl.hasClass('touch')) {
                            if ($this.find('.item:first-child .mkd-mobile-video-image').length > 0) {
                                src = imageRegex.exec($this.find('.item:first-child .mkd-mobile-video-image').attr('style'));
                            } else {
                                src = imageRegex.exec($this.find('.item:first-child .mkd-image').attr('style'));
                            }
                            if (src) {
                                backImg = new Image();
                                backImg.src = src[1];
                                $(backImg).load(function () {
                                    $('.mkd-slider-preloader').fadeOut(500);
                                    initiateSlider($this, totalItemCount, slideAnimationTimeout);
                                });
                            }
                        } else {
                            if ($this.find('.item:first-child video').length > 0) {
                                $this.find('.item:first-child video').get(0).addEventListener('loadeddata', function () {
                                    $('.mkd-slider-preloader').fadeOut(500);
                                    initiateSlider($this, totalItemCount, slideAnimationTimeout);
                                });
                            } else {
                                src = imageRegex.exec($this.find('.item:first-child .mkd-image').attr('style'));
                                if (src) {
                                    backImg = new Image();
                                    backImg.src = src[1];
                                    $(backImg).load(function () {
                                        $('.mkd-slider-preloader').fadeOut(500);
                                        initiateSlider($this, totalItemCount, slideAnimationTimeout);
                                    });
                                }
                            }
                        }
                        /*** wait until first video or image is loaded and than initiate slider - end ***/

                        /* before slide transition - start */
                        $this.on('slide.bs.carousel', function () {
                            $this.addClass('mkd-in-progress');
                            $this.find('.active .mkd-slider-content-outer').fadeTo(250, 0);
                        });
                        /* before slide transition - end */

                        /* after slide transition - start */
                        $this.on('slid.bs.carousel', function () {
                            $this.removeClass('mkd-in-progress');
                            $this.find('.active .mkd-slider-content-outer').fadeTo(0, 1);

                            // setting numbers on carousel controls
                            if ($this.hasClass('mkd-slider-numbers')) {
                                var currentItem = $('.item').index($('.item.active')[0]) + 1;
                                setPrevNextNumbers($this, currentItem, totalItemCount);
                            }

                            // initiate image animation on active slide and reset all others
                            $('.item.mkd-animate-image .mkd-image').stop().css({
                                'transform': '',
                                '-webkit-transform': ''
                            });
                            if ($('.item.active').hasClass('mkd-animate-image') && mkd.windowWidth > 1000) {
                                $('.item.mkd-animate-image.active .mkd-image').transformAnimate({
                                    transform: "matrix(" + matrixArray[$('.item.mkd-animate-image.active').data('mkd_animate_image')] + ")",
                                    duration: 30000
                                });
                            }
                        });
                        /* after slide transition - end */

                        /* swipe functionality - start */
                        $this.swipe({
                            swipeLeft: function () {
                                $this.carousel('next');
                            },
                            swipeRight: function () {
                                $this.carousel('prev');
                            },
                            threshold: 20
                        });
                        /* swipe functionality - end */

                    });

                    //adding parallax functionality on slider
                    if ($('.no-touch .carousel').length) {
                        var skrollr_slider = skrollr.init({
                            smoothScrolling: false,
                            forceHeight: false
                        });
                        skrollr_slider.refresh();
                    }

                    $(window).scroll(function () {
                        //set control class for slider in order to change header style
                        if ($('.mkd-slider .carousel').height() < mkd.scroll) {
                            $('.mkd-slider .carousel').addClass('mkd-disable-slider-header-style-changing');
                        } else {
                            $('.mkd-slider .carousel').removeClass('mkd-disable-slider-header-style-changing');
                            mkdCheckSliderForHeaderStyle($('.mkd-slider .carousel .active'), $('.mkd-slider .carousel').hasClass('mkd-header-effect'));
                        }

                        //hide slider when it is out of viewport
                        if ($('.mkd-slider .carousel').hasClass('mkd-full-screen') && mkd.scroll > mkd.windowHeight && mkd.windowWidth > 1000) {
                            $('.mkd-slider .carousel').find('.carousel-inner, .carousel-indicators').hide();
                        } else if (!$('.mkd-slider .carousel').hasClass('mkd-full-screen') && mkd.scroll > $('.mkd-slider .carousel').height() && mkd.windowWidth > 1000) {
                            $('.mkd-slider .carousel').find('.carousel-inner, .carousel-indicators').hide();
                        } else {
                            $('.mkd-slider .carousel').find('.carousel-inner, .carousel-indicators').show();
                        }
                    });
                }
            }
        };
    };

    /**
     * Check if slide effect on header style changing
     * @param slide, current slide
     * @param headerEffect, flag if slide
     */

    function mkdCheckSliderForHeaderStyle(slide, headerEffect) {

        if ($('.mkd-slider .carousel').not('.mkd-disable-slider-header-style-changing').length > 0) {

            var slideHeaderStyle = "";
            if (slide.hasClass('light')) {
                slideHeaderStyle = 'mkd-light-header';
            }
            if (slide.hasClass('dark')) {
                slideHeaderStyle = 'mkd-dark-header';
            }

            if (slideHeaderStyle !== "") {
                if (headerEffect) {
                    mkd.body.removeClass('mkd-dark-header mkd-light-header').addClass(slideHeaderStyle);
                }
            } else {
                if (headerEffect) {
                    mkd.body.removeClass('mkd-dark-header mkd-light-header').addClass(mkd.defaultHeaderStyle);
                }

            }
        }
    }

    function mkdInfoBox() {
        var infoBoxes = $('.mkd-info-box-holder');

        var getBottomHeight = function (bottomHolder) {
            if (bottomHolder.length) {
                return bottomHolder.height();
            }

            return false;
        };

        var infoBoxesHeight = function () {
            if (infoBoxes.length) {
                var maxHeight = 0;
                var heightestBox;

                infoBoxes.each(function () {
                    var bottomHolder = $(this).find('.mkd-ib-bottom-holder');
                    var topHolder = $(this).find('.mkd-ib-top-holder');

                    var currentHeight = getBottomHeight(bottomHolder) + topHolder.height();

                    maxHeight = Math.max(maxHeight, currentHeight);

                    if (maxHeight <= currentHeight) {
                        heightestBox = $(this);
                        maxHeight = currentHeight;
                    }
                });

                infoBoxes.height(maxHeight);
            }
        };

        var initHover = function (infoBox) {
            var timeline = new TimelineLite({paused: true}),
                topHolder = infoBox.find('.mkd-ib-top-holder'),
                bottomHolder = infoBox.find('.mkd-ib-bottom-holder'),
                bottomHeight = getBottomHeight(bottomHolder);

            timeline.to(topHolder, 0.6, {y: -(bottomHeight / 2), ease: Back.easeInOut.config(2)});
            timeline.to(bottomHolder, 0.4, {y: -(bottomHeight / 2), opacity: 1, ease: Back.easeOut}, '-=0.3');

            infoBox.hover(function () {
                timeline.restart();
            }, function () {
                timeline.reverse();
            });
        };

        if (infoBoxes.length) {
            infoBoxesHeight();

            $(mkd.window).resize(function () {
                infoBoxesHeight();
            });

            infoBoxes.each(function () {
                var thisInfoBox = $(this);
                initHover(thisInfoBox);

                $(mkd.window).resize(function () {
                    initHover(thisInfoBox);
                });
            });
        }
    }

    function mkdProcess() {
        var processes = $('.mkd-process-holder');

        var processAnimation = function (process) {
            if (!mkd.body.hasClass('mkd-no-animations-on-touch')) {
                var items = process.find('.mkd-process-item-holder');
                var background = process.find('.mkd-process-bg-holder');

                process.appear(function () {
                    $(this).addClass('appeared');

                    process.find(".mkd-process-item-holder").each(function (i) {
                        var thisProcess = $(this);

                        setTimeout(function () {
                            thisProcess.addClass("item-appeared");
                        }, i * 250);
                    });

                }, {accX: 0, accY: mkdGlobalVars.vars.mkdElementAppearAmount});
            }
        };

        return {
            init: function () {
                if (processes.length) {
                    processes.each(function () {
                        processAnimation($(this));
                    });
                }
            }
        };
    }

    function mkdComparisonPricingTables() {
        var pricingTablesHolder = $('.mkd-comparision-pricing-tables-holder');

        var alterPricingTableColumn = function (holder) {
            var featuresHolder = holder.find('.mkd-cpt-features-item');
            var pricingTables = holder.find('.mkd-comparision-table-holder');

            if (pricingTables.length) {
                pricingTables.each(function () {
                    var currentPricingTable = $(this);
                    var pricingItems = currentPricingTable.find('.mkd-cpt-table-content li');

                    if (pricingItems.length) {
                        pricingItems.each(function (i) {
                            var pricingItemFeature = featuresHolder[i];
                            var pricingItem = this;
                            var pricingItemContent = pricingItem.innerHTML;

                            if (typeof pricingItemFeature !== 'undefined') {
                                pricingItem.innerHTML = '<span class="mkd-cpt-table-item-feature">' + $(pricingItemFeature).text() + ': </span>' + pricingItemContent;
                            }
                        });
                    }
                });
            }
        };

        return {
            init: function () {
                if (pricingTablesHolder.length) {
                    pricingTablesHolder.each(function () {
                        alterPricingTableColumn($(this));
                    });
                }
            }
        };
    }

    function mkdProgressBarVertical() {
        var progressBars = $('.mkd-vertical-progress-bar-holder');

        var animateProgressBar = function (progressBar) {

            progressBar.appear(function () {
                var barHolder = progressBar.find('.mkd-vpb-bar');
                var activeBar = progressBar.find('.mkd-vpb-active-bar');
                var percentage = barHolder.data('percent');

                activeBar.animate({
                    height: percentage + '%'
                }, 1500);

            }, {accX: 0, accY: mkdGlobalVars.vars.mkdElementAppearAmount});
        };

        var animatePercentageNumber = function (progressBar) {
            progressBar.appear(function () {
                var barHolder = progressBar.find('.mkd-vpb-bar');
                var percentage = barHolder.data('percent');
                var percentHolder = progressBar.find('.mkd-vpb-percent-number');

                percentHolder.countTo({
                    from: 0,
                    to: percentage,
                    speed: 1500,
                    refreshInterval: 50
                });
            });
        };

        return {
            init: function () {

                if (progressBars.length) {

                    progressBars.each(function () {
                        animateProgressBar($(this));
                        animatePercentageNumber($(this));
                    });
                }
            }
        };
    }

    function mkdIconProgressBar() {
        var progressBars = $('.mkd-icon-progress-bar');

        var animateActiveIcons = function (progressBar) {
            var timeouts = [];
            progressBar.appear(function () {
                var numberOfActive = parseInt(progressBar.data('number-of-active-icons'));
                var icons = progressBar.find('.mkd-ipb-icon');
                var customColor = progressBar.data('icon-active-color');

                if (typeof numberOfActive !== 'undefined') {

                    icons.each(function (i) {
                        if (i < numberOfActive) {
                            var time = (i + 1) * 150;
                            var currentIcon = $(this);

                            timeouts[i] = setTimeout(function () {
                                animateSingleIcon(currentIcon, customColor);
                                $(icons[i]).addClass('active');
                            }, time);
                        }
                    });
                }
            }, {accX: 0, accY: mkdGlobalVars.vars.mkdElementAppearAmount});
        };

        var animateSingleIcon = function (icon, customColor) {
            icon.addClass('mkd-ipb-active');

            if (typeof customColor !== 'undefined' && customColor !== '') {
                icon.find('.mkd-ipb-icon-elem').css('color', customColor);
            }
        };

        return {
            init: function () {
                if (progressBars.length) {
                    progressBars.each(function () {
                        animateActiveIcons($(this));
                    });
                }
            }
        };
    }

    function mkdResizeBlogSlider(slider) {
        if (slider !== undefined && slider.hasClass('masonry')) {
            var slides = slider.find('article');
            var sliderHeight = slider.find('.slick-track').height();

            if (slides.length) {
                slides.each(function () {
                    var thisSlide = $(this);
                    var setHeight = sliderHeight - $(this).find('.mkd-post-info').outerHeight();

                    if (thisSlide.hasClass('format-link') || thisSlide.hasClass('format-quote')) {
                        thisSlide.find('.mkd-post-text-inner').css('height', setHeight);
                    }
                    else {
                        thisSlide.find('.mkd-post-text-inner').css('height', setHeight - $(this).find('.mkd-post-image').outerHeight());
                    }
                });

                $(window).resize(function () {
                    if (this.resizeTO) clearTimeout(this.resizeTO);
                    this.resizeTO = setTimeout(function () {
                        $(this).trigger('resizeEnd');
                    }, 500);
                });

                $(window).bind('resizeEnd', function () {
                    slides.each(function () {
                        $(this).find('.mkd-post-text-inner').css('height', 'auto');
                    });

                    sliderHeight = slider.find('.slick-track').height();

                    slides.each(function () {
                        var thisSlide = $(this);
                        var setHeight = sliderHeight - $(this).find('.mkd-post-info').outerHeight();

                        if (thisSlide.hasClass('format-link') || thisSlide.hasClass('format-quote')) {
                            thisSlide.find('.mkd-post-text-inner').css('height', setHeight);
                        }
                        else {
                            thisSlide.find('.mkd-post-text-inner').css('height', setHeight - $(this).find('.mkd-post-image').outerHeight());
                        }
                    });

                });
            }
        }
    }

    function mkdBlogSlider() {
        var blogSliders = $('.mkd-blog-slider-holder');

        if (blogSliders.length) {
            blogSliders.each(function () {
                var thisSlider = $(this);

                var dots;
                if (dots = typeof thisSlider.data('dots') !== 'undefined' && thisSlider.data('dots') === 'yes') {
                    thisSlider.addClass('slick-dots');
                }

                thisSlider.on('init', function () {

                    // change default opacity on init
                    thisSlider.waitForImages(function () {
                        setTimeout(mkdResizeBlogSlider(thisSlider), 400);
                    });
                    thisSlider.addClass('appeared');
                }).slick({
                    dots: dots,
                    arrows: false,
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    infinite: false,
                    responsive: [
                        {
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 2
                            }
                        },
                        {
                            breakpoint: 769,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        }
                    ]
                });
            });
        }
    }

    function mkdBlogLatestPosts() {
        var blogSliders = $('.mkd-latest-posts-widget .mkd-blog-list-holder.mkd-simple');

        if (blogSliders.length) {
            blogSliders.each(function () {
                var visibleSlides = $(this).data('number_of_visible_posts');

                var thisSlider = $(this).find('.mkd-blog-list');

                thisSlider.on('init', function () {

                    // change default opacity on init
                    thisSlider.addClass('appeared');
                }).slick({
                    dots: false,
                    arrows: true,
                    vertical: true,
                    slidesToShow: visibleSlides,
                    slidesToScroll: visibleSlides,
                    verticalSwiping: true,
                    infinite: false
                });
            });
        }
    }

    function mkdTeamSlider() {
        var teamSliders = $('.mkd-team-slider');

        if (teamSliders.length) {
            teamSliders.each(function () {


                var thisSlider = $(this);

                var dots = (thisSlider.data('dots') == 'yes');

                if (dots) {
                    thisSlider.addClass('slick-dots');
                }

                var numberOfItems = thisSlider.data('number_of_items');

                thisSlider.on('init', function () {

                    // change default opacity on init
                    thisSlider.addClass('appeared');
                }).slick({
                    infinite: true,
                    autoplay: true,
                    autoplaySpeed: 3000,
                    cssEase: 'cubic-bezier(.38,.76,0,.87)',
                    dots: dots,
                    arrows: false,
                    slidesToScroll: 1,
                    slidesToShow: numberOfItems,
                    responsive: [
                        {
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: 3,
                                slidesToScroll: 3
                            }
                        },
                        {
                            breakpoint: 769,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 2
                            }
                        },
                        {
                            breakpoint: 601,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        }
                    ]

                });
            });
        }
    }

    function mkdCardSlider() {
        var cardSliders = $('.mkd-card-slider');

        if (cardSliders.length) {
            cardSliders.each(function () {


                var thisSlider = $(this);

                var dots = (thisSlider.data('dots') == 'yes');

                if (dots) {
                    thisSlider.addClass('slick-dots');
                }

                var numberOfItems = thisSlider.data('number_of_items');

                thisSlider.on('init', function () {

                    // change default opacity on init
                    thisSlider.addClass('appeared');
                }).slick({
                    dots: dots,
                    arrows: false,
                    slidesToScroll: numberOfItems,
                    slidesToShow: numberOfItems,
                    responsive: [
                        {
                            breakpoint: 1281,
                            settings: {
                                slidesToShow: 3,
                                slidesToScroll: 3
                            }
                        },
                        {
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 2
                            }
                        },
                        {
                            breakpoint: 769,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        }
                    ]

                });
            });
        }
    }

    /**
     * Device Slider shortcode
     */
    function mkdDeviceSlider() {

        var deviceSliders = $('.mkd-device-slider');

        if (deviceSliders.length) {
            deviceSliders.each(function () {

                var thisSlider = $(this);

                thisSlider.on('init', function () {

                    // change default opacity on init
                    thisSlider.addClass('appeared');
                }).slick({
                    autoplay: true,
                    autoplaySpeed: 3000
                });
            });
        }
    }

    function mkdInitMobileSlider() {

        var mobileSliders = $('.mkd-mobile-slider');

        if (mobileSliders.length) {
            mobileSliders.each(function () {

                var thisSlider = $(this);

                thisSlider.on('init', function () {

                    // change default opacity on init
                    thisSlider.addClass('appeared');
                }).slick({
                    infinite: true,
                    centerMode: true,
                    centerPadding: '20%',
                    autoplay: true,
                    autoplaySpeed: 3000,
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    swipeToSlide: true,
                    touchThreshold: 30,
                    speed: 400,
                    fade: false,
                    cssEase: 'cubic-bezier(.38,.76,0,.87)',
                    responsive: [
                        {
                            breakpoint: 1025,
                            settings: {
                                centerMode: true,
                                centerPadding: '12%',
                                autoplay: true,
                                autoplaySpeed: 3000,
                                slidesToShow: 3,
                                slidesToScroll: 1,
                                swipeToSlide: true,
                                touchThreshold: 30
                            }
                        },
                        {
                            breakpoint: 769,
                            settings: {
                                centerMode: true,
                                centerPadding: '25%',
                                autoplay: true,
                                autoplaySpeed: 3000,
                                slidesToShow: 1,
                                slidesToScroll: 1,
                                swipeToSlide: true,
                                touchThreshold: 30
                            }
                        },
                        {
                            breakpoint: 481,
                            settings: {
                                centerMode: true,
                                centerPadding: '22%',
                                autoplay: true,
                                autoplaySpeed: 3000,
                                slidesToShow: 1,
                                slidesToScroll: 1,
                                swipeToSlide: true,
                                touchThreshold: 30
                            }
                        }
                    ]
                });
            });
        }

    }

    function mkdCenteredSlider() {
        var centeredSliders = $('.mkd-centered-slider');

        var centerPadding = mkd.windowWidth / 3 + 'px';

        if (centeredSliders.length) {
            centeredSliders.each(function () {
                var thisSlider = $(this);

                if (thisSlider.width() >= mkd.windowWidth) {
                    thisSlider.on('init', function () {
                        // change default opacity on init
                        $(this).addClass('full-width');
                        thisSlider.css({'opacity': '1'});
                    }).slick({
                        infinite: true,
                        autoplay: true,
                        autoplaySpeed: 3000,
                        dots: false,
                        arrows: false,
                        centerMode: true,
                        centerPadding: centerPadding,
                        slidesToShow: 1,
                        speed: 800,
                        fade: false,
                        cssEase: 'cubic-bezier(.38,.76,0,.87)',
                        responsive: [
                            {
                                breakpoint: 1025,
                                settings: {
                                    centerMode: false,
                                    slidesToShow: 1,
                                    autoplay: true
                                }
                            },
                            {
                                breakpoint: 769,
                                settings: {
                                    centerMode: false,
                                    slidesToShow: 1,
                                    autoplay: true
                                }
                            },
                            {
                                breakpoint: 481,
                                settings: {
                                    centerMode: false,
                                    slidesToShow: 1,
                                    autoplay: true
                                }
                            }]
                    });

                    thisSlider.find('.slick-center').css({"transform": "scale(1.2)", "z-index": "11"});

                    thisSlider.on('beforeChange', function () {
                        $(this).find('.slick-center').css({"transform": "scale(1)", "z-index": "10"});
                    });

                    thisSlider.on('afterChange', function () {
                        $(this).find('.slick-center').css({"transform": "scale(1.2)", "z-index": "11"});
                    });
                }
                else {
                    thisSlider.on('init', function () {
                        // change default opacity on init
                        thisSlider.css({'opacity': '1'});
                    }).slick({
                        infinite: true,
                        autoplay: true,
                        autoplaySpeed: 3000,
                        dots: false,
                        arrows: false,
                        centerMode: false,
                        centerPadding: centerPadding,
                        slidesToShow: 1,
                        speed: 800,
                        fade: false,
                        cssEase: 'cubic-bezier(.38,.76,0,.87)',
                    });
                }
            });
        }
    }


    function horizontalTimeline() {
        var timelines = $('.mkd-horizontal-timeline'),
            eventsMinDistance;

        function initTimeline(timelines) {
            timelines.each(function () {
                var timeline = $(this),
                    timelineComponents = {};

                eventsMinDistance = timeline.data('distance');

                //cache timeline components
                timelineComponents['timelineWrapper'] = timeline.find('.mkd-horizontal-timeline-events-wrapper');
                timelineComponents['eventsWrapper'] = timelineComponents['timelineWrapper'].children('.mkd-horizontal-timeline-events');
                timelineComponents['fillingLine'] = timelineComponents['eventsWrapper'].children('.mkd-horizontal-timeline-filling-line');
                timelineComponents['timelineEvents'] = timelineComponents['eventsWrapper'].find('a');
                timelineComponents['timelineDates'] = parseDate(timelineComponents['timelineEvents']);
                timelineComponents['eventsMinLapse'] = minLapse(timelineComponents['timelineDates']);
                timelineComponents['timelineNavigation'] = timeline.find('.mkd-timeline-navigation');
                timelineComponents['eventsContent'] = timeline.children('.mkd-horizontal-timeline-events-content');

                //select initial event
                timelineComponents['timelineEvents'].first().addClass('selected');
                timelineComponents['eventsContent'].find('li').first().addClass('selected');

                //assign a left postion to the single events along the timeline
                setDatePosition(timelineComponents, eventsMinDistance);
                //assign a width to the timeline
                var timelineTotWidth = setTimelineWidth(timelineComponents, eventsMinDistance);
                //the timeline has been initialize - show it
                timeline.addClass('loaded');

                //assign a left position to events and set timeline width on resize
                $(window).resize(function () {
                    setDatePosition(timelineComponents, eventsMinDistance);
                    setTimelineWidth(timelineComponents, eventsMinDistance);
                });

                //detect click on the next arrow
                timelineComponents['timelineNavigation'].on('click', '.next', function (event) {
                    event.preventDefault();
                    updateSlide(timelineComponents, timelineTotWidth, 'next');
                });
                //detect click on the prev arrow
                timelineComponents['timelineNavigation'].on('click', '.prev', function (event) {
                    event.preventDefault();
                    updateSlide(timelineComponents, timelineTotWidth, 'prev');
                });
                //detect click on the a single event - show new event content
                timelineComponents['eventsWrapper'].on('click', 'a', function (event) {
                    event.preventDefault();
                    timelineComponents['timelineEvents'].removeClass('selected');
                    $(this).addClass('selected');
                    updateOlderEvents($(this));
                    updateFilling($(this), timelineComponents['fillingLine'], timelineTotWidth);
                    updateVisibleContent($(this), timelineComponents['eventsContent']);
                });

                //on swipe, show next/prev event content
                timelineComponents['eventsContent'].on('swipeleft', function () {
                    showNewContent(timelineComponents, timelineTotWidth, 'next');
                });
                timelineComponents['eventsContent'].on('swiperight', function () {
                    showNewContent(timelineComponents, timelineTotWidth, 'prev');
                });

                //keyboard navigation
                $(document).keyup(function (event) {
                    if (event.which == '37' && elementInViewport(timeline.get(0))) {
                        showNewContent(timelineComponents, timelineTotWidth, 'prev');
                    } else if (event.which == '39' && elementInViewport(timeline.get(0))) {
                        showNewContent(timelineComponents, timelineTotWidth, 'next');
                    }
                });


            });
        }

        function updateSlide(timelineComponents, timelineTotWidth, string) {
            //retrieve translateX value of timelineComponents['eventsWrapper']
            var translateValue = getTranslateValue(timelineComponents['eventsWrapper']),
                wrapperWidth = Number(timelineComponents['timelineWrapper'].css('width').replace('px', ''));
            //translate the timeline to the left('next')/right('prev')
            (string == 'next') ?
                translateTimeline(timelineComponents, translateValue - wrapperWidth + eventsMinDistance, wrapperWidth - timelineTotWidth) :
                translateTimeline(timelineComponents, translateValue + wrapperWidth - eventsMinDistance);
        }

        function showNewContent(timelineComponents, timelineTotWidth, string) {
            //go from one event to the next/previous one
            var visibleContent = timelineComponents['eventsContent'].find('.selected'),
                newContent = ( string == 'next' ) ? visibleContent.next() : visibleContent.prev();

            if (newContent.length > 0) { //if there's a next/prev event - show it
                var selectedDate = timelineComponents['eventsWrapper'].find('.selected'),
                    newEvent = ( string == 'next' ) ? selectedDate.parent('li').next('li').children('a') : selectedDate.parent('li').prev('li').children('a');

                updateFilling(newEvent, timelineComponents['fillingLine'], timelineTotWidth);
                updateVisibleContent(newEvent, timelineComponents['eventsContent']);
                newEvent.addClass('selected');
                selectedDate.removeClass('selected');
                updateOlderEvents(newEvent);
                updateTimelinePosition(string, newEvent, timelineComponents);
            }
        }

        function updateTimelinePosition(string, event, timelineComponents) {
            //translate timeline to the left/right according to the position of the selected event
            var eventStyle = window.getComputedStyle(event.get(0), null),
                eventLeft = Number(eventStyle.getPropertyValue("left").replace('px', '')),
                timelineWidth = Number(timelineComponents['timelineWrapper'].css('width').replace('px', '')),
                timelineTotWidth = Number(timelineComponents['eventsWrapper'].css('width').replace('px', ''));
            var timelineTranslate = getTranslateValue(timelineComponents['eventsWrapper']);

            if ((string == 'next' && eventLeft > timelineWidth - timelineTranslate) || (string == 'prev' && eventLeft < -timelineTranslate)) {
                translateTimeline(timelineComponents, -eventLeft + timelineWidth / 2, timelineWidth - timelineTotWidth);
            }
        }

        function translateTimeline(timelineComponents, value, totWidth) {
            var eventsWrapper = timelineComponents['eventsWrapper'].get(0);
            value = (value > 0) ? 0 : value; //only negative translate value
            value = ( !(typeof totWidth === 'undefined') && value < totWidth ) ? totWidth : value; //do not translate more than timeline width
            setTransformValue(eventsWrapper, 'translateX', value + 'px');
            //update navigation arrows visibility
            (value === 0 ) ? timelineComponents['timelineNavigation'].find('.prev').addClass('inactive') : timelineComponents['timelineNavigation'].find('.prev').removeClass('inactive');
            (value == totWidth ) ? timelineComponents['timelineNavigation'].find('.next').addClass('inactive') : timelineComponents['timelineNavigation'].find('.next').removeClass('inactive');
        }

        function updateFilling(selectedEvent, filling, totWidth) {
            //change .mkd-horizontal-timeline-filling-line length according to the selected event
            var eventStyle = window.getComputedStyle(selectedEvent.get(0), null),
                eventLeft = eventStyle.getPropertyValue("left"),
                eventWidth = eventStyle.getPropertyValue("width");
            eventLeft = Number(eventLeft.replace('px', '')) + Number(eventWidth.replace('px', '')) / 2;
            var scaleValue = eventLeft / totWidth;
            setTransformValue(filling.get(0), 'scaleX', scaleValue);
        }

        function setDatePosition(timelineComponents, min) {
            var deviceWidthPercentage = 1;

            if (mkd.windowWidth < 600) {
                deviceWidthPercentage = 0.5;
            }

            for (var i = 0; i < timelineComponents['timelineDates'].length; i++) {
                var distance = daydiff(timelineComponents['timelineDates'][0], timelineComponents['timelineDates'][i]),
                    distanceNorm = Math.round(distance / timelineComponents['eventsMinLapse']) + 2;
                timelineComponents['timelineEvents'].eq(i).css('left', deviceWidthPercentage * distanceNorm * min + 'px');
            }
        }

        function setTimelineWidth(timelineComponents, width) {
            var deviceWidthPercentage = 1;

            if (mkd.windowWidth < 600) {
                deviceWidthPercentage = 0.5;
            }

            var timeSpan = daydiff(timelineComponents['timelineDates'][0], timelineComponents['timelineDates'][timelineComponents['timelineDates'].length - 1]),
                timeSpanNorm = timeSpan / timelineComponents['eventsMinLapse'],
                timeSpanNorm = Math.round(timeSpanNorm) + 4,
                totalWidth = timeSpanNorm * width * deviceWidthPercentage;
            timelineComponents['eventsWrapper'].css('width', totalWidth + 'px');
            updateFilling(timelineComponents['eventsWrapper'].find('a.selected'), timelineComponents['fillingLine'], totalWidth);
            updateTimelinePosition('next', timelineComponents['eventsWrapper'].find('a.selected'), timelineComponents);

            return totalWidth;
        }

        function updateVisibleContent(event, eventsContent) {
            var eventDate = event.data('date'),
                visibleContent = eventsContent.find('.selected'),
                selectedContent = eventsContent.find('[data-date="' + eventDate + '"]'),
                selectedContentHeight = selectedContent.height();

            if (selectedContent.index() > visibleContent.index()) {
                var classEnetering = 'selected enter-right',
                    classLeaving = 'leave-left';
            } else {
                var classEnetering = 'selected enter-left',
                    classLeaving = 'leave-right';
            }

            selectedContent.attr('class', classEnetering);
            visibleContent.attr('class', classLeaving).one('webkitAnimationEnd oanimationend msAnimationEnd animationend', function () {
                visibleContent.removeClass('leave-right leave-left');
                selectedContent.removeClass('enter-left enter-right');
            });
            eventsContent.css('height', selectedContentHeight + 'px');
        }

        function updateOlderEvents(event) {
            event.parent('li').prevAll('li').children('a').addClass('older-event').end().end().nextAll('li').children('a').removeClass('older-event');
        }

        function getTranslateValue(timeline) {
            var timelineStyle = window.getComputedStyle(timeline.get(0), null),
                timelineTranslate = timelineStyle.getPropertyValue("-webkit-transform") ||
                    timelineStyle.getPropertyValue("-moz-transform") ||
                    timelineStyle.getPropertyValue("-ms-transform") ||
                    timelineStyle.getPropertyValue("-o-transform") ||
                    timelineStyle.getPropertyValue("transform");

            if (timelineTranslate.indexOf('(') >= 0) {
                var timelineTranslate = timelineTranslate.split('(')[1];
                timelineTranslate = timelineTranslate.split(')')[0];
                timelineTranslate = timelineTranslate.split(',');
                var translateValue = timelineTranslate[4];
            } else {
                var translateValue = 0;
            }

            return Number(translateValue);
        }

        function setTransformValue(element, property, value) {
            element.style["-webkit-transform"] = property + "(" + value + ")";
            element.style["-moz-transform"] = property + "(" + value + ")";
            element.style["-ms-transform"] = property + "(" + value + ")";
            element.style["-o-transform"] = property + "(" + value + ")";
            element.style["transform"] = property + "(" + value + ")";
        }

        //based on http://stackoverflow.com/questions/542938/how-do-i-get-the-number-of-days-between-two-dates-in-javascript
        function parseDate(events) {
            var dateArrays = [];
            events.each(function () {
                var singleDate = $(this),
                    dateCompStr = new String(singleDate.data('date')),
                    dateComp = dateCompStr.split('T');

                if (dateComp.length > 1) { //both DD/MM/YEAR and time are provided
                    var dayComp = dateComp[0].split('/'),
                        timeComp = dateComp[1].split(':');
                } else if (dateComp[0].indexOf(':') >= 0) { //only time is provide
                    var dayComp = ["2000", "0", "0"],
                        timeComp = dateComp[0].split(':');
                } else { //only DD/MM/YEAR
                    var dayComp = dateComp[0].split('/'),
                        timeComp = ["0", "0"];
                }
                var newDate = new Date(dayComp[2], dayComp[1] - 1, dayComp[0], timeComp[0], timeComp[1]);
                dateArrays.push(newDate);
            });
            return dateArrays;
        }

        function daydiff(first, second) {
            return Math.round((second - first));
        }

        function minLapse(dates) {
            //determine the minimum distance among events
            var dateDistances = [];
            for (var i = 1; i < dates.length; i++) {
                var distance = daydiff(dates[i - 1], dates[i]);
                dateDistances.push(distance);
            }
            return Math.min.apply(null, dateDistances);
        }

        /*
         How to tell if a DOM element is visible in the current viewport?
         http://stackoverflow.com/questions/123999/how-to-tell-if-a-dom-element-is-visible-in-the-current-viewport
         */
        function elementInViewport(el) {
            var top = el.offsetTop;
            var left = el.offsetLeft;
            var width = el.offsetWidth;
            var height = el.offsetHeight;

            while (el.offsetParent) {
                el = el.offsetParent;
                top += el.offsetTop;
                left += el.offsetLeft;
            }

            return (
                top < (window.pageYOffset + window.innerHeight) &&
                left < (window.pageXOffset + window.innerWidth) &&
                (top + height) > window.pageYOffset &&
                (left + width) > window.pageXOffset
            );
        }

        function checkMQ() {
            //check if mobile or desktop device
            return window.getComputedStyle(document.querySelector('.mkd-horizontal-timeline'), '::before').getPropertyValue('content').replace(/'/g, "").replace(/"/g, "");
        }

        return {
            init: function () {
                (timelines.length > 0) && initTimeline(timelines);
            }
        }
    }

    function emptySpaceResponsive() {
        var emptySpaces = $('.vc_empty_space');

        var sizes = {
            'large_laptop': 1559,
            'laptop': 1279,
            'tablet_landscape': 1023,
            'tablet_portrait': 767,
            'phone_landscape': 599,
            'phone_portrait': 479
        }

        var sizeValues = function () {
            var values = [];
            for (var size in sizes) {
                values.push(sizes[size]);
            }
            ;

            return values;
        }();

        var getHeights = function (emptySpace) {
            var heights = {};

            for (var size in sizes) {
                var dataValue = emptySpace.data(size);
                if (typeof dataValue !== 'undefined' && dataValue !== '') {
                    heights[size] = dataValue;
                }
            }

            return heights;
        };

        var usedSizes = function (emptySpace) {
            var usedSizes = [];

            for (var size in sizes) {
                var dataValue = emptySpace.data(size);
                if (typeof dataValue !== 'undefined' && dataValue !== '') {
                    usedSizes.push(sizes[size]);
                }
            }

            return usedSizes;
        };

        var resizeEmptySpace = function (heights, emptySpace) {
            if (typeof heights !== 'undefined') {
                var originalHeight = emptySpace.data('original-height');
                var sizeValues = usedSizes(emptySpace);
                var heightestSize = Math.max.apply(null, sizeValues);

                for (var size in sizes) {
                    if (mkd.windowWidth <= sizes[size]) {
                        emptySpace.height(heights[size]);
                    } else if (mkd.windowWidth > heightestSize) {
                        emptySpace.height(originalHeight);
                    }
                }
            }
        };

        return {
            init: function () {
                if (emptySpaces.length) {
                    emptySpaces.each(function () {
                        var heights = getHeights($(this));

                        resizeEmptySpace(heights, $(this));

                        var thisEmptySpace = $(this);

                        $(window).resize(function () {
                            resizeEmptySpace(heights, thisEmptySpace);
                        });
                    });
                }
            }
        }
    }

    /*
     **	Vertical Split Slider
     */
    function mkdInitVerticalSplitSlider() {

        if (mkd.body.hasClass('mkd-vertical-split-screen-initialized')) {
            mkd.body.removeClass('mkd-vertical-split-screen-initialized');
            $.fn.multiscroll.destroy();
        }

        var defaultHeaderStyle = '';
        if (mkd.body.hasClass('mkd-light-header')) {
            defaultHeaderStyle = 'light';
        } else if (mkd.body.hasClass('mkd-dark-header')) {
            defaultHeaderStyle = 'dark';
        }

        if ($('.mkd-vertical-split-slider').length) {
            var slider = $('.mkd-vertical-split-slider');

            slider.height(mkd.windowHeight).animate({opacity: 1}, 300);
            slider.multiscroll({
                scrollingSpeed: 700,
                easing: 'easeInOutQuart',
                navigation: true,
                useAnchorsOnLoad: false,
                sectionSelector: '.mkd-vss-ms-section',
                leftSelector: '.mkd-vss-ms-left',
                rightSelector: '.mkd-vss-ms-right',
                afterRender: function () {
                    mkdCheckVerticalSplitSectionsForHeaderStyle($('.mkd-vss-ms-right .mkd-vss-ms-section:last-child').data('header-style'), defaultHeaderStyle);
                    mkd.body.addClass('mkd-vertical-split-screen-initialized');

                    var contactForm7 = $('div.wpcf7 > form');
                    if(contactForm7.length) {
                        contactForm7.each(function(){
                            var thisForm = $(this);

                            thisForm.find('.wpcf7-submit').off().on('click', function(e){
                                e.preventDefault();
                                wpcf7.submit(thisForm);
                            });
                        });
                    } // this function need to be initialized after initVerticalSplitSlide

                    //prepare html for smaller screens - start //
                    var verticalSplitSliderResponsive = $("<div class='mkd-vertical-split-slider-responsive' />");
                    slider.after(verticalSplitSliderResponsive);
                    var leftSide = $('.mkd-vertical-split-slider .mkd-vss-ms-left > div');
                    var rightSide = $('.mkd-vertical-split-slider .mkd-vss-ms-right > div');

                    for (var i = 0; i < leftSide.length; i++) {
                        verticalSplitSliderResponsive.append($(leftSide[i]).clone(true));
                        verticalSplitSliderResponsive.append($(rightSide[leftSide.length - 1 - i]).clone(true));
                    }

                    //prepare google maps clones
                    if ($('.mkd-vertical-split-slider-responsive .mkd-google-map').length) {
                        $('.mkd-vertical-split-slider-responsive .mkd-google-map').each(function () {
                            var map = $(this);
                            map.empty();
                            var num = Math.floor((Math.random() * 100000) + 1);
                            map.attr('id', 'mkd-map-' + num);
                            map.data('unique-id', num);
                        });
                    }

                    mkdButton().init();
                    mkdInitPortfolioListMasonry();
                    mkdInitPortfolioListPinterest();
                    mkdInitPortfolio();
                    mkdShowGoogleMap();
                    mkdInitProgressBars();
                },

                onLeave: function (index, nextIndex, direction) {

                    $("#multiscroll-nav").removeClass("direction-up direction-down").addClass("direction-" + direction);
                    mkdInitProgressBars();
                    mkdCheckVerticalSplitSectionsForHeaderStyle($($('.mkd-vss-ms-right .mkd-vss-ms-section')[$(".mkd-vss-ms-right .mkd-vss-ms-section").length - nextIndex]).data('header-style'), defaultHeaderStyle);
                }
            });

            if (mkd.windowWidth <= 1024) {
                $.fn.multiscroll.destroy();
            } else {
                $.fn.multiscroll.build();
            }

            $(window).resize(function () {
                if (mkd.windowWidth <= 1024) {
                    $.fn.multiscroll.destroy();
                } else {
                    $.fn.multiscroll.build();
                }

            });
        }
    }

    /*
     **	Check slides on load and slide change for header style changing
     */
    function mkdCheckVerticalSplitSectionsForHeaderStyle(section_header_style, default_header_style) {

        if (section_header_style != undefined && section_header_style != '') {
            mkd.body.removeClass('mkd-light-header mkd-dark-header').addClass('mkd-' + section_header_style + '-header');
        } else if (default_header_style != '') {
            mkd.body.removeClass('mkd-light-header mkd-dark-header').addClass('mkd-' + default_header_style + '-header');
        } else {
            mkd.body.removeClass('mkd-light-header mkd-dark-header');
        }
    }

    function mkdInitMiniTextSlider() {
        var sliders = $('.mkd-mini-text-slider');

        if (sliders.length) {
            sliders.each(function () {
                var mts = $(this).find('.mkd-mts-inner');
                mts.owlCarousel({
                    singleItem: true,
                    autoPlay: 4000,
                    navigation: true,
                    autoHeight: false,
                    pagination: false,
                    slideSpeed: 450,
                    stopOnHover: true,
                    transitionStyle: 'backSlide', //fade, fadeUp, backSlide, goDown
                    navigationText: [
                        '<span class="mkd-prev-icon"><span class="arrow_carrot-left"></span></span>',
                        '<span class="mkd-next-icon"><span class="arrow_carrot-right"></span></span>'
                    ],
                    afterInit: function () {
                        mts.css('visibility', 'visible');
                    }
                });
            });
        }
    }

    /**
     * Tab Slider
     */
    function mkdTabSlider() {
        var tabSliders = $('.mkd-tab-slider-container');

        if (tabSliders.length) {
            tabSliders.each(function () {
                $(this).flexslider({
                    slideshow: false,
                    animation: 'slide',
                    manualControls: '.mkd-tab-slider-nav .mkd-tab-slider-nav-item',
                    selector: '.mkd-tab-slider-container-inner li',
                    directionNav: false
                });
            });
        }
    }

    function mkdPlaylist() {
        var playlists = $('.mkd-playlist');

        if (playlists.length) {
            playlists.each(function () {

                var buttons = $(this).find('.mkd-playlist-control');
                var playListItems = $(this).find('.mkd-playlist-item');
                var audioTracks = playListItems.find('audio');

                buttons.each(function () {
                    var button = $(this);
                    var playListItem = button.parent();
                    var audioTrack = button.find('audio').get(0);
                    button.on('click', function () {
                        if (playListItem.hasClass('in-progress')) {
                            audioTrack.pause();
                            playListItem.addClass('paused').removeClass('in-progress');
                        } else if (playListItem.hasClass('paused')) {
                            audioTrack.play();
                            playListItem.addClass('playing in-progress').removeClass('paused')
                        } else {
                            audioTracks.each(function () {
                                this.pause();
                                this.currentTime = 0;
                            });
                            playListItems.removeClass('playing in-progress paused');
                            audioTrack.play();
                            playListItem.addClass('playing in-progress')
                        }
                    });

                    button.find('audio').bind("ended", function () {
                        playListItem.removeClass('playing in-progress');
                    });
                });
            });
        }
    }

    /*
     **	Init product list Masonry type
     */
    function mkdInitProductListMasonry() {
        var productList = $('.mkd-pl-holder.woocommerce.masonry');
        if (productList.length) {
            productList.each(function () {
                var thisProductList = $(this).children('.mkd-pl-outer');


                mkdResizeProductMasonry(thisProductList);
                mkdInitProductMasonry(thisProductList);

                $(window).resize(function () {
                    mkdResizeProductMasonry(thisProductList);
                    mkdInitProductMasonry(thisProductList);
                });
            });
        }
    }


    /*
     **	Init product list Lookbook Masonry type
     */
    function mkdInitProductListLookbookMasonry() {
        var productList = $('.mkd-pl-holder.woocommerce.lookbook-masonry');
        if (productList.length) {
            productList.each(function () {
                var thisProductList = $(this).children('.mkd-pl-outer');


                mkdResizeProductMasonry(thisProductList);
                mkdInitProductMasonry(thisProductList);

                $(window).resize(function () {
                    mkdResizeProductMasonry(thisProductList);
                    mkdInitProductMasonry(thisProductList);
                });
            });
        }
    }

    function mkdInitProductMasonry(container) {
        container.animate({opacity: 1});
        container.waitForImages(function () {
            container.isotope({
                itemSelector: '.mkd-pl-item',
                resizable: false,
                layoutMode: 'packery',
                packery: {
                    columnWidth: '.mkd-product-list-masonry-grid-sizer'
                }
            });
            container.addClass('mkd-appeared');
        });
    }

    function mkdResizeProductMasonry(container) {

        var size = container.find('.mkd-product-list-masonry-grid-sizer').width() * 1.25;

        var defaultMasonryItem = container.find('.servicemaster_mikado_square');
        var largeWidthMasonryItem = container.find('.servicemaster_mikado_large_width');
        var largeHeightMasonryItem = container.find('.servicemaster_mikado_large_height');
        var largeWidthHeightMasonryItem = container.find('.servicemaster_mikado_large_width_height');

        defaultMasonryItem.css('height', size);

        if (mkd.windowWidth > 600) {
            largeWidthHeightMasonryItem.css('height', Math.round(2 * size));
            largeHeightMasonryItem.css('height', Math.round(2 * size));
            largeWidthMasonryItem.css('height', size);
        } else {
            largeWidthHeightMasonryItem.css('height', size);
            largeHeightMasonryItem.css('height', size);
        }
    }

    /**
     * Cards Gallery shortcode
     */
    function mkdBundleAnimation(item, holder) {
        if (holder.hasClass('mkd-bundle-animation')) {
            var stop = false;
            var card_inner = item.find('.mkd-bundle-item');
            card_inner.css('top', card_inner.data('bundle-move-top'));

            var update = function () {
                var percentage = ((mkd.windowHeight - item[0].getBoundingClientRect().top) * 100) / (mkd.windowHeight / 2);
                if (!stop && item[0].getBoundingClientRect().top <= mkd.windowHeight && item[0].getBoundingClientRect().top >= mkd.windowHeight / 2) {
                    card_inner.css('top', (100 - percentage) / 100 * card_inner.data('bundle-move-top'));
                } else if (item[0].getBoundingClientRect().top < mkd.windowHeight / 2) {
                    card_inner.css('top', 0);
                    stop = true;
                    holder.removeClass('mkd-no-events');
                }
            }
            mkd.window.bind('scroll', update).resize(update);
            update();
        }
    }

    /**
     * Cards Gallery shortcode
     */
    function mkdCardsGallery() {
        var cardGalleries = $('.mkd-cards-gallery-holder');

        if (cardGalleries.length) {

            cardGalleries.each(function () {
                var gallery = $(this);
                var galleryHeight = gallery.height();
                gallery.children('.mkd-cards-gallery').css('height', galleryHeight);
                $(window).resize(function () {
                    galleryHeight = gallery.find('.card:last-child').height();
                    gallery.children('.mkd-cards-gallery').css('height', galleryHeight);
                });

                var cards = gallery.find('.card');
                var fake_card = gallery.find('.fake_card');
                fake_card.css('display', 'none');

                cards.each(function () {
                    var card = $(this);
                    mkdBundleAnimation(card, gallery);
                    card.click(function () {

                        if (!cards.last().is(card)) {
                            card.fadeOut(0, function () {
                                card.addClass('mkd-transform-y');
                                card.insertAfter(cards.last()).fadeIn(200, 'easeInOutQuint',
                                    function () {
                                        card.removeClass('mkd-transform-y');
                                    });
                                cards = gallery.find('.card');
                            });
                            return false;
                        }
                    });
                });
            });


        }
    }

    /**
     * Advanced Slider shortcode
     */
    var mkdAdvancedSlider = mkd.modules.shortcodes.mkdAdvancedSlider = function () {

        var handleMovement = function (slides, slider, curSlideId, newSlideId, newSlide, centerSlider) {
            slider.data('slide', newSlideId).attr('data-slide', newSlideId);
            slider.find('.mkd-advanced-slider').css('margin-left', centerSlider ? (-newSlide.position().left + $(slider).outerWidth() / 2 - newSlide.outerWidth() / 2) : -newSlide.position().left);

            setTimeout(function () {
                mkd.modules.common.mkdLazyImages();
            }, 500); //500 is duration of margin animation

            var direction = curSlideId < newSlideId ? 1 : -1;

            if (direction > 0 && newSlideId == slides.length) {
                slider.find('.button[data-direction="next"]').addClass('hidden');
            } else {
                slider.find('.button[data-direction="next"]').removeClass('hidden');
            }
            if (direction < 0 && newSlideId == 1) {
                slider.find('.button[data-direction="prev"]').addClass('hidden');
            } else {
                slider.find('.button[data-direction="prev"]').removeClass('hidden');
            }
        }

        var setSliderToCenter = function (slider, activeMiddleSlide, centerSlider) {
            slider.each(function (i, slider) {
                var slides = $(slider).find('.mkd-advanced-slider > .slide'),
                    dots = $(slider).find('.dot'),
                    center = Math.round(slides.length / 2) - 1;

                if (activeMiddleSlide) {
                    if (dots.length > 0) {
                        $(dots[center]).click();
                    } else {
                        handleMovement(slides, $(slider), 1, center, $(slides[center]), centerSlider);
                    }
                    $(slider).data('slide', center + 1).attr('data-slide', center + 1);
                    $(slider).find('.button').removeClass('hidden');
                } else {
                    if (dots.length > 0) {
                        $(dots[0]).click();
                    } else {
                        handleMovement(slides, $(slider), 1, 1, $(slides[0]), centerSlider);
                    }
                }
            })
        }

        //set max width of slides
        var maxWidthOfSlides = function (slides, slider, autoheight) {
            slider.find('.mkd-advanced-slider').width(99999);
            slides.each(function () {
                $(this).css('max-width', slider.outerWidth());
            });
        }

        //initialize card headers
        var initCardHeaders = function () {

            var headers = $('.cards');
            headers.each(function () {
                var header = $(this);
                var cards = header.find('.card');
                cards.each(function () {
                    var card = $(this);
                    card.click(function () {
                        if (!cards.last().is(card)) {
                            card.detach();
                            card.insertAfter(cards.last());
                            cards = header.find('.card');
                        }
                        return false;
                    })
                });
            });
        }

        //initialize card headers hover animation
        var initAdvancedHeadersHoverAnimation = function () {
            var headers = $('.cards');
            headers.each(function () {
                var header = $(this);
                var cards = header.find('.card').get().reverse();
                header.appear(function () {
                    $(cards).each(function (i) {
                        var card = $(this);
                        setTimeout(function () {
                            card.addClass('hovered');
                            setTimeout(function () {
                                card.removeClass('hovered');
                            }, 600);
                        }, 200 * i);

                    });
                }, {accX: 0, accY: -(mkd.windowHeight / 3)});
            });
        }

        //initialize card panes changing
        var initCardPanes = function () {
            $('.mkd-advanced-holder .card').each(function (i, card) {
                $(card).click(function () {
                    var pane = $('.' + $(card).data('value'));
                    pane.closest('.mkd-advanced-panes').find('.pane').removeClass('active');
                    pane.addClass('active');
                    mkd.modules.common.mkdLazyImages();
                    return false;
                })
            });

            $('.mkd-advanced-holder').each(function (i, holder) {
                $(holder).find('.mkd-advanced-panes .pane').last().addClass('active'); //set last slider as active
                //replace cards headers in right place
                if ($(holder).find('.mkd-advanced-panes .pane .card').length) {
                    $(holder).find('.mkd-advanced-panes .pane .card').each(function (i, card) {
                        $(card).detach();
                        $(holder).find('.mkd-advanced-header').append($(card));
                    });
                } else {
                    $(holder).find('.mkd-advanced-header').remove();
                }
            });
        }

        //set height of slider
        var setHeightOfSlider = function () {
            $('.mkd-advanced-panes').each(function () {
                var $this = $(this);
                var maxHeight = -1;
                var element_height = $this.find('.pane').height();
                maxHeight = maxHeight > element_height ? maxHeight : element_height;
                $this.height(maxHeight);
            });
        }

        //set swipe functionality on all sliders
        var setSwipeFunctionality = function (slider) {
            slider.swipe({
                swipeLeft: function () {
                    slider.find('.button[data-direction="next"]').click();
                },
                swipeRight: function () {
                    slider.find('.button[data-direction="prev"]').click();
                },
                threshold: 20
            });
        }


        //initialize sliders in every pane
        var initiateSliders = function () {
            $('.mkd-advanced-slider-holder').each(function (i, slider) {
                var slides = $(slider).find('.mkd-advanced-slider > .slide'),
                    activeMiddleSlide = $(slider).data('active-middle-slide'),
                    centerSlider = $(slider).data('center');

                //handle navigation arros click
                $(slider).find('.button').each(function (i, button) {
                    $(button).click(function () {
                        var direction = $(button).data('direction') == 'prev' ? -1 : 1,
                            curSlideId = $(slider).data('slide'),
                            newSlideId = $(slider).data('slide') + direction,
                            newSlide = $(slider).find('.slide[data-slide="' + newSlideId + '"]');

                        if (newSlide.length) {
                            handleMovement(slides, $(slider), curSlideId, newSlideId, newSlide, centerSlider);
                            $(slider).find('.dot').removeClass('active').filter('[data-slide="' + newSlideId + '"]').addClass('active');
                        }
                        return false;
                    });
                });

                //handle navigation bullets click
                $(slider).find('.dot').each(function (i, dot) {
                    $(dot).click(function () {
                        $(slider).find('.dot').removeClass('active');
                        $(dot).addClass('active');
                        var curSlideId = $(slider).data('slide'),
                            newSlideId = $(dot).data('slide'),
                            newSlide = $(slider).find('.slide[data-slide="' + newSlideId + '"]');
                        if (newSlide.length) {
                            handleMovement(slides, $(slider), curSlideId, newSlideId, newSlide, centerSlider);
                        }
                        return false;
                    });
                });


                /** 1. **/ maxWidthOfSlides(slides, $(slider), false);
                /** 2. **/ setSliderToCenter($(slider), activeMiddleSlide, centerSlider);
                /** 3. **/ setSwipeFunctionality($(slider));
                $(window).resize(function () {
                    maxWidthOfSlides(slides, $(slider), true);
                    setSliderToCenter($(slider), activeMiddleSlide, centerSlider);
                    setHeightOfSlider();
                });
            });
        }


        return {
            init: function () {
                if ($('.mkd-advanced-slider-holder').length) {
                    initiateSliders();
                    setHeightOfSlider();
                    initCardPanes();
                    initCardHeaders();
                }
            },
            load: function () {
                if ($('.mkd-advanced-slider-holder').length) {
                    setHeightOfSlider();
                    initAdvancedHeadersHoverAnimation();
                }
            }
        };

    }

    var mkdReservationFormDatePicker = function () {
        var datepicker = $('.mkd-ot-date');

        if (datepicker.length) {
            datepicker.each(function () {
                $(this).datepicker({
                    prevText: '<span class="arrow_carrot-left"></span>',
                    nextText: '<span class="arrow_carrot-right"></span>'
                });
            });
        }
    };

    function mkdTiltHoverEffect() {
        var tiltElements = $('.mkd-hover-tilt');

        if (tiltElements.length && !$('html').hasClass('touch')) {
            tiltElements.each(function () {
                var tiltElement = $(this),
                    imageWrappers = tiltElement.find('.mkd-ptf-item-image-holder'),
                    maxMove = 10, //maximum movement in px
                    move = 0, //move
                    w,
                    h,
                    topOffset,
                    leftOffset,
                    xPos,
                    yPos,
                    xShift,
                    yShift,
                    pause,
                    pauseFlag = true;

                //tilt set
                imageWrappers.mouseenter(function () {
                    var currentWrapper = $(this);

                    w = currentWrapper.outerWidth();
                    h = currentWrapper.outerHeight();
                    topOffset = currentWrapper.offset().top;
                    leftOffset = currentWrapper.offset().left;
                    xPos = 0;
                    yPos = 0;

                    currentWrapper.css('transform', 'translate3d(0,0,0) scale(1.03)');

                    pause = setTimeout(function () {
                        pauseFlag = false;
                    }, 200); //wait for image to be zoomed in

                    currentWrapper.mousemove(function (event) {
                        if (pauseFlag) {
                            event.stopPropagation();
                        }
                        else {
                            currentWrapper.css('transition', 'none');

                            xPos = event.pageX - leftOffset;
                            yPos = event.pageY - topOffset;
                            xShift = ((w / 2 - xPos) / w * 2) * move;
                            yShift = ((h / 2 - yPos) / h * 2) * move;

                            var transformOffset = "translate3d(" + xShift + "px, " + yShift + "px, 0) scale(1.03)";

                            currentWrapper.css('transform', transformOffset);

                            if (move < maxMove) {
                                move += 0.3; //increment slowly to its final value to avoid flicker on first move
                            }
                        }
                    });

                });

                //tilt reset
                imageWrappers.mouseleave(function () {
                    clearTimeout(pause);
                    move = 0;
                    pauseFlag = true;

                    var currentWrapper = $(this);
                    currentWrapper.css('transition', 'all .2s');
                    currentWrapper.css('transform', 'translate3d(0,0,0) scale(1)');
                });
            });
        }
    }

    /*
     **	Elements Holder responsive style
     */
    function mkdInitElementsHolderResponsiveStyle() {

        var elementsHolder = $('.mkd-elements-holder');

        if (elementsHolder.length) {
            elementsHolder.each(function () {
                var thisElementsHolder = $(this),
                    elementsHolderItem = thisElementsHolder.children('.mkd-elements-holder-item'),
                    style = '',
                    responsiveStyle = '';

                elementsHolderItem.each(function () {
                    var thisItem = $(this),
                        itemClass = '',
                        largeLaptop = '',
                        smallLaptop = '',
                        ipadLandscape = '',
                        ipadPortrait = '',
                        mobileLandscape = '',
                        mobilePortrait = '';


                    if (typeof thisItem.data('item-class') !== 'undefined' && thisItem.data('item-class') !== false) {
                        itemClass = thisItem.data('item-class');
                    }
                    if (typeof thisItem.data('1280-1440') !== 'undefined' && thisItem.data('1280-1440') !== false) {
                        largeLaptop = thisItem.data('1280-1440');
                    }
                    if (typeof thisItem.data('1024-1280') !== 'undefined' && thisItem.data('1024-1280') !== false) {
                        smallLaptop = thisItem.data('1024-1280');
                    }
                    if (typeof thisItem.data('768-1024') !== 'undefined' && thisItem.data('768-1024') !== false) {
                        ipadLandscape = thisItem.data('768-1024');
                    }
                    if (typeof thisItem.data('600-768') !== 'undefined' && thisItem.data('600-768') !== false) {
                        ipadPortrait = thisItem.data('600-768');
                    }
                    if (typeof thisItem.data('480-600') !== 'undefined' && thisItem.data('480-600') !== false) {
                        mobileLandscape = thisItem.data('480-600');
                    }
                    if (typeof thisItem.data('480') !== 'undefined' && thisItem.data('480') !== false) {
                        mobilePortrait = thisItem.data('480');
                    }

                    if (largeLaptop.length || smallLaptop.length || ipadLandscape.length || ipadPortrait.length || mobileLandscape.length || mobilePortrait.length) {

                        if (largeLaptop.length) {
                            responsiveStyle += "@media only screen and (min-width: 1280px) and (max-width: 1440px) {.mkd-elements-holder-item-content." + itemClass + " { padding: " + largeLaptop + " !important; } }";
                        }
                        if (smallLaptop.length) {
                            responsiveStyle += "@media only screen and (min-width: 1024px) and (max-width: 1280px) {.mkd-elements-holder-item-content." + itemClass + " { padding: " + smallLaptop + " !important; } }";
                        }
                        if (ipadLandscape.length) {
                            responsiveStyle += "@media only screen and (min-width: 768px) and (max-width: 1024px) {.mkd-elements-holder-item-content." + itemClass + " { padding: " + ipadLandscape + " !important; } }";
                        }
                        if (ipadPortrait.length) {
                            responsiveStyle += "@media only screen and (min-width: 600px) and (max-width: 768px) {.mkd-elements-holder-item-content." + itemClass + " { padding: " + ipadPortrait + " !important; } }";
                        }
                        if (mobileLandscape.length) {
                            responsiveStyle += "@media only screen and (min-width: 480px) and (max-width: 600px) {.mkd-elements-holder-item-content." + itemClass + " { padding: " + mobileLandscape + " !important; } }";
                        }
                        if (mobilePortrait.length) {
                            responsiveStyle += "@media only screen and (max-width: 480px) {.mkd-elements-holder-item-content." + itemClass + " { padding: " + mobilePortrait + " !important; } }";
                        }
                    }
                });

                if (responsiveStyle.length) {
                    style = '<style type="text/css" data-type="connect_mikado_modules_shortcodes_eh_custom_css">' + responsiveStyle + '</style>';
                }

                if (style.length) {
                    $('head').append(style);
                }
            });
        }
    }

    /*
     **	Background Slider
     */
    function mkdInitBackgroundSlider() {

        var backgroundSliders = $('.mkd-bckg-slider');

        if (backgroundSliders.length) {
            backgroundSliders.each(function () {


                var thisSlider = $(this);

                thisSlider.on('init', function () {

                    // change default opacity on init
                    thisSlider.addClass('appeared');
                }).slick({
                    dots: false,
                    arrows: true,
                    slidesToScroll: 1,
                    slidesToShow: 1
                });
            });
        }

    }


    /*
     * Unordered List animation
     */
    function mkdInitUnorderedList() {
        var unorderedLists = $('.mkd-unordered-list.mkd-animate-list');

        if (unorderedLists.length && !mkd.htmlEl.hasClass('touch')) {
            unorderedLists.appear(function () {
                var unorderedList = $(this),
                    listItems = unorderedList.find('li');

                listItems.each(function (i) {
                    var listItem = $(this);

                    setTimeout(function () {
                        listItem.addClass('mkd-list-item-appeared')
                    }, i * 150);
                });
            }, {accX: 0, accY: mkdGlobalVars.vars.mkdElementAppearAmount});
        }
    }


    /*
     * Table Shortcode animation
     */
    function mkdInitTableShortcode() {
        var tableShortcodes = $('.mkd-table-shortcode-holder.mkd-animate-table');

        if (tableShortcodes.length && !mkd.htmlEl.hasClass('touch')) {
            tableShortcodes.appear(function () {
                var tableShortcode = $(this),
                    tableItems = tableShortcode.find('.mkd-table-content-item-inner');

                tableItems.each(function (i) {
                    var tableItem = $(this);

                    setTimeout(function () {
                        tableItem.addClass('mkd-table-item-appeared')
                    }, i * 100);
                });
            }, {accX: 0, accY: mkdGlobalVars.vars.mkdElementAppearAmount});
        }
    }


    /*
     * Icon With Text Shortcode animation
     */
    function mkdInitIWTShortcode() {
        var iwtShortcodes = $('.mkd-iwt.mkd-iwt-loading-animation');

        if (iwtShortcodes.length && !mkd.htmlEl.hasClass('touch')) {
            iwtShortcodes.appear(function () {
                var iwtShortcode = $(this),
                    delay = parseInt(iwtShortcode.data('loading-animation-delay'));

                if (!delay > 0) {
                    delay = 0;
                }

                setTimeout(function () {
                    iwtShortcode.addClass('mkd-iwt-item-appeared');
                }, delay);
            }, {accX: 0, accY: mkdGlobalVars.vars.mkdElementAppearAmount});
        }
    }


    /*
     * Image With Text Over hover
     */
    function mkdInitImageWithTextOver() {
        var iwtoShortcodes = $('.mkd-iwt-over');

        if (iwtoShortcodes.length) {
            iwtoShortcodes.each(function () {
                var iwtoShortcode = $(this),
                    textHolder = iwtoShortcode.find('.mkd-text-holder'),
                    hoverColor = textHolder.data('hover-color'),
                    hoverBckgColor = textHolder.data('hover-background-color');

                if (hoverColor) {
                    var tempColor = textHolder.css('color');

                    iwtoShortcode.mouseenter(function () {
                        textHolder.css('color', hoverColor);
                    }).mouseleave(function () {
                        textHolder.css('color', tempColor);
                    });
                }

                if (hoverBckgColor) {
                    var tempBckgColor = textHolder.css('background-color');

                    iwtoShortcode.mouseenter(function () {
                        textHolder.css('background-color', hoverBckgColor);
                    }).mouseleave(function () {
                        textHolder.css('background-color', tempBckgColor);
                    });
                }

            });
        }
    }

    /**
     * Init Text Marquee effect
     */
    function mkdInitTextMarquee() {
        var textMarqueeShortcodes = $('.mkd-text-marquee');

        if (textMarqueeShortcodes.length) {
            textMarqueeShortcodes.each(function () {
                var textMarqueeShortcode = $(this),
                    marqueeElements = textMarqueeShortcode.find('.mkd-marquee-element'),
                    originalText = marqueeElements.filter('.mkd-original-text'),
                    auxText = marqueeElements.filter('.mkd-aux-text');

                var calcWidth = function (element) {
                    var width;

                    if (textMarqueeShortcode.outerWidth() > element.outerWidth()) {
                        width = textMarqueeShortcode.outerWidth();
                    } else {
                        width = element.outerWidth();
                    }

                    return width;
                }

                var marqueeEffect = function () {
                    mkd.modules.common.mkdRequestAnimationFrame();

                    var delta = 1, //pixel movement
                        speedCoeff = 1, // below 1 to slow down, above 1 to speed up
                        marqueeWidth = calcWidth(originalText);

                    marqueeElements.css({'width': marqueeWidth}) // set the same width to both elements
                    auxText.css('left', marqueeWidth); //set to the right of the inital marquee element

                    //movement loop
                    marqueeElements.each(function (i) {
                        var marqueeElement = $(this),
                            currentPos = 0;

                        var mkdInfiniteScrollEffect = function () {
                            currentPos -= delta;

                            //move marquee element
                            if (marqueeElement.position().left <= -marqueeWidth) {
                                marqueeElement.css('left', parseInt(marqueeWidth - delta));
                                currentPos = 0;
                            }

                            marqueeElement.css('transform', 'translate3d(' + speedCoeff * currentPos + 'px,0,0)');

                            requestNextAnimationFrame(mkdInfiniteScrollEffect);

                            $(window).resize(function () {
                                marqueeWidth = calcWidth(originalText);
                                currentPos = 0;
                                originalText.css('left', 0);
                                auxText.css('left', marqueeWidth); //set to the right of the inital marquee element
                            });
                        };

                        mkdInfiniteScrollEffect();
                    });
                }

                marqueeEffect();
            });
        }
    }
})
(jQuery);
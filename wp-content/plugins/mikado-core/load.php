<?php

require_once 'const.php';
require_once 'lib/helpers/helpers.php';

//load import
require_once 'import/mkd-import.php';

//load lib
require_once 'lib/post-type-interface.php';
require_once 'lib/shortcode-interface.php';
require_once 'lib/google-fonts.php';

//load post-post-types
require_once 'post-types/portfolio/lib/portfolio-query.php';
require_once 'post-types/portfolio/portfolio-register.php';
require_once 'post-types/portfolio/shortcodes/portfolio-list/portfolio-list.php';
require_once 'post-types/portfolio/shortcodes/portfolio-slider/portfolio-slider.php';
require_once 'post-types/testimonials/testimonials-register.php';
require_once 'post-types/testimonials/shortcodes/testimonials.php';
require_once 'post-types/carousels/carousel-register.php';
require_once 'post-types/carousels/shortcodes/carousel.php';
require_once 'post-types/slider/slider-register.php';
require_once 'post-types/slider/tax-custom-fields.php';
require_once 'post-types/slider/shortcodes/slider.php';
require_once 'post-types/post-types-register.php'; //this has to be loaded last

//load shortcodes inteface
require_once 'lib/shortcode-loader.php';


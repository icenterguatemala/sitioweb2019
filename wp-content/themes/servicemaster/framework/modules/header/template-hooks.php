<?php

//top line
add_action('servicemaster_mikado_before_page_header', 'servicemaster_mikado_get_header_top_line');

//top header bar
add_action('servicemaster_mikado_before_page_header', 'servicemaster_mikado_get_header_top');

//mobile header
add_action('servicemaster_mikado_after_page_header', 'servicemaster_mikado_get_mobile_header');
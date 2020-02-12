<?php

if (!function_exists('servicemaster_mikado_header_meta_box_map')) {
	function servicemaster_mikado_header_meta_box_map() {

		$header_meta_box = servicemaster_mikado_add_meta_box(
			array(
				'scope' => array('page', 'portfolio-item', 'post'),
				'title' => esc_html__('Header', 'servicemaster'),
				'name'  => 'header_meta'
			)
		);

		$temp_holder_show = '';
		$temp_holder_hide = '';
		$temp_array_standard = array();
		$temp_array_standard_extended = array();
		$temp_array_box = array();
		$temp_array_divided = array();
		$temp_array_minimal = array();
		$temp_array_centered = array();
		$temp_array_tabbed = array();
		$temp_array_vertical = array();
		$temp_array_vertical_compact = array();
		$temp_array_top_header = array(
			'hidden_value'  => 'default',
			'hidden_values' => array('header-vertical', 'header-vertical-compact'));
		$temp_array_top_line = array(
			'hidden_value'  => 'default',
			'hidden_values' => array('header-vertical', 'header-vertical-compact'));
		$temp_array_behaviour = array();
		switch (servicemaster_mikado_options()->getOptionValue('header_type')) {

			case 'header-standard':
				$temp_holder_show = '#mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_behaviour_meta';
				$temp_holder_hide = '#mkd_mkd_header_vertical_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container,#mkd_mkd_header_vertical_compact_type_meta_container';

				$temp_array_standard = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact',
					)
				);

				$temp_array_standard_extended = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-box',
						'header-tabbed',
						'header-vertical-compact',
					)
				);

				$temp_array_box = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-tabbed',
						'header-vertical-compact',
					)
				);

				$temp_array_minimal = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact',
					)
				);

				$temp_array_divided = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact',
					)
				);

				$temp_array_centered = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-divided',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact',
					)
				);

				$temp_array_tabbed = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-vertical-compact',
					)
				);

				$temp_array_vertical = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact',
					)
				);

				$temp_array_vertical_compact = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical'
					)
				);

				$temp_array_behaviour = array(
					'hidden_values' => array('header-vertical', 'header-vertical-compact')
				);

				break;

			case 'header-standard-extended':
				$temp_holder_show = '#mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_behaviour_meta';
				$temp_holder_hide = '#mkd_mkd_header_vertical_type_meta_container, #mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container, #mkd_mkd_header_vertical_compact_type_meta_container';

				$temp_array_standard = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_standard_extended = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_box = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_minimal = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_divided = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_centered = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-divided',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_tabbed = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical_compact = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical'
					)
				);

				$temp_array_behaviour = array(
					'hidden_values' => array('header-vertical', 'header-vertical-compact')
				);

				break;

			case 'header-box':
				$temp_holder_show = '#mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_behaviour_meta';
				$temp_holder_hide = '#mkd_mkd_header_vertical_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container, #mkd_mkd_header_vertical_compact_type_meta_container';

				$temp_array_standard = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_standard_extended = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_box = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_minimal = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_divided = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_centered = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-divided',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_tabbed = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical_compact = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical'
					)
				);

				$temp_array_behaviour = array(
					'hidden_values' => array('header-vertical', 'header-vertical-compact')
				);

				break;


			case 'header-minimal':
				$temp_holder_show = '#mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_behaviour_meta';
				$temp_holder_hide = '#mkd_mkd_header_vertical_type_meta_container, #mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container,#mkd_mkd_header_vertical_compact_type_meta_container';

				$temp_array_standard = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_standard_extended = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_box = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_minimal = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'header-standard',
						'header-vertical',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_divided = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_centered = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-divided',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_tabbed = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical_compact = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical'
					)
				);

				$temp_array_behaviour = array(
					'hidden_values' => array('header-vertical', 'header-vertical-compact')
				);

				break;

			case 'header-divided':
				$temp_holder_show = '#mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_behaviour_meta';
				$temp_holder_hide = '#mkd_mkd_header_vertical_type_meta_container, #mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container,#mkd_mkd_header_vertical_compact_type_meta_container';

				$temp_array_standard = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_standard_extended = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_box = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_minimal = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_divided = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_centered = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-divided',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_tabbed = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-vertical-compact'
					)
				);


				$temp_array_vertical = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical_compact = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical'
					)
				);

				$temp_array_behaviour = array(
					'hidden_values' => array('header-vertical', 'header-vertical-compact')
				);

				break;

			case 'header-centered':
				$temp_holder_show = '#mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_behaviour_meta';
				$temp_holder_hide = '#mkd_mkd_header_vertical_type_meta_container, #mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container,#mkd_mkd_header_vertical_compact_type_meta_container';

				$temp_array_standard = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_standard_extended = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_box = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_minimal = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_divided = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_centered = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-divided',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_tabbed = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical_compact = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical'
					)
				);

				$temp_array_behaviour = array(
					'hidden_values' => array('header-vertical', 'header-vertical-compact')
				);

				break;

			case 'header-tabbed':
				$temp_holder_show = '#mkd_mkd_header_tabbed_type_meta_container, #mkd_mkd_header_behaviour_meta';
				$temp_holder_hide = '#mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_vertical_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_box_type_meta_container,#mkd_mkd_header_vertical_compact_type_meta_container';

				$temp_array_standard = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_standard_extended = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_box = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_minimal = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_divided = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_centered = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-divided',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_tabbed = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical_compact = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical'
					)
				);

				$temp_array_behaviour = array(
					'hidden_values' => array('header-vertical', 'header-vertical-compact')
				);

				break;

			case 'header-vertical':
				$temp_holder_show = '#mkd_mkd_header_vertical_type_meta_container';
				$temp_holder_hide = '#mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_behaviour_meta, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container,#mkd_mkd_header_vertical_compact_type_meta_container';

				$temp_array_standard = array(
					'hidden_values' => array(
						'',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_standard_extended = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_box = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_minimal = array(
					'hidden_values' => array(
						'',
						'header-vertical',
						'header-standard',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_divided = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_centered = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-divided',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_tabbed = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical_compact = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical'
					)
				);

				$temp_array_behaviour = array(
					'hidden_values' => array('', 'header-vertical', 'header-vertical-compact')
				);

				break;

			case 'header-vertical-compact':
				$temp_holder_show = '#mkd_mkd_header_vertical_compact_type_meta_container';
				$temp_holder_hide = '#mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_behaviour_meta, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container,#mkd_mkd_header_vertical_type_meta_container';

				$temp_array_standard = array(
					'hidden_values' => array(
						'',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_standard_extended = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_box = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_minimal = array(
					'hidden_values' => array(
						'',
						'header-vertical',
						'header-standard',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_divided = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_centered = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-vertical',
						'header-divided',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_tabbed = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-vertical',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical = array(
					'hidden_values' => array(
						'',
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical-compact'
					)
				);

				$temp_array_vertical_compact = array(
					'hidden_value'  => 'default',
					'hidden_values' => array(
						'header-standard',
						'header-minimal',
						'header-divided',
						'header-centered',
						'header-standard-extended',
						'header-box',
						'header-tabbed',
						'header-vertical'
					)
				);

				$temp_array_behaviour = array(
					'hidden_values' => array('', 'header-vertical', 'header-vertical-compact')
				);

				break;
		}


		servicemaster_mikado_add_meta_box_field(
			array(
				'parent'        => $header_meta_box,
				'type'          => 'select',
				'name'          => 'mkd_enable_wide_menu_background_meta',
				'default_value' => '',
				'label'         => esc_html__('Enable Full Width Background for Wide Dropdown Type', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will show full width background  for wide dropdown type', 'servicemaster'),
				'options'       => array(
					''    => '',
					'no'  => esc_html__('No', 'servicemaster'),
					'yes' => esc_html__('Yes', 'servicemaster')
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_header_type_meta',
				'type'          => 'select',
				'default_value' => '',
				'label'         => esc_html__('Choose Header Type', 'servicemaster'),
				'description'   => esc_html__('Select header type layout', 'servicemaster'),
				'parent'        => $header_meta_box,
				'options'       => array(
					''                         => 'Default',
					'header-standard'          => esc_html__('Standard Header', 'servicemaster'),
					'header-standard-extended' => esc_html__('Standard Extended Header', 'servicemaster'),
					'header-box'               => esc_html__('"In The Box" Header', 'servicemaster'),
					'header-minimal'           => esc_html__('Minimal Header', 'servicemaster'),
					'header-divided'           => esc_html__('Divided Header', 'servicemaster'),
					'header-centered'          => esc_html__('Centered Header', 'servicemaster'),
					'header-tabbed'            => esc_html__('Tabbed Header', 'servicemaster'),
					'header-vertical'          => esc_html__('Vertical Header', 'servicemaster'),
					'header-vertical-compact'  => esc_html__('Vertical Compact Header', 'servicemaster')
				),
				'args'          => array(
					"dependence" => true,
					"hide"       => array(
						""                         => $temp_holder_hide,
						'header-standard'          => '#mkd_mkd_header_vertical_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container,#mkd_mkd_header_vertical_compact_type_meta_container',
						'header-standard-extended' => '#mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_vertical_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container,#mkd_mkd_header_vertical_compact_type_meta_container',
						'header-box'               => '#mkd_mkd_header_vertical_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container,#mkd_mkd_header_vertical_compact_type_meta_container',
						'header-minimal'           => '#mkd_mkd_header_vertical_type_meta_container, #mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container,#mkd_mkd_header_vertical_compact_type_meta_container',
						'header-divided'           => '#mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_vertical_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container,#mkd_mkd_header_vertical_compact_type_meta_container',
						'header-centered'          => '#mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_vertical_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container,#mkd_mkd_header_vertical_compact_type_meta_container',
						'header-tabbed'            => '#mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_vertical_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_box_type_meta_container,#mkd_mkd_header_vertical_compact_type_meta_container',
						'header-vertical'          => '#mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_top_bar_container_meta_container, #mkd_mkd_top_line_container_meta_container, #mkd_mkd_header_behaviour_meta, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container,#mkd_mkd_header_vertical_compact_type_meta_container',
						'header-vertical-compact'  => '#mkd_mkd_header_standard_type_meta_container, #mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_top_bar_container_meta_container, #mkd_mkd_top_line_container_meta_container, #mkd_mkd_header_behaviour_meta, #mkd_mkd_header_divided_type_meta_container, #mkd_mkd_header_centered_type_meta_container, #mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_header_box_type_meta_container, #mkd_mkd_header_tabbed_type_meta_container,#mkd_mkd_header_vertical_type_meta_container'
					),
					"show"       => array(
						""                         => $temp_holder_show,
						"header-standard"          => '#mkd_mkd_header_standard_type_meta_container, #mkd_mkd_top_bar_container_meta_container, #mkd_mkd_top_line_container_meta_container, #mkd_mkd_header_behaviour_meta',
						"header-standard-extended" => '#mkd_mkd_header_standard_extended_type_meta_container, #mkd_mkd_top_bar_container_meta_container, #mkd_mkd_top_line_container_meta_container, #mkd_mkd_header_behaviour_meta',
						"header-box"               => '#mkd_mkd_header_box_type_meta_container, #mkd_mkd_top_bar_container_meta_container, #mkd_mkd_top_line_container_meta_container, #mkd_mkd_header_behaviour_meta',
						"header-minimal"           => '#mkd_mkd_header_minimal_type_meta_container, #mkd_mkd_top_bar_container_meta_container, #mkd_mkd_top_line_container_meta_container, #mkd_mkd_header_behaviour_meta',
						'header-divided'           => '#mkd_mkd_header_divided_type_meta_container, #mkd_mkd_top_bar_container_meta_container, #mkd_mkd_top_line_container_meta_container, #mkd_mkd_header_behaviour_meta',
						'header-centered'          => '#mkd_mkd_header_centered_type_meta_container, #mkd_mkd_top_bar_container_meta_container, #mkd_mkd_top_line_container_meta_container, #mkd_mkd_header_behaviour_meta',
						"header-tabbed"            => '#mkd_mkd_header_tabbed_type_meta_container, #mkd_mkd_top_bar_container_meta_container, #mkd_mkd_top_line_container_meta_container, #mkd_mkd_header_behaviour_meta',
						"header-vertical"          => '#mkd_mkd_header_vertical_type_meta_container',
						"header-vertical-compact"  => '#mkd_mkd_header_vertical_compact_type_meta_container'
					)
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array_merge(
				array(
					'parent'          => $header_meta_box,
					'type'            => 'select',
					'name'            => 'mkd_header_behaviour_meta',
					'default_value'   => '',
					'label'           => esc_html__('Choose Header behaviour', 'servicemaster'),
					'description'     => esc_html__('Select the behaviour of header when you scroll down to page', 'servicemaster'),
					'options'         => array(
						''                                => '',
						'no-behavior'                     => esc_html__('No Behavior', 'servicemaster'),
						'sticky-header-on-scroll-up'      => esc_html__('Sticky on scrol up', 'servicemaster'),
						'sticky-header-on-scroll-down-up' => esc_html__('Sticky on scrol up/down', 'servicemaster'),
						'fixed-on-scroll'                 => esc_html__('Fixed on scroll', 'servicemaster')
					),
					'hidden_property' => 'mkd_header_type_meta',
					'hidden_value'    => '',
					'args'            => array(
						'dependence' => true,
						'show'       => array(
							''                                => '',
							'sticky-header-on-scroll-up'      => '',
							'sticky-header-on-scroll-down-up' => '#mkd_mkd_sticky_amount_container_meta_container',
							'no-behavior'                     => ''
						),
						'hide'       => array(
							''                                => '#mkd_mkd_sticky_amount_container_meta_container',
							'sticky-header-on-scroll-up'      => '#mkd_mkd_sticky_amount_container_meta_container',
							'sticky-header-on-scroll-down-up' => '',
							'no-behavior'                     => '#mkd_mkd_sticky_amount_container_meta_container'
						)
					)
				),
				$temp_array_behaviour
			)
		);

		$sticky_amount_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $header_meta_box,
				'name'            => 'mkd_sticky_amount_container_meta_container',
				'hidden_property' => 'mkd_header_behaviour_meta',
				'hidden_value'    => '',
				'hidden_values'   => array('', 'no-behavior', 'sticky-header-on-scroll-up'),
			)
		);

		$sticky_amount_group = servicemaster_mikado_add_admin_group(array(
			'name'        => 'sticky_amount_group',
			'title'       => esc_html__('Scroll Amount for Sticky Header Appearance', 'servicemaster'),
			'parent'      => $sticky_amount_container,
			'description' => esc_html__('Enter the amount of pixels for sticky header appearance, or set browser height to "Yes" for predefined sticky header appearance amount', 'servicemaster')
		));

		$sticky_amount_row = servicemaster_mikado_add_admin_row(array(
			'name'   => 'sticky_amount_group',
			'parent' => $sticky_amount_group
		));

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'   => 'mkd_scroll_amount_for_sticky_meta',
				'type'   => 'textsimple',
				'label'  => esc_html__('Amount in px', 'servicemaster'),
				'parent' => $sticky_amount_row,
				'args'   => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_scroll_amount_for_sticky_fullscreen_meta',
				'type'          => 'yesnosimple',
				'label'         => esc_html__('Browser Height', 'servicemaster'),
				'default_value' => 'no',
				'parent'        => $sticky_amount_row
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_header_style_meta',
				'type'          => 'select',
				'default_value' => '',
				'label'         => esc_html__('Header Skin', 'servicemaster'),
				'description'   => esc_html__('Choose a header style to make header elements (logo, main menu, side menu button) in that predefined style', 'servicemaster'),
				'parent'        => $header_meta_box,
				'options'       => array(
					''             => '',
					'light-header' => esc_html__('Light', 'servicemaster'),
					'dark-header'  => esc_html__('Dark', 'servicemaster')
				)
			)
		);

		/* Main menu per page style - START */

		$main_menu_style_group = servicemaster_mikado_add_admin_group(
			array(
				'name'        => 'main_menu_style_group',
				'title'       => esc_html__('Main Menu Style', 'servicemaster'),
				'description' => esc_html__('Define styles for Main menu area', 'servicemaster'),
				'parent'      => $header_meta_box
			)
		);

		$main_menu_style_row1 = servicemaster_mikado_add_admin_row(
			array(
				'name'   => 'main_menu_style_row1',
				'next'   => true,
				'parent' => $main_menu_style_group
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'   => 'mkd_menu_color_meta',
				'type'   => 'colorsimple',
				'label'  => esc_html__('Text Color', 'servicemaster'),
				'parent' => $main_menu_style_row1
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'   => 'mkd_menu_hovercolor_meta',
				'type'   => 'colorsimple',
				'label'  => esc_html__('Hover Text Color', 'servicemaster'),
				'parent' => $main_menu_style_row1
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'   => 'mkd_menu_activecolor_meta',
				'type'   => 'colorsimple',
				'label'  => esc_html__('Active Text Color', 'servicemaster'),
				'parent' => $main_menu_style_row1
			)
		);

		$main_menu_style_row2 = servicemaster_mikado_add_admin_row(
			array(
				'name'   => 'main_menu_style_row2',
				'next'   => true,
				'parent' => $main_menu_style_group
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'   => 'mkd_menu_text_background_color_meta',
				'type'   => 'colorsimple',
				'label'  => esc_html__('Text Background Color', 'servicemaster'),
				'parent' => $main_menu_style_row2
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'   => 'mkd_menu_hover_background_color_meta',
				'type'   => 'colorsimple',
				'label'  => esc_html__('Hover Text Background Color', 'servicemaster'),
				'parent' => $main_menu_style_row2
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'   => 'mkd_menu_active_background_color_meta',
				'type'   => 'colorsimple',
				'label'  => esc_html__('Active Text Background Color', 'servicemaster'),
				'parent' => $main_menu_style_row2
			)
		);

		/* Main menu per page style - END */

		servicemaster_mikado_add_meta_box_field(
			array(
				'parent'        => $header_meta_box,
				'type'          => 'select',
				'name'          => 'mkd_enable_header_style_on_scroll_meta',
				'default_value' => '',
				'label'         => esc_html__('Enable Header Style on Scroll', 'servicemaster'),
				'description'   => esc_html__('Enabling this option, header will change style depending on row settings for dark/light style', 'servicemaster'),
				'options'       => array(
					''    => '',
					'no'  => esc_html__('No', 'servicemaster'),
					'yes' => esc_html__('Yes', 'servicemaster')
				)
			)
		);

		$header_standard_type_meta_container = servicemaster_mikado_add_admin_container(
			array_merge(
				array(
					'parent'          => $header_meta_box,
					'name'            => 'mkd_header_standard_type_meta_container',
					'hidden_property' => 'mkd_header_type_meta',

				),
				$temp_array_standard
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'mkd_custom_sidebar_header_standard_meta',
			'type'        => 'selectblank',
			'label'       => esc_html__('Choose Widget Area to Display', 'servicemaster'),
			'description' => esc_html__('Choose Custom Widget area to display in Header', 'servicemaster'),
			'parent'      => $header_standard_type_meta_container,
			'options'     => servicemaster_mikado_get_custom_sidebars()
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_position_header_standard_meta',
			'type'          => 'select',
			'label'         => esc_html__('Menu Area position', 'servicemaster'),
			'description'   => esc_html__('Set menu area position', 'servicemaster'),
			'parent'        => $header_standard_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''       => esc_html__('Default', 'servicemaster'),
				'center' => esc_html__('Center', 'servicemaster'),
				'left'   => esc_html__('Left', 'servicemaster'),
				'right'  => esc_html__('Right', 'servicemaster'),
			)
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_in_grid_header_standard_meta',
			'type'          => 'select',
			'label'         => esc_html__('Header In Grid', 'servicemaster'),
			'description'   => esc_html__('Set header content to be in grid', 'servicemaster'),
			'parent'        => $header_standard_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => esc_html__('Default', 'servicemaster'),
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'hide'       => array(
					''    => '#mkd_menu_area_in_grid_header_standard_container',
					'no'  => '#mkd_menu_area_in_grid_header_standard_container',
					'yes' => ''
				),
				'show'       => array(
					''    => '',
					'no'  => '',
					'yes' => '#mkd_menu_area_in_grid_header_standard_container'
				)
			)
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_sticky_header_in_grid_meta',
			'type'          => 'select',
			'label'         => esc_html__('Sticky Header In Grid', 'servicemaster'),
			'description'   => esc_html__('Set sticky header content to be in grid', 'servicemaster'),
			'parent'        => $header_standard_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => esc_html__('Default', 'servicemaster'),
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'hide'       => array(
					''    => '#mkd_menu_area_in_grid_header_standard_container',
					'no'  => '#mkd_menu_area_in_grid_header_standard_container',
					'yes' => ''
				),
				'show'       => array(
					''    => '',
					'no'  => '',
					'yes' => '#mkd_menu_area_in_grid_header_standard_container'
				)
			)
		));

		$menu_area_in_grid_header_standard_container = servicemaster_mikado_add_admin_container(array(
			'type'            => 'container',
			'name'            => 'menu_area_in_grid_header_standard_container',
			'parent'          => $header_standard_type_meta_container,
			'hidden_property' => 'mkd_menu_area_in_grid_header_standard_meta',
			'hidden_value'    => 'no',
			'hidden_values'   => array('', 'no')
		));


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_grid_background_color_header_standard_meta',
				'type'        => 'color',
				'label'       => esc_html__('Grid Background Color', 'servicemaster'),
				'description' => esc_html__('Set grid background color for header area', 'servicemaster'),
				'parent'      => $menu_area_in_grid_header_standard_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_grid_background_transparency_header_standard_meta',
				'type'        => 'text',
				'label'       => esc_html__('Grid Background Transparency', 'servicemaster'),
				'description' => esc_html__('Set grid background transparency for header (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $menu_area_in_grid_header_standard_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_in_grid_shadow_header_standard_meta',
			'type'          => 'select',
			'label'         => esc_html__('Grid Area Shadow', 'servicemaster'),
			'description'   => esc_html__('Set shadow on grid area', 'servicemaster'),
			'parent'        => $menu_area_in_grid_header_standard_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_background_color_header_standard_meta',
				'type'        => 'color',
				'label'       => esc_html__('Background Color', 'servicemaster'),
				'description' => esc_html__('Choose a background color for header area', 'servicemaster'),
				'parent'      => $header_standard_type_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_background_transparency_header_standard_meta',
				'type'        => 'text',
				'label'       => esc_html__('Transparency', 'servicemaster'),
				'description' => esc_html__('Choose a transparency for the header background color (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $header_standard_type_meta_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_shadow_header_standard_meta',
			'type'          => 'select',
			'label'         => esc_html__('Header Area Shadow', 'servicemaster'),
			'description'   => esc_html__('Set shadow on header area', 'servicemaster'),
			'parent'        => $header_standard_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));

		$header_minimal_type_meta_container = servicemaster_mikado_add_admin_container(
			array_merge(
				array(
					'parent'          => $header_meta_box,
					'name'            => 'mkd_header_minimal_type_meta_container',
					'hidden_property' => 'mkd_header_type_meta',

				),
				$temp_array_minimal
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_in_grid_header_minimal_meta',
			'type'          => 'select',
			'label'         => esc_html__('Header In Grid', 'servicemaster'),
			'description'   => esc_html__('Set header content to be in grid', 'servicemaster'),
			'parent'        => $header_minimal_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => esc_html__('Default', 'servicemaster'),
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'hide'       => array(
					''    => '#mkd_menu_area_in_grid_header_minimal_container',
					'no'  => '#mkd_menu_area_in_grid_header_minimal_container',
					'yes' => ''
				),
				'show'       => array(
					''    => '',
					'no'  => '',
					'yes' => '#mkd_menu_area_in_grid_header_minimal_container'
				)
			)
		));

		$menu_area_in_grid_header_minimal_container = servicemaster_mikado_add_admin_container(array(
			'type'            => 'container',
			'name'            => 'menu_area_in_grid_header_minimal_container',
			'parent'          => $header_minimal_type_meta_container,
			'hidden_property' => 'mkd_menu_area_in_grid_header_minimal_meta',
			'hidden_value'    => 'no',
			'hidden_values'   => array('', 'no')
		));


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_grid_background_color_header_minimal_meta',
				'type'        => 'color',
				'label'       => esc_html__('Grid Background Color', 'servicemaster'),
				'description' => esc_html__('Set grid background color for header area', 'servicemaster'),
				'parent'      => $menu_area_in_grid_header_minimal_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_grid_background_transparency_header_minimal_meta',
				'type'        => 'text',
				'label'       => esc_html__('Grid Background Transparency', 'servicemaster'),
				'description' => esc_html__('Set grid background transparency for header (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $menu_area_in_grid_header_minimal_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_in_grid_shadow_header_minimal_meta',
			'type'          => 'select',
			'label'         => esc_html__('Grid Area Shadow', 'servicemaster'),
			'description'   => esc_html__('Set shadow on grid area', 'servicemaster'),
			'parent'        => $menu_area_in_grid_header_minimal_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_background_color_header_minimal_meta',
				'type'        => 'color',
				'label'       => esc_html__('Background Color', 'servicemaster'),
				'description' => esc_html__('Choose a background color for header area', 'servicemaster'),
				'parent'      => $header_minimal_type_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_background_transparency_header_minimal_meta',
				'type'        => 'text',
				'label'       => esc_html__('Transparency', 'servicemaster'),
				'description' => esc_html__('Choose a transparency for the header background color (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $header_minimal_type_meta_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_shadow_header_minimal_meta',
			'type'          => 'select',
			'label'         => esc_html__('Header Area Shadow', 'servicemaster'),
			'description'   => esc_html__('Set shadow on header area', 'servicemaster'),
			'parent'        => $header_minimal_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_fullscreen_menu_background_image_meta',
				'type'          => 'image',
				'default_value' => '',
				'label'         => esc_html__('Fullscreen Background Image', 'servicemaster'),
				'description'   => esc_html__('Set background image for Fullscreen Menu', 'servicemaster'),
				'parent'        => $header_minimal_type_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_disable_fullscreen_menu_background_image_meta',
				'type'          => 'yesno',
				'default_value' => 'no',
				'label'         => esc_html__('Disable Fullscreen Background Image', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will hide background image in Fullscreen Menu', 'servicemaster'),
				'parent'        => $header_minimal_type_meta_container
			)
		);

		$header_divided_type_meta_container = servicemaster_mikado_add_admin_container(
			array_merge(
				array(
					'parent'          => $header_meta_box,
					'name'            => 'mkd_header_divided_type_meta_container',
					'hidden_property' => 'mkd_header_type_meta',

				),
				$temp_array_divided
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_in_grid_header_divided_meta',
			'type'          => 'select',
			'label'         => esc_html__('Header In Grid', 'servicemaster'),
			'description'   => esc_html__('Set header content to be in grid', 'servicemaster'),
			'parent'        => $header_divided_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => esc_html__('Default', 'servicemaster'),
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'hide'       => array(
					''    => '#mkd_menu_area_in_grid_header_divided_container',
					'no'  => '#mkd_menu_area_in_grid_header_divided_container',
					'yes' => ''
				),
				'show'       => array(
					''    => '',
					'no'  => '',
					'yes' => '#mkd_menu_area_in_grid_header_divided_container'
				)
			)
		));

		$menu_area_in_grid_header_divided_container = servicemaster_mikado_add_admin_container(array(
			'type'            => 'container',
			'name'            => 'menu_area_in_grid_header_divided_container',
			'parent'          => $header_divided_type_meta_container,
			'hidden_property' => 'mkd_menu_area_in_grid_header_divided_meta',
			'hidden_value'    => 'no',
			'hidden_values'   => array('', 'no')
		));


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_grid_background_color_header_divided_meta',
				'type'        => 'color',
				'label'       => esc_html__('Grid Background Color', 'servicemaster'),
				'description' => esc_html__('Set grid background color for header area', 'servicemaster'),
				'parent'      => $menu_area_in_grid_header_divided_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_grid_background_transparency_header_divided_meta',
				'type'        => 'text',
				'label'       => esc_html__('Grid Background Transparency', 'servicemaster'),
				'description' => esc_html__('Set grid background transparency for header (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $menu_area_in_grid_header_divided_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_in_grid_shadow_header_divided_meta',
			'type'          => 'select',
			'label'         => esc_html__('Grid Area Shadow', 'servicemaster'),
			'description'   => esc_html__('Set shadow on grid area', 'servicemaster'),
			'parent'        => $menu_area_in_grid_header_divided_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_background_color_header_divided_meta',
				'type'        => 'color',
				'label'       => esc_html__('Background Color', 'servicemaster'),
				'description' => esc_html__('Choose a background color for header area', 'servicemaster'),
				'parent'      => $header_divided_type_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_background_transparency_header_divided_meta',
				'type'        => 'text',
				'label'       => esc_html__('Transparency', 'servicemaster'),
				'description' => esc_html__('Choose a transparency for the header background color (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $header_divided_type_meta_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_shadow_header_divided_meta',
			'type'          => 'select',
			'label'         => esc_html__('Header Area Shadow', 'servicemaster'),
			'description'   => esc_html__('Set shadow on header area', 'servicemaster'),
			'parent'        => $header_divided_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));

		$header_centered_type_meta_container = servicemaster_mikado_add_admin_container(
			array_merge(
				array(
					'parent'          => $header_meta_box,
					'name'            => 'mkd_header_centered_type_meta_container',
					'hidden_property' => 'mkd_header_type_meta',

				),
				$temp_array_centered
			)
		);

		servicemaster_mikado_add_admin_section_title(array(
			'name'   => 'logo_area_centered_title',
			'parent' => $header_centered_type_meta_container,
			'title'  => esc_html__('Logo Area', 'servicemaster')
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_logo_area_in_grid_header_centered_meta',
			'type'          => 'select',
			'label'         => esc_html__('Logo Area In Grid', 'servicemaster'),
			'description'   => esc_html__('Set logo area content to be in grid', 'servicemaster'),
			'parent'        => $header_centered_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => esc_html__('Default', 'servicemaster'),
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'hide'       => array(
					''    => '#mkd_logo_area_in_grid_header_centered_container',
					'no'  => '#mkd_logo_area_in_grid_header_centered_container',
					'yes' => ''
				),
				'show'       => array(
					''    => '',
					'no'  => '',
					'yes' => '#mkd_logo_area_in_grid_header_centered_container'
				)
			)
		));

		$logo_area_in_grid_header_centered_container = servicemaster_mikado_add_admin_container(array(
			'type'            => 'container',
			'name'            => 'logo_area_in_grid_header_centered_container',
			'parent'          => $header_centered_type_meta_container,
			'hidden_property' => 'mkd_logo_area_in_grid_header_centered_meta',
			'hidden_value'    => 'no',
			'hidden_values'   => array('', 'no')
		));


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_logo_area_grid_background_color_header_centered_meta',
				'type'        => 'color',
				'label'       => esc_html__('Grid Background Color', 'servicemaster'),
				'description' => esc_html__('Set grid background color for logo area', 'servicemaster'),
				'parent'      => $logo_area_in_grid_header_centered_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_logo_area_grid_background_transparency_header_centered_meta',
				'type'        => 'text',
				'label'       => esc_html__('Grid Background Transparency', 'servicemaster'),
				'description' => esc_html__('Set grid background transparency for logo area (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $logo_area_in_grid_header_centered_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_logo_area_in_grid_border_header_centered_meta',
			'type'          => 'select',
			'label'         => esc_html__('Grid Area Border', 'servicemaster'),
			'description'   => esc_html__('Set border on grid area', 'servicemaster'),
			'parent'        => $logo_area_in_grid_header_centered_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'hide'       => array(
					''    => '#mkd_logo_area_in_grid_border_header_centered_container',
					'no'  => '#mkd_logo_area_in_grid_border_header_centered_container',
					'yes' => ''
				),
				'show'       => array(
					''    => '',
					'no'  => '',
					'yes' => '#mkd_logo_area_in_grid_border_header_centered_container'
				)
			)
		));

		$logo_area_in_grid_border_header_centered_container = servicemaster_mikado_add_admin_container(array(
			'type'            => 'container',
			'name'            => 'logo_area_in_grid_border_header_centered_container',
			'parent'          => $logo_area_in_grid_header_centered_container,
			'hidden_property' => 'mkd_logo_area_in_grid_border_header_centered_meta',
			'hidden_value'    => 'no',
			'hidden_values'   => array('', 'no')
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'mkd_logo_area_in_grid_border_color_header_centered_meta',
			'type'        => 'color',
			'label'       => esc_html__('Border Color', 'servicemaster'),
			'description' => esc_html__('Set border color for grid area', 'servicemaster'),
			'parent'      => $logo_area_in_grid_border_header_centered_container
		));


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_logo_area_background_color_header_centered_meta',
				'type'        => 'color',
				'label'       => esc_html__('Background Color', 'servicemaster'),
				'description' => esc_html__('Choose a background color for logo area', 'servicemaster'),
				'parent'      => $header_centered_type_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_logo_area_background_transparency_header_centered_meta',
				'type'        => 'text',
				'label'       => esc_html__('Transparency', 'servicemaster'),
				'description' => esc_html__('Choose a transparency for the logo area background color (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $header_centered_type_meta_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_logo_area_border_header_centered_meta',
			'type'          => 'select',
			'label'         => esc_html__('Logo Area Border', 'servicemaster'),
			'description'   => esc_html__('Set border on logo area', 'servicemaster'),
			'parent'        => $header_centered_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'hide'       => array(
					''    => '#mkd_logo_border_bottom_color_container',
					'no'  => '#mkd_logo_border_bottom_color_container',
					'yes' => ''
				),
				'show'       => array(
					''    => '',
					'no'  => '',
					'yes' => '#mkd_logo_border_bottom_color_container'
				)
			)
		));

		$border_bottom_color_centered_container = servicemaster_mikado_add_admin_container(array(
			'type'            => 'container',
			'name'            => 'logo_border_bottom_color_container',
			'parent'          => $header_centered_type_meta_container,
			'hidden_property' => 'mkd_logo_area_border_header_centered_meta',
			'hidden_value'    => 'no',
			'hidden_values'   => array('', 'no')
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'mkd_logo_area_border_color_header_centered_meta',
			'type'        => 'color',
			'label'       => esc_html__('Border Color', 'servicemaster'),
			'description' => esc_html__('Choose color of logo area bottom border', 'servicemaster'),
			'parent'      => $border_bottom_color_centered_container
		));

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_logo_wrapper_padding_header_centered_meta',
				'type'        => 'text',
				'label'       => esc_html__('Logo Padding', 'servicemaster'),
				'description' => esc_html__('Insert padding in format: 0px 0px 1px 0px', 'servicemaster'),
				'parent'      => $header_centered_type_meta_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_admin_section_title(array(
			'name'   => 'menu_area_centered_title',
			'parent' => $header_centered_type_meta_container,
			'title'  => esc_html__('Menu Area', 'servicemaster')
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_in_grid_header_centered_meta',
			'type'          => 'select',
			'label'         => esc_html__('Menu Area In Grid', 'servicemaster'),
			'description'   => esc_html__('Set menu area content to be in grid', 'servicemaster'),
			'parent'        => $header_centered_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => esc_html__('Default', 'servicemaster'),
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'hide'       => array(
					''    => '#mkd_menu_area_in_grid_header_centered_container',
					'no'  => '#mkd_menu_area_in_grid_header_centered_container',
					'yes' => ''
				),
				'show'       => array(
					''    => '',
					'no'  => '',
					'yes' => '#mkd_menu_area_in_grid_header_centered_container'
				)
			)
		));

		$menu_area_in_grid_header_centered_container = servicemaster_mikado_add_admin_container(array(
			'type'            => 'container',
			'name'            => 'menu_area_in_grid_header_centered_container',
			'parent'          => $header_centered_type_meta_container,
			'hidden_property' => 'mkd_menu_area_in_grid_header_centered_meta',
			'hidden_value'    => 'no',
			'hidden_values'   => array('', 'no')
		));


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_grid_background_color_header_centered_meta',
				'type'        => 'color',
				'label'       => esc_html__('Grid Background Color', 'servicemaster'),
				'description' => esc_html__('Set grid background color for menu area', 'servicemaster'),
				'parent'      => $menu_area_in_grid_header_centered_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_grid_background_transparency_header_centered_meta',
				'type'        => 'text',
				'label'       => esc_html__('Grid Background Transparency', 'servicemaster'),
				'description' => esc_html__('Set grid background transparency for menu area (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $menu_area_in_grid_header_centered_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_in_grid_shadow_header_centered_meta',
			'type'          => 'select',
			'label'         => esc_html__('Grid Area Shadow', 'servicemaster'),
			'description'   => esc_html__('Set shadow on grid area', 'servicemaster'),
			'parent'        => $menu_area_in_grid_header_centered_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_background_color_header_centered_meta',
				'type'        => 'color',
				'label'       => esc_html__('Background Color', 'servicemaster'),
				'description' => esc_html__('Choose a background color for menu area', 'servicemaster'),
				'parent'      => $header_centered_type_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_background_transparency_header_centered_meta',
				'type'        => 'text',
				'label'       => esc_html__('Transparency', 'servicemaster'),
				'description' => esc_html__('Choose a transparency for the menu area background color (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $header_centered_type_meta_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_shadow_header_centered_meta',
			'type'          => 'select',
			'label'         => esc_html__('Menu Area Shadow', 'servicemaster'),
			'description'   => esc_html__('Set shadow on menu area', 'servicemaster'),
			'parent'        => $header_centered_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));


		$header_standard_extended_type_meta_container = servicemaster_mikado_add_admin_container(
			array_merge(
				array(
					'parent'          => $header_meta_box,
					'name'            => 'mkd_header_standard_extended_type_meta_container',
					'hidden_property' => 'mkd_header_type_meta',

				),
				$temp_array_standard_extended
			)
		);

		servicemaster_mikado_add_admin_section_title(array(
			'name'   => 'logo_area_standard_extended_title',
			'parent' => $header_standard_extended_type_meta_container,
			'title'  => esc_html__('Logo Area', 'servicemaster')
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_logo_area_in_grid_header_standard_extended_meta',
			'type'          => 'select',
			'label'         => esc_html__('Logo Area In Grid', 'servicemaster'),
			'description'   => esc_html__('Set logo area content to be in grid', 'servicemaster'),
			'parent'        => $header_standard_extended_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => esc_html__('Default', 'servicemaster'),
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'hide'       => array(
					''    => '#mkd_logo_area_in_grid_header_standard_extended_container',
					'no'  => '#mkd_logo_area_in_grid_header_standard_extended_container',
					'yes' => ''
				),
				'show'       => array(
					''    => '',
					'no'  => '',
					'yes' => '#mkd_logo_area_in_grid_header_standard_extended_container'
				)
			)
		));

		$logo_area_in_grid_header_standard_extended_container = servicemaster_mikado_add_admin_container(array(
			'type'            => 'container',
			'name'            => 'logo_area_in_grid_header_standard_extended_container',
			'parent'          => $header_standard_extended_type_meta_container,
			'hidden_property' => 'mkd_logo_area_in_grid_header_standard_extended_meta',
			'hidden_value'    => 'no',
			'hidden_values'   => array('', 'no')
		));


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_logo_area_grid_background_color_header_standard_extended_meta',
				'type'        => 'color',
				'label'       => esc_html__('Grid Background Color', 'servicemaster'),
				'description' => esc_html__('Set grid background color for logo area', 'servicemaster'),
				'parent'      => $logo_area_in_grid_header_standard_extended_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_logo_area_grid_background_transparency_header_standard_extended_meta',
				'type'        => 'text',
				'label'       => esc_html__('Grid Background Transparency', 'servicemaster'),
				'description' => esc_html__('Set grid background transparency for logo area (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $logo_area_in_grid_header_standard_extended_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_logo_area_in_grid_border_header_standard_extended_meta',
			'type'          => 'select',
			'label'         => esc_html__('Grid Area Border', 'servicemaster'),
			'description'   => esc_html__('Set border on grid area', 'servicemaster'),
			'parent'        => $logo_area_in_grid_header_standard_extended_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'hide'       => array(
					''    => '#mkd_logo_area_in_grid_border_header_standard_extended_container',
					'no'  => '#mkd_logo_area_in_grid_border_header_standard_extended_container',
					'yes' => ''
				),
				'show'       => array(
					''    => '',
					'no'  => '',
					'yes' => '#mkd_logo_area_in_grid_border_header_standard_extended_container'
				)
			)
		));

		$logo_area_in_grid_border_header_standard_extended_container = servicemaster_mikado_add_admin_container(array(
			'type'            => 'container',
			'name'            => 'logo_area_in_grid_border_header_standard_extended_container',
			'parent'          => $logo_area_in_grid_header_standard_extended_container,
			'hidden_property' => 'mkd_logo_area_in_grid_border_header_standard_extended_meta',
			'hidden_value'    => 'no',
			'hidden_values'   => array('', 'no')
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'mkd_logo_area_in_grid_border_color_header_standard_extended_meta',
			'type'        => 'color',
			'label'       => esc_html__('Border Color', 'servicemaster'),
			'description' => esc_html__('Set border color for grid area', 'servicemaster'),
			'parent'      => $logo_area_in_grid_border_header_standard_extended_container
		));


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_logo_area_background_color_header_standard_extended_meta',
				'type'        => 'color',
				'label'       => esc_html__('Background Color', 'servicemaster'),
				'description' => esc_html__('Choose a background color for logo area', 'servicemaster'),
				'parent'      => $header_standard_extended_type_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_logo_area_background_transparency_header_standard_extended_meta',
				'type'        => 'text',
				'label'       => esc_html__('Transparency', 'servicemaster'),
				'description' => esc_html__('Choose a transparency for the logo area background color (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $header_standard_extended_type_meta_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_logo_area_border_header_standard_extended_meta',
			'type'          => 'select',
			'label'         => esc_html__('Logo Area Border', 'servicemaster'),
			'description'   => esc_html__('Set border on logo area', 'servicemaster'),
			'parent'        => $header_standard_extended_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'hide'       => array(
					''    => '#mkd_logo_border_bottom_color_standard_extended_container',
					'no'  => '#mkd_logo_border_bottom_color_standard_extended_container',
					'yes' => ''
				),
				'show'       => array(
					''    => '',
					'no'  => '',
					'yes' => '#mkd_logo_border_bottom_color_standard_extended_container'
				)
			)
		));

		$border_bottom_color_standard_extended_container = servicemaster_mikado_add_admin_container(array(
			'type'            => 'container',
			'name'            => 'logo_border_bottom_color_standard_extended_container',
			'parent'          => $header_standard_extended_type_meta_container,
			'hidden_property' => 'mkd_logo_area_border_header_standard_extended_meta',
			'hidden_value'    => 'no',
			'hidden_values'   => array('', 'no')
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'mkd_logo_area_border_color_header_standard_extended_meta',
			'type'        => 'color',
			'label'       => esc_html__('Border Color', 'servicemaster'),
			'description' => esc_html__('Choose color of logo area bottom border', 'servicemaster'),
			'parent'      => $border_bottom_color_standard_extended_container
		));

		servicemaster_mikado_add_admin_section_title(array(
			'name'   => 'menu_area_standard_extended_title',
			'parent' => $header_standard_extended_type_meta_container,
			'title'  => esc_html__('Menu Area', 'servicemaster')
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_in_grid_header_standard_extended_meta',
			'type'          => 'select',
			'label'         => esc_html__('Menu Area In Grid', 'servicemaster'),
			'description'   => esc_html__('Set menu area content to be in grid', 'servicemaster'),
			'parent'        => $header_standard_extended_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => esc_html__('Default', 'servicemaster'),
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'hide'       => array(
					''    => '#mkd_menu_area_in_grid_header_standard_extended_container',
					'no'  => '#mkd_menu_area_in_grid_header_standard_extended_container',
					'yes' => ''
				),
				'show'       => array(
					''    => '',
					'no'  => '',
					'yes' => '#mkd_menu_area_in_grid_header_standard_extended_container'
				)
			)
		));

		$menu_area_in_grid_header_standard_extended_container = servicemaster_mikado_add_admin_container(array(
			'type'            => 'container',
			'name'            => 'menu_area_in_grid_header_standard_extended_container',
			'parent'          => $header_standard_extended_type_meta_container,
			'hidden_property' => 'mkd_menu_area_in_grid_header_standard_extended_meta',
			'hidden_value'    => 'no',
			'hidden_values'   => array('', 'no')
		));


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_grid_background_color_header_standard_extended_meta',
				'type'        => 'color',
				'label'       => esc_html__('Grid Background Color', 'servicemaster'),
				'description' => esc_html__('Set grid background color for menu area', 'servicemaster'),
				'parent'      => $menu_area_in_grid_header_standard_extended_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_grid_background_transparency_header_standard_extended_meta',
				'type'        => 'text',
				'label'       => esc_html__('Grid Background Transparency', 'servicemaster'),
				'description' => esc_html__('Set grid background transparency for menu area (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $menu_area_in_grid_header_standard_extended_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_in_grid_shadow_header_standard_extended_meta',
			'type'          => 'select',
			'label'         => esc_html__('Grid Area Shadow', 'servicemaster'),
			'description'   => esc_html__('Set shadow on grid area', 'servicemaster'),
			'parent'        => $menu_area_in_grid_header_standard_extended_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_background_color_header_standard_extended_meta',
				'type'        => 'color',
				'label'       => esc_html__('Background Color', 'servicemaster'),
				'description' => esc_html__('Choose a background color for menu area', 'servicemaster'),
				'parent'      => $header_standard_extended_type_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_background_transparency_header_standard_extended_meta',
				'type'        => 'text',
				'label'       => esc_html__('Transparency', 'servicemaster'),
				'description' => esc_html__('Choose a transparency for the menu area background color (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $header_standard_extended_type_meta_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_shadow_header_standard_extended_meta',
			'type'          => 'select',
			'label'         => esc_html__('Menu Area Shadow', 'servicemaster'),
			'description'   => esc_html__('Set shadow on menu area', 'servicemaster'),
			'parent'        => $header_standard_extended_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));

		$header_box_type_meta_container = servicemaster_mikado_add_admin_container(
			array_merge(
				array(
					'parent'          => $header_meta_box,
					'name'            => 'mkd_header_box_type_meta_container',
					'hidden_property' => 'mkd_header_type_meta',

				),
				$temp_array_box
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_position_header_box_meta',
			'type'          => 'select',
			'label'         => esc_html__('Menu Area position', 'servicemaster'),
			'description'   => esc_html__('Set menu area position', 'servicemaster'),
			'parent'        => $header_box_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''       => esc_html__('Default', 'servicemaster'),
				'center' => esc_html__('Center', 'servicemaster'),
				'left'   => esc_html__('Left', 'servicemaster'),
				'right'  => esc_html__('Right', 'servicemaster'),
			)
		));

		servicemaster_mikado_add_meta_box_field(
			array(
				'parent'        => $header_box_type_meta_container,
				'type'          => 'select',
				'name'          => 'mkd_top_area_gradient_header_box_meta',
				'default_value' => '',
				'label'         => esc_html__('Top Area Gradient Background', 'servicemaster'),
				'description'   => esc_html__('Set gradient background for top menu area', 'servicemaster'),
				'options'       => servicemaster_mikado_get_gradient_left_to_right_styles('', true)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_grid_background_color_header_box_meta',
				'type'        => 'color',
				'label'       => esc_html__('Background Color', 'servicemaster'),
				'description' => esc_html__('Set grid background color for header area', 'servicemaster'),
				'parent'      => $header_box_type_meta_container
			)
		);


		$header_tabbed_type_meta_container = servicemaster_mikado_add_admin_container(
			array_merge(
				array(
					'parent'          => $header_meta_box,
					'name'            => 'mkd_header_tabbed_type_meta_container',
					'hidden_property' => 'mkd_header_type_meta',

				),
				$temp_array_tabbed
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_background_color_header_tabbed_meta',
				'type'        => 'color',
				'label'       => esc_html__('Background Color', 'servicemaster'),
				'description' => esc_html__('Choose a background color for header area', 'servicemaster'),
				'parent'      => $header_tabbed_type_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_menu_area_background_transparency_header_tabbed_meta',
				'type'        => 'text',
				'label'       => esc_html__('Transparency', 'servicemaster'),
				'description' => esc_html__('Choose a transparency for the header background color (0 = fully transparent, 1 = opaque)', 'servicemaster'),
				'parent'      => $header_tabbed_type_meta_container,
				'args'        => array(
					'col_width' => 2
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_menu_area_shadow_header_tabbed_meta',
			'type'          => 'select',
			'label'         => esc_html__('Header Area Shadow', 'servicemaster'),
			'description'   => esc_html__('Set shadow on header area', 'servicemaster'),
			'parent'        => $header_tabbed_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));

		$top_bar_container = servicemaster_mikado_add_admin_container(
			array_merge(
				array(
					'parent'          => $header_meta_box,
					'name'            => 'mkd_top_bar_container_meta_container',
					'hidden_property' => 'mkd_header_type_meta',

				),
				$temp_array_top_header
			)
		);

		servicemaster_mikado_add_admin_section_title(array(
			'name'   => 'top_bar_section_title',
			'parent' => $top_bar_container,
			'title'  => esc_html__('Top Bar', 'servicemaster')
		));

		$top_bar_global_option = servicemaster_mikado_options()->getOptionValue('top_bar');

		$top_bar_default_dependency = array(
			'' => '#mkd_top_bar_container_no_style'
		);

		$top_bar_show_array = array(
			'yes' => '#mkd_top_bar_container_no_style'
		);

		$top_bar_hide_array = array(
			'no' => '#mkd_top_bar_container_no_style'
		);

		if ($top_bar_global_option === 'yes') {
			$top_bar_show_array = array_merge($top_bar_show_array, $top_bar_default_dependency);
			$top_bar_container_hide_array = array('no');
		} else {
			$top_bar_hide_array = array_merge($top_bar_hide_array, $top_bar_default_dependency);
			$top_bar_container_hide_array = array('', 'no');
		}


		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_top_bar_meta',
			'type'          => 'select',
			'label'         => esc_html__('Enable Top Bar on This Page', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will enable top bar on this page', 'servicemaster'),
			'parent'        => $top_bar_container,
			'default_value' => '',
			'options'       => array(
				''    => esc_html__('Default', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster'),
				'no'  => esc_html__('No', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'show'       => $top_bar_show_array,
				'hide'       => $top_bar_hide_array
			)
		));

		$top_bar_container = servicemaster_mikado_add_admin_container_no_style(array(
			'name'            => 'top_bar_container_no_style',
			'parent'          => $top_bar_container,
			'hidden_property' => 'mkd_top_bar_meta',
			'hidden_value'    => 'no',
			'hidden_values'   => $top_bar_container_hide_array
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_top_bar_in_grid_meta',
			'type'          => 'select',
			'label'         => esc_html__('Top Bar In Grid', 'servicemaster'),
			'description'   => esc_html__('Set top bar content to be in grid', 'servicemaster'),
			'parent'        => $top_bar_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'    => 'mkd_top_bar_skin_meta',
			'type'    => 'select',
			'label'   => esc_html__('Top Bar Skin', 'servicemaster'),
			'options' => array(
				''      => esc_html__('Default', 'servicemaster'),
				'light' => esc_html__('White', 'servicemaster'),
				'dark'  => esc_html__('Black', 'servicemaster'),
				'gray'  => esc_html__('Gray', 'servicemaster'),
			),
			'parent'  => $top_bar_container
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'   => 'mkd_top_bar_background_color_meta',
			'type'   => 'color',
			'label'  => esc_html__('Top Bar Background Color', 'servicemaster'),
			'parent' => $top_bar_container
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'mkd_top_bar_background_transparency_meta',
			'type'        => 'text',
			'label'       => esc_html__('Top Bar Background Color Transparency', 'servicemaster'),
			'description' => esc_html__('Set top bar background color transparenct. Value should be between 0 and 1', 'servicemaster'),
			'parent'      => $top_bar_container,
			'args'        => array(
				'col_width' => 3
			)
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_top_bar_border_meta',
			'type'          => 'select',
			'label'         => esc_html__('Top Bar Border', 'servicemaster'),
			'description'   => esc_html__('Set border on top bar', 'servicemaster'),
			'parent'        => $top_bar_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'hide'       => array(
					''    => '#mkd_top_bar_border_container',
					'no'  => '#mkd_top_bar_border_container',
					'yes' => ''
				),
				'show'       => array(
					''    => '',
					'no'  => '',
					'yes' => '#mkd_top_bar_border_container'
				)
			)
		));

		$top_bar_border_container = servicemaster_mikado_add_admin_container(array(
			'type'            => 'container',
			'name'            => 'top_bar_border_container',
			'parent'          => $top_bar_container,
			'hidden_property' => 'mkd_top_bar_border_meta',
			'hidden_value'    => 'no',
			'hidden_values'   => array('', 'no')
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'mkd_top_bar_border_color_meta',
			'type'        => 'color',
			'label'       => esc_html__('Border Color', 'servicemaster'),
			'description' => esc_html__('Choose color for top bar border', 'servicemaster'),
			'parent'      => $top_bar_border_container
		));


		$top_line_container = servicemaster_mikado_add_admin_container(
			array_merge(
				array(
					'parent'          => $header_meta_box,
					'name'            => 'mkd_top_line_container_meta_container',
					'hidden_property' => 'mkd_header_type_meta',

				),
				$temp_array_top_line
			)
		);

		servicemaster_mikado_add_admin_section_title(array(
			'name'   => 'top_line_section_title',
			'parent' => $top_line_container,
			'title'  => esc_html__('Top LIne', 'servicemaster')
		));

		$top_line_global_option = servicemaster_mikado_options()->getOptionValue('top_line');

		$top_line_default_dependency = array(
			'' => '#mkd_top_line_container_no_style'
		);

		$top_line_show_array = array(
			'yes' => '#mkd_top_line_container_no_style'
		);

		$top_line_hide_array = array(
			'no' => '#mkd_top_line_container_no_style'
		);

		if ($top_line_global_option === 'yes') {
			$top_line_show_array = array_merge($top_line_show_array, $top_line_default_dependency);
			$top_line_container_hide_array = array('no');
		} else {
			$top_line_hide_array = array_merge($top_line_hide_array, $top_line_default_dependency);
			$top_line_container_hide_array = array('', 'no');
		}


		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_top_line_meta',
			'type'          => 'select',
			'label'         => esc_html__('Enable Top Line on This Page', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will enable top line on this page', 'servicemaster'),
			'parent'        => $top_line_container,
			'default_value' => '',
			'options'       => array(
				''    => esc_html__('Default', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster'),
				'no'  => esc_html__('No', 'servicemaster')
			),
			'args'          => array(
				'dependence' => true,
				'show'       => $top_line_show_array,
				'hide'       => $top_line_hide_array
			)
		));

		$top_line_container = servicemaster_mikado_add_admin_container_no_style(array(
			'name'            => 'top_line_container_no_style',
			'parent'          => $top_line_container,
			'hidden_property' => 'mkd_top_line_meta',
			'hidden_value'    => 'no',
			'hidden_values'   => $top_line_container_hide_array
		));

		$group_top_line_colors = servicemaster_mikado_add_admin_group(array(
			'name'        => 'group_line_colors',
			'title'       => esc_html__('Top Line Colors', 'servicemaster'),
			'description' => esc_html__('Define colors for top line (not all of them are mandatory)', 'servicemaster'),
			'parent'      => $top_line_container
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'   => 'mkd_top_line_color_1_meta',
			'type'   => 'colorsimple',
			'label'  => esc_html__('Color 1', 'servicemaster'),
			'parent' => $group_top_line_colors
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'   => 'mkd_top_line_color_2_meta',
			'type'   => 'colorsimple',
			'label'  => esc_html__('Color 2', 'servicemaster'),
			'parent' => $group_top_line_colors
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'   => 'mkd_top_line_color_3_meta',
			'type'   => 'colorsimple',
			'label'  => esc_html__('Color 3', 'servicemaster'),
			'parent' => $group_top_line_colors
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'   => 'mkd_top_line_color_4_meta',
			'type'   => 'colorsimple',
			'label'  => esc_html__('Color 4', 'servicemaster'),
			'parent' => $group_top_line_colors
		));

		$header_vertical_type_meta_container = servicemaster_mikado_add_admin_container(
			array_merge(
				array(
					'parent'          => $header_meta_box,
					'name'            => 'mkd_header_vertical_type_meta_container',
					'hidden_property' => 'mkd_header_type_meta'
				),
				$temp_array_vertical
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'mkd_vertical_header_background_color_meta',
			'type'        => 'color',
			'label'       => esc_html__('Background Color', 'servicemaster'),
			'description' => esc_html__('Set background color for vertical menu', 'servicemaster'),
			'parent'      => $header_vertical_type_meta_container
		));

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_vertical_header_background_image_meta',
				'type'          => 'image',
				'default_value' => '',
				'label'         => esc_html__('Background Image', 'servicemaster'),
				'description'   => esc_html__('Set background image for vertical menu', 'servicemaster'),
				'parent'        => $header_vertical_type_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_disable_vertical_header_background_image_meta',
				'type'          => 'yesno',
				'default_value' => 'no',
				'label'         => esc_html__('Disable Background Image', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will hide background image in Vertical Menu', 'servicemaster'),
				'parent'        => $header_vertical_type_meta_container
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_vertical_header_shadow_meta',
			'type'          => 'select',
			'label'         => esc_html__('Shadow', 'servicemaster'),
			'description'   => esc_html__('Set shadow on vertical menu', 'servicemaster'),
			'parent'        => $header_vertical_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_vertical_header_center_content_meta',
			'type'          => 'select',
			'label'         => esc_html__('Center Content', 'servicemaster'),
			'description'   => esc_html__('Set content in vertical center', 'servicemaster'),
			'parent'        => $header_vertical_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));

		$header_vertical_compact_type_meta_container = servicemaster_mikado_add_admin_container(
			array_merge(
				array(
					'parent'          => $header_meta_box,
					'name'            => 'mkd_header_vertical_compact_type_meta_container',
					'hidden_property' => 'mkd_header_type_meta'
				),
				$temp_array_vertical_compact
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'        => 'mkd_vertical_compact_header_background_color_meta',
			'type'        => 'color',
			'label'       => esc_html__('Background Color', 'servicemaster'),
			'description' => esc_html__('Set background color for vertical compact menu', 'servicemaster'),
			'parent'      => $header_vertical_compact_type_meta_container
		));

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_vertical_compact_header_shadow_meta',
			'type'          => 'select',
			'label'         => esc_html__('Shadow', 'servicemaster'),
			'description'   => esc_html__('Set shadow on vertical menu', 'servicemaster'),
			'parent'        => $header_vertical_compact_type_meta_container,
			'default_value' => '',
			'options'       => array(
				''    => '',
				'no'  => esc_html__('No', 'servicemaster'),
				'yes' => esc_html__('Yes', 'servicemaster')
			)
		));

	}

	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_header_meta_box_map');
}

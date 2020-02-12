<?php

if (!function_exists('servicemaster_mikado_general_meta_box_map')) {
	function servicemaster_mikado_general_meta_box_map() {

		$general_meta_box = servicemaster_mikado_add_meta_box(
			array(
				'scope' => array('page', 'portfolio-item', 'post'),
				'title' => esc_html__('General', 'servicemaster'),
				'name'  => 'general_meta'
			)
		);


		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_first_color_meta',
				'type'          => 'color',
				'default_value' => '',
				'label'         => esc_html__('Page Main Color', 'servicemaster'),
				'description'   => esc_html__('Choose page main color', 'servicemaster'),
				'parent'        => $general_meta_box
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_page_background_color_meta',
				'type'          => 'color',
				'default_value' => '',
				'label'         => esc_html__('Page Background Color', 'servicemaster'),
				'description'   => esc_html__('Choose background color for page content', 'servicemaster'),
				'parent'        => $general_meta_box
			)
		);

		servicemaster_mikado_add_meta_box_field(array(
			'name'          => 'mkd_comments_background_color_meta',
			'type'          => 'color',
			'label'         => esc_html__('Comments Background Color', 'servicemaster'),
			'description'   => esc_html__('Choose comments background color', 'servicemaster'),
			'parent'        => $general_meta_box,
		));

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_page_padding_meta',
				'type'          => 'text',
				'default_value' => '',
				'label'         => esc_html__('Page Padding', 'servicemaster'),
				'description'   => esc_html__('Insert padding in format 10px 10px 10px 10px', 'servicemaster'),
				'parent'        => $general_meta_box
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_page_content_behind_header_meta',
				'type'          => 'yesno',
				'default_value' => 'no',
				'label'         => esc_html__('Always put content behind header', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will put page content behind page header', 'servicemaster'),
				'parent'        => $general_meta_box,
				'args'          => array(
					'suffix' => 'px'
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_enable_paspartu_meta',
				'type'          => 'select',
				'default_value' => '',
				'label'         => esc_html__('Passepartout', 'servicemaster'),
				'description'   => esc_html__('Enabling this option will display passepartout around site content', 'servicemaster'),
				'parent'        => $general_meta_box,
				'options'       => array(
					''    => '',
					'no'  => esc_html__('No', 'servicemaster'),
					'yes' => esc_html__('Yes', 'servicemaster')
				),
				'args'          => array(
					'dependence' => true,
					'hide'       => array(
						''    => '',
						'no'  => '#mkd_mkd_paspartu_meta_container',
						'yes' => ''
					),
					'show'       => array(
						''    => '#mkd_mkd_paspartu_meta_container',
						'no'  => '',
						'yes' => '#mkd_mkd_paspartu_meta_container'
					)
				)
			)
		);

		$paspartu_meta_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $general_meta_box,
				'name'            => 'mkd_paspartu_meta_container',
				'hidden_property' => 'mkd_enable_paspartu_meta',
				'hidden_values'   => array('', 'no')
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_paspartu_color_meta',
				'type'          => 'color',
				'default_value' => '',
				'label'         => esc_html__('Passepartout Color', 'servicemaster'),
				'description'   => esc_html__('Choose passepartout color. Default value is #fff', 'servicemaster'),
				'parent'        => $paspartu_meta_container,
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_paspartu_size_meta',
				'type'          => 'text',
				'default_value' => '',
				'label'         => esc_html__('Passepartout Size', 'servicemaster'),
				'description'   => esc_html__('Enter size amount for passepartout.Default value is 15px', 'servicemaster'),
				'parent'        => $paspartu_meta_container,
				'args'          => array(
					'col_width' => 3
				)
			)
		);

        servicemaster_mikado_add_meta_box_field(
            array(
                'name'          => 'mkd_page_slider_meta',
                'type'          => 'text',
                'default_value' => '',
                'label'         => esc_html__('Slider Shortcode', 'servicemaster'),
                'description'   => esc_html__('Paste your slider shortcode here', 'servicemaster'),
                'parent'        => $general_meta_box
            )
        );

		if (servicemaster_mikado_options()->getOptionValue('smooth_pt_true_ajax') === 'yes') {
			servicemaster_mikado_add_meta_box_field(
				array(
					'name'          => 'mkd_page_transition_type',
					'type'          => 'selectblank',
					'label'         => esc_html__('Page Transition', 'servicemaster'),
					'description'   => esc_html__('Choose the type of transition to this page', 'servicemaster'),
					'parent'        => $general_meta_box,
					'default_value' => '',
					'options'       => array(
						'no-animation' => esc_html__('No animation', 'servicemaster'),
						'fade'         => esc_html__('Fade', 'servicemaster')
					)
				)
			);
		}

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_page_comments_meta',
				'type'        => 'selectblank',
				'label'       => esc_html__('Show Comments', 'servicemaster'),
				'description' => esc_html__('Enabling this option will show comments on your page', 'servicemaster'),
				'parent'      => $general_meta_box,
                'default_value' => '',
				'options'     => array(
					'yes' => esc_html__('Yes', 'servicemaster'),
					'no'  => esc_html__('No', 'servicemaster')
				)
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_boxed_meta',
				'type'          => 'select',
				'default_value' => '',
				'label'         => esc_html__('Boxed Layout', 'servicemaster'),
				'description'   => '',
				'parent'        => $general_meta_box,
				'options'       => array(
					''    => '',
					'yes' => esc_html__('Yes', 'servicemaster'),
					'no'  => esc_html__('No', 'servicemaster'),
				),
				'args'          => array(
					"dependence" => true,
					'show'       => array(
						''    => '',
						'yes' => '#mkd_mkd_boxed_container_meta',
						'no'  => '',

					),
					'hide'       => array(
						''    => '#mkd_mkd_boxed_container_meta',
						'yes' => '',
						'no'  => '#mkd_mkd_boxed_container_meta',
					)
				)
			)
		);

		$boxed_container = servicemaster_mikado_add_admin_container(
			array(
				'parent'          => $general_meta_box,
				'name'            => 'mkd_boxed_container_meta',
				'hidden_property' => 'mkd_boxed_meta',
				'hidden_values'   => array('', 'no')
			)
		);

        servicemaster_mikado_add_meta_box_field(
            array(
                'name'        => 'mkd_header_footer_in_box_meta',
                'type'          => 'select',
                'default_value' => '',
                'label'         => esc_html__('Boxed Layout Header/Footer', 'servicemaster'),
                'description'   => esc_html__('Choose if the Header and the Footer will be placed in a boxed layout or fullwidth.', 'servicemaster'),
                'parent'      => $boxed_container,
                'options'       => array(
                    ''    => '',
                    'yes' => esc_html__('Yes', 'servicemaster'),
                    'no'  => esc_html__('No', 'servicemaster'),
                )
            )
        );

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_page_background_color_in_box_meta',
				'type'        => 'color',
				'label'       => esc_html__('Page Background Color', 'servicemaster'),
				'description' => esc_html__('Choose the page background color outside box.', 'servicemaster'),
				'parent'      => $boxed_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_boxed_pattern_background_image_meta',
				'type'        => 'image',
				'label'       => esc_html__('Background Pattern', 'servicemaster'),
				'description' => esc_html__('Choose an image to be used as background pattern', 'servicemaster'),
				'parent'      => $boxed_container
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'        => 'mkd_boxed_background_image_meta',
				'type'        => 'image',
				'label'       => esc_html__('Background Image', 'servicemaster'),
				'description' => esc_html__('Choose an image to be displayed in background', 'servicemaster'),
				'parent'      => $boxed_container,
			)
		);

		servicemaster_mikado_add_meta_box_field(
			array(
				'name'          => 'mkd_boxed_background_image_attachment_meta',
				'type'          => 'select',
				'default_value' => 'fixed',
				'label'         => esc_html__('Background Image Attachment', 'servicemaster'),
				'description'   => esc_html__('Choose background image attachment if background image option is set', 'servicemaster'),
				'parent'        => $boxed_container,
				'options'       => array(
					'fixed'  => esc_html__('Fixed', 'servicemaster'),
					'scroll' => esc_html__('Scroll', 'servicemaster')
				)
			)
		);

	}

	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_general_meta_box_map');
}
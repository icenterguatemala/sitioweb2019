<?php

if(!function_exists('servicemaster_mikado_fullscreen_menu_options_map')) {

    function servicemaster_mikado_fullscreen_menu_options_map() {

        $fullscreen_panel = servicemaster_mikado_add_admin_panel(
            array(
                'title'           => esc_html__('Fullscreen Menu', 'servicemaster'),
                'name'            => 'panel_fullscreen_menu',
                'page'            => '_header_page',
                'hidden_property' => 'header_type',
                'hidden_value'    => '',
                'hidden_values'   => array(
                    'header-standard',
                    'header-standard-extended',
                    'header-box',
                    'header-vertical',
                    'header-divided',
                    'header-centered',
                    'header-tabbed',
                    'header-vertical-compact',
                )
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $fullscreen_panel,
                'type'          => 'select',
                'name'          => 'fullscreen_menu_animation_style',
                'default_value' => 'fade-push-text-top',
                'label'         => esc_html__('Fullscreen Menu Overlay Animation', 'servicemaster'),
                'description'   => esc_html__('Choose animation type for fullscreen menu overlay', 'servicemaster'),
                'options'       => array(
                    'fade-push-text-top'   => esc_html__('Fade Push Text Top', 'servicemaster'),
                    'fade-push-text-right' => esc_html__('Fade Push Text Right', 'servicemaster'),
                    'fade-text-scaledown'  => esc_html__('Fade Text Scaledown', 'servicemaster')
                )
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $fullscreen_panel,
                'type'          => 'image',
                'name'          => 'fullscreen_logo',
                'default_value' => '',
                'label'         => esc_html__('Logo in Fullscreen Menu Overlay', 'servicemaster'),
                'description'   => esc_html__('Place logo in top left corner of fullscreen menu overlay', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $fullscreen_panel,
                'type'          => 'yesno',
                'name'          => 'fullscreen_in_grid',
                'default_value' => 'no',
                'label'         => esc_html__('Fullscreen Menu in Grid', 'servicemaster'),
                'description'   => esc_html__('Enabling this option will put fullscreen menu content in grid', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $fullscreen_panel,
                'type'          => 'selectblank',
                'name'          => 'fullscreen_alignment',
                'default_value' => '',
                'label'         => esc_html__('Fullscreen Menu Alignment', 'servicemaster'),
                'description'   => esc_html__('Choose alignment for fullscreen menu content', 'servicemaster'),
                'options'       => array(
                    "left"   => esc_html__("Left", 'servicemaster'),
                    "center" => esc_html__("Center", 'servicemaster'),
                    "right"  => esc_html__("Right", 'servicemaster')
                )
            )
        );

        $background_group = servicemaster_mikado_add_admin_group(
            array(
                'parent'      => $fullscreen_panel,
                'name'        => 'background_group',
                'title'       => esc_html__('Background', 'servicemaster'),
                'description' => esc_html__('Select a background color and transparency for Fullscreen Menu (0 = fully transparent, 1 = opaque)', 'servicemaster')

            )
        );

        $background_group_row = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $background_group,
                'name'   => 'background_group_row'
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $background_group_row,
                'type'          => 'colorsimple',
                'name'          => 'fullscreen_menu_background_color',
                'default_value' => '',
                'label'         => esc_html__('Background Color', 'servicemaster')
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $background_group_row,
                'type'          => 'textsimple',
                'name'          => 'fullscreen_menu_background_transparency',
                'default_value' => '',
                'label'         => esc_html__('Transparency (values:0-1)', 'servicemaster')
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $fullscreen_panel,
                'type'          => 'image',
                'name'          => 'fullscreen_menu_background_image',
                'default_value' => '',
                'label'         => esc_html__('Background Image', 'servicemaster'),
                'description'   => esc_html__('Choose a background image for Fullscreen Menu background', 'servicemaster')
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $fullscreen_panel,
                'type'          => 'image',
                'name'          => 'fullscreen_menu_pattern_image',
                'default_value' => '',
                'label'         => esc_html__('Pattern Background Image', 'servicemaster'),
                'description'   => esc_html__('Choose a pattern image for Fullscreen Menu background', 'servicemaster')
            )
        );

//1st level style group
        $first_level_style_group = servicemaster_mikado_add_admin_group(
            array(
                'parent'      => $fullscreen_panel,
                'name'        => 'first_level_style_group',
                'title'       => esc_html__('1st Level Style', 'servicemaster'),
                'description' => esc_html__('Define styles for 1st level in Fullscreen Menu', 'servicemaster')
            )
        );

        $first_level_style_row1 = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $first_level_style_group,
                'name'   => 'first_level_style_row1'
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $first_level_style_row1,
                'type'          => 'colorsimple',
                'name'          => 'fullscreen_menu_color',
                'default_value' => '',
                'label'         => esc_html__('Text Color', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $first_level_style_row1,
                'type'          => 'colorsimple',
                'name'          => 'fullscreen_menu_hover_color',
                'default_value' => '',
                'label'         => esc_html__('Hover Text Color', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $first_level_style_row1,
                'type'          => 'colorsimple',
                'name'          => 'fullscreen_menu_active_color',
                'default_value' => '',
                'label'         => esc_html__('Active Text Color', 'servicemaster'),
            )
        );

        $first_level_style_row2 = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $first_level_style_group,
                'name'   => 'first_level_style_row2'
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $first_level_style_row2,
                'type'          => 'colorsimple',
                'name'          => 'fullscreen_menu_hover_background_color',
                'default_value' => '',
                'label'         => esc_html__('Background Hover Color', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $first_level_style_row2,
                'type'          => 'colorsimple',
                'name'          => 'fullscreen_menu_active_background_color',
                'default_value' => '',
                'label'         => esc_html__('Background Active Color', 'servicemaster'),
            )
        );

        $first_level_style_row3 = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $first_level_style_group,
                'name'   => 'first_level_style_row3'
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $first_level_style_row3,
                'type'          => 'fontsimple',
                'name'          => 'fullscreen_menu_google_fonts',
                'default_value' => '-1',
                'label'         => esc_html__('Font Family', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $first_level_style_row3,
                'type'          => 'textsimple',
                'name'          => 'fullscreen_menu_fontsize',
                'default_value' => '',
                'label'         => esc_html__('Font Size', 'servicemaster'),
                'args'          => array(
                    'suffix' => 'px'
                )
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $first_level_style_row3,
                'type'          => 'textsimple',
                'name'          => 'fullscreen_menu_lineheight',
                'default_value' => '',
                'label'         => esc_html__('Line Height', 'servicemaster'),
                'args'          => array(
                    'suffix' => 'px'
                )
            )
        );

        $first_level_style_row4 = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $first_level_style_group,
                'name'   => 'first_level_style_row4'
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $first_level_style_row4,
                'type'          => 'selectblanksimple',
                'name'          => 'fullscreen_menu_fontstyle',
                'default_value' => '',
                'label'         => esc_html__('Font Style', 'servicemaster'),
                'options'       => servicemaster_mikado_get_font_style_array()
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $first_level_style_row4,
                'type'          => 'selectblanksimple',
                'name'          => 'fullscreen_menu_fontweight',
                'default_value' => '',
                'label'         => esc_html__('Font Weight', 'servicemaster'),
                'options'       => servicemaster_mikado_get_font_weight_array()
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $first_level_style_row4,
                'type'          => 'textsimple',
                'name'          => 'fullscreen_menu_letterspacing',
                'default_value' => '',
                'label'         => esc_html__('Letter Spacing', 'servicemaster'),
                'args'          => array(
                    'suffix' => 'px'
                )
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $first_level_style_row4,
                'type'          => 'selectblanksimple',
                'name'          => 'fullscreen_menu_texttransform',
                'default_value' => '',
                'label'         => esc_html__('Text Transform', 'servicemaster'),
                'options'       => servicemaster_mikado_get_text_transform_array()
            )
        );

//2nd level style group
        $second_level_style_group = servicemaster_mikado_add_admin_group(
            array(
                'parent'      => $fullscreen_panel,
                'name'        => 'second_level_style_group',
                'title'       => esc_html__('2nd Level Style', 'servicemaster'),
                'description' => esc_html__('Define styles for 2nd level in Fullscreen Menu', 'servicemaster')
            )
        );

        $second_level_style_row1 = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $second_level_style_group,
                'name'   => 'second_level_style_row1'
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $second_level_style_row1,
                'type'          => 'colorsimple',
                'name'          => 'fullscreen_menu_color_2nd',
                'default_value' => '',
                'label'         => esc_html__('Text Color', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $second_level_style_row1,
                'type'          => 'colorsimple',
                'name'          => 'fullscreen_menu_hover_color_2nd',
                'default_value' => '',
                'label'         => esc_html__('Hover Text Color', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $second_level_style_row1,
                'type'          => 'colorsimple',
                'name'          => 'fullscreen_menu_hover_background_color_2nd',
                'default_value' => '',
                'label'         => esc_html__('Background Hover Color', 'servicemaster'),
            )
        );

        $second_level_style_row2 = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $second_level_style_group,
                'name'   => 'second_level_style_row2'
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $second_level_style_row2,
                'type'          => 'fontsimple',
                'name'          => 'fullscreen_menu_google_fonts_2nd',
                'default_value' => '-1',
                'label'         => esc_html__('Font Family', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $second_level_style_row2,
                'type'          => 'textsimple',
                'name'          => 'fullscreen_menu_fontsize_2nd',
                'default_value' => '',
                'label'         => esc_html__('Font Size', 'servicemaster'),
                'args'          => array(
                    'suffix' => 'px'
                )
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $second_level_style_row2,
                'type'          => 'textsimple',
                'name'          => 'fullscreen_menu_lineheight_2nd',
                'default_value' => '',
                'label'         => esc_html__('Line Height', 'servicemaster'),
                'args'          => array(
                    'suffix' => 'px'
                )
            )
        );

        $second_level_style_row3 = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $second_level_style_group,
                'name'   => 'second_level_style_row3'
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $second_level_style_row3,
                'type'          => 'selectblanksimple',
                'name'          => 'fullscreen_menu_fontstyle_2nd',
                'default_value' => '',
                'label'         => esc_html__('Font Style', 'servicemaster'),
                'options'       => servicemaster_mikado_get_font_style_array()
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $second_level_style_row3,
                'type'          => 'selectblanksimple',
                'name'          => 'fullscreen_menu_fontweight_2nd',
                'default_value' => '',
                'label'         => esc_html__('Font Weight', 'servicemaster'),
                'options'       => servicemaster_mikado_get_font_weight_array()
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $second_level_style_row3,
                'type'          => 'textsimple',
                'name'          => 'fullscreen_menu_letterspacing_2nd',
                'default_value' => '',
                'label'         => esc_html__('Letter Spacing', 'servicemaster'),
                'args'          => array(
                    'suffix' => 'px'
                )
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $second_level_style_row3,
                'type'          => 'selectblanksimple',
                'name'          => 'fullscreen_menu_texttransform_2nd',
                'default_value' => '',
                'label'         => esc_html__('Text Transform', 'servicemaster'),
                'options'       => servicemaster_mikado_get_text_transform_array()
            )
        );

        $third_level_style_group = servicemaster_mikado_add_admin_group(
            array(
                'parent'      => $fullscreen_panel,
                'name'        => 'third_level_style_group',
                'title'       => esc_html__('3rd Level Style', 'servicemaster'),
                'description' => esc_html__('Define styles for 3rd level in Fullscreen Menu', 'servicemaster')
            )
        );

        $third_level_style_row1 = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $third_level_style_group,
                'name'   => 'third_level_style_row1'
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $third_level_style_row1,
                'type'          => 'colorsimple',
                'name'          => 'fullscreen_menu_color_3rd',
                'default_value' => '',
                'label'         => esc_html__('Text Color', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $third_level_style_row1,
                'type'          => 'colorsimple',
                'name'          => 'fullscreen_menu_hover_color_3rd',
                'default_value' => '',
                'label'         => esc_html__('Hover Text Color', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $third_level_style_row1,
                'type'          => 'colorsimple',
                'name'          => 'fullscreen_menu_hover_background_color_3rd',
                'default_value' => '',
                'label'         => esc_html__('Background Hover Color', 'servicemaster'),
            )
        );

        $third_level_style_row2 = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $third_level_style_group,
                'name'   => 'second_level_style_row2'
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $third_level_style_row2,
                'type'          => 'fontsimple',
                'name'          => 'fullscreen_menu_google_fonts_3rd',
                'default_value' => '-1',
                'label'         => esc_html__('Font Family', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $third_level_style_row2,
                'type'          => 'textsimple',
                'name'          => 'fullscreen_menu_fontsize_3rd',
                'default_value' => '',
                'label'         => esc_html__('Font Size', 'servicemaster'),
                'args'          => array(
                    'suffix' => 'px'
                )
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $third_level_style_row2,
                'type'          => 'textsimple',
                'name'          => 'fullscreen_menu_lineheight_3rd',
                'default_value' => '',
                'label'         => esc_html__('Line Height', 'servicemaster'),
                'args'          => array(
                    'suffix' => 'px'
                )
            )
        );

        $third_level_style_row3 = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $third_level_style_group,
                'name'   => 'second_level_style_row3'
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $third_level_style_row3,
                'type'          => 'selectblanksimple',
                'name'          => 'fullscreen_menu_fontstyle_3rd',
                'default_value' => '',
                'label'         => esc_html__('Font Style', 'servicemaster'),
                'options'       => servicemaster_mikado_get_font_style_array()
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $third_level_style_row3,
                'type'          => 'selectblanksimple',
                'name'          => 'fullscreen_menu_fontweight_3rd',
                'default_value' => '',
                'label'         => esc_html__('Font Weight', 'servicemaster'),
                'options'       => servicemaster_mikado_get_font_weight_array()
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $third_level_style_row3,
                'type'          => 'textsimple',
                'name'          => 'fullscreen_menu_letterspacing_3rd',
                'default_value' => '',
                'label'         => esc_html__('Letter Spacing', 'servicemaster'),
                'args'          => array(
                    'suffix' => 'px'
                )
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $third_level_style_row3,
                'type'          => 'selectblanksimple',
                'name'          => 'fullscreen_menu_texttransform_3rd',
                'default_value' => '',
                'label'         => esc_html__('Text Transform', 'servicemaster'),
                'options'       => servicemaster_mikado_get_text_transform_array()
            )
        );

        $icon_colors_group = servicemaster_mikado_add_admin_group(
            array(
                'parent'      => $fullscreen_panel,
                'name'        => 'fullscreen_menu_icon_colors_group',
                'title'       => esc_html__('Full Screen Menu Icon Style', 'servicemaster'),
                'description' => esc_html__('Define styles for Fullscreen Menu Icon', 'servicemaster')
            )
        );

        $icon_colors_row1 = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $icon_colors_group,
                'name'   => 'icon_colors_row1'
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent' => $icon_colors_row1,
                'type'   => 'colorsimple',
                'name'   => 'fullscreen_menu_icon_color',
                'label'  => esc_html__('Color', 'servicemaster'),
            )
        );
        servicemaster_mikado_add_admin_field(
            array(
                'parent' => $icon_colors_row1,
                'type'   => 'colorsimple',
                'name'   => 'fullscreen_menu_icon_hover_color',
                'label'  => esc_html__('Hover Color', 'servicemaster'),
            )
        );
        $icon_colors_row2 = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $icon_colors_group,
                'name'   => 'icon_colors_row2',
                'next'   => true
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent' => $icon_colors_row2,
                'type'   => 'colorsimple',
                'name'   => 'fullscreen_menu_light_icon_color',
                'label'  => esc_html__('Light Menu Icon Color', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent' => $icon_colors_row2,
                'type'   => 'colorsimple',
                'name'   => 'fullscreen_menu_light_icon_hover_color',
                'label'  => esc_html__('Light Menu Icon Hover Color', 'servicemaster'),
            )
        );

        $icon_colors_row3 = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $icon_colors_group,
                'name'   => 'icon_colors_row3',
                'next'   => true
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent' => $icon_colors_row3,
                'type'   => 'colorsimple',
                'name'   => 'fullscreen_menu_dark_icon_color',
                'label'  => esc_html__('Dark Menu Icon Color', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent' => $icon_colors_row3,
                'type'   => 'colorsimple',
                'name'   => 'fullscreen_menu_dark_icon_hover_color',
                'label'  => esc_html__('Dark Menu Icon Hover Color', 'servicemaster'),
            )
        );

        $icon_colors_row4 = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $icon_colors_group,
                'name'   => 'icon_colors_row4',
                'next'   => true
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent' => $icon_colors_row4,
                'type'   => 'colorsimple',
                'name'   => 'fullscreen_menu_icon_background_color',
                'label'  => esc_html__('Background Color', 'servicemaster'),
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent' => $icon_colors_row4,
                'type'   => 'colorsimple',
                'name'   => 'fullscreen_menu_icon_background_hover_color',
                'label'  => esc_html__('Background Hover Color', 'servicemaster'),
            )
        );

        $icon_spacing_group = servicemaster_mikado_add_admin_group(
            array(
                'parent'      => $fullscreen_panel,
                'name'        => 'icon_spacing_group',
                'title'       => esc_html__('Full Screen Menu Icon Spacing', 'servicemaster'),
                'description' => esc_html__('Define padding and margin for full screen menu icon', 'servicemaster')
            )
        );

        $icon_spacing_row = servicemaster_mikado_add_admin_row(
            array(
                'parent' => $icon_spacing_group,
                'name'   => 'icon_spacing_row'
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $icon_spacing_row,
                'type'          => 'textsimple',
                'name'          => 'fullscreen_menu_icon_padding_left',
                'default_value' => '',
                'label'         => esc_html__('Padding Left', 'servicemaster'),
                'args'          => array(
                    'suffix' => 'px'
                )
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $icon_spacing_row,
                'type'          => 'textsimple',
                'name'          => 'fullscreen_menu_icon_padding_right',
                'default_value' => '',
                'label'         => esc_html__('Padding Right', 'servicemaster'),
                'args'          => array(
                    'suffix' => 'px'
                )
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $icon_spacing_row,
                'type'          => 'textsimple',
                'name'          => 'fullscreen_menu_icon_margin_left',
                'default_value' => '',
                'label'         => esc_html__('Margin Left', 'servicemaster'),
                'args'          => array(
                    'suffix' => 'px'
                )
            )
        );

        servicemaster_mikado_add_admin_field(
            array(
                'parent'        => $icon_spacing_row,
                'type'          => 'textsimple',
                'name'          => 'fullscreen_menu_icon_margin_right',
                'default_value' => '',
                'label'         => esc_html__('Margin Right', 'servicemaster'),
                'args'          => array(
                    'suffix' => 'px'
                )
            )
        );

    }

    add_action('servicemaster_mikado_header_options_map', 'servicemaster_mikado_fullscreen_menu_options_map');

}
<?php

if (!function_exists('servicemaster_mikado_social_options_map')) {

	function servicemaster_mikado_social_options_map() {

		servicemaster_mikado_add_admin_page(
			array(
				'slug'  => '_social_page',
				'title' => esc_html__('Social Networks', 'servicemaster'),
				'icon'  => 'icon_group'
			)
		);

		/**
		 * Enable Social Share
		 */
		$panel_social_share = servicemaster_mikado_add_admin_panel(array(
			'page'  => '_social_page',
			'name'  => 'panel_social_share',
			'title' => esc_html__('Enable Social Share', 'servicemaster'),
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'yesno',
			'name'          => 'enable_social_share',
			'default_value' => 'no',
			'label'         => esc_html__('Enable Social Share', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will allow social share on networks of your choice', 'servicemaster'),
			'args'          => array(
				'dependence'             => true,
				'dependence_hide_on_yes' => '',
				'dependence_show_on_yes' => '#mkd_panel_social_networks, #mkd_panel_show_social_share_on'
			),
			'parent'        => $panel_social_share
		));

		$panel_show_social_share_on = servicemaster_mikado_add_admin_panel(array(
			'page'            => '_social_page',
			'name'            => 'panel_show_social_share_on',
			'title'           => esc_html__('Show Social Share On', 'servicemaster'),
			'hidden_property' => esc_html__('enable_social_share', 'servicemaster'),
			'hidden_value'    => 'no'
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'yesno',
			'name'          => 'enable_social_share_on_post',
			'default_value' => 'no',
			'label'         => esc_html__('Posts', 'servicemaster'),
			'description'   => esc_html__('Show Social Share on Blog Posts', 'servicemaster'),
			'parent'        => $panel_show_social_share_on
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'yesno',
			'name'          => 'enable_social_share_on_page',
			'default_value' => 'no',
			'label'         => esc_html__('Pages', 'servicemaster'),
			'description'   => esc_html__('Show Social Share on Pages', 'servicemaster'),
			'parent'        => $panel_show_social_share_on
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'yesno',
			'name'          => 'enable_social_share_on_attachment',
			'default_value' => 'no',
			'label'         => esc_html__('Media', 'servicemaster'),
			'description'   => esc_html__('Show Social Share for Images and Videos', 'servicemaster'),
			'parent'        => $panel_show_social_share_on
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'yesno',
			'name'          => 'enable_social_share_on_portfolio-item',
			'default_value' => 'no',
			'label'         => esc_html__('Portfolio Item', 'servicemaster'),
			'description'   => esc_html__('Show Social Share for Portfolio Items', 'servicemaster'),
			'parent'        => $panel_show_social_share_on
		));

		if (servicemaster_mikado_is_woocommerce_installed()) {
			servicemaster_mikado_add_admin_field(array(
				'type'          => 'yesno',
				'name'          => 'enable_social_share_on_product',
				'default_value' => 'no',
				'label'         => esc_html__('Product', 'servicemaster'),
				'description'   => esc_html__('Show Social Share for Product Items', 'servicemaster'),
				'parent'        => $panel_show_social_share_on
			));
		}

		/**
		 * Social Share Networks
		 */
		$panel_social_networks = servicemaster_mikado_add_admin_panel(array(
			'page'            => '_social_page',
			'name'            => 'panel_social_networks',
			'title'           => esc_html__('Social Networks', 'servicemaster'),
			'hidden_property' => 'enable_social_share',
			'hidden_value'    => 'no'
		));

		/**
		 * Facebook
		 */
		servicemaster_mikado_add_admin_section_title(array(
			'parent' => $panel_social_networks,
			'name'   => 'facebook_title',
			'title'  => esc_html__('Share on Facebook', 'servicemaster'),
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'yesno',
			'name'          => 'enable_facebook_share',
			'default_value' => 'no',
			'label'         => esc_html__('Enable Share', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will allow sharing via Facebook', 'servicemaster'),
			'args'          => array(
				'dependence'             => true,
				'dependence_hide_on_yes' => '',
				'dependence_show_on_yes' => '#mkd_enable_facebook_share_container'
			),
			'parent'        => $panel_social_networks
		));

		$enable_facebook_share_container = servicemaster_mikado_add_admin_container(array(
			'name'            => 'enable_facebook_share_container',
			'hidden_property' => 'enable_facebook_share',
			'hidden_value'    => 'no',
			'parent'          => $panel_social_networks
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'image',
			'name'          => 'facebook_icon',
			'default_value' => '',
			'label'         => esc_html__('Upload Icon', 'servicemaster'),
			'parent'        => $enable_facebook_share_container
		));

		/**
		 * Twitter
		 */
		servicemaster_mikado_add_admin_section_title(array(
			'parent' => $panel_social_networks,
			'name'   => 'twitter_title',
			'title'  => esc_html__('Share on Twitter', 'servicemaster'),
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'yesno',
			'name'          => 'enable_twitter_share',
			'default_value' => 'no',
			'label'         => esc_html__('Enable Share', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will allow sharing via Twitter', 'servicemaster'),
			'args'          => array(
				'dependence'             => true,
				'dependence_hide_on_yes' => '',
				'dependence_show_on_yes' => '#mkd_enable_twitter_share_container'
			),
			'parent'        => $panel_social_networks
		));

		$enable_twitter_share_container = servicemaster_mikado_add_admin_container(array(
			'name'            => 'enable_twitter_share_container',
			'hidden_property' => 'enable_twitter_share',
			'hidden_value'    => 'no',
			'parent'          => $panel_social_networks
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'image',
			'name'          => 'twitter_icon',
			'default_value' => '',
			'label'         => esc_html__('Upload Icon', 'servicemaster'),
			'parent'        => $enable_twitter_share_container
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'text',
			'name'          => 'twitter_via',
			'default_value' => '',
			'label'         => esc_html__('Via', 'servicemaster'),
			'parent'        => $enable_twitter_share_container
		));

		/**
		 * Google Plus
		 */
		servicemaster_mikado_add_admin_section_title(array(
			'parent' => $panel_social_networks,
			'name'   => 'google_plus_title',
			'title'  => esc_html__('Share on Google Plus', 'servicemaster'),
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'yesno',
			'name'          => 'enable_google_plus_share',
			'default_value' => 'no',
			'label'         => esc_html__('Enable Share', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will allow sharing via Google Plus', 'servicemaster'),
			'args'          => array(
				'dependence'             => true,
				'dependence_hide_on_yes' => '',
				'dependence_show_on_yes' => '#mkd_enable_google_plus_container'
			),
			'parent'        => $panel_social_networks
		));

		$enable_google_plus_container = servicemaster_mikado_add_admin_container(array(
			'name'            => 'enable_google_plus_container',
			'hidden_property' => 'enable_google_plus_share',
			'hidden_value'    => 'no',
			'parent'          => $panel_social_networks
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'image',
			'name'          => 'google_plus_icon',
			'default_value' => '',
			'label'         => esc_html__('Upload Icon', 'servicemaster'),
			'parent'        => $enable_google_plus_container
		));

		/**
		 * Linked In
		 */
		servicemaster_mikado_add_admin_section_title(array(
			'parent' => $panel_social_networks,
			'name'   => 'linkedin_title',
			'title'  => esc_html__('Share on LinkedIn', 'servicemaster')
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'yesno',
			'name'          => 'enable_linkedin_share',
			'default_value' => 'no',
			'label'         => esc_html__('Enable Share', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will allow sharing via LinkedIn', 'servicemaster'),
			'args'          => array(
				'dependence'             => true,
				'dependence_hide_on_yes' => '',
				'dependence_show_on_yes' => '#mkd_enable_linkedin_container'
			),
			'parent'        => $panel_social_networks
		));

		$enable_linkedin_container = servicemaster_mikado_add_admin_container(array(
			'name'            => 'enable_linkedin_container',
			'hidden_property' => 'enable_linkedin_share',
			'hidden_value'    => 'no',
			'parent'          => $panel_social_networks
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'image',
			'name'          => 'linkedin_icon',
			'default_value' => '',
			'label'         => esc_html__('Upload Icon', 'servicemaster'),
			'parent'        => $enable_linkedin_container
		));

		/**
		 * Tumblr
		 */
		servicemaster_mikado_add_admin_section_title(array(
			'parent' => $panel_social_networks,
			'name'   => 'tumblr_title',
			'title'  => esc_html__('Share on Tumblr', 'servicemaster')
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'yesno',
			'name'          => 'enable_tumblr_share',
			'default_value' => 'no',
			'label'         => esc_html__('Enable Share', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will allow sharing via Tumblr', 'servicemaster'),
			'args'          => array(
				'dependence'             => true,
				'dependence_hide_on_yes' => '',
				'dependence_show_on_yes' => '#mkd_enable_tumblr_container'
			),
			'parent'        => $panel_social_networks
		));

		$enable_tumblr_container = servicemaster_mikado_add_admin_container(array(
			'name'            => 'enable_tumblr_container',
			'hidden_property' => 'enable_tumblr_share',
			'hidden_value'    => 'no',
			'parent'          => $panel_social_networks
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'image',
			'name'          => 'tumblr_icon',
			'default_value' => '',
			'label'         => esc_html__('Upload Icon', 'servicemaster'),
			'parent'        => $enable_tumblr_container
		));

		/**
		 * Pinterest
		 */
		servicemaster_mikado_add_admin_section_title(array(
			'parent' => $panel_social_networks,
			'name'   => 'pinterest_title',
			'title'  => esc_html__('Share on Pinterest', 'servicemaster'),
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'yesno',
			'name'          => 'enable_pinterest_share',
			'default_value' => 'no',
			'label'         => esc_html__('Enable Share', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will allow sharing via Pinterest', 'servicemaster'),
			'args'          => array(
				'dependence'             => true,
				'dependence_hide_on_yes' => '',
				'dependence_show_on_yes' => '#mkd_enable_pinterest_container'
			),
			'parent'        => $panel_social_networks
		));

		$enable_pinterest_container = servicemaster_mikado_add_admin_container(array(
			'name'            => 'enable_pinterest_container',
			'hidden_property' => 'enable_pinterest_share',
			'hidden_value'    => 'no',
			'parent'          => $panel_social_networks
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'image',
			'name'          => 'pinterest_icon',
			'default_value' => '',
			'label'         => esc_html__('Upload Icon', 'servicemaster'),
			'parent'        => $enable_pinterest_container
		));

		/**
		 * VK
		 */
		servicemaster_mikado_add_admin_section_title(array(
			'parent' => $panel_social_networks,
			'name'   => 'vk_title',
			'title'  => esc_html__('Share on VK', 'servicemaster')
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'yesno',
			'name'          => 'enable_vk_share',
			'default_value' => 'no',
			'label'         => esc_html__('Enable Share', 'servicemaster'),
			'description'   => esc_html__('Enabling this option will allow sharing via VK', 'servicemaster'),
			'args'          => array(
				'dependence'             => true,
				'dependence_hide_on_yes' => '',
				'dependence_show_on_yes' => '#mkd_enable_vk_container'
			),
			'parent'        => $panel_social_networks
		));

		$enable_vk_container = servicemaster_mikado_add_admin_container(array(
			'name'            => 'enable_vk_container',
			'hidden_property' => 'enable_vk_share',
			'hidden_value'    => 'no',
			'parent'          => $panel_social_networks
		));

		servicemaster_mikado_add_admin_field(array(
			'type'          => 'image',
			'name'          => 'vk_icon',
			'default_value' => '',
			'label'         => esc_html__('Upload Icon', 'servicemaster'),
			'parent'        => $enable_vk_container
		));

		if (defined('MIKADO_TWITTER_FEED_VERSION')) {
			$twitter_panel = servicemaster_mikado_add_admin_panel(array(
				'title' => esc_html__('Twitter', 'servicemaster'),
				'name'  => 'panel_twitter',
				'page'  => '_social_page'
			));

			servicemaster_mikado_add_admin_twitter_button(array(
				'name'   => 'twitter_button',
				'parent' => $twitter_panel
			));
		}

		if (defined('MIKADO_INSTAGRAM_FEED_VERSION')) {
			$instagram_panel = servicemaster_mikado_add_admin_panel(array(
				'title' => esc_html__('Instagram', 'servicemaster'),
				'name'  => 'panel_instagram',
				'page'  => '_social_page'
			));

			servicemaster_mikado_add_admin_instagram_button(array(
				'name'   => 'instagram_button',
				'parent' => $instagram_panel
			));
		}
	}

	add_action('servicemaster_mikado_options_map', 'servicemaster_mikado_social_options_map', 15);
}
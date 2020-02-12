<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPC_Enqueue' ) ) :

class WPC_Enqueue {

	/**
	 * constructor
	 **/
	function __construct() {
		add_action( 'wp_loaded', array( &$this, 'register_scripts' ), 0 );
		add_action( 'wp_loaded', array( &$this, 'register_styles' ), 0 );
	}

	function register_scripts() {
		//admin left submenu
		wp_register_script( 'add-subsubmenu', WPC()->plugin_url . 'js/subsubmenu.js', array(), WPC_CLIENT_VER );

		//clients page
		wp_register_script( 'wpc-chosen-js', WPC()->plugin_url . 'js/chosen/chosen.jquery.min.js', array('jquery'), WPC_CLIENT_VER );

		//settings page
		wp_register_script( 'wpc-checkboxes-js', WPC()->plugin_url . 'js/jquery.ibutton.js', array('jquery'), WPC_CLIENT_VER );

		//top slider
		wp_register_script( 'wpc-slider-js', WPC()->plugin_url . 'js/wpc_slider/jquery.wpc_slider.js', array('jquery'), WPC_CLIENT_VER );

		//file uploaders
		wp_register_script( 'wp-client-uploadifive', WPC()->plugin_url . 'js/jquery.uploadifive.min.js', array('jquery'), WPC_CLIENT_VER, true );
		wp_register_script( 'wp-client-plupload', WPC()->plugin_url . 'js/plupload/plupload.full.min.js' );
		wp_register_script( 'wp-client-jquery-queue-plupload', WPC()->plugin_url . 'js/plupload/jquery.plupload.queue/jquery.plupload.queue.min.js', array('wp-client-plupload'), WPC_CLIENT_VER );

		//shutter-box
		wp_register_script( 'wpc-shutter-box-script', WPC()->plugin_url . 'js/shutter-box/shutter_box_core.js', array('jquery'), WPC_CLIENT_VER, true );

		//private messages page
		wp_register_script( 'wpc-select-js', WPC()->plugin_url . 'js/wpc_select/wpc_select.js', array('jquery'), WPC_CLIENT_VER );
		wp_register_script( 'wpc-admin-messages-js', WPC()->plugin_url . 'js/admin/messages.js', array('wpc-select-js'), WPC_CLIENT_VER );

		//templates page
		wp_register_script( 'wpc-diff-js', WPC()->plugin_url . 'js/diff_match_patch.js' );

		wp_register_script( 'jquery-base64', WPC()->plugin_url . 'js/jquery.b_64.min.js', array( 'jquery' ), WPC_CLIENT_VER );
		wp_register_script( 'wpc-zeroclipboard-js', WPC()->plugin_url . 'js/zeroclipboard/zeroclipboard.js' );
		wp_register_script( 'jquery-md5', WPC()->plugin_url . 'js/md5.js', array( 'jquery' ), WPC_CLIENT_VER, true );
		wp_register_script( 'wpc-nested-sortable-js', WPC()->plugin_url . 'js/jquery.mjs.nestedSortable.js', array( 'jquery' ), WPC_CLIENT_VER, true );

		wp_register_script( 'wpc-admin-advanced_menu_settings-js', WPC()->plugin_url . 'js/admin/location_advanced_settings.js', array( 'jquery' ), WPC_CLIENT_VER );

		//assign popups
		wp_register_script( 'wpc-new-assign-popup-js', WPC()->plugin_url . 'js/new-assign-popup.js', array('jquery'), WPC_CLIENT_VER );

		//media buttons
		wp_register_script( 'wpc_media_button', WPC()->plugin_url . 'js/wpc_shortcodes.js', array('jquery'), WPC_CLIENT_VER, true );
		wp_register_script( 'wpc_client_admin_login', WPC()->plugin_url . 'js/admin_relogin.js', array('jquery'), WPC_CLIENT_VER );

		wp_register_script( 'wp-client-ez_hub_bar', WPC()->plugin_url . 'js/pages/ez_hub_bar.js', array(), WPC_CLIENT_VER, true );

		wp_register_script( 'wpc-textext-core-js', WPC()->plugin_url . 'js/textext/textext.core.js', array(), WPC_CLIENT_VER );
		wp_register_script( 'wpc-textext-focus-js', WPC()->plugin_url . 'js/textext/textext.plugin.focus.js', array(), WPC_CLIENT_VER );
		wp_register_script( 'wpc-textext-tags-js', WPC()->plugin_url . 'js/textext/textext.plugin.tags.js', array(), WPC_CLIENT_VER );
		wp_register_script( 'wpc-textext-prompt-js', WPC()->plugin_url . 'js/textext/textext.plugin.prompt.js', array(), WPC_CLIENT_VER );
		wp_register_script( 'wpc-textext-autocomplete-js', WPC()->plugin_url . 'js/textext/textext.plugin.autocomplete.js', array(), WPC_CLIENT_VER );
		wp_register_script( 'wpc-textext-ajax-js', WPC()->plugin_url . 'js/textext/textext.plugin.ajax.js', array(), WPC_CLIENT_VER );
		wp_register_script( 'wpc-textext-arrow-js', WPC()->plugin_url . 'js/textext/textext.plugin.arrow.js', array(), WPC_CLIENT_VER );

		wp_register_script( 'wpc_validation_custom_field', WPC()->plugin_url . 'js/custom_fields.js', array('jquery'), WPC_CLIENT_VER );
		wp_register_script( 'wpc_mask', WPC()->plugin_url . 'js/jquery.mask.js', array('jquery'), WPC_CLIENT_VER );

		wp_register_script( 'wpc_custom_datepicker', WPC()->plugin_url . 'js/custom_datepicker.js', array('jquery-ui-datepicker'), WPC_CLIENT_VER );

		wp_register_script( 'wpc-files-shortcode-list-js', WPC()->plugin_url . 'js/pages/shortcode_files_list.js', false, WPC_CLIENT_VER, true );
		wp_register_script( 'wpc-files-blog-shortcode-js', WPC()->plugin_url . 'js/pages/shortcode_files_blog.js', false, WPC_CLIENT_VER, true );
		wp_register_script( 'wpc-files-table-shortcode-js', WPC()->plugin_url . 'js/pages/shortcode_files_table.js', false, WPC_CLIENT_VER, true );
		wp_register_script( 'wpc-treetable-js', WPC()->plugin_url . 'js/jquery.treetable.js', false, WPC_CLIENT_VER, true );
		wp_register_script( 'wpc-files-tree-shortcode-mobile-js', WPC()->plugin_url . 'js/pages/shortcode_files_tree_mobile.js', false, WPC_CLIENT_VER, true );
		wp_register_script( 'wpc-files-tree-shortcode-js', WPC()->plugin_url . 'js/pages/shortcode_files_tree.js', false, WPC_CLIENT_VER, true );
		wp_register_script( 'wpc-files-shortcode-js', WPC()->plugin_url . 'js/pages/shortcode_files.js', false, WPC_CLIENT_VER );

		wp_register_script( 'wp-client-password-protect', WPC()->plugin_url . 'js/password_protect.js', array('password-strength-meter'), WPC_CLIENT_VER, true );

		wp_register_script( 'wpc-user-activity-alert-js', WPC()->plugin_url . 'js/user-activity-alert.js', false, WPC_CLIENT_VER, true );

		//client profile
        wp_register_script( 'wpc_client_profile', WPC()->plugin_url . 'js/pages/profile.js', false, WPC_CLIENT_VER );
        //staff profile
        wp_register_script( 'wpc_client_staff_profile', WPC()->plugin_url . 'js/pages/profile_staff.js', false, WPC_CLIENT_VER );

        wp_register_script( 'wpc_login_page', WPC()->plugin_url . 'js/pages/login.js', false, WPC_CLIENT_VER );

        wp_register_script( 'wpc-pagel-tree-shortcode-js', WPC()->plugin_url . 'js/pages/shortcode_pagel_tree.js', false, WPC_CLIENT_VER, true );
		wp_register_script( 'wpc-pagel-list-shortcode-js', WPC()->plugin_url . 'js/pages/shortcode_pagel_list.js', false, WPC_CLIENT_VER, true );

		wp_register_script( 'wpc_client_com', WPC()->plugin_url . 'js/pages/private_messages.js', false, WPC_CLIENT_VER, true );

		wp_register_script( 'wpc_registration', WPC()->plugin_url . 'js/pages/registration.js', array(), WPC_CLIENT_VER, true );

		wp_register_script( 'wpc_add_staff', WPC()->plugin_url . 'js/pages/add_staff.js', false, WPC_CLIENT_VER );

		wp_register_script( 'wpc_edit_portal_page', WPC()->plugin_url . 'js/pages/wpc_edit_portal_page.js', false, WPC_CLIENT_VER, true );

		wp_register_script( 'wpc-tutorial', WPC()->plugin_url . 'js/tutorial.js', array( 'jquery', 'wp-pointer' ), WPC_CLIENT_VER, true );

		wp_register_script( 'js-stripe', 'https://js.stripe.com/v1/', array(), WPC_CLIENT_VER, true );
		wp_register_script( 'stripe-token', WPC()->plugin_url . 'includes/payment_gateways/stripe-files/stripe_token.js', array('js-stripe'), WPC_CLIENT_VER, true );

		wp_register_script( 'wpc-jqplot', WPC()->plugin_url . '/js/jqPlot/jquery.jqplot.min.js', false, WPC_CLIENT_VER, true );
		wp_register_script( 'wpc-logAxisRenderer', WPC()->plugin_url . '/js/jqPlot/jqplot.logAxisRenderer.min.js', false, WPC_CLIENT_VER, true );
		wp_register_script( 'wpc-canvasTextRenderer', WPC()->plugin_url . '/js/jqPlot/jqplot.canvasTextRenderer.min.js', false, WPC_CLIENT_VER, true );
		wp_register_script( 'wpc-canvasAxisLabelRenderer', WPC()->plugin_url . '/js/jqPlot/jqplot.canvasAxisLabelRenderer.min.js', false, WPC_CLIENT_VER, true );
		wp_register_script( 'wpc-canvasAxisTickRenderer', WPC()->plugin_url . '/js/jqPlot/jqplot.canvasAxisTickRenderer.min.js', false, WPC_CLIENT_VER, true );
		wp_register_script( 'wpc-dateAxisRenderer', WPC()->plugin_url . '/js/jqPlot/jqplot.dateAxisRenderer.min.js', false, WPC_CLIENT_VER, true );
	    wp_register_script( 'wpc-categoryAxisRenderer', WPC()->plugin_url . '/js/jqPlot/jqplot.categoryAxisRenderer.min.js', false, WPC_CLIENT_VER, true );
	    wp_register_script( 'wpc-barRenderer', WPC()->plugin_url . '/js/jqPlot/jqplot.barRenderer.min.js', false, WPC_CLIENT_VER, true );
	    wp_register_script( 'wpc-highlighter', WPC()->plugin_url . '/js/jqPlot/jqplot.highlighter.min.js', false, WPC_CLIENT_VER, true );
	    wp_register_script( 'wpc-cursor', WPC()->plugin_url . '/js/jqPlot/jqplot.cursor.min.js', false, WPC_CLIENT_VER, true );

	    wp_register_script( 'wpc_email_sending', WPC()->plugin_url . 'js/admin/email_sending.js', false, WPC_CLIENT_VER, true );
	}


	function register_styles() {
		$uploads = wp_upload_dir();

		wp_register_style('wpc-wizard_setup', WPC()->plugin_url . 'css/admin/style_wizard_setup.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wp-client-style-for-menu', WPC()->plugin_url . 'css/style_menu.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-chosen-style', WPC()->plugin_url . 'js/chosen/chosen.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wp-client-style', WPC()->plugin_url . 'css/style.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-slider-css', WPC()->plugin_url . 'js/wpc_slider/jquery.wpc_slider.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wp-client-additional-style', WPC()->plugin_url . 'css/additional_style.css', array(), WPC_CLIENT_VER );

		wp_register_style( 'wp-client-uploadifive', WPC()->plugin_url . 'css/uploadifive.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wp-client-plupload', WPC()->plugin_url . 'js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-dashboard-style', WPC()->plugin_url . 'css/admin/dashboard.css', array(), WPC_CLIENT_VER );

		wp_register_style( 'wpc-shutter-box-style', WPC()->plugin_url . 'js/shutter-box/shutter_box.css');
		wp_register_style( 'wpc-import-export-style', WPC()->plugin_url . 'css/admin/import_export.css', array(), WPC_CLIENT_VER );

		wp_register_style( 'wpc-checkboxes-css', WPC()->plugin_url . 'js/jquery.ibutton.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-auto-convert-rules-style', WPC()->plugin_url . 'css/admin/auto_convert_rules.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-select-style', WPC()->plugin_url . 'js/wpc_select/wpc_select.css', array(), WPC_CLIENT_VER );

		wp_register_style( 'wpc-ui-style', WPC()->plugin_url . 'css/jqueryui/jquery-ui-1.10.3.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-templates-style', WPC()->plugin_url . 'css/admin/templates.css', array(), WPC_CLIENT_VER );

		wp_register_style( 'wpc-admin-pp_categories-style', WPC()->plugin_url . 'css/admin/pp_categories.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-portal-pages-style', WPC()->plugin_url . 'css/admin/portal_pages.css', array(), WPC_CLIENT_VER);
		wp_register_style( 'wpc-admin-portalhubs-style', WPC()->plugin_url . 'css/admin/portalhub.css', array(), WPC_CLIENT_VER);
		wp_register_style( 'wpc-admin-file-tags-style', WPC()->plugin_url . 'css/admin/file_tags.css', array(), WPC_CLIENT_VER);
		wp_register_style( 'wpc-admin-file-downloads-style', WPC()->plugin_url . 'css/admin/file_downloads.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-tags-style', WPC()->plugin_url . 'css/admin/tags.css', array(), WPC_CLIENT_VER);
		wp_register_style( 'wpc-admin-file-categories-style', WPC()->plugin_url . 'css/admin/file_categories.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-files-style', WPC()->plugin_url . 'css/admin/files.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wp-client-avatar-style', WPC()->plugin_url . 'css/admin/avatar.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-messages-style', WPC()->plugin_url . 'css/admin/messages.css', array( 'wp-client-avatar-style' ), WPC_CLIENT_VER );

		wp_register_style( 'wpc-admin-clients-style', WPC()->plugin_url . 'css/admin/clients.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-managers-style', WPC()->plugin_url . 'css/admin/managers.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-admins-style', WPC()->plugin_url . 'css/admin/admins.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-staffs-style', WPC()->plugin_url . 'css/admin/staffs.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-approve-staffs-style', WPC()->plugin_url . 'css/admin/approve_staff.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-approve-clients-style', WPC()->plugin_url . 'css/admin/approve_clients.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-users-convert-style', WPC()->plugin_url . 'css/admin/users_convert.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-admin-profile-style', WPC()->plugin_url . 'css/admin/admin_edit.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-staff-profile-style', WPC()->plugin_url . 'css/admin/client_staff_edit.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-manager-profile-style', WPC()->plugin_url . 'css/admin/manager_edit.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-client-profile-style', WPC()->plugin_url . 'css/admin/client_edit.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-admin-payments-style', WPC()->plugin_url . 'css/admin/payments.css', array(), WPC_CLIENT_VER );

		wp_register_style( 'wpc-add_shortcodes', WPC()->plugin_url . 'css/admin/add_shortcodes.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc_custom_style', $uploads['baseurl'] . '/wpc_custom_style.css', array(), WPC_CLIENT_VER );

		wp_register_style( 'wpc_user_style', WPC()->plugin_url . 'css/user_style.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc_user_general_style', WPC()->plugin_url . 'css/user/general.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wp-client-ez-hub-bar-style', WPC()->plugin_url . 'css/ez_hub_bar.css', array(), WPC_CLIENT_VER );

		wp_register_style( 'wpc-textext-core-css', WPC()->plugin_url . 'css/textext/textext.core.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-textext-focus-css', WPC()->plugin_url . 'css/textext/textext.plugin.focus.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-textext-tags-css', WPC()->plugin_url . 'css/textext/textext.plugin.tags.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-textext-prompt-css', WPC()->plugin_url . 'css/textext/textext.plugin.prompt.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-textext-autocomplete-css', WPC()->plugin_url . 'css/textext/textext.plugin.autocomplete.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-textext-arrow-css', WPC()->plugin_url . 'css/textext/textext.plugin.arrow.css', array(), WPC_CLIENT_VER );

		wp_register_style( 'wp-client-files-list-style', WPC()->plugin_url . 'css/pages/shortcode_files.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wp-client-blog-style', WPC()->plugin_url . 'css/pages/shortcode_files_blog.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wp-client-files-table-style', WPC()->plugin_url . 'css/pages/shortcode_files_table.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wp-client-tree-style', WPC()->plugin_url . 'css/pages/shortcode_files_tree.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-files-tree-style', WPC()->plugin_url . 'css/jquery.treetable.css', array(), WPC_CLIENT_VER );

		wp_register_style( 'wpc_login_page', WPC()->plugin_url . 'css/pages/login.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wp-pagel-tree-style', WPC()->plugin_url . 'css/pages/shortcode_pagel_tree.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-pagel-tree-style', WPC()->plugin_url . 'css/jquery.treetable.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wp-pagel-list-style', WPC()->plugin_url . 'css/pages/shortcode_pagel_list.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc-private-messages-style', WPC()->plugin_url . 'css/pages/private_messages.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wpc_staff_directory', WPC()->plugin_url . 'css/pages/staff_directory.css', array(), WPC_CLIENT_VER );
		wp_register_style( 'wp-client-jqplot-style', WPC()->plugin_url . '/css/jqPlot/jquery.jqplot.min.css', array(), WPC_CLIENT_VER );
	}
}

endif;

new WPC_Enqueue();
<?php
namespace ServiceMaster\Modules\Header\Types;

use ServiceMaster\Modules\Header\Lib\HeaderType;

/**
 * Class that represents Header Minimal layout and option
 *
 * Class HeaderMinimal
 */
class HeaderMinimal extends HeaderType {
	protected $heightOfTransparency;
	protected $heightOfCompleteTransparency;
	protected $headerHeight;
	protected $mobileHeaderHeight;

	/**
	 * Sets slug property which is the same as value of option in DB
	 */
	public function __construct() {
		$this->slug = 'header-minimal';

		if (!is_admin()) {

			$menuAreaHeight = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('menu_area_height_header_minimal'));
			$this->menuAreaHeight = $menuAreaHeight !== '' ? $menuAreaHeight : 100;

			$mobileHeaderHeight = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('mobile_header_height'));
			$this->mobileHeaderHeight = $mobileHeaderHeight !== '' ? $mobileHeaderHeight : 100;

			add_action('wp', array($this, 'setHeaderHeightProps'));

			add_filter('servicemaster_mikado_js_global_variables', array($this, 'getGlobalJSVariables'));
			add_filter('servicemaster_mikado_per_page_js_vars', array($this, 'getPerPageJSVariables'));
			add_filter('servicemaster_mikado_add_page_custom_style', array($this, 'headerPerPageStyles'));
		}
	}

	public function headerPerPageStyles($style) {
		$id = servicemaster_mikado_get_page_id();
		$class_prefix = servicemaster_mikado_get_unique_page_class();
		$main_menu_style = array();
		$main_menu_grid_style = array();
		$disable_shadow = servicemaster_mikado_get_meta_field_intersect('menu_area_shadow_header_minimal', $id) == 'no';
		$disable_grid_shadow = servicemaster_mikado_get_meta_field_intersect('menu_area_in_grid_shadow_header_minimal', $id) == 'no';
        $current_style = '';

		$main_menu_selector = array($class_prefix . '.mkd-header-minimal .mkd-menu-area');
		$main_menu_grid_selector = array($class_prefix . '.mkd-header-minimal .mkd-page-header .mkd-menu-area .mkd-grid .mkd-vertical-align-containers');

		/* header style - start */

		$menu_area_background_color = get_post_meta($id, 'mkd_menu_area_background_color_header_minimal_meta', true);
		$menu_area_background_transparency = get_post_meta($id, 'mkd_menu_area_background_transparency_header_minimal_meta', true);

		if ($menu_area_background_transparency === '') {
			$menu_area_background_transparency = 1;
		}

		$menu_area_background_color_rgba = servicemaster_mikado_rgba_color($menu_area_background_color, $menu_area_background_transparency);

		if ($menu_area_background_color_rgba !== null) {
			$main_menu_style['background-color'] = $menu_area_background_color_rgba;
		}
		/* header style - end */

		/* header in grid style - start */

		if (!$disable_grid_shadow) {
			$main_menu_grid_style['box-shadow'] = '0px 1px 3px rgba(0,0,0,0.15)';
		}

		$menu_area_grid_background_color = get_post_meta($id, 'mkd_menu_area_grid_background_color_header_minimal_meta', true);
		$menu_area_grid_background_transparency = get_post_meta($id, 'mkd_menu_area_grid_background_transparency_header_minimal_meta', true);

		if ($menu_area_grid_background_transparency === '') {
			$menu_area_grid_background_transparency = 1;
		}

		$menu_area_grid_background_color_rgba = servicemaster_mikado_rgba_color($menu_area_grid_background_color, $menu_area_grid_background_transparency);

		if ($menu_area_grid_background_color_rgba !== null) {
			$main_menu_grid_style['background-color'] = $menu_area_grid_background_color_rgba;
		}

		/* header in grid style - end */

        $current_style .= servicemaster_mikado_dynamic_css($main_menu_selector, $main_menu_style);
        $current_style .= servicemaster_mikado_dynamic_css($main_menu_grid_selector, $main_menu_grid_style);

        $style = $current_style . $style;

        return $style;
	}

	/**
	 * Loads template file for this header type
	 *
	 * @param array $parameters associative array of variables that needs to passed to template
	 */
	public function loadTemplate($parameters = array()) {
		$id = servicemaster_mikado_get_page_id();

		$parameters['menu_area_in_grid'] = servicemaster_mikado_get_meta_field_intersect('menu_area_in_grid_header_minimal', $id) == 'yes' ? true : false;

		$parameters = apply_filters('servicemaster_mikado_header_minimal_parameters', $parameters);

		servicemaster_mikado_get_module_template_part('templates/types/' . $this->slug, $this->moduleName, '', $parameters);
	}

	/**
	 * Sets header height properties after WP object is set up
	 */
	public function setHeaderHeightProps() {
		$this->heightOfTransparency = $this->calculateHeightOfTransparency();
		$this->heightOfCompleteTransparency = $this->calculateHeightOfCompleteTransparency();
		$this->headerHeight = $this->calculateHeaderHeight();
		$this->mobileHeaderHeight = $this->calculateMobileHeaderHeight();
	}

	/**
	 * Returns total height of transparent parts of header
	 *
	 * @return int
	 */
	public function calculateHeightOfTransparency() {
		$id = servicemaster_mikado_get_page_id();
		$transparencyHeight = 0;

		if (get_post_meta($id, 'mkd_menu_area_background_color_header_minimal_meta', true) !== '') {
			$menuAreaTransparent = get_post_meta($id, 'mkd_menu_area_background_color_header_minimal_meta', true) !== '' &&
				get_post_meta($id, 'mkd_menu_area_background_transparency_header_minimal_meta', true) !== '1';
		} elseif (servicemaster_mikado_options()->getOptionValue('menu_area_background_color_header_minimal') == '') {
			$menuAreaTransparent = servicemaster_mikado_options()->getOptionValue('menu_area_grid_background_color_header_minimal') !== '' &&
				servicemaster_mikado_options()->getOptionValue('menu_area_grid_background_transparency_header_minimal') !== '1';
		} else {
			$menuAreaTransparent = servicemaster_mikado_options()->getOptionValue('menu_area_background_color_header_minimal') !== '' &&
				servicemaster_mikado_options()->getOptionValue('menu_area_background_transparency_header_minimal') !== '1';
		}

        $sliderExists        = get_post_meta($id, 'mkd_page_slider_meta', true) !== '';
		$contentBehindHeader = get_post_meta($id, 'mkd_page_content_behind_header_meta', true) === 'yes';

		if ($sliderExists || $contentBehindHeader) {
			$menuAreaTransparent = true;
		}

		if ($menuAreaTransparent) {
			$transparencyHeight = $this->menuAreaHeight;

			if (servicemaster_mikado_is_top_bar_enabled() && (servicemaster_mikado_is_top_bar_transparent() || $contentBehindHeader)) {
				$transparencyHeight += servicemaster_mikado_get_top_bar_height();
			}
		}

		return $transparencyHeight;
	}

	/**
	 * Returns height of completely transparent header parts
	 *
	 * @return int
	 */
	public function calculateHeightOfCompleteTransparency() {
		$id = servicemaster_mikado_get_page_id();
		$transparencyHeight = 0;

		if (get_post_meta($id, 'mkd_menu_area_background_color_header_minimal_meta', true) !== '') {
			$menuAreaTransparent = get_post_meta($id, 'mkd_menu_area_background_color_header_minimal_meta', true) !== '' &&
				get_post_meta($id, 'mkd_menu_area_background_transparency_header_minimal_meta', true) === '0';
		} elseif (servicemaster_mikado_options()->getOptionValue('menu_area_background_color_header_minimal') == '') {
			$menuAreaTransparent = servicemaster_mikado_options()->getOptionValue('menu_area_grid_background_color_header_minimal') !== '' &&
				servicemaster_mikado_options()->getOptionValue('menu_area_grid_background_transparency_header_minimal') === '0';
		} else {
			$menuAreaTransparent = servicemaster_mikado_options()->getOptionValue('menu_area_background_color_header_minimal') !== '' &&
				servicemaster_mikado_options()->getOptionValue('menu_area_background_transparency_header_minimal') === '0';
		}

		if ($menuAreaTransparent) {
			$transparencyHeight = $this->menuAreaHeight;
		}

		return $transparencyHeight;
	}


	/**
	 * Returns total height of header
	 *
	 * @return int|string
	 */
	public function calculateHeaderHeight() {
		$headerHeight = $this->menuAreaHeight;
		if (servicemaster_mikado_is_top_bar_enabled()) {
			$headerHeight += servicemaster_mikado_get_top_bar_height();
		}

		return $headerHeight;
	}

	/**
	 * Returns total height of mobile header
	 *
	 * @return int|string
	 */
	public function calculateMobileHeaderHeight() {
		$mobileHeaderHeight = $this->mobileHeaderHeight;

		return $mobileHeaderHeight;
	}

	/**
	 * Returns global js variables of header
	 *
	 * @param $globalVariables
	 *
	 * @return int|string
	 */
	public function getGlobalJSVariables($globalVariables) {
		$globalVariables['mkdLogoAreaHeight'] = 0;
		$globalVariables['mkdMenuAreaHeight'] = $this->headerHeight;
		$globalVariables['mkdMobileHeaderHeight'] = $this->mobileHeaderHeight;

		return $globalVariables;
	}

	/**
	 * Returns per page js variables of header
	 *
	 * @param $perPageVars
	 *
	 * @return int|string
	 */
	public function getPerPageJSVariables($perPageVars) {
		//calculate transparency height only if header has no sticky behaviour
		if (!in_array(servicemaster_mikado_get_meta_field_intersect('header_behaviour'), array(
			'sticky-header-on-scroll-up',
			'sticky-header-on-scroll-down-up'
		))
		) {
			$perPageVars['mkdHeaderTransparencyHeight'] = $this->headerHeight - (servicemaster_mikado_get_top_bar_height() + $this->heightOfCompleteTransparency);
		} else {
			$perPageVars['mkdHeaderTransparencyHeight'] = 0;
		}

		return $perPageVars;
	}
}
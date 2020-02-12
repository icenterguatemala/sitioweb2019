<?php
namespace ServiceMaster\Modules\Header\Types;

use ServiceMaster\Modules\Header\Lib\HeaderType;

/**
 * Class that represents Header 'In The Box' layout and option
 *
 * Class HeaderBox
 */
class HeaderBox extends HeaderType {
	protected $heightOfTransparency;
	protected $heightOfCompleteTransparency;
	protected $headerHeight;
	protected $mobileHeaderHeight;

	/**
	 * Sets slug property which is the same as value of option in DB
	 */
	public function __construct() {
		$this->slug = 'header-box';

		if (!is_admin()) {

			$menuAreaHeight = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('menu_area_height_header_box'));
			$this->menuAreaHeight = $menuAreaHeight !== '' ? intval($menuAreaHeight) : 100;

			$mobileHeaderHeight = servicemaster_mikado_filter_px(servicemaster_mikado_options()->getOptionValue('mobile_header_height'));
			$this->mobileHeaderHeight = $mobileHeaderHeight !== '' ? intval($mobileHeaderHeight) : 100;

			add_action('wp', array($this, 'setHeaderHeightProps'));

			add_filter('servicemaster_mikado_js_global_variables', array($this, 'getGlobalJSVariables'));
			add_filter('servicemaster_mikado_per_page_js_vars', array($this, 'getPerPageJSVariables'));
			add_filter('servicemaster_mikado_add_page_custom_style', array($this, 'headerPerPageStyles'));
		}
	}

	public function headerPerPageStyles($style) {
		$id = servicemaster_mikado_get_page_id();
		$class_prefix = servicemaster_mikado_get_unique_page_class();
		$main_menu_grid_style = array();
        $current_style = '';

		$main_menu_grid_selector = array($class_prefix . '.mkd-header-box .mkd-page-header .mkd-menu-area .mkd-grid .mkd-vertical-align-containers');

		/* header in grid style - start */

		$menu_area_grid_background_color = get_post_meta($id, 'mkd_menu_area_grid_background_color_header_box_meta', true);
		$menu_area_grid_background_transparency = 1;


		$menu_area_grid_background_color_rgba = servicemaster_mikado_rgba_color($menu_area_grid_background_color, $menu_area_grid_background_transparency);

		if ($menu_area_grid_background_color_rgba !== null) {
			$main_menu_grid_style['background-color'] = $menu_area_grid_background_color_rgba;
		}

		/* header in grid style - end */

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

		$parameters['menu_area_in_grid'] = servicemaster_mikado_get_meta_field_intersect('menu_area_in_grid_header_box', $id) == 'yes' ? true : false;
		$parameters['menu_area_position'] = servicemaster_mikado_get_meta_field_intersect('menu_area_position_header_box', $id);

		$parameters = apply_filters('servicemaster_mikado_header_box_parameters', $parameters);

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

        $sliderExists        = get_post_meta($id, 'mkd_page_slider_meta', true) !== '';


		$transparencyHeight = 3 * $this->menuAreaHeight / 4;

		if (($sliderExists && servicemaster_mikado_is_top_bar_enabled()) || servicemaster_mikado_is_top_bar_enabled() && servicemaster_mikado_is_top_bar_transparent()
		) {
			$transparencyHeight += servicemaster_mikado_get_top_bar_height();
		}


		return $transparencyHeight;
	}

	/**
	 * Returns height of completely transparent header parts
	 *
	 * @return int
	 */
	public function calculateHeightOfCompleteTransparency() {

		$transparencyHeight = 3 * $this->menuAreaHeight / 4;

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
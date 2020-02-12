<?php
namespace MikadoCore\CPT\Slider;

use MikadoCore\Lib;

/**
 * Class SliderRegister
 * @package MikadoCore\CPT\Slider
 */
class SliderRegister implements Lib\PostTypeInterface {
	/**
	 * @var string
	 */
	private $base;

	public function __construct() {
		$this->base    = 'slides';
		$this->taxBase = 'slides_category';
	}

	/**
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	/**
	 * Registers custom post type with WordPress
	 */
	public function register() {
		$this->registerPostType();
		$this->registerTax();
	}

	/**
	 * Registers custom post type with WordPress
	 */
	private function registerPostType() {
		global $servicemaster_Framework;

		$menuPosition = 5;
		$menuIcon     = 'dashicons-admin-post';

		if(mkd_core_theme_installed()) {
			$menuPosition = $servicemaster_Framework->getSkin()->getMenuItemPosition('slider');
			$menuIcon     = $servicemaster_Framework->getSkin()->getMenuIcon('slider');
		}

		register_post_type($this->base,
			array(
				'labels'        => array(
					'name'          => esc_html__('Mikado Slider', 'mikado-core'),
					'menu_name'     => esc_html__('Mikado Slider', 'mikado-core'),
					'all_items'     => esc_html__('Slides', 'mikado-core'),
					'add_new'       => esc_html__('Add New Slide', 'mikado-core'),
					'singular_name' => esc_html__('Slide', 'mikado-core'),
					'add_item'      => esc_html__('New Slide', 'mikado-core'),
					'add_new_item'  => esc_html__('Add New Slide', 'mikado-core'),
					'edit_item'     => esc_html__('Edit Slide', 'mikado-core')
				),
				'public'        => false,
				'show_in_menu'  => true,
				'rewrite'       => array('slug' => 'slides'),
				'menu_position' => $menuPosition,
				'show_ui'       => true,
				'has_archive'   => false,
				'hierarchical'  => false,
				'supports'      => array('title', 'thumbnail', 'page-attributes'),
				'menu_icon'     => $menuIcon
			)
		);
	}

	/**
	 * Registers custom taxonomy with WordPress
	 */
	private function registerTax() {
		$labels = array(
			'name'              => esc_html__('Sliders', 'mikado-core'),
			'singular_name'     => esc_html__('Slider', 'mikado-core'),
			'search_items'      => esc_html__('Search Sliders', 'mikado-core'),
			'all_items'         => esc_html__('All Sliders', 'mikado-core'),
			'parent_item'       => esc_html__('Parent Slider', 'mikado-core'),
			'parent_item_colon' => esc_html__('Parent Slider:', 'mikado-core'),
			'edit_item'         => esc_html__('Edit Slider', 'mikado-core'),
			'update_item'       => esc_html__('Update Slider', 'mikado-core'),
			'add_new_item'      => esc_html__('Add New Slider', 'mikado-core'),
			'new_item_name'     => esc_html__('New Slider Name', 'mikado-core'),
			'menu_name'         => esc_html__('Sliders', 'mikado-core'),
		);

		register_taxonomy($this->taxBase, array($this->base), array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'query_var'         => true,
			'show_admin_column' => true,
			'rewrite'           => array('slug' => 'slides-category'),
		));
	}
}
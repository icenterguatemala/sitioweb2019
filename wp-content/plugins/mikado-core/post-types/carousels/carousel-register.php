<?php
namespace MikadoCore\CPT\Carousels;

use MikadoCore\Lib;

/**
 * Class CarouselRegister
 * @package MikadoCore\CPT\Carousels
 */
class CarouselRegister implements Lib\PostTypeInterface {
	/**
	 * @var string
	 */
	private $base;
	/**
	 * @var string
	 */
	private $taxBase;

	public function __construct() {
		$this->base = 'carousels';
		$this->taxBase = 'carousels_category';
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
		$menuIcon = 'dashicons-admin-post';
		if (mkd_core_theme_installed()) {
			$menuPosition = $servicemaster_Framework->getSkin()->getMenuItemPosition('carousel');
			$menuIcon = $servicemaster_Framework->getSkin()->getMenuIcon('carousel');
		}

		register_post_type($this->base,
			array(
				'labels'        => array(
					'name'          => esc_html__('Mikado Carousel', 'mikado-core'),
					'menu_name'     => esc_html__('Mikado Carousel', 'mikado-core'),
					'all_items'     => esc_html__('Carousel Items', 'mikado-core'),
					'add_new'       => esc_html__('Add New Carousel Item', 'mikado-core'),
					'singular_name' => esc_html__('Carousel Item', 'mikado-core'),
					'add_item'      => esc_html__('New Carousel Item', 'mikado-core'),
					'add_new_item'  => esc_html__('Add New Carousel Item', 'mikado-core'),
					'edit_item'     => esc_html__('Edit Carousel Item', 'mikado-core')
				),
				'public'        => false,
				'show_in_menu'  => true,
				'rewrite'       => array('slug' => 'carousels'),
				'menu_position' => $menuPosition,
				'show_ui'       => true,
				'has_archive'   => false,
				'hierarchical'  => false,
				'supports'      => array('title'),
				'menu_icon'     => $menuIcon
			)
		);
	}

	/**
	 * Registers custom taxonomy with WordPress
	 */
	private function registerTax() {
		$labels = array(
			'name'              => esc_html__('Carousels', 'mikado-core'),
			'singular_name'     => esc_html__('Carousel', 'mikado-core'),
			'search_items'      => esc_html__('Search Carousels', 'mikado-core'),
			'all_items'         => esc_html__('All Carousels', 'mikado-core'),
			'parent_item'       => esc_html__('Parent Carousel', 'mikado-core'),
			'parent_item_colon' => esc_html__('Parent Carousel:', 'mikado-core'),
			'edit_item'         => esc_html__('Edit Carousel', 'mikado-core'),
			'update_item'       => esc_html__('Update Carousel', 'mikado-core'),
			'add_new_item'      => esc_html__('Add New Carousel', 'mikado-core'),
			'new_item_name'     => esc_html__('New Carousel Name', 'mikado-core'),
			'menu_name'         => esc_html__('Carousels', 'mikado-core'),
		);

		register_taxonomy($this->taxBase, array($this->base), array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'query_var'         => true,
			'show_admin_column' => true,
			'rewrite'           => array('slug' => 'carousels-category'),
		));
	}

}
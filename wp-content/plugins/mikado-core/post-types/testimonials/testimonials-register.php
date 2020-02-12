<?php
namespace MikadoCore\CPT\Testimonials;

use MikadoCore\Lib;


/**
 * Class TestimonialsRegister
 * @package MikadoCore\CPT\Testimonials
 */
class TestimonialsRegister implements Lib\PostTypeInterface {
	/**
	 * @var string
	 */
	private $base;

	public function __construct() {
		$this->base = 'testimonials';
		$this->taxBase = 'testimonials_category';
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
	 * Regsiters custom post type with WordPress
	 */
	private function registerPostType() {
		global $servicemaster_Framework;

		$menuPosition = 5;
		$menuIcon = 'dashicons-admin-post';

		if (mkd_core_theme_installed()) {
			$menuPosition = $servicemaster_Framework->getSkin()->getMenuItemPosition('testimonial');
			$menuIcon = $servicemaster_Framework->getSkin()->getMenuIcon('testimonial');
		}

		register_post_type('testimonials',
			array(
				'labels'        => array(
					'name'          => esc_html__('Testimonials', 'mikado-core'),
					'singular_name' => esc_html__('Testimonial', 'mikado-core'),
					'add_item'      => esc_html__('New Testimonial', 'mikado-core'),
					'add_new_item'  => esc_html__('Add New Testimonial', 'mikado-core'),
					'edit_item'     => esc_html__('Edit Testimonial', 'mikado-core')
				),
				'public'        => false,
				'show_in_menu'  => true,
				'rewrite'       => array('slug' => 'testimonials'),
				'menu_position' => $menuPosition,
				'show_ui'       => true,
				'has_archive'   => false,
				'hierarchical'  => false,
				'supports'      => array('title', 'thumbnail'),
				'menu_icon'     => $menuIcon
			)
		);
	}

	/**
	 * Registers custom taxonomy with WordPress
	 */
	private function registerTax() {
		$labels = array(
			'name'              => esc_html__('Testimonials Categories', 'mikado-core'),
			'singular_name'     => esc_html__('Testimonial Category', 'mikado-core'),
			'search_items'      => esc_html__('Search Testimonials Categories', 'mikado-core'),
			'all_items'         => esc_html__('All Testimonials Categories', 'mikado-core'),
			'parent_item'       => esc_html__('Parent Testimonial Category', 'mikado-core'),
			'parent_item_colon' => esc_html__('Parent Testimonial Category:', 'mikado-core'),
			'edit_item'         => esc_html__('Edit Testimonials Category', 'mikado-core'),
			'update_item'       => esc_html__('Update Testimonials Category', 'mikado-core'),
			'add_new_item'      => esc_html__('Add New Testimonials Category', 'mikado-core'),
			'new_item_name'     => esc_html__('New Testimonials Category Name', 'mikado-core'),
			'menu_name'         => esc_html__('Testimonials Categories', 'mikado-core'),
		);

		register_taxonomy($this->taxBase, array($this->base), array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'query_var'         => true,
			'show_admin_column' => true,
			'rewrite'           => array('slug' => 'testimonials-category'),
		));
	}

}
<?php
namespace MikadoCore\CPT\Portfolio;

use MikadoCore\Lib\PostTypeInterface;

/**
 * Class PortfolioRegister
 * @package MikadoCore\CPT\Portfolio
 */
class PortfolioRegister implements PostTypeInterface {
	/**
	 * @var string
	 */
	private $base;

	public function __construct() {
		$this->base    = 'portfolio-item';
		$this->taxBase = 'portfolio-category';

		add_filter('single_template', array($this, 'registerSingleTemplate'));
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
		$this->registerTagTax();
	}

	/**
	 * Registers portfolio single template if one does'nt exists in theme.
	 * Hooked to single_template filter
	 *
	 * @param $single string current template
	 *
	 * @return string string changed template
	 */
	public function registerSingleTemplate($single) {
		global $post;

		if($post->post_type == $this->base) {
			if(!file_exists(get_template_directory().'/single-portfolio-item.php')) {
				return MIKADO_CORE_CPT_PATH.'/portfolio/templates/single-'.$this->base.'.php';
			}
		}

		return $single;
	}

	/**
	 * Registers custom post type with WordPress
	 */
	private function registerPostType() {
		global $servicemaster_Framework, $servicemaster_options;

		$menuPosition = 5;
		$menuIcon     = 'dashicons-admin-post';
		$slug         = $this->base;

		if(mkd_core_theme_installed()) {
			$menuPosition = $servicemaster_Framework->getSkin()->getMenuItemPosition('portfolio');
			$menuIcon     = $servicemaster_Framework->getSkin()->getMenuIcon('portfolio');

			if(isset($servicemaster_options['portfolio_single_slug'])) {
				if($servicemaster_options['portfolio_single_slug'] != "") {
					$slug = $servicemaster_options['portfolio_single_slug'];
				}
			}
		}

		register_post_type($this->base,
			array(
				'labels'        => array(
					'name'          => esc_html__('Portfolio', 'mikado-core'),
					'singular_name' => esc_html__('Portfolio Item', 'mikado-core'),
					'add_item'      => __('New Portfolio Item', 'mikado-core'),
					'add_new_item'  => esc_html__('Add New Portfolio Item', 'mikado-core'),
					'edit_item'     => esc_html__('Edit Portfolio Item', 'mikado-core')
				),
				'public'        => true,
				'has_archive'   => true,
				'rewrite'       => array('slug' => $slug),
				'menu_position' => $menuPosition,
				'show_ui'       => true,
				'supports'      => array(
					'author',
					'title',
					'editor',
					'thumbnail',
					'excerpt',
					'page-attributes',
					'comments'
				),
				'menu_icon'     => $menuIcon
			)
		);
	}

	/**
	 * Registers custom taxonomy with WordPress
	 */
	private function registerTax() {
		$labels = array(
			'name'              => esc_html__('Portfolio Categories', 'taxonomy general name'),
			'singular_name'     => esc_html__('Portfolio Category', 'taxonomy singular name'),
			'search_items'      => esc_html__('Search Portfolio Categories', 'mikado-core'),
			'all_items'         => esc_html__('All Portfolio Categories', 'mikado-core'),
			'parent_item'       => esc_html__('Parent Portfolio Category', 'mikado-core'),
			'parent_item_colon' => esc_html__('Parent Portfolio Category:', 'mikado-core'),
			'edit_item'         => esc_html__('Edit Portfolio Category', 'mikado-core'),
			'update_item'       => esc_html__('Update Portfolio Category', 'mikado-core'),
			'add_new_item'      => esc_html__('Add New Portfolio Category', 'mikado-core'),
			'new_item_name'     => esc_html__('New Portfolio Category Name', 'mikado-core'),
			'menu_name'         => esc_html__('Portfolio Categories', 'mikado-core'),
		);

		register_taxonomy($this->taxBase, array($this->base), array(
			'hierarchical' => true,
			'labels'       => $labels,
			'show_ui'      => true,
			'query_var'    => true,
			'rewrite'      => array('slug' => 'portfolio-category'),
		));
	}

	/**
	 * Registers custom tag taxonomy with WordPress
	 */
	private function registerTagTax() {
		$labels = array(
			'name'              => esc_html__('Portfolio Tags', 'taxonomy general name'),
			'singular_name'     => esc_html__('Portfolio Tag', 'taxonomy singular name'),
			'search_items'      => esc_html__('Search Portfolio Tags', 'mikado-core'),
			'all_items'         => esc_html__('All Portfolio Tags', 'mikado-core'),
			'parent_item'       => esc_html__('Parent Portfolio Tag', 'mikado-core'),
			'parent_item_colon' => esc_html__('Parent Portfolio Tags:', 'mikado-core'),
			'edit_item'         => esc_html__('Edit Portfolio Tag', 'mikado-core'),
			'update_item'       => esc_html__('Update Portfolio Tag', 'mikado-core'),
			'add_new_item'      => esc_html__('Add New Portfolio Tag', 'mikado-core'),
			'new_item_name'     => esc_html__('New Portfolio Tag Name', 'mikado-core'),
			'menu_name'         => esc_html__('Portfolio Tags', 'mikado-core'),
		);

		register_taxonomy('portfolio-tag', array($this->base), array(
			'hierarchical' => false,
			'labels'       => $labels,
			'show_ui'      => true,
			'query_var'    => true,
			'rewrite'      => array('slug' => 'portfolio-tag'),
		));
	}
}
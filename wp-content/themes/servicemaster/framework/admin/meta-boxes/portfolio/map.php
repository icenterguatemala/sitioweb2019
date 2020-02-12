<?php

if (!function_exists('servicemaster_mikado_portfolio_meta_box_map')) {
	function servicemaster_mikado_portfolio_meta_box_map() {

		$mkd_pages = array();
		$pages = get_pages();
		global $servicemaster_Framework;

		foreach($pages as $page) {
			$mkd_pages[$page->ID] = $page->post_title;
		}

		//Portfolio Images

		$mkdPortfolioImages = new ServiceMasterMikadoMetaBox("portfolio-item", esc_html__('Portfolio Images (multiple upload)','servicemaster'), '', '', 'portfolio_images');
		$servicemaster_Framework->mkdMetaBoxes->addMetaBox("portfolio_images",$mkdPortfolioImages);

		$mkd_portfolio_image_gallery = new ServiceMasterMikadoMultipleImages("mkd_portfolio-image-gallery", esc_html__('Portfolio Images','servicemaster'), esc_html('Choose your portfolio images','servicemaster'));
		$mkdPortfolioImages->addChild("mkd_portfolio-image-gallery",$mkd_portfolio_image_gallery);

		//Portfolio Images/Videos 2

		$mkdPortfolioImagesVideos2 = new ServiceMasterMikadoMetaBox("portfolio-item", esc_html__('Portfolio Images/Videos (single upload)','servicemaster'));
		$servicemaster_Framework->mkdMetaBoxes->addMetaBox("portfolio_images_videos2",$mkdPortfolioImagesVideos2);

		$mkd_portfolio_images_videos2 = new ServiceMasterMikadoImagesVideosFramework(esc_html__('Portfolio Images/Videos 2', 'servicemaster'),esc_html__('ThisIsDescription', 'servicemaster'));
		$mkdPortfolioImagesVideos2->addChild("mkd_portfolio_images_videos2",$mkd_portfolio_images_videos2);

		//Portfolio Additional Sidebar Items

		$mkdAdditionalSidebarItems = new ServiceMasterMikadoMetaBox("portfolio-item", esc_html__('Additional Portfolio Sidebar Items' , 'servicemaster'));
		$servicemaster_Framework->mkdMetaBoxes->addMetaBox("portfolio_properties",$mkdAdditionalSidebarItems);

		$mkd_portfolio_properties = new ServiceMasterMikadoOptionsFramework(esc_html__('Portfolio Properties','servicemaster'),esc_html__('ThisIsDescription','servicemaster'));
		$mkdAdditionalSidebarItems->addChild("mkd_portfolio_properties",$mkd_portfolio_properties);

	}
	add_action('servicemaster_mikado_meta_boxes_map', 'servicemaster_mikado_portfolio_meta_box_map');
}
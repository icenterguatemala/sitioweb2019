<?php get_header(); ?>

<div class="mkd-container mkd-404-page">
	<?php do_action('servicemaster_mikado_after_container_open'); ?>
	<div class="mkd-page-not-found">
		<h6 class="mkd-error-page-subtitle">
			<?php if (servicemaster_mikado_options()->getOptionValue('404_subtitle')) {
				echo esc_html(servicemaster_mikado_options()->getOptionValue('404_subtitle'));
			} else {
				esc_html_e('404 error page', 'servicemaster');
			} ?>
		</h6>

		<h1 class="mkd-error-page-title">
			<?php if (servicemaster_mikado_options()->getOptionValue('404_title')) {
				echo esc_html(servicemaster_mikado_options()->getOptionValue('404_title'));
			} else {
				esc_html_e('Sorry, something went wrong', 'servicemaster');
			} ?>
		</h1>
		<?php
		if (servicemaster_mikado_core_installed()) {
			$params = array('custom_class' => 'mkd-404-button');
			if (servicemaster_mikado_options()->getOptionValue('404_back_to_home')) {
				$params['text'] = servicemaster_mikado_options()->getOptionValue('404_back_to_home');
			} else {
				$params['text'] = esc_html__('Homepage Now', 'servicemaster');
			}

			$params['link'] = esc_url(home_url('/'));
			$params['target'] = '_self';
			echo servicemaster_mikado_execute_shortcode('mkd_button', $params);
		} ?>
		<div class="mkd-404-image">
			<img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/404.png') ?>"
				 alt="<?php esc_attr_e('404', 'servicemaster'); ?>"/>
		</div>
	</div>
	<?php do_action('servicemaster_mikado_before_container_close'); ?>
</div>

<?php wp_footer(); ?>

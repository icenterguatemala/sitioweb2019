<?php if (servicemaster_mikado_options()->getOptionValue('portfolio_single_hide_author') !== 'yes') : ?>

	<div class="mkd-portfolio-author-holder clearfix">
		<div class="mkd-image-author-holder clearfix">
			<div class="mkd-author-description-image">
				<?php echo servicemaster_mikado_kses_img(get_avatar(get_the_author_meta('ID'), 40)); ?>
			</div>
			<div class="mkd-author-name-position">
				<h6 class="mkd-author-name">
					<span class="mkd-label-by"><?php esc_html_e('by', 'servicemaster'); ?></span>
					<?php
					if (get_the_author_meta('first_name') != "" || get_the_author_meta('last_name') != "") {
						echo esc_attr(get_the_author_meta('first_name')) . " " . esc_attr(get_the_author_meta('last_name'));
					} else {
						echo esc_attr(get_the_author_meta('display_name'));
					}
					?>
				</h6>
			</div>
		</div>
	</div>
<?php endif; ?>
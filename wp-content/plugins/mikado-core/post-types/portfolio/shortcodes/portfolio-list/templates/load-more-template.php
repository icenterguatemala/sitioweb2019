<?php if ($query_results->max_num_pages > 1) { ?>
	<div class="mkd-ptf-list-paging">
		<span class="mkd-ptf-list-load-more">
			<?php if (mkd_core_theme_installed()) : ?>
				<?php
				echo servicemaster_mikado_get_button_html(array(
					'link' => 'javascript: void(0)',
					'text' => esc_html__('Load More', 'mikado-core'),
					'size' => 'small'
				));
				?>
			<?php else: ?>
				<a href="javascript: void(0)"><?php esc_html_e('Load More', 'mikado-core'); ?></a>
			<?php endif; ?>
		</span>
	</div>
<?php }
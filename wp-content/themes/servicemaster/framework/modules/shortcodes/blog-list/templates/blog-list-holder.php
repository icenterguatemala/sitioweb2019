<div <?php servicemaster_mikado_class_attribute($holder_classes); ?> <?php echo servicemaster_mikado_get_inline_attrs($data_attrs); ?>>
	<?php if ($type !== 'masonry') { ?>
	<div class="mkd-blog-list clearfix">
		<?php } ?>
		<?php if ($type == 'masonry') { ?>
			<div class="mkd-blog-masonry-grid-sizer"></div>
			<div class="mkd-blog-masonry-grid-gutter"></div>
		<?php }
		$html = '';
		$post_count = 1;
		if ($query_result->have_posts()):
			while ($query_result->have_posts()) : $query_result->the_post();
				if (($post_count % $columns === 1 || $columns == 1) && $type === 'simple') {
					$html .= '<div class="mkd-blog-list-row clearfix">';
				}
				$html .= servicemaster_mikado_get_shortcode_module_template_part('templates/' . $type, 'blog-list', '', $params);

				if (($post_count % $columns === 0 || $columns == 1) && $type === 'simple') {
					$html .= '</div>';
				}
				$post_count++;
			endwhile;

			//if posts number less than columns
			if ((($post_count - 1) % $columns !== 0 && $columns != 1) && $type === 'simple') {
				$html .= '</div>';
			}
			print $html;
		else: ?>
			<div class="mkd-blog-list-messsage">
				<p><?php esc_html_e('No posts were found.', 'servicemaster'); ?></p>
			</div>
		<?php endif;
		wp_reset_postdata();
		?>
		<?php if ($type !== 'masonry') { ?>
	</div>
<?php } ?>
</div>

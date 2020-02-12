<?php if(is_array($features) && count($features)) : ?>
	<div <?php servicemaster_mikado_class_attribute($holder_classes); ?>>
		<div class="mkd-cpt-features-holder mkd-cpt-table">
			<div class="mkd-cpt-features-title-holder mkd-cpt-table-head-holder">
				<div class="mkd-cpt-table-head-holder-inner">
					<h3 class="mkd-cpt-features-title"><?php echo wp_kses_post(preg_replace('#^<\/p>|<p>$#', '', $title)); ?></h3>
				</div>
			</div>
			<div class="mkd-cpt-features-list-holder mkd-cpt-table-content">
				<ul class="mkd-cpt-features-list">
					<?php foreach($features as $feature) : ?>
						<li class="mkd-cpt-features-item"><h6><?php echo esc_html($feature); ?></h6></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php echo do_shortcode($content); ?>
	</div>
<?php endif; ?>
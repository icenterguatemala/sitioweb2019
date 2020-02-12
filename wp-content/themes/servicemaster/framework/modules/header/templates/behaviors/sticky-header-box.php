<?php do_action('servicemaster_mikado_before_sticky_header'); ?>

	<div class="mkd-sticky-header">
		<?php do_action('servicemaster_mikado_after_sticky_menu_html_open'); ?>
		<div class="mkd-sticky-holder">
			<?php if($sticky_header_in_grid) : ?>
			<div class="mkd-grid">
				<?php endif; ?>
				<div class=" mkd-vertical-align-containers">
					<div class="mkd-position-left">
						<div class="mkd-position-left-inner">
							<?php if (!$hide_logo) {
								servicemaster_mikado_get_logo('sticky');
							} ?>
						</div>
						<?php if ($menu_area_position === 'left') {
							servicemaster_mikado_get_sticky_menu('mkd-sticky-nav');
						}
						?>
					</div>
					<?php
					if ($menu_area_position === 'center') { ?>
						<div class="mkd-position-center">
							<div class="mkd-position-center-inner">
								<?php servicemaster_mikado_get_sticky_menu('mkd-sticky-nav'); ?>
							</div>
						</div>
					<?php } ?>
					<div class="mkd-position-right">
						<div class="mkd-position-right-inner">
							<?php
							if ($menu_area_position === 'right') {
								servicemaster_mikado_get_sticky_menu('mkd-sticky-nav');
							}
							?>
							<?php if(get_post_meta(servicemaster_mikado_get_page_id(), 'mkd_custom_sidebar_header_standard_meta', true) !== ''){ ?>
								<div class="mkd-sticky-right-widget">
									<div class="mkd-sticky-right-widget-inner">
										<?php dynamic_sidebar(get_post_meta(servicemaster_mikado_get_page_id(), 'mkd_custom_sidebar_header_standard_meta', true));?>
									</div>
								</div>
							<?php }else if(is_active_sidebar('mkd-sticky-right')){ ?>
								<div class="mkd-sticky-right-widget-area">
									<?php dynamic_sidebar('mkd-sticky-right'); ?>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php if($sticky_header_in_grid) : ?>
			</div>
		<?php endif; ?>
		</div>
	</div>

<?php do_action('servicemaster_mikado_after_sticky_header'); ?>
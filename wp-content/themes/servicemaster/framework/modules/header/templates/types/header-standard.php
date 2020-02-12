<?php do_action('servicemaster_mikado_before_page_header'); ?>

<header class="mkd-page-header">
	<?php if ($show_fixed_wrapper) : ?>
	<div class="mkd-fixed-wrapper">
		<?php endif; ?>
		<div class="mkd-menu-area">
			<?php if ($menu_area_in_grid) : ?>
			<div class="mkd-grid">
				<?php endif; ?>
				<?php do_action('servicemaster_mikado_after_header_menu_area_html_open') ?>
				<div class="mkd-vertical-align-containers">
					<div class="mkd-position-left">
						<div class="mkd-position-left-inner">
							<?php if (!$hide_logo) {
								servicemaster_mikado_get_logo();
							} ?>
						</div>
						<?php if ($menu_area_position === 'left') {
							servicemaster_mikado_get_main_menu();
						}
						?>
					</div>
					<?php
					if ($menu_area_position === 'center') { ?>
						<div class="mkd-position-center">
							<div class="mkd-position-center-inner">
								<?php servicemaster_mikado_get_main_menu(); ?>
							</div>
						</div>
					<?php } ?>
					<div class="mkd-position-right">
						<div class="mkd-position-right-inner">
							<?php
							if ($menu_area_position === 'right') {
								servicemaster_mikado_get_main_menu();
							}
							if (get_post_meta(servicemaster_mikado_get_page_id(), 'mkd_custom_sidebar_header_standard_meta', true) !== '') { ?>
								<div class="mkd-right-from-main-menu-widget">
									<div class="mkd-right-from-main-menu-widget-inner">
										<?php dynamic_sidebar(get_post_meta(servicemaster_mikado_get_page_id(), 'mkd_custom_sidebar_header_standard_meta', true)); ?>
									</div>
								</div>
							<?php } else if (is_active_sidebar('mkd-right-from-main-menu')) { ?>
								<div class="mkd-main-menu-widget-area">
									<div class="mkd-main-menu-widget-area-inner">
										<?php dynamic_sidebar('mkd-right-from-main-menu'); ?>
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php if ($menu_area_in_grid) : ?>
			</div>
		<?php endif; ?>
		</div>
		<?php if ($show_fixed_wrapper) : ?>
	</div>
<?php endif; ?>
	<?php if ($show_sticky) {
		servicemaster_mikado_get_sticky_header('standard');
	} ?>
</header>

<?php do_action('servicemaster_mikado_after_page_header'); ?>


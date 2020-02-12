<?php do_action('servicemaster_mikado_before_sticky_header'); ?>

	<div class="mkd-sticky-header">
		<?php do_action('servicemaster_mikado_after_sticky_menu_html_open'); ?>
		<div class="mkd-sticky-holder">
			<?php if($sticky_header_in_grid) : ?>
			<div class="mkd-grid">
				<?php endif; ?>
                <div class="mkd-vertical-align-containers">
                    <div class="mkd-position-left">
                        <div class="mkd-position-left-inner">
                            <?php servicemaster_mikado_get_divided_left_main_menu('sticky'); ?>
                        </div>
                    </div>
                    <div class="mkd-position-center">
                        <div class="mkd-position-center-inner">
                            <?php if(!$hide_logo) {
                                servicemaster_mikado_get_logo('divided-sticky');
                            } ?>
                        </div>
                    </div>
                    <div class="mkd-position-right">
                        <div class="mkd-position-right-inner">
                            <?php servicemaster_mikado_get_divided_right_main_menu('sticky'); ?>
                        </div>
                    </div>
                </div>
				<?php if($sticky_header_in_grid) : ?>
			</div>
		<?php endif; ?>
		</div>
	</div>

<?php do_action('servicemaster_mikado_after_sticky_header'); ?>
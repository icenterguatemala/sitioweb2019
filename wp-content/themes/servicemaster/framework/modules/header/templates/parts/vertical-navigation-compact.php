<?php do_action('servicemaster_mikado_before_top_navigation'); ?>
<div class="mkd-vertical-menu-outer">
	<nav data-navigation-type='float' class="mkd-vertical-menu mkd-vertical-dropdown-float">
		<?php
		wp_nav_menu(array(
			'theme_location'  => 'vertical-compact-navigation',
			'container'       => '',
			'container_class' => '',
			'menu_class'      => '',
			'menu_id'         => '',
			'fallback_cb'     => 'top_navigation_fallback',
			'link_before'     => '<span>',
			'link_after'      => '</span>',
			'walker'          => new ServiceMasterMikadoVerticalCompactNavigationWalker()
		));
		?>
	</nav>
</div>
<?php do_action('servicemaster_mikado_after_top_navigation'); ?>
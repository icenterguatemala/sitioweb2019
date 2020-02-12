<div class="mkd-team-slide">
	<div class="mkd-team-slide-inner">
		<?php if ($team_member_image !== ''): ?>
			<div class="mkd-member-image">
				<?php echo wp_get_attachment_image($team_member_image, 'full'); ?>
			</div>
		<?php endif; ?>
		<div class="mkd-content">
			<?php if ($name !== ''): ?>
				<h3 class="mkd-name">
					<?php echo esc_html($name); ?>
				</h3>
			<?php endif; ?>
			<?php if ($position !== ''): ?>
				<div class="mkd-position">
					<?php echo esc_html($position); ?>
				</div>
			<?php endif; ?>
			<?php if ($text !== ''): ?>
				<div class="mkd-text">
					<?php echo esc_html($text); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
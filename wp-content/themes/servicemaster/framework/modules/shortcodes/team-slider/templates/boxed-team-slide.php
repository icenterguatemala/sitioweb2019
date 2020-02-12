<div class="mkd-team-slide">
	<div class="mkd-team-slide-inner">
		<div class="mkd-logo-text">
			<?php if ($logo_image !== ''): ?>
				<div class="mkd-logo-image">
					<?php echo wp_get_attachment_image($logo_image, 'full'); ?>
				</div>
			<?php endif; ?>
			<?php if ($text !== ''): ?>
				<div class="mkd-text">
					<?php echo esc_html($text); ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="mkd-team-member-info">
			<?php if ($team_member_image !== ''): ?>
				<div class="mkd-member-image">
					<?php echo wp_get_attachment_image($team_member_image, 'full'); ?>
				</div>
			<?php endif; ?>
			<?php if ($name !== ''): ?>
				<h5 class="mkd-name">
					<?php echo esc_html($name); ?>
				</h5>
			<?php endif; ?>
			<?php if ($position !== ''): ?>
				<div class="mkd-position">
					<?php echo esc_html($position); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
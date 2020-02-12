<?php
/**
 * Team simple shortcode template
 */
global $servicemaster_IconCollections;
$number_of_social_icons = 5;
?>

<div class="mkd-team">
	<div class="mkd-team-inner">
		<?php if ($team_image !== '') { ?>
			<div class="mkd-team-image">
				<img src="<?php print $team_image_src; ?>" alt="mkd-team-image"/>
			</div>
		<?php } ?>

		<?php if ($team_name !== '' || $team_position !== '' || $team_description != "") { ?>
			<div class="mkd-team-info">
				<?php if ($team_name !== '' || $team_position !== '') { ?>
					<div class="mkd-team-title-holder">
						<?php if ($team_name !== '') { ?>
							<h5 class="mkd-team-name"><?php echo esc_attr($team_name); ?></h5>
						<?php } ?>
						<?php if ($team_position !== "") { ?>
							<h6 class="mkd-team-position"><?php echo esc_attr($team_position) ?></h6>
						<?php } ?>
					</div>
				<?php } ?>

				<?php if ($team_description != "") { ?>
					<div class="mkd-team-text">
						<div class="mkd-team-text-inner">
							<div class="mkd-team-description">
								<p><?php echo esc_attr($team_description) ?></p>
							</div>
						</div>
					</div>
				<?php }
				if (!empty($link) && !empty($link_text)) :?>
					<div class="mkd-team-button">
						<?php echo servicemaster_mikado_get_button_html($button_parameters);?>
					</div>
				<?php endif; ?>
				<div class="mkd-team-social-holder-between">
					<div class="mkd-team-social">
						<div class="mkd-team-social-inner">
							<div class="mkd-team-social-wrapp">

								<?php foreach ($team_social_icons as $team_social_icon) {
									print $team_social_icon;
								} ?>

							</div>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
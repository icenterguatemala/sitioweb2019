<?php if ($response->status) : ?>
	<?php if (is_array($response->data) && count($response->data)) : ?>
		<div class="mkd-twitter-slider clearfix">
			<span aria-hidden="true" class="twitter-icon mkd-icon-font-elegant social_twitter" <?php servicemaster_mikado_inline_style($tweet_styles); ?>></span>
			<div class="mkd-twitter-slider-inner" <?php servicemaster_mikado_inline_style($tweet_styles); ?>>
				<?php foreach ($response->data as $tweet) : ?>
					<div class="item mkd-twitter-slider-item">
						<h2>
							<?php echo MikadoTwitterApi::getInstance()->getHelper()->getTweetText($tweet); ?>
						</h2>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

<?php else: ?>
	<?php echo esc_html($response->message); ?>
<?php endif; ?>

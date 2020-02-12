<div class="mkd-rf-holder <?php echo esc_attr($holder_classes); ?>">
	<?php if($open_table_id !== '') : ?>
		<form class="mkd-rf" target="_blank" action="http://www.opentable.com/restaurant-search.aspx" name="mkd-rf">
			<div class="mkd-rf-row clearfix">
				<div class="mkd-rf-col-holder">
					<div class="mkd-rf-field-holder clearfix">
						<select name="partySize" class="mkd-ot-people">
							<option value="1"><?php esc_html_e('1 Person', 'servicemaster'); ?></option>
							<option value="2"><?php esc_html_e('2 People', 'servicemaster'); ?></option>
							<option value="3"><?php esc_html_e('3 People', 'servicemaster'); ?></option>
							<option value="4"><?php esc_html_e('4 People', 'servicemaster'); ?></option>
							<option value="5"><?php esc_html_e('5 People', 'servicemaster'); ?></option>
							<option value="6"><?php esc_html_e('6 People', 'servicemaster'); ?></option>
							<option value="7"><?php esc_html_e('7 People', 'servicemaster'); ?></option>
							<option value="8"><?php esc_html_e('8 People', 'servicemaster'); ?></option>
							<option value="9"><?php esc_html_e('9 People', 'servicemaster'); ?></option>
							<option value="10"><?php esc_html_e('10 People', 'servicemaster'); ?></option>
						</select>
					<span class="mkd-rf-icon">
						<span class="icon-user-follow"></span>
					</span>
					</div>
				<span class="mkd-rf-label">
					<?php esc_html_e('For', 'servicemaster'); ?>
				</span>
				</div>
				<div class="mkd-rf-col-holder">
					<div class="mkd-rf-field-holder clearfix">
						<input type="text" value="<?php echo date('m/d/Y'); ?>" class="mkd-ot-date" name="startDate">
					<span class="mkd-rf-icon">
						<span class="icon-calendar"></span>
					</span>
					</div>
				<span class="mkd-rf-label">
					<?php esc_html_e('At', 'servicemaster'); ?>
				</span>

				</div>
				<div class="mkd-rf-col-holder mkd-rf-time-col">
					<div class="mkd-rf-field-holder clearfix">
						<select name="ResTime" class="mkd-ot-time">
							<option value="5:30pm">6:30 am</option>
							<option value="5:30pm">7:00 am</option>
							<option value="5:30pm">7:30 am</option>
							<option value="5:30pm">8:00 am</option>
							<option value="5:30pm">8:30 am</option>
							<option value="5:30pm">9:00 am</option>
							<option value="5:30pm">9:30 am</option>
							<option value="5:30pm">10:00 am</option>
							<option value="5:30pm">10:30 am</option>
							<option value="5:30pm">11:00 am</option>
							<option value="5:30pm">11:30 am</option>
							<option value="5:30pm">12:00 pm</option>
							<option value="5:30pm">12:30 pm</option>
							<option value="5:30pm">1:00 pm</option>
							<option value="5:30pm">1:30 pm</option>
							<option value="5:30pm">2:00 pm</option>
							<option value="5:30pm">2:30 pm</option>
							<option value="5:30pm">3:00 pm</option>
							<option value="5:30pm">3:30 pm</option>
							<option value="5:30pm">4:00 pm</option>
							<option value="5:30pm">4:30 pm</option>
							<option value="5:30pm">5:00 pm</option>
							<option value="5:30pm">5:30 pm</option>
							<option value="6:00pm">6:00 pm</option>
							<option value="6:30pm">6:30 pm</option>
							<option value="7:00pm" selected="selected">7:00 pm</option>
							<option value="7:30pm">7:30 pm</option>
							<option value="8:00pm">8:00 pm</option>
							<option value="8:30pm">8:30 pm</option>
							<option value="9:00pm">9:00 pm</option>
							<option value="9:30pm">9:30 pm</option>
							<option value="10:00pm">10:00 pm</option>
							<option value="10:30pm">10:30 pm</option>
							<option value="11:00pm">11:00 pm</option>
							<option value="11:30pm">11:30 pm</option>
						</select>
					<span class="mkd-rf-icon">
						<span class="icon-clock"></span>
					</span>
					</div>
				</div>
				<div class="mkd-rf-col-holder mkd-rf-btn-holder">

						<?php echo servicemaster_mikado_get_button_html(
							array(
								'html_type'    => 'button',
								'text'         => __('Book a Table', 'servicemaster'),
								'input_name'   => 'mkd-rf-submit',
								'size'         => 'small'
							)
						) ?>
				</div>
			</div>

			<input type="hidden" name="RestaurantID" class="RestaurantID" value="<?php echo esc_attr($open_table_id); ?>">
			<input type="hidden" name="rid" class="rid" value="<?php echo esc_attr($open_table_id); ?>">
			<input type="hidden" name="GeoID" class="GeoID" value="15">
			<input type="hidden" name="txtDateFormat" class="txtDateFormat" value="MM/dd/yyyy">
			<input type="hidden" name="RestaurantReferralID" class="RestaurantReferralID" value="<?php echo esc_attr($open_table_id); ?>">

		</form>
	<?php else: ?>
		<p><?php esc_html_e('You haven\'t added OpenTable ID', 'servicemaster'); ?></p>
	<?php endif; ?>
</div>
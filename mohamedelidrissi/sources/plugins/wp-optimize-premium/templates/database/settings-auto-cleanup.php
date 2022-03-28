<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>

	<h3><?php _e('Scheduled clean-up settings', 'wp-optimize'); ?></h3>

	<div class="wpo-fieldgroup">
		<p><a href="#" id="wpo-add-event" class="wpo-repeater__add"><span class="dashicons dashicons-plus"></span> <?php _e('Add scheduled task', 'wp-optimize'); ?></a></p>
		<div id="wp-optimize-auto-options">
			<div class="wpo_auto_event_heading wpo_no_schedules"><?php echo __('There are no scheduled tasks.', 'wp-optimize'); ?></div>
			<div id="save_settings_reminder" class="save_settings_reminder"><?php _e('Remember to save your settings so that your changes take effect.', 'wp-optimize');?></div>
			<p class="innodb_force_optimize--container<?php echo $show_innodb_option ? '' : ' hidden'; ?>">
				<span class="dashicons dashicons-warning"></span>
				<strong><?php _e('Tables using the InnoDB engine', 'wp-optimize'); ?></strong><br>
				<input name="auto-innodb" id="auto-innodb" type="checkbox" value="true" <?php checked($options->get_option('auto-innodb'), 'true'); ?>>
				<label for="auto-innodb"><?php _e('Optimize InnoDB tables anyway.', 'wp-optimize'); ?></label>
				<a href="https://getwpo.com/faqs/" target="_blank"><?php _e('Warning: you should read the FAQ about the risks of this operation first.', 'wp-optimize'); ?></a>
			</p>
			<div id="wpo_auto_events" class="wpo_cf">
				<?php

				$auto_options = WP_Optimize_Premium()->get_scheduled_optimizations();
				if (!empty($auto_options)) {
				?>
					<div class="wpo_auto_event_heading_container wpo_cf">
						<div class="wpo_optimizations wpo_auto_event_heading"><?php _e('Optimizations', 'wp-optimize'); ?></div>
						<div class="wpo_schedule wpo_auto_event_heading"><?php _e('Type', 'wp-optimize'); ?></div>
						<div class="wpo_schedule_details wpo_auto_event_heading"><?php _e('Schedule', 'wp-optimize'); ?></div>
						<div class="wpo_schedule_status wpo_auto_event_heading"><?php _e('Status', 'wp-optimize'); ?></div>
						<div class="wpo_actions wpo_auto_event_heading"><?php _e('Actions', 'wp-optimize'); ?></div>
					</div>
				<?php
				}

				foreach ($auto_options as $index => $event) {
					$div_schedule_fields_opened = false;
					wpo_display_event_summary($index, $event);
					printf('<div class="wpo_auto_event wpo_cf" data-count="%s" style="display: none;">', $index);
					foreach ($event as $key => $value) {
						switch ($key) {
							case 'optimization':
								wpo_display_optimizations($value, $index);
								break;
							case 'schedule_type':
								wpo_display_schedule_type($value, $index);
								echo '<div class="wpo_schedule_fields">';
								$div_schedule_fields_opened = true;
								break;
							case 'date':
								wpo_display_date($value, $index);
								break;
							case 'time':
								wpo_display_time($value, $index);
								break;
							case 'week':
								wpo_display_week($value, $index);
								break;
							case 'day':
								wpo_display_week_day($value, $index);
								break;
							case 'day_number':
								wpo_display_day_number($value, $index);
								break;
							case 'status':
								if ($div_schedule_fields_opened) {
									echo '</div>';
									$div_schedule_fields_opened = false;
								}
								wpo_display_status($value, $index);
								break;
						}
					}

					if ($div_schedule_fields_opened) {
						echo '</div>';
						$div_schedule_fields_opened = false;
					}

					wpo_display_actions($index);
					echo '</div>';
				}
				?>
			</div>
		</div>
	</div>
<?php
/**
 * Displays scheduled event summary
 *
 * @param integer $index Count of events
 * @param array   $event An array with event details
 *
 * @return void
 */
function wpo_display_event_summary($index, $event) {
	// var_dump($event);
	printf('<div class="wpo_scheduled_event wpo_cf" data-count="%s">'."\n", $index);

	if (isset($event['optimization']) && is_array($event['optimization'])) wpo_display_optimization_list($event['optimization']);
	if (isset($event['schedule_type'])) {
	?>

	<div class="wpo_schedule"><?php echo ucfirst(substr($event['schedule_type'], 4)); ?></div>

	<?php } ?>
	<div class="wpo_schedule_details">
		<?php
		if (isset($event['date'])) {
			printf('<span class="wpo_schedule_date">%s</span>', sprintf(__('Date: %s', 'wp-optimize'), $event['date']));
		}
		if (isset($event['time'])) {
			printf('<span class="wpo_schedule_time">%s</span>', sprintf(__('Time: %s', 'wp-optimize'), $event['time']));
		}
		if (isset($event['week'])) {
			printf('<span class="wpo_schedule_week">%s</span>', sprintf(__('Week: %s', 'wp-optimize'), $event['week']));
		}
		if (isset($event['day'])) {
			$week_days = WP_Optimize_Premium::get_week_days();
			printf('<span class="wpo_schedule_week_day">%s</span>', sprintf(__('Day: %s', 'wp-optimize'), $week_days[$event['day']]));
		}
		if (isset($event['day_number'])) {
			printf('<span class="wpo_schedule_day_number">%s</span>', sprintf(__('Date: %s', 'wp-optimize'), $event['day_number']));
		}
		?>
	</div>
	<?php if (isset($event['status'])) { ?>

			<div class="wpo_schedule_status">
				<?php echo ('1' == $event['status']) ? __('Active', 'wp-optimize') : __('Inactive', 'wp-optimize'); ?>
			</div>
	<?php
	}

	wpo_display_actions($index, true);
	?>

	</div> <!-- End of .wpo_scheduled_event -->

	<?php
}

/**
 * Displays available auto optimizations as select field
 *
 * @param array   $optimizations An array of available auto optimizations
 * @param integer $count         Index of scheduled events
 *
 * @return void
 */
function wpo_display_optimizations($optimizations, $count) {
	$auto_optimizations = WP_Optimize_Premium::get_auto_optimizations();
	$html = '';
	$html .= sprintf('<select class="wpo_auto_optimizations" name="wp-optimize-auto[%s][optimization][]" multiple="multiple">', $count);
	foreach ($auto_optimizations as $id => $details) {
		$selected = in_array($id, $optimizations) ? 'selected="selected"' : '';
		$html .= sprintf('<option value="%1$s" %2$s>%3$s</option>', $id, $selected, $details['optimization']);
	}
	$html .= '</select>';
	echo $html;
}

/**
 * Displays schedule type as select field
 *
 * @param array   $schedule_type Date value
 * @param integer $count         Index of scheduled events
 *
 * @return void
 */
function wpo_display_schedule_type($schedule_type, $count) {
	$schedule_types = WP_Optimize_Premium::get_schedule_types();
	$html = '';
	$html .= sprintf('<select class="wpo_schedule_type" name="wp-optimize-auto[%s][schedule_type]">', $count);
	foreach ($schedule_types as $key => $value) {
		$html .= sprintf('<option value="%1$s" %2$s>%3$s</option>', $key, selected($schedule_type, $key, false), $value);
	}
	$html .= '</select>';
	echo $html;
}

/**
 * Displays date field
 *
 * @param string  $date  Date value
 * @param integer $count Index of scheduled events
 *
 * @return void
 */
function wpo_display_date($date, $count) {
	$today = date("Y-m-d");
	$html = '';
	$html .= sprintf('<label>%s', __('Date:', 'wp-optimize'));
	$html .= sprintf('<input type="date" name="wp-optimize-auto[%1$s][date]" value="%2$s" min="%3$s">', $count, $date, $today);
	$html .= '</label>';
	echo $html;
}

/**
 * Displays time field
 *
 * @param string  $time  Time value
 * @param integer $count Index of scheduled events
 *
 * @return void
 */
function wpo_display_time($time, $count) {
	$html = '';
	$html .= sprintf('<label>%s', __('Time:', 'wp-optimize'));
	$html .= sprintf('<input type="time" name="wp-optimize-auto[%1$s][time]" value="%2$s">', $count, $time);
	$html .= '</label>';
	echo $html;
}

/**
 * Displays week select field
 *
 * @param array   $week  An array of 2 weeks
 * @param integer $count Index of scheduled events
 *
 * @return void
 */
function wpo_display_week($week, $count) {
	$weeks = array('1st' => __('1st', 'wp-optimize'), '2nd' => __('2nd', 'wp-optimize'));
	$html = '';
	$html .= sprintf('<select class="wpo_week_number" name="wp-optimize-auto[%s][week]">', $count);
	foreach ($weeks as $key => $value) {
		$html .= sprintf('<option value="%1$s" %2$s>%3$s</option>', $key, selected($week, $key, false), $value);
	}
	$html .= '</select>';
	echo $html;
}

/**
 * Displays week days select field
 *
 * @param array   $week_day An array of week days
 * @param integer $count    Index of scheduled events
 *
 * @return void
 */
function wpo_display_week_day($week_day, $count) {
	$week_days = WP_Optimize_Premium::get_week_days();
	$html = '';
	$html .= sprintf('<select class="wpo_week_days" name="wp-optimize-auto[%s][day]">', $count);
	foreach ($week_days as $key => $value) {
		$html .= sprintf('<option value="%1$s" %2$s>%3$s</option>', $key, selected($week_day, $key, false), $value);
	}
	$html .= '</select>';
	echo $html;
}

/**
 * Displays days select field
 *
 * @param array   $day   An array of days of a month
 * @param integer $count Index of scheduled events
 *
 * @return void
 */
function wpo_display_day_number($day, $count) {
	$days = WP_Optimize_Premium::get_days();
	$html = '';
	$html .= sprintf('<label>%s</label>', __('Day Number:', 'wp-optimize'));
	$html .= sprintf('<select class="wpo_day_number" name="wp-optimize-auto[%s][day_number]">', $count);
	foreach ($days as $value) {
		$html .= sprintf('<option value="%1$s" %2$s>%3$s</option>', $value, selected($day, $value, false), $value);
	}
	$html .= '</select>';
	echo $html;
}

/**
 * Displays status field
 *
 * @param integer $status Status value
 * @param integer $count  Index of scheduled events
 *
 * @return void
 */
function wpo_display_status($status, $count) {
	echo '<div class="wpo_event_status">';
	printf('<label><input type="checkbox" name="wp-optimize-auto[%1$s][status]" value="1" %2$s>%3$s</label>', $count, checked($status, 1, false), __('Active', 'wp-optimize'));
	echo '</div>';
}

/**
 * Displays actions field
 *
 * @param integer $index Count of event
 * @param boolean $edit  Boolean value to determine edit action
 *
 * @return void
 */
function wpo_display_actions($index, $edit = false) {
	echo '<div class="wpo_event_actions">';
	if (true === $edit) {
		printf('<span class="wpo_edit_event" title="%s"><span class="dashicons dashicons-edit"></span></span>', __('Edit', 'wp-optimize'));
	}
	printf('<span class="wpo_remove_event" title="%d" data-count="%s"><span class="dashicons dashicons-no-alt"></span></span>', __('Remove this task', 'wp-optimize'), $index);
	echo '</div>';
}

/**
 * Displays date field
 *
 * @param array $optimizations An array of selected optimizations
 *
 * @return void
 */
function wpo_display_optimization_list($optimizations) {
	$auto_optimizations = WP_Optimize_Premium::get_auto_optimizations();
	$html = '';
	$html .= '<ul class="wpo_optimizations">';
	foreach ($optimizations as $optimization) {
		$html .= sprintf('<li data-optimization="%s"><span class="dashicons dashicons-arrow-right"></span>%s</li>', $optimization, $auto_optimizations[$optimization]['optimization']);
	}
	$html .= '</ul>';
	echo $html;
}

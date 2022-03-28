<?php

if (!defined('WPO_PLUGIN_MAIN_PATH')) die('No direct access allowed');

if (!class_exists('WP_Optimize_Cron_Scheduler')) {

class WP_Optimize_Cron_Scheduler {

	/**
	 * Holds an instance of semaphore lock class
	 *
	 * @var WP_Optimize_Semaphore $_lock
	 */
	protected static $_lock = null;

	/**
	 * Adds a method to run when premium cron event fires
	 */
	private function __construct() {
		add_action('wpo_cron_event3', array($this, 'cron_action'), 10, 2);
	}

	/**
	 * Returns singleton instance of this class
	 *
	 * @return null|WP_Optimize_Cron_Scheduler Singleton Instance
	 */
	public static function get_instance() {
		static $instance = null;
		if (null === $instance) {
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * Executed this function on cron event
	 *
	 * @param  string $cron_id  ID of scheduled cron
	 * @param  string $schedule Name of scheduled cron
	 * @return void
	 */
	public function cron_action($cron_id, $schedule) {

		$options = WP_Optimize()->get_options();
		WP_Optimize()->log('WPO: Starting cron_action()');
		$auto_options = $options->get_option('auto');
		$auto_updated_options = $options->get_option('auto-updated');
		$auto_backup_enabled = $options->get_option('enable-auto-backup-scheduled');
		
		$options->update_option('last-optimized', time());
		// Currently the output of the optimizations is not saved/used/logged.
		$optimizer = WP_Optimize()->get_optimizer();
		$tablesstatus = $optimizer->get_table_information();

		if ($this->set_semaphore_lock()) {

			if ('true' == $auto_backup_enabled) {
				do_action('updraft_backup_database');
			}
			
			foreach ($auto_options[$cron_id]['optimization'] as $optimization_id) {
				if ('optimizetables' === $optimization_id) {
					// Add message to log about innoDB tables if need.
					if (false === $tablesstatus['is_optimizable'] && $tablesstatus['inno_db_tables'] > 0) {
						$force_optimization = ('true' == $options->get_option('auto-innodb')) ? true : false;
						if ($force_optimization) {
							WP_Optimize()->log('WPO: Chooses to optimize InnoDB tables');
						} else {
							WP_Optimize()->log('WPO: Chooses to not optimize InnoDB tables');
						}
						$optimizer->do_optimization($optimizer->get_optimization($optimization_id, array('force' => $force_optimization)));
					} else {
						$optimizer->do_optimization($optimization_id);
					}
				} else {
					$optimizer->do_optimization($optimization_id);
				}
			}
			$this->release_semaphore_lock();
		}

		// Remove once event from options table
		if (false !== strpos($schedule, 'wpo_once')) {
			unset($auto_updated_options[$cron_id]);
			$options->update_option('auto-updated', $auto_updated_options);
		}

		// call actions after optimisation. used to send message in email logger.
		do_action('wp_optimize_after_optimizations');
	}


	/**
	 * Returns next event timestamp
	 *
	 * @return timestamp | false
	 */
	public function wpo_cron_next_event() {
		$cron_events = get_option('cron');
		ksort($cron_events);
		$wpo_cron_event3 = array();
		foreach ($cron_events as $timestamp => $schedule) {
			if (!is_array($schedule)) continue;
			foreach ($schedule as $key => $value) {
				if ('wpo_cron_event3' == $key) {
					$wpo_cron_event3[$timestamp][$key] = $value;
				}
			}
		}

		$keys = array_keys($wpo_cron_event3);
		if (!empty($keys)) {
			return $keys[0];
		}
		return false;
	}

	/**
	 * Activate WPO auto events
	 *
	 * @return void
	 */
	public function cron_activate() {
		$options = WP_Optimize()->get_options();

		$auto_options = $options->get_option('auto-updated');

		if (!is_array($auto_options)) {
			$auto_options = array();
		}
		$new_auto_options = array();
		$cron_id = 0;
		foreach ($auto_options as $event) {
			if ('0' == $event['status']) continue;
			$new_auto_options[$cron_id] = $event;
			WP_Optimize()->log($event['schedule_type']);
			switch ($event['schedule_type']) {
				case 'wpo_once':
					$this->set_once_cron($event, $cron_id);
					break;
				case 'wpo_daily':
					$this->set_daily_cron($event, $cron_id);
					break;
				case 'wpo_weekly':
					$this->set_weekly_cron($event, $cron_id);
					break;
				case 'wpo_fortnightly':
					$this->set_fortnightly_cron($event, $cron_id);
					break;
				case 'wpo_monthly':
					$this->set_monthly_cron($event, $cron_id);
					break;
			}
			$cron_id++;
		}
		$options->update_option('auto', $new_auto_options);

	}

	/**
	 * Activate daily events
	 *
	 * @param  array   $event   Details of event
	 * @param  integer $cron_id ID of cron schedule
	 * @return void
	 */
	private function set_daily_cron($event, $cron_id) {
		$selected_schedule = "wpo_daily";

		$cron_schedule_user_date_time = strtotime(date("Y-m-d") . ' ' . $event['time']);
		$gmt_offset = HOUR_IN_SECONDS * get_option('gmt_offset');
		$cron_schedule_date_time = $cron_schedule_user_date_time - $gmt_offset;

		if ($cron_schedule_date_time < time()) {
			$cron_schedule_date_time += DAY_IN_SECONDS;
		}

		wp_schedule_event($cron_schedule_date_time, $selected_schedule, 'wpo_cron_event3', array($cron_id, $selected_schedule));
		WP_Optimize()->log('wpo_cron_event3 - ' . $cron_id . ' - ' . $selected_schedule);
	}

	/**
	 * Activate weekly events
	 *
	 * @param  array   $event   Details of event
	 * @param  integer $cron_id ID of cron schedule
	 * @return void
	 */
	private function set_weekly_cron($event, $cron_id) {
		$selected_schedule = "wpo_weekly";

		$user_day_number = $event['day'];

		// Need to match between $wp_locale->get_weekday() and php's date('N')
		if (1 === $user_day_number) {
			$user_day_number = 7;
		} else {
			$user_day_number--;
		}

		$today_day_number = date('N');
		$cron_schedule_user_date_time = strtotime(date("Y-m-d") . ' ' . $event['time']);
		$week_offset = ($user_day_number - $today_day_number) * DAY_IN_SECONDS;
		$gmt_offset = HOUR_IN_SECONDS * get_option('gmt_offset');
		$cron_schedule_date_time = $cron_schedule_user_date_time - $gmt_offset + $week_offset;

		if ($cron_schedule_date_time < time()) {
			$cron_schedule_date_time += WEEK_IN_SECONDS;
		}

		wp_schedule_event($cron_schedule_date_time, $selected_schedule, 'wpo_cron_event3', array($cron_id, $selected_schedule));
		WP_Optimize()->log('wpo_cron_event3 - ' . $cron_id . ' - ' . $selected_schedule);
	}


	/**
	 * Activate fortnightly events
	 *
	 * @param  array   $event   Details of event
	 * @param  integer $cron_id ID of cron schedule
	 * @return void
	 */
	private function set_fortnightly_cron($event, $cron_id) {
		$selected_schedule = "wpo_fortnightly";

		$user_week_number = $event['week'];
		$user_day_number = $event['day'];
		$today_day_number = date('N');

		// Need to match between $wp_locale->get_weekday() and php's date('N')
		if (1 === $user_day_number) {
			$user_day_number = 7;
		} else {
			$user_day_number--;
		}

		$cron_schedule_user_date_time = strtotime(date("Y-m-d") . ' ' . $event['time']);
		$week_offset = ($user_day_number - $today_day_number) * DAY_IN_SECONDS;
		$gmt_offset = HOUR_IN_SECONDS * get_option('gmt_offset');
		$cron_schedule_date_time = $cron_schedule_user_date_time - $gmt_offset + $week_offset;

		if ($cron_schedule_date_time < time() || '2nd' == $user_week_number) {
			$cron_schedule_date_time += WEEK_IN_SECONDS;
		}

		wp_schedule_event($cron_schedule_date_time, $selected_schedule, 'wpo_cron_event3', array($cron_id, $selected_schedule));
		WP_Optimize()->log('wpo_cron_event3 - ' . $cron_id . ' - ' . $selected_schedule);
	}

	/**
	 * Activate monthly events
	 *
	 * @param  array   $event   Details of event
	 * @param  integer $cron_id ID of cron schedule
	 * @return void
	 */
	private function set_monthly_cron($event, $cron_id) {
		$selected_schedule = 'wpo_monthly';

		$user_day_number = min($event['day_number'], date("t"));
		$schedule_day_number = $user_day_number;
		$cron_schedule_user_date_time = strtotime(date("Y-m-" . $schedule_day_number) . ' ' . $event['time']);
		$gmt_offset = HOUR_IN_SECONDS * get_option('gmt_offset');
		$cron_schedule_date_time = $cron_schedule_user_date_time - $gmt_offset;

		wp_schedule_event($cron_schedule_date_time, $selected_schedule, 'wpo_cron_event3', array($cron_id, $selected_schedule));
		WP_Optimize()->log('wpo_cron_event3 - ' . $cron_id . ' - ' . $selected_schedule);
	}

	/**
	 * Activate once (one off) events
	 *
	 * @param  array   $event   Details of event
	 * @param  integer $cron_id ID of cron schedule
	 * @return void
	 */
	private function set_once_cron($event, $cron_id) {
		$cron_schedule_user_date_time_human = $event['date'] . ' ' . $event['time'];
		$cron_schedule_user_date_time = strtotime($cron_schedule_user_date_time_human);

		$gmt_offset = HOUR_IN_SECONDS * get_option('gmt_offset');
		$cron_schedule_date_time = $cron_schedule_user_date_time - $gmt_offset;
		wp_schedule_single_event($cron_schedule_date_time, 'wpo_cron_event3', array($cron_id, 'wpo_once'));
		WP_Optimize()->log('wpo_cron_event3 - once');
	}

	/**
	 * Using semaphore lock before starting optimization
	 *
	 * @param string $semaphore Name of semaphore lock
	 *
	 * @return boolean Semaphore lock status
	 */
	public function set_semaphore_lock($semaphore = 'wpo') {
		// Are we doing an action called by the WP scheduler? If so, we want to check when that last happened; the point being that the dodgy WP scheduler, when overloaded, can call the event multiple times - and sometimes, it evades the semaphore because it calls a second run after the first has finished, or > 3 minutes (our semaphore lock time) later
		// doing_action() was added in WP 3.9
		// wp_cron() can be called from the 'init' action

		if (function_exists('doing_action') && (doing_action('init') || constant('DOING_CRON')) && doing_action('wpo_cron_event3')) {
			$last_scheduled_action_called_at = get_option("wpo_last_scheduled_$semaphore");
			// 11 minutes - so, we're assuming that they haven't custom-modified their schedules to run scheduled optimizations more often than that. If they have, they need also to use the filter to override this check.
			$seconds_ago = time() - $last_scheduled_action_called_at;
			if ($last_scheduled_action_called_at && $seconds_ago < 660 && apply_filters('wpo_check_repeated_scheduled_optimizations', true)) {
				WP_Optimize()->log(sprintf('Scheduled optimization aborted - another optimization of this type was apparently invoked by the WordPress scheduler only %d seconds ago - the WordPress scheduler invoking events multiple times usually indicates a very overloaded server (or other plugins that mis-use the scheduler)', $seconds_ago));
				return false;
			}
		}
		update_option("wpo_last_scheduled_$semaphore", time());

		include_once(WPO_PLUGIN_MAIN_PATH.'/includes/class-semaphore.php');
		self::$_lock = new WP_Optimize_Semaphore($semaphore);

		$semaphore_log_message = 'Requesting semaphore lock ('.$semaphore.')';
		if (!empty($last_scheduled_action_called_at)) {
			$semaphore_log_message .= " (apparently via scheduler: last_scheduled_action_called_at=$last_scheduled_action_called_at, seconds_ago=$seconds_ago)";
		} else {
			$semaphore_log_message .= " (apparently not via scheduler)";
		}

		WP_Optimize()->log($semaphore_log_message);
		if (!self::$_lock->lock()) {
			WP_Optimize()->log('Failed to gain semaphore lock ('.$semaphore.') - another optimization of this type is apparently already active - aborting');
			return false;
		}
		return true;
	}

	/**
	 * Releases semaphore lock after optimization is complete.
	 */
	public function release_semaphore_lock() {
		if (!empty(self::$_lock)) self::$_lock->unlock();
	}

	/**
	 * Deactivate WPO events by clearing the hook
	 *
	 * @return void
	 */
	public function wpo_cron_deactivate() {
		$auto_options = WP_Optimize()->get_options()->get_option('auto');
		foreach ($auto_options as $cron_id => $event) {
			if (!isset($event['schedule_type'])) continue;
			wp_clear_scheduled_hook('wpo_cron_event3', array($cron_id, $event['schedule_type']));
		}
	}
}

}

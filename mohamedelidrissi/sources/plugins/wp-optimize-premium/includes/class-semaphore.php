<?php

if (!defined('ABSPATH')) die('No direct access.');

/**
 * Semaphore Lock Management
 * Adapted from WP Social under the GPL - thanks to Alex King (https://github.com/crowdfavorite/wp-social)
 */
class WP_Optimize_Semaphore {

	/**
	 * Initializes the semaphore object.
	 *
	 * @param string $semaphore Name of semaphore lock
	 * @return WP_Optimize_Semaphore
	 */
	public function __construct($semaphore = 'lock') {
		// Make sure the options for semaphores exist
		global $wpdb;
		$results = $wpdb->get_results("
		SELECT option_id
			FROM $wpdb->options
			WHERE option_name IN ('wpo_locked_$semaphore', 'wpo_unlocked_$semaphore', 'wpo_last_lock_time_$semaphore', 'wpo_semaphore_$semaphore')
		");

		if (!is_array($results) || count($results) < 3) {

			if (is_array($results) && count($results) > 0) {
				WP_Optimize()->log("Semaphore ($semaphore, ".$wpdb->options.") in an impossible/broken state - fixing (".count($results).")");
			} else {
				WP_Optimize()->log("Semaphore ($semaphore, ".$wpdb->options.") being initialised");
			}

			$wpdb->query("
			DELETE FROM $wpdb->options
			WHERE option_name IN ('wpo_locked_$semaphore', 'wpo_unlocked_$semaphore', 'wpo_last_lock_time_$semaphore', 'wpo_semaphore_$semaphore')
		");

			$wpdb->query($wpdb->prepare("
			INSERT INTO $wpdb->options (option_name, option_value, autoload)
			VALUES
			('wpo_unlocked_$semaphore', '1', 'no'),
			('wpo_last_lock_time_$semaphore', '%s', 'no'),
			('wpo_semaphore_$semaphore', '0', 'no')
		", current_time('mysql', 1)));
		}
		$this->lock_name = $semaphore;
	}

	/**
	 * Lock Broke
	 *
	 * @var boolean
	 */
	protected $lock_broke = false;

	public $lock_name = 'lock';

	/**
	 * Attempts to start the lock. If the rename works, the lock is started.
	 *
	 * @return bool
	 */
	public function lock() {
		global $wpdb;

		// Attempt to set the lock
		$affected = $wpdb->query("
			UPDATE $wpdb->options
			   SET option_name = 'wpo_locked_".$this->lock_name."'
			 WHERE option_name = 'wpo_unlocked_".$this->lock_name."'
		");

		if ('0' == $affected && !$this->stuck_check()) {
			WP_Optimize()->log('Semaphore lock (' . $this->lock_name . ', ' . $wpdb->options . ') failed (line ' . __LINE__ . ')');
			return false;
		}

		// Check to see if all processes are complete
		$affected = $wpdb->query("
			UPDATE $wpdb->options
			   SET option_value = CAST(option_value AS UNSIGNED) + 1
			 WHERE option_name = 'wpo_semaphore_".$this->lock_name."'
			   AND option_value = '0'
		");
		if ('1' != $affected) {
			if (!$this->stuck_check()) {
				WP_Optimize()->log('Semaphore lock (' . $this->lock_name . ', ' . $wpdb->options . ') failed (line ' . __LINE__ . ')');
				return false;
			}

			// Reset the semaphore to 1
			$wpdb->query("
				UPDATE $wpdb->options
				   SET option_value = '1'
				 WHERE option_name = 'wpo_semaphore_".$this->lock_name."'
			");

			WP_Optimize()->log('Semaphore (' . $this->lock_name . ', ' . $wpdb->options . ') reset to 1');
		}

		// Set the lock time
		$wpdb->query($wpdb->prepare("
			UPDATE $wpdb->options
			   SET option_value = %s
			 WHERE option_name = 'wpo_last_lock_time_".$this->lock_name."'
		", current_time('mysql', 1)));
		WP_Optimize()->log('Set semaphore last lock (' . $this->lock_name . ') time to ' . current_time('mysql', 1));

		WP_Optimize()->log('Semaphore lock (' . $this->lock_name . ') complete');
		return true;
	}

	/**
	 * Increment the semaphore.
	 *
	 * @param  array $filters
	 * @return Social_Semaphore
	 */
	public function increment(array $filters = array()) {
		global $wpdb;

		if (count($filters)) {
			// Loop through all of the filters and increment the semaphore
			foreach ($filters as $priority) {
				for ($i = 0, $j = count($priority); $i < $j; ++$i) {
					$this->increment();
				}
			}
		} else {
			$wpdb->query("
				UPDATE $wpdb->options
				   SET option_value = CAST(option_value AS UNSIGNED) + 1
				 WHERE option_name = 'wpo_semaphore_".$this->lock_name."'
			");
			WP_Optimize()->log('Incremented the semaphore ('.$this->lock_name.') by 1');
		}

		return $this;
	}

	/**
	 * Decrements the semaphore.
	 *
	 * @return void
	 */
	public function decrement() {
		global $wpdb;

		$wpdb->query("
			UPDATE $wpdb->options
			   SET option_value = CAST(option_value AS UNSIGNED) - 1
			 WHERE option_name = 'wpo_semaphore_".$this->lock_name."'
			   AND CAST(option_value AS UNSIGNED) > 0
		");
		WP_Optimize()->log('Decremented the semaphore (' . $this->lock_name . ') by 1');
	}

	/**
	 * Unlocks the process.
	 *
	 * @return bool
	 */
	public function unlock() {
		global $wpdb;

		// Decrement for the master process.
		$this->decrement();

		$result = $wpdb->query("
			UPDATE $wpdb->options
			   SET option_name = 'wpo_unlocked_".$this->lock_name."'
			 WHERE option_name = 'wpo_locked_".$this->lock_name."'
		");

		if ('1' == $result) {
			WP_Optimize()->log('Semaphore (' . $this->lock_name . ') unlocked');
			return true;
		}

		WP_Optimize()->log('Semaphore (' . $this->lock_name . ', ' . $wpdb->options . ') still locked (' . $result . ')');
		return false;
	}

	/**
	 * Attempts to jiggle the stuck lock loose.
	 *
	 * @return bool
	 */
	private function stuck_check() {
		global $wpdb;

		// Check to see if we already broke the lock.
		if ($this->lock_broke) {
			return true;
		}

		$current_time = current_time('mysql', 1);
		$three_minutes_before = gmdate('Y-m-d H:i:s', time()-(defined('WPO_SEMAPHORE_LOCK_WAIT') ? WPO_SEMAPHORE_LOCK_WAIT : 180));

		$affected = $wpdb->query($wpdb->prepare("
			UPDATE $wpdb->options
			   SET option_value = %s
			 WHERE option_name = 'wpo_last_lock_time_".$this->lock_name."'
			   AND option_value <= %s
		", $current_time, $three_minutes_before));

		if ('1' == $affected) {
			WP_Optimize()->log('Semaphore (' . $this->lock_name . ', ' . $wpdb->options . ') was stuck, set lock time to ' . $current_time);
			$this->lock_broke = true;
			return true;
		}

		return false;
	}
} // End WP_Optimize_Semaphore

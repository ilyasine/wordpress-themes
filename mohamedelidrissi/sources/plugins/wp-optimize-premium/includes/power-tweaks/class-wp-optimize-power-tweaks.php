<?php

if (!defined('ABSPATH')) die('No direct access allowed');

class WP_Optimize_Power_Tweaks {

	/**
	 * Store the tweaks
	 *
	 * @var array
	 */
	private $tweaks = array();

	/**
	 * Initialize the tweaks
	 */
	public function __construct() {
		// Include the abstract class
		include_once WPO_PLUGIN_MAIN_PATH.'/includes/power-tweaks/abstract-class-wp-optimize-power-tweak.php';
		add_filter('wp_optimize_admin_page_WP-Optimize_tabs', array($this, 'add_admin_tab'));
		add_action('wp_optimize_admin_page_WP-Optimize_power-tweaks', array($this, 'output_admin_tab'), 30);
		add_action('wpo_premium_scripts_styles', array($this, 'enqueue_scripts'), 20, 3);
		$this->initialize_tweaks();
	}

	/**
	 * Undocumented function
	 *
	 * @param string $min_or_not_internal
	 * @param bool   $min_or_not
	 * @param string $enqueue_version
	 * @return void
	 */
	public function enqueue_scripts($min_or_not_internal, $min_or_not, $enqueue_version) {
		wp_enqueue_script('wp-optimize-power-tweaks', WPO_PLUGIN_URL . 'js/power-tweaks' . $min_or_not_internal . '.js', array('jquery', 'wp-optimize-send-command', 'wp-optimize-admin-js'), $enqueue_version);
	}

	/**
	 * Adde the tab
	 *
	 * @param array $tabs
	 * @return array
	 */
	public function add_admin_tab($tabs) {
		$tabs['power-tweaks'] = __('Power tweaks', 'wp-optimize');
		return $tabs;
	}

	/**
	 * Output the admin tab
	 */
	public function output_admin_tab() {
		WP_Optimize()->include_template('database/power-tweaks.php');
	}

	/**
	 * Initialization
	 *
	 * @return void
	 */
	public function initialize_tweaks() {
		// Get all the tweaks files
		$path = WPO_PLUGIN_MAIN_PATH.'includes/power-tweaks/';
		$handle = opendir($path);
		if (false === $handle) return;
		$file = readdir($handle);

		// Get the active tweaks
		$active_tweaks = $this->get_active_tweaks();

		while (false !== $file) {
			if (preg_match('/^class-wp-optimize-power-tweak--/i', $file)) {
				// Include each tweak.
				$tweak_instance = include_once $path . $file;
				// If the instance isn't what it's supposed to be, move on to the next
				if (!$tweak_instance || !is_a($tweak_instance, 'WP_Optimize_Power_Tweak')) {
					$file = readdir($handle);
					continue;
				}
				$tweak_name = $tweak_instance->get_name();
				// Run the tweak
				if (in_array($tweak_name, $active_tweaks)) {
					// If the tweak is active, run it
					if ($tweak_instance->test_availability()) {
						$tweak_instance->run();
					} else {
						// If the active tweak is not available, deactivate it
						$this->deactivate($tweak_name);
					}
				}
				$this->tweaks[$tweak_name] = $tweak_instance;
			}
			$file = readdir($handle);
		}
	}

	/**
	 * Get the active tweaks
	 *
	 * @return array
	 */
	public function get_active_tweaks() {
		return WP_Optimize()->get_options()->get_option('active_power_tweaks', array());
	}

	/**
	 * Activate a tweak
	 *
	 * @param string $tweak
	 * @return boolean|WP_Error
	 */
	public function activate($tweak) {
		if (!isset($this->tweaks[$tweak])) return new WP_Error('power_tweak_missing', sprintf(__('The power tweak %s does not exist, so could not be activated.', 'wp-optimize'), $tweak));
		$active = $this->get_active_tweaks();
		if (in_array($tweak, $active)) return true;
		// Test the tweak
		$is_available = $this->tweaks[$tweak]->test_availability();
		if (!$is_available || is_wp_error($is_available)) {
			return $is_available;
		}
		// Add the tweak
		$active[] = $tweak;
		WP_Optimize()->get_options()->update_option('active_power_tweaks', $active);
		WP_Optimize()->get_options()->update_option('tweak_updated_status_'.$tweak, time(), false);
		return array(
			'message' => __('The tweak was successfully activated.', 'wp-optimize'),
			'last_updated' => WP_Optimize()->format_date_time(time())
		);
	}

	/**
	 * Run a tweak
	 * Those tweaks are once-off items
	 *
	 * @param string $tweak
	 * @return boolean|WP_Error
	 */
	public function run($tweak) {
		if (!isset($this->tweaks[$tweak])) return new WP_Error('power_tweak_missing', __('The power tweak %s does not exist, so could not be run.', 'wp-optimize'));
		// Test the tweak
		$is_available = $this->tweaks[$tweak]->test_availability();
		if (!$is_available || is_wp_error($is_available)) {
			return $is_available;
		}
		$result = $this->tweaks[$tweak]->run();
		WP_Optimize()->get_options()->update_option('tweak_last_run_'.$tweak, time(), false);
		return array_merge(
			array(
				'last_updated' => WP_Optimize()->format_date_time(time())
			),
			$result
		);
	}

	/**
	 * Deactivate a tweak
	 *
	 * @param string $tweak
	 * @return boolean
	 */
	public function deactivate($tweak) {
		if (!isset($this->tweaks[$tweak])) return new WP_Error('power_tweak_missing', sprintf(__('The power tweak %s does not exist, so could not be deactivated.', 'wp-optimize'), $tweak));
		$active = $this->get_active_tweaks();
		if (!in_array($tweak, $active)) return true;

		// Add the tweak
		if (method_exists($this->tweaks[$tweak], 'deactivate')) {
			if (!$this->tweaks[$tweak]->deactivate()) return false;
		}

		WP_Optimize()->get_options()->update_option('active_power_tweaks', array_diff(array($tweak), $active));
		WP_Optimize()->get_options()->update_option('tweak_updated_status_'.$tweak, time(), false);

		return array(
			'message' => __('The tweak was successfully deactivated.', 'wp-optimize'),
			'last_updated' => WP_Optimize()->format_date_time(time())
		);
	}
}

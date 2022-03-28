<?php

if (!defined('ABSPATH')) die('No direct access allowed');

abstract class WP_Optimize_Power_Tweak {

	protected $data = array();

	/**
	 * Initialize the tweak
	 */
	public function __construct() {
		add_action('wpo_power_tweaks_output', array($this, 'output'));
	}

	/**
	 * Run the tweak
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->tweak_name;
	}

	/**
	 * Admin output
	 *
	 * @return void
	 */
	public function output() {
		WP_Optimize()->include_template('database/power-tweak.php', false, $this->get_data());
	}

	/**
	 * Get the tweak's data for admin display
	 *
	 * @return array
	 */
	public function get_data() {
		return array_merge(
			array(
				'is_active' => $this->is_active(),
				'action_type' => $this->get_action_type(),
				'tweak_name' => $this->tweak_name,
				'is_available' => $this->test_availability(),
				'last_run' => WP_Optimize()->get_options()->get_option('tweak_last_run_'.$this->tweak_name, false),
				'updated_status' => WP_Optimize()->get_options()->get_option('tweak_updated_status_'.$this->tweak_name, false),
				'faq_link' => $this->tweak_link
			),
			wp_parse_args(
				$this->get_labels(),
				array(
					'details' => '',
					'toggle_label' => __('Enable', 'wp-optimize')
				)
			)
		);
	}

	/**
	 * Get the action type
	 *
	 * @return string
	 */
	public function get_action_type() {
		return $this->action_type;
	}

	/**
	 * Undocumented function
	 *
	 * @return boolean
	 */
	public function is_active() {
		global $wp_optimize_premium;
		$active_tweaks = $wp_optimize_premium->power_tweaks->get_active_tweaks();
		return in_array($this->tweak_name, $active_tweaks);
	}

	/**
	 * Set the required values
	 *
	 * @return array
	 */
	abstract public function get_labels();

	/**
	 * Test to find out if the test is usable on the site
	 *
	 * @return boolean
	 */
	abstract public function test_availability();

	/**
	 * Run the tweak
	 *
	 * @return void
	 */
	abstract public function run();
}

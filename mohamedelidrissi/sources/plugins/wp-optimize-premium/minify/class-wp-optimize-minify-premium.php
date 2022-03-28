<?php
if (!defined('ABSPATH')) die('No direct access allowed');

class WP_Optimize_Minify_Premium {
	public function __construct() {
		add_action('wpo_premium_scripts_styles', array($this, 'enqueue_scripts'), 20, 3);
		add_filter('wpo_save_minify_settings', array($this, 'save_settings'), 20);
	}

	/**
	 * Enqueue scripts and styles required for premium version
	 *
	 * @param string $min_or_not_internal - The .min-version-number suffix to use on internal assets
	 * @param string $min_or_not          - The .min suffix to use on third party assets
	 * @param string $enqueue_version     - The enqueued version
	 * @return void
	 */
	public function enqueue_scripts($min_or_not_internal, $min_or_not, $enqueue_version) {
		wp_enqueue_script('wp-optimize-minify-premium', WPO_PLUGIN_URL . 'js/wpo-asset-preload' . $min_or_not_internal . '.js', array('jquery', 'wp-optimize-send-command', 'wp-optimize-admin-js', 'backbone'), $enqueue_version);
		wp_localize_script('wp-optimize-minify-premium', 'wp_optimize_minify_premium', array('home_url' => home_url()));
	}

	/**
	 * Filters the data when saving Minify settings
	 *
	 * @param array $settings - The original settings
	 * @return array
	 */
	public function save_settings($settings) {
		if (isset($settings['hpreload']) && isset($_POST['data']) && isset($_POST['data']['hpreload'])) {
			$settings['hpreload'] = stripslashes($_POST['data']['hpreload']);
		}
		return $settings;
	}
}

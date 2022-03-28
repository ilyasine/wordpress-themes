<?php

if (!defined('WPO_PLUGIN_MAIN_PATH')) die('No direct access allowed');

/**
 * Class WP_Optimize_Cloudflare
 */
class WP_Optimize_Cloudflare {

	public $cloudflare_api;

	protected $config;

	private $cache_purged = false;

	public function __construct($config) {
		$this->config = $config;

		$api_email = isset($this->config['cloudflare_api_email']) ? $this->config['cloudflare_api_email'] : '';
		$api_key = isset($this->config['cloudflare_api_key']) ? $this->config['cloudflare_api_key'] : '';
		$api_token = isset($this->config['cloudflare_api_token']) ? $this->config['cloudflare_api_token'] : '';
		$this->cloudflare_api = new WP_Optimize_Cloudflare_API($api_email, $api_key, $api_token);

		// Filters and actions required for Cloudflare support.
		add_filter('wpo_save_cache_settings_validation', array($this, 'validate_cloudflare_settings'));

		add_action('wpo_page_cache_advanced_settings', array($this, 'cloudflare_settings'));

		add_action('wpo_delete_cache_by_url', array($this, 'purge_cloudflare_single_url'), 10, 2);
		add_action('wpo_cache_flush', array($this, 'purge_cloudflare_cache'), 10, 2);
	}

	/**
	 * Include Cloudflare settings template.
	 *
	 * @param array $cache_options
	 */
	public function cloudflare_settings($cache_options) {

		$cloudflare_plugin_credentials = $this->get_cloudflare_plugin_credentials();

		$cloudflare_api_email = isset($cache_options['cloudflare_api_email']) ? $cache_options['cloudflare_api_email'] : $cloudflare_plugin_credentials['email'];
		$cloudflare_api_key = isset($cache_options['cloudflare_api_key']) ? $cache_options['cloudflare_api_key'] : $cloudflare_plugin_credentials['key'];
		$cloudflare_api_token = isset($cache_options['cloudflare_api_token']) ? $cache_options['cloudflare_api_token'] : $cloudflare_plugin_credentials['token'];

		$extract = array(
			'show_cloudflare_settings' => apply_filters('show_cloudflare_settings', isset($_SERVER['HTTP_CF_RAY'])),
			'display' => $cache_options['enable_page_caching'],
			'purge_cloudflare_cache' => isset($cache_options['purge_cloudflare_cache']) ? $cache_options['purge_cloudflare_cache'] : false,
			'cloudflare_api_email' => $cloudflare_api_email,
			'cloudflare_api_key' => $cloudflare_api_key,
			'cloudflare_api_token' => $cloudflare_api_token,
		);

		WP_Optimize()->include_template('cache/page-cache-cloudflare.php', false, $extract);
	}

	/**
	 * Validate Cloudflare settings by running test request to Cloudflare API.
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function validate_cloudflare_settings($settings) {
		$result = array(
			'result' => true,
			'js_trigger' => 'validate_cloudflare_settings',
		);

		$cloudflare_enabled = isset($settings['purge_cloudflare_cache']) ? $settings['purge_cloudflare_cache'] : false;

		if ($cloudflare_enabled) {
			$cloudflare = new WP_Optimize_Cloudflare_API($settings['cloudflare_api_email'], $settings['cloudflare_api_key'], $settings['cloudflare_api_token']);
			$test_zones = $cloudflare->get_zones(1, 1);

			if (is_wp_error($test_zones)) {
				$result['result'] = false;
				$result['cloudflare_error'] = __('It was not possible to Connect to Cloudflare with the provided credentials.', 'wp-optimize');
			}
		}

		delete_transient('wpo_cloudflare_zone_id');

		return $result;
	}

	/**
	 * Check if Cloudflare plugin is active.
	 *
	 * @return Boolean
	 */
	public function is_cloudflare_plugin_active() {
		$db_info = WP_Optimize()->get_db_info();
		$cloudflare_plugin_status = $db_info->get_plugin_status('cloudflare');
		return $cloudflare_plugin_status['installed'] && $cloudflare_plugin_status['active'];
	}

	/**
	 * Get Cloudflare plugin credentials from WordPress options.
	 *
	 * @return array
	 */
	public function get_cloudflare_plugin_credentials() {
		$email = get_option('cloudflare_api_email', '');
		$key = get_option('cloudflare_api_key', '');
		$token = '';

		// if in Cloudflare plugin settings stored Auth token then we return just token.
		if ('' != trim($key) && !WP_Optimize_Cloudflare_API::is_auth_key($key)) {
			$token = $key;
			$email = '';
			$key = '';
		}

		return array(
			'email' => $email,
			'key' => $key,
			'token' => $token,
		);
	}

	/**
	 * Purge single page from Cloudflare cache.
	 *
	 * @param string $url
	 * @param bool   $recursive when $recursive is true we purge cache completely.
	 */
	public function purge_cloudflare_single_url($url, $recursive) {

		if (!$this->is_purge_cloudflare_cache_enabled() || $this->cache_purged) return;

		if ($recursive) {
			$this->purge_cloudflare_cache();
			return;
		}

		$zone_id = $this->get_site_cloudflare_zone_id();
		if ($zone_id) {
			$this->cloudflare_api->purge_urls($zone_id, array($url));
		}
	}

	/**
	 * Purge Cloudflare cache.
	 */
	public function purge_cloudflare_cache() {
		if (!$this->is_purge_cloudflare_cache_enabled() || $this->cache_purged) return;

		// if transient is set then we don't call purge request again.
		if (true == get_transient('wpo_cloudflare_cache_purged')) return;

		$zone_id = $this->get_site_cloudflare_zone_id();
		if ($zone_id) {
			$this->cloudflare_api->purge_cache($zone_id);
		}

		// set cache_purged flag to avoid duplicated purging requests.
		$this->cache_purged = true;
		// set transient for 10 seconds to avoid redundant requests to api.
		set_transient('wpo_cloudflare_cache_purged', true, 10);
	}

	/**
	 * Get Cloudflare zone id for current site.
	 *
	 * @return string
	 */
	private function get_site_cloudflare_zone_id() {
		$zone_id = get_transient('wpo_cloudflare_zone_id');

		if ($zone_id) return $zone_id;

		$zone_id = $this->cloudflare_api->get_zone_id_by_site_url(site_url());

		set_transient('wpo_cloudflare_zone_id', $zone_id, 3600);

		return $zone_id;
	}

	/**
	 * Check if purge Cloudflare option enabled.
	 *
	 * @return bool
	 */
	private function is_purge_cloudflare_cache_enabled() {
		return isset($this->config['purge_cloudflare_cache']) ? $this->config['purge_cloudflare_cache'] : false;
	}
}

<?php

if (!defined('WPO_PLUGIN_MAIN_PATH')) die('No direct access allowed');

/**
 * Class WP_Optimize_Cloudflare_API
 */
class WP_Optimize_Cloudflare_API {

	const API_URL = 'https://api.cloudflare.com/client/v4/';

	private $api_email;

	private $api_key;

	private $api_token;

	/**
	 * WP_Optimize_Cloudflare constructor.
	 *
	 * @param string $api_email
	 * @param string $api_key
	 * @param string $api_token
	 */
	public function __construct($api_email, $api_key, $api_token) {
		$this->api_email = trim($api_email);
		$this->api_key = trim($api_key);
		$this->api_token = trim($api_token);
	}

	/**
	 * Get Cloudflare zone id by site url.
	 *
	 * @param string $site_url
	 * @return string
	 */
	public function get_zone_id_by_site_url($site_url) {

		$url_parts = parse_url($site_url);
		$site_domain = $url_parts['host'];

		$zone_id = '';
		$zones = $this->get_all_zones();

		if (!empty($zones)) {
			foreach ($zones as $zone) {

				if ('active' != $zone['status']) continue;

				if ($site_domain == $zone['name']) {
					// if zone domain name equal to site domain then save result and stop search.
					$zone_id = $zone['id'];
					break;
				} else {
					// possibly site domain is a zone subdomain
					$regexp = '/^(.*\.)?'.str_replace('.', '\.', $zone['name']).'$/i';

					if (preg_match($regexp, $site_domain)) $zone_id = $zone['id'];
				}
			}
		}

		return $zone_id;
	}

	/**
	 * Get list with all Cloudflare zones(domains) defined in the accounts.
	 *
	 * @return array|WP_Error
	 */
	public function get_all_zones() {
		$all_zones = array();
		$page = 0;
		$total_pages = 1;

		while ($page < $total_pages) {
			$page++;

			$zones_page = $this->get_zones($page);

			if (is_wp_error($zones_page)) return $zones_page;

			if (false == $zones_page['success']) {
				return new WP_Error($zones_page['errors'][0]['code'], $zones_page['errors'][0]['message']);
			}


			if (!empty($zones_page['result']) && is_array($zones_page['result'])) $all_zones = array_merge($all_zones, $zones_page['result']);

			$total_pages = isset($zones_page['result_info']['total_pages']) ? $zones_page['result_info']['total_pages'] : $total_pages;
		}

		return $all_zones;
	}

	/**
	 * Get page from list with Cloudflare zones.
	 *
	 * @param int	 $page
	 * @param int 	 $per_page
	 *
	 * @return array|WP_Error
	 */
	public function get_zones($page = 1, $per_page = 50) {

		$query_params = array(
			'page' => $page,
			'per_page' => $per_page, // max value is 50.
		);

		return $this->do_request('GET', 'zones', array(), $query_params);
	}

	/**
	 * Purge cache for specified zone.
	 *
	 * @param string $zone_id
	 * @return array|WP_Error
	 */
	public function purge_cache($zone_id) {
		return $this->do_request('POST', 'zones/'.$zone_id.'/purge_cache', array(
			'purge_everything' => true,
		));
	}

	/**
	 * Purge specified urls in selected zone.
	 *
	 * @param string $zone_id
	 * @param array  $files
	 * @return array|WP_Error
	 */
	public function purge_urls($zone_id, $files) {
		return $this->do_request('POST', 'zones/'.$zone_id.'/purge_cache', array(
			'files' => $files,
		));
	}

	/**
	 * Do request to Cloudflare API.
	 *
	 * @param string $method       HTTP method
	 * @param string $endpoint 	   Cloudflare API endpoint
	 * @param array  $params       params will send in the request body
	 * @param array  $query_params params (page, per_page, ...) will encoded and added to request url
	 * @return array|WP_Error
	 */
	public function do_request($method, $endpoint, $params = array(), $query_params = array()) {

		$response_codes = array(
			400 => 'Request was invalid',
			401	=> 'User does not have permission',
			403	=> 'Request not authenticated',
			429	=> 'Client is rate limited',
			405	=> 'Incorrect HTTP method provided',
			415	=> 'Response is not valid JSON',
		);

		$headers = array(
			'Content-Type' => 'application/json',
		);

		// Possible two cases when auth key is an api key or a token.
		// In each case we send certain headers.
		if ('' != $this->api_email && self::is_auth_key($this->api_key)) {
			$headers['X-Auth-Email'] = $this->api_email;
			$headers['X-Auth-Key'] = $this->api_key;
		} else {
			$headers['Authorization'] = 'Bearer '.$this->api_token;
		}

		$request_params = array(
			'method' => $method,
			'timeout' => 30,
			'headers' => $headers,
			'body' => json_encode($params),
		);

		$url = self::API_URL . $endpoint. (!empty($query_params) ? '?'.http_build_query($query_params) : '');

		$this->debug($url);
		$this->debug($request_params);

		$response = wp_remote_request($url, $request_params);

		if (is_wp_error($response)) {
			$this->debug($response);
			return $response;
		}

		$this->debug($response['body']);

		if (200 != $response['response']['code']) {
			return new WP_Error($response['response']['code'], $response_codes[$response['response']['code']]);
		}

		return json_decode($response['body'], true);
	}

	/**
	 * Check if current string is an auth key for api. Auth key is a hexadecimal string.
	 *
	 * @param string $string
	 * @return bool
	 */
	public static function is_auth_key($string) {
		return (bool) preg_match('/^[0-9a-f]+$/', $string);
	}

	/**
	 * Write debug information into error log.
	 *
	 * @param mixed $info
	 */
	private function debug($info) {
		if (defined('WP_DEBUG') && WP_DEBUG) {
			if (is_array($info) || is_object($info)) $info = serialize($info);
			error_log("WP_Optimize_Cloudflare: $info");
		}
	}
}

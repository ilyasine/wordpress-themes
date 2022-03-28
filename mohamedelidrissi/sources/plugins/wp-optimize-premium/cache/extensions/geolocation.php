<?php
/**
 * /!\ This is currently only suppoted for when WooCommerce is enabled, and the geolocation is enabled.
 */

if (!defined('ABSPATH')) die('No direct access allowed');

/**
 * Get the country code
 *
 * @return string
 */
function wpo_cache_get_visitor_country_code() {
	static $country_code;

	if (!empty($country_code)) return $country_code;

	$defaults = wpo_cache_config_get('default_values');

	// 1. check if the server has geolocated the user already:
	if (!empty($_SERVER["HTTP_CF_IPCOUNTRY"])) {
		// CloudFlare has a variable available.
		$country_code = strtoupper($_SERVER["HTTP_CF_IPCOUNTRY"]);
	} elseif (!empty($_SERVER['GEOIP_COUNTRY_CODE'])) {
		// WP.com VIP has a variable available.
		$country_code = strtoupper($_SERVER['GEOIP_COUNTRY_CODE']);
	} elseif (!empty($_SERVER['HTTP_X_COUNTRY_CODE'])) {
		// VIP Go has a variable available also.
		$country_code = strtoupper($_SERVER['HTTP_X_COUNTRY_CODE']);
	} elseif (isset($defaults['wc_geolocate']) && $defaults['wc_geolocate']) {
		// wc_geolocate is only true if PHP is >= 5.4
		include 'inc/geolite2-compatibility.php';
		$country_code = wpo_get_country_code_from_geolite2($defaults['wc_geolocate_database'], $defaults['WC_ABSPATH']);
	}

	if (!empty($country_code)) return $country_code;

	// 3. Use default country
	if (isset($defaults['wc_default_country'])) {
		$country_code = $defaults['wc_default_country'];
		return $country_code;
	}

	return '';

}

/**
 * Get the user's IP Address (Extracted from WooCommerce WC_Geolocation::get_ip_address())
 *
 * @return string
 */
function wpo_cache_get_visitor_ip_address() {
	if (isset($_SERVER['HTTP_X_REAL_IP'])) {
		return $_SERVER['HTTP_X_REAL_IP'];
	} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		// Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
		// Make sure we always only send through the first IP in the list which should always be the client IP.
		return (string) trim(current(preg_split('/,/', $_SERVER['HTTP_X_FORWARDED_FOR'])));
	} elseif (isset($_SERVER['REMOTE_ADDR'])) {
		return $_SERVER['REMOTE_ADDR'];
	}
	return '';
}

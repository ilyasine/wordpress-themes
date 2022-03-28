<?php

if (!defined('ABSPATH')) die('No direct access allowed');

// works since WP 4.6
if (function_exists('add_filter')) {
	add_action('wpo_cache_extensions_loaded', 'wpo_cache_ext_set_default_values');
}

/**
 * When cookies aren't set, set the default $_COOKIE variable to allow serving files from cache
 *
 * @return void
 */
if (!function_exists('wpo_cache_ext_set_default_values')) {
	function wpo_cache_ext_set_default_values() {
		$cookies = wpo_cache_cookies();
		$defaults = wpo_cache_config_get('default_values');
		if (in_array('aelia_cs_selected_currency', $cookies) && !isset($_COOKIE['aelia_cs_selected_currency'])) {
			if (!empty($defaults) && isset($defaults['woocommerce_currency'])) {
				$_COOKIE['aelia_cs_selected_currency'] = $defaults['woocommerce_currency'];
			}
		}

		// Set default billing country. If the corresponding cache file exists, it will be served.
		if (in_array('woocommerce_tax_country', $cookies) && !isset($_COOKIE['woocommerce_tax_country'])) {
			if (wpo_cache_config_get('enable_cache_per_country')) {
				$_COOKIE['woocommerce_tax_country'] = wpo_cache_get_visitor_country_code();
				if (!headers_sent()) setcookie('woocommerce_tax_country', $_COOKIE['woocommerce_tax_country'], (time() + 30 * DAY_IN_SECONDS), '/');
			}
		}

	}
}

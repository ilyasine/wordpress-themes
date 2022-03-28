<?php

if (!defined('ABSPATH')) die('No direct access allowed');

/**
 * Get the country code using Geolite DB (using WooCommerce libraries)
 *
 * @param array $database  - The database path
 * @param array $base_path - The base path (WC_ABSPATH)
 * @return mixed
 */
function wpo_get_country_code_from_geolite2($database, $base_path) {
	// Check if at least one of the files exists, and if the server is compatible with geolite
	if (file_exists($base_path.'includes/libraries/geolite2/Reader/Decoder.php')) {
		// WC < 3.9
		require_once $base_path.'includes/libraries/geolite2/Reader/Decoder.php';
		require_once $base_path.'includes/libraries/geolite2/Reader/InvalidDatabaseException.php';
		require_once $base_path.'includes/libraries/geolite2/Reader/Metadata.php';
		require_once $base_path.'includes/libraries/geolite2/Reader/Util.php';
		require_once $base_path.'includes/libraries/geolite2/Reader.php';
	} elseif (file_exists($base_path.'vendor/maxmind-db/reader/src/MaxMind/Db/Reader/Decoder.php')) {
		// WC >= 3.9
		require_once $base_path.'vendor/maxmind-db/reader/src/MaxMind/Db/Reader/Decoder.php';
		require_once $base_path.'vendor/maxmind-db/reader/src/MaxMind/Db/Reader/InvalidDatabaseException.php';
		require_once $base_path.'vendor/maxmind-db/reader/src/MaxMind/Db/Reader/Metadata.php';
		require_once $base_path.'vendor/maxmind-db/reader/src/MaxMind/Db/Reader/Util.php';
		require_once $base_path.'vendor/maxmind-db/reader/src/MaxMind/Db/Reader.php';
	}

	if (class_exists('MaxMind\Db\Reader')) {
		try {
			$reader = new MaxMind\Db\Reader($database); // phpcs:ignore PHPCompatibility.LanguageConstructs.NewLanguageConstructs.t_ns_separatorFound
			$data   = $reader->get(wpo_cache_get_visitor_ip_address());

			if (isset($data['country']['iso_code'])) {
				$iso_code = $data['country']['iso_code'];
			}

			$reader->close();
			if (!empty($iso_code)) {
				return strtoupper($iso_code);
			}
		} catch (Exception $e) {
			if (defined('WP_DEBUG') && WP_DEBUG || isset($_GET['wpo_cache_debug'])) {
				error_log('wpo_get_country_code_from_geolite2: '.$e->getMessage());
			}
		}
	}

	return '';
}

<?php

if (!defined('ABSPATH')) die('No direct access allowed');

require_once ABSPATH.'/wp-includes/pluggable.php';

// Add filter for filtering user specific cache filename.
if (!empty($GLOBALS['wpo_cache_config']['enable_user_specific_cache'])) {
	add_filter('wpo_cache_filename', 'wpo_user_cache_filename');
}

/**
 * Get username from WordPress cookies.
 */
if (!function_exists('wpo_username_from_cookies')) :
function wpo_username_from_cookies() {

	global $wpdb;

	// initialize database if it isn't initialized.
	if (!$wpdb) {
		require_wp_db();
		wp_set_wpdb_vars();
	}

	// get wordpress_logged_in cookie values.
	$cookie_parts = array();
	foreach ($_COOKIE as $key => $value) {
		if (!preg_match('/^wordpress_logged_in_.+/', $key)) continue;

		$cookie_parts = explode('|', $value);
	}

	if (4 > count($cookie_parts)) return false;

	list($username, $expiration, $token, $hmac) = $cookie_parts;

	// if cookies expired then return false.
	if ($expiration < time()) return false;

	// get user password hash from database.
	$user = $wpdb->get_row($wpdb->prepare("SELECT user_pass FROM {$wpdb->users} WHERE user_login=%s", $username));

	// if used doesn't exist return false.
	if (!is_object($user)) return false;

	// validate cookie hash, used code from:
	// wp_validate_auth_cookie() wp-includes/pluggable.php

	$pass_frag = substr($user->user_pass, 8, 4);

	$key = hash_hmac('md5', $username . '|' . $pass_frag . '|' . $expiration . '|' . $token, wp_salt('logged_in'));

	$algo = function_exists('hash') ? 'sha256' : 'sha1';
	$hash = hash_hmac($algo, $username . '|' . $expiration . '|' . $token, $key);

	if (!hash_equals($hash, $hmac)) return false; // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.hash_equalsFound

	return $username;
}
endif;

if (!function_exists('wpo_user_cache_filename')) :

/**
 * Filters cached filename.
 *
 * @param string $filename source filename
 *
 * @return $string
 */
function wpo_user_cache_filename($filename) {
	
	if (!wpo_cache_loggedin_users()) return $filename;

	$username = wpo_username_from_cookies();
	
	if (!$username) return $filename;
	
	$salt = wp_salt();

	$encoded_filename = sha1($filename.$salt.$username);
	
	return $encoded_filename;
}

endif;

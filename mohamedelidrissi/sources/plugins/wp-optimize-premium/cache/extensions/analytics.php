<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (function_exists('add_filter')) {
	function wpo_cache_ignore_analytics_query_variables($exclude) {
		$exclude = array_merge($exclude, array('utm_campaign', 'utm_medium', 'utm_source', 'utm_content', 'utm_term'));
		return $exclude;
	};

	add_filter('wpo_cache_ignore_query_variables', 'wpo_cache_ignore_analytics_query_variables');
}

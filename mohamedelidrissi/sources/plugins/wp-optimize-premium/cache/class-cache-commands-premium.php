<?php

if (!defined('ABSPATH')) die('No direct access allowed');

/**
 * All cache commands that are intended to be available for calling from any sort of control interface (e.g. wp-admin, UpdraftCentral) go in here. All public methods should either return the data to be returned, or a WP_Error with associated error code, message and error data.
 */
class WP_Optimize_Cache_Commands_Premium extends WP_Optimize_Cache_Commands {

	/**
	 * Command to disable caching/lazy-load for the selected post
	 *
	 * @param {array} $params ['post_id' => (int), 'meta_key' => '_wpo_disable_caching | _wpo_disable_lazyload', 'disable' => (bool)]
	 * @return array
	 */
	public function change_post_disable_option($params) {

		$accepted_keys = array('_wpo_disable_caching', '_wpo_disable_lazyload');

		$meta_key = isset($params['meta_key']) ? $params['meta_key'] : '_wpo_disable_caching';

		if (!in_array($meta_key, $accepted_keys)) {
			return array(
				'result' => false,
				'messages' => array(),
				'errors' => array(
					'Not accepted meta_key value',
				)
			);
		}

		if (!isset($params['post_id'])) {
			return array(
				'result' => false,
				'messages' => array(),
				'errors' => array(
					'No post was indicated.',
				)
			);
		}

		$post_id = $params['post_id'];
		$disable = isset($params['disable']) && ('false' != $params['disable']);

		if ($disable) {
			update_post_meta($post_id, $meta_key, $disable);
		} else {
			delete_post_meta($post_id, $meta_key);
		}

		$disable_caching = get_post_meta($post_id, $meta_key, true);

		if ($disable_caching) {
			WPO_Page_Cache::delete_single_post_cache($post_id);
		}

		return array(
			'result' => true,
			'disabled' => (bool) $disable_caching,
		);
	}
}

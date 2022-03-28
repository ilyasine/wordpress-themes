<?php

if (!defined('WPO_VERSION')) die('No direct access allowed');

if (!class_exists('Updraft_Abstract_Logger')) require_once(WPO_PLUGIN_MAIN_PATH.'/includes/class-updraft-abstract-logger.php');
if (!class_exists('Updraft_PHP_Logger')) require_once(WPO_PLUGIN_MAIN_PATH.'/includes/class-updraft-php-logger.php');


/**
 * Class WP_Optimization_Images_Shutdown
 *
 * Used for handling fatal errors in unused images optimization.
 */
class WP_Optimization_Images_Shutdown {

	static private $_instance;

	private $_active = false;

	private $_logger = false;

	private $_info = array();

	protected $meta = array();

	/**
	 * WP_Optimization_Images_Shutdown constructor.
	 */
	private function __construct() {
		$this->_logger = new Updraft_PHP_Logger();

		$this->load_values();

		// use WordPress filter to handle fatal errors before WordPress output.
		add_filter('wp_php_error_message', array($this, 'shutdown'), 1);

		register_shutdown_function(array($this, 'shutdown'));
	}

	/**
	 * Get WP_Optimization_Images_Shutdown instance.
	 *
	 * @return WP_Optimization_Images_Shutdown
	 */
	static public function get_instance() {
		if (!self::$_instance) {
			self::$_instance = new WP_Optimization_Images_Shutdown();
		}

		return self::$_instance;
	}

	/**
	 * Activate shutdown handler.
	 */
	public function activate() {
		$this->_active = true;
	}

	/**
	 * Deactivate shutdown handler.
	 */
	public function deactivate() {
		$this->_active = false;
	}

	/**
	 * Get current shutdown status.
	 *
	 * @return bool
	 */
	public function is_active() {
		return $this->_active;
	}

	/**
	 * Set custom variable value. Used to store temporary values in transients.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set_value($key, $value) {
		$this->_info[$key] = $value;
	}

	/**
	 * Get custom variable value.
	 *
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed|null
	 */
	public function get_value($key, $default = null) {
		if (array_key_exists($key, $this->_info)) return $this->_info[$key];

		return $default;
	}

	/**
	 * Delete custom value.
	 *
	 * @param string $key
	 */
	public function delete_value($key) {
		if (array_key_exists($key, $this->_info)) unset($this->_info[$key]);
	}

	/**
	 * Load custom values from options.
	 */
	public function load_values() {
		if (is_multisite()) {
			$this->_info = get_site_option('wp-optimize-wpo_images_shutdown', array());
		} else {
			$this->_info = get_option('wp-optimize-wpo_images_shutdown', array());
		}
	}

	/**
	 * Save custom values into options.
	 */
	public function save_values() {
		if (is_multisite()) {
			return update_site_option('wp-optimize-wpo_images_shutdown', $this->_info);
		} else {
			return update_option('wp-optimize-wpo_images_shutdown', $this->_info);
		}
	}

	/**
	 * Clear saved values.
	 */
	public function reset_values() {
		$this->_info = array();
		$this->save_values();
	}

	/**
	 * Set meta data which will returned in the output when script ending with failure.
	 *
	 * @param array $meta
	 */
	public function set_meta($meta) {
		$this->meta = $meta;
	}

	/**
	 * Get meta data.
	 *
	 * @return array
	 */
	public function get_meta() {
		return $this->meta;
	}

	/**
	 * Shutdown function.
	 */
	public function shutdown() {
		if (!$this->is_active()) return;
		// flush cached values.
		WP_Optimize_Transients_Cache::get_instance()->flush();

		// Unlock queue if queue is set.
		$queue_id = $this->get_value('queue_id');
		if ($queue_id) {
			WP_Optimize_Tasks_Queue::this($queue_id)->unlock();
		}

		if (defined('WP_OPTIMIZE_UNUSED_IMAGES_LOG') && WP_OPTIMIZE_UNUSED_IMAGES_LOG) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
			$trace = debug_backtrace();

			foreach ($trace as $line) {
				$this->_logger->debug(json_encode($line));
			}
		}

		$this->save_bad_post();
		$this->save_values();

		$result = array(
			'result' => array(
				'meta' => $this->get_meta(),
			),
			'messages' => array(),
			'errors' => array(),
		);

		// send status header 200 and keep optimization working on frontend.
		status_header(200);

		// send correct answer and keep optimization working on frontend.
		echo json_encode($result);

		exit();
	}

	/**
	 * Check if post is in the "bad posts" list. "Bad" means that the post raise fatal error
	 * when we call do_shortcode(post_content) for it. If the post is "bad" then we don't call
	 * do_shortcode() for this post and parse images in the original post_content.
	 *
	 * @param int $blog_id
	 * @param int $post_id
	 * @return bool
	 */
	public function is_bad_post($blog_id, $post_id) {
		$bad_posts = $this->get_value('bad_posts', array());
		return (array_key_exists($blog_id, $bad_posts) && array_key_exists($post_id, $bad_posts[$blog_id]));
	}

	/**
	 * Save post information (if there any) in "bad posts" lists. "Bad" means that the post raise fatal error
	 * when we call do_shortcode(post_content) for it. If the post is "bad" then we don't call
	 * do_shortcode() for this post and parse images in the original post_content.
	 */
	private function save_bad_post() {
		$last_post_id = $this->get_value('last_post_id');
		if (null === $last_post_id) return;

		$blog_id = $this->get_value('blog_id');

		$bad_posts = $this->get_value('bad_posts', array());

		if (!array_key_exists($blog_id, $bad_posts)) $bad_posts[$blog_id] = array();

		$bad_posts[$blog_id][$last_post_id] = true;

		$this->delete_value('blog_id');
		$this->delete_value('last_post_id');

		$this->set_value('bad_posts', $bad_posts);
	}
}

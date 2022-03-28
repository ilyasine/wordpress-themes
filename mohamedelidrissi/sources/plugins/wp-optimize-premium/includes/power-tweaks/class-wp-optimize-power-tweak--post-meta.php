<?php

if (!defined('ABSPATH')) die('No direct access allowed');

class WP_Optimize_Power_Tweak__Post_Meta extends WP_Optimize_Power_Tweak {

	/**
	 * Tweak identifier
	 *
	 * @var string
	 */
	protected $tweak_name = 'post-meta';

	/**
	 * FAQ link
	 *
	 * @var string
	 */
	protected $tweak_link = 'https://getwpo.com/faqs/create-index-on-post-meta-table/';

	/**
	 * Action type (`activate` for recurring, `run` for one-shot actions)
	 *
	 * @var string
	 */
	protected $action_type = 'run';

	/**
	 * Initialize the tweaks
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get the labels
	 *
	 * @return array
	 */
	public function get_labels() {
		return array(
			'title' => __('Index Post Meta Table', 'wp-optimize'),
			'run' => __('Resize and index the meta_key column', 'wp-optimize'),
			'description' => __('By default, searches on the WordPress "post meta" database table are significantly slower than necessary because the table permits very rarely-used key sizes of longer than 191 characters, which prevents indexing the table. This tweak checks if anything in your database uses such long keys, and if not, creates an index by lowering the limit down to 191 characters.', 'wp-optimize'),
			'details' => __('Changes the table scheme for your postmeta table by reducing the maximum length of the meta_key field down to 191 characters (if nothing already exists longer than that).', 'wp-optimize')
		);
	}

	/**
	 * Run the tweak
	 *
	 * @return array
	 */
	public function run() {
		$success = array('message' => __('The post meta index was created succesfully.', 'wp-optimize'));
		$failure = array('message' => __('The post meta index creation was unsuccessful.', 'wp-optimize'));

		if($this->create_post_meta_index()) return $success;
		return $failure;
	}

	/**
	 * Test the availability of the tweak
	 *
	 * @return boolean
	 */
	public function test_availability() {
		$last_run = WP_Optimize()->get_options()->get_option('tweak_last_run_post-meta', false);
		if (false === $last_run) {
			return $this->can_create_post_meta_index();
		}
		return false;
	}

	/**
	 * Create post meta index
	 */
	private function create_post_meta_index() {
		return $this->alter_post_meta_index(191);
	}

	/**
	 * Check whether an index can be created for `post_meta` table
	 *
	 * @return boolean
	 */
	private function can_create_post_meta_index() {
		global $wpdb;
		$col_info = $wpdb->get_col_length($wpdb->postmeta, 'meta_key');
		if ($col_info['length'] <= 191) return false;

		global $wpdb;
		$sql = "SELECT meta_key FROM $wpdb->postmeta" .
		 " WHERE LENGTH(meta_key) > 191 LIMIT 1";
		$wpdb->get_results($sql);

		return $wpdb->num_rows < 1;
	}

	/**
	 * Alters post meta index
	 */
	private function alter_post_meta_index($column_length = 255) {
		global $wpdb;
		$sql = "SHOW INDEX FROM $wpdb->postmeta WHERE KEY_NAME = 'meta_key'";
		$result = $wpdb->query($sql);
		if (false !== $result) {
			$sql = "DROP INDEX meta_key ON $wpdb->postmeta";
			$result = $wpdb->query($sql);
		}
		$sql = $wpdb->prepare(
			"ALTER TABLE $wpdb->postmeta MODIFY COLUMN meta_key VARCHAR(%d)",
			$column_length
		);
		$result = $wpdb->query($sql);

		if (false !== $result) {
			$sql = "CREATE INDEX meta_key ON $wpdb->postmeta (meta_key)";
			$result = $wpdb->query($sql);
			if (false !== $result) return true;
		}
		return false;
	}
}

return new WP_Optimize_Power_Tweak__Post_Meta();

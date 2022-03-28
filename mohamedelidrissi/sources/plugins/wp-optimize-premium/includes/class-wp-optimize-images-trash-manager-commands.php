<?php
/**
 *  WP-Optimize Images trash commands class
 */

if (!defined('ABSPATH')) die('Access denied.');

if (!class_exists('Updraft_Task_Manager_Commands_1_0')) require_once(WPO_PLUGIN_MAIN_PATH . 'vendor/team-updraft/common-libs/src/updraft-tasks/class-updraft-task-manager-commands.php');

if (!class_exists('WP_Optimize_Images_Trash_Manager_Commands')) :

class WP_Optimize_Images_Trash_Manager_Commands extends Updraft_Task_Manager_Commands_1_0 {

	/**
	 * The commands constructor
	 *
	 * @param mixed $task_manager - A task manager instance
	 */
	public function __construct($task_manager) {
		parent::__construct($task_manager);
	}

	/**
	 * Returns a list of commands available for images trash related operations
	 */
	public static function get_allowed_ajax_commands() {

		$commands = apply_filters('updraft_task_manager_allowed_ajax_commands', array());

		$trash_commands = array(
			'move_images_to_trash',
			'get_trash_images',
			'restore_images_from_trash',
			'remove_trash_images',
		);

		return array_merge($commands, $trash_commands);
	}

	/**
	 * Move selected inmages to trash
	 *
	 * @param array $data
	 */
	public function move_images_to_trash($data) {

		$blogs_updated = array();

		foreach ($data['images'] as $image) {

			// get information about image from the filename.
			$options = $this->parse_posted_image($image);

			// set updated flag for the image blog
			$blogs_updated[$options['blog_id']] = true;

			// add task to queue for processing images
			WP_Optimize_Images_Trash_Task::create_task('move_image_to_trash', '', $options);
		}

		// process tasks queue and move images to trash.
		$this->task_manager->process_queue('move_image_to_trash');
		$this->task_manager->clean_up_old_tasks('move_image_to_trash');

		// delete cached data for updated blogs.
		foreach (array_keys($blogs_updated) as $id) {
			$this->task_manager->clear_cached_data($id);
		}

		$success_count = WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('move_image_to_trash_success', 0);
		$total_count = $success_count + WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('move_image_to_trash_failure', 0);

		$moved_images = WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('moved_images', array());
		WP_Optimization_images::instance()->delete_selected_images_from_cache($moved_images);

		$message = sprintf(__('%s of %s images were successfully moved to the trash', 'wp-optimize'), $success_count, $total_count);

		return array('success' => true, 'message' => $message);
	}

	/**
	 * Get images list those are in the trash.
	 *
	 * @param array $data
	 */
	public function get_trash_images($data) {
		$length = isset($data['length']) ? $data['length'] : 99;
		$offset = isset($data['offset']) ? $data['offset'] : 0;
		$blog_id = isset($data['blog_id']) ? $data['blog_id'] : 1;

		if (is_multisite()) {
			switch_to_blog($blog_id);
		}

		$base_url = WP_Optimize_Images_Trash_Manager::instance()->get_trash_url();
		$images = WP_Optimize_Images_Trash_Manager::instance()->get_trash_images($length, $offset, $blog_id);
		$images_total = (int) $images['total'];
		unset($images['total']);

		if (is_multisite()) {
			restore_current_blog();
		}

		return array(
			'success' => true,
			'images' => $images,
			'total' => $images_total,
			'base_url' => $base_url,
		);
	}

	/**
	 * Restore images fron unused images trash.
	 *
	 * @param array $data
	 * @return array
	 */
	public function restore_images_from_trash($data) {
		$blogs_updated = array();

		foreach ($data['images'] as $image) {
			$blogs_updated[$this->get_blog_id_from_image_filename($image)] = true;

			// add task to queue for processing images
			WP_Optimize_Images_Trash_Task::create_task('restore_image_from_trash', '', array(
				'file' => $image,
			));
		}

		// process tasks queue and move images to trash.
		$this->task_manager->process_queue('restore_image_from_trash');
		$this->task_manager->clean_up_old_tasks('restore_image_from_trash');

		// delete cached data for updated blogs.
		foreach (array_keys($blogs_updated) as $id) {
			$this->task_manager->clear_cached_data($id);
		}

		$success_count = WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('restore_image_from_trash_success', 0);
		$total_count = $success_count + WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('restore_image_from_trash_failure', 0);

		$restored_images = WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('restored_images', array());
		WP_Optimization_images::instance()->add_selected_images_to_cache($restored_images);

		$message = sprintf(__('%s of %s images were successfully restored from the trash', 'wp-optimize'), $success_count, $total_count);

		return array('success' => true, 'message' => $message);

	}

	/**
	 * Permanently remove selected unused images from trash.
	 */
	public function remove_trash_images($data) {
		$blogs_updated = array();

		foreach ($data['images'] as $image) {
			$blogs_updated[$this->get_blog_id_from_image_filename($image)] = true;

			// add task to queue for processing images
			WP_Optimize_Images_Trash_Task::create_task('remove_trash_images', '', array(
				'file' => $image,
			));
		}

		// process tasks queue and move images to trash.
		$this->task_manager->process_queue('remove_trash_images');
		$this->task_manager->clean_up_old_tasks('remove_trash_images');

		// delete cached data for updated blogs.
		foreach (array_keys($blogs_updated) as $id) {
			$this->task_manager->clear_cached_data($id);
		}

		$success_count = WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('remove_trash_images_success', 0);
		$total_count = $success_count + WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('remove_trash_images_failure', 0);

		$message = sprintf(__('%s of %s images were permanently removed from trash', 'wp-optimize'), $success_count, $total_count);

		return array('success' => true, 'message' => $message);
	}

	/**
	 * Parse posted image information string like {blog_id}_{image_id|file} an return as associative array.
	 *
	 * @param string $image
	 * @return array
	 */
	private function parse_posted_image($image) {
		preg_match('/^(\d+)_(.+)$/', $image, $image_info_parts);

		$image_id_posted = is_numeric($image_info_parts[2]);

		return array(
			'original' => $image,
			'blog_id' => $image_info_parts[1],
			'image_id' => $image_id_posted ? $image_info_parts[2] : 0,
			'file' => $image_id_posted ? '' : $image_info_parts[2],
		);
	}

	/**
	 * Get blog id for image filename in unused images trash directory.
	 *
	 * @param string $image
	 *
	 * @return int
	 */
	private function get_blog_id_from_image_filename($image) {
		if (preg_match('/\/(\d+)_(.+)$/', $image, $match)) {
			return $match[1];
		}

		return 0;
	}
}

endif;

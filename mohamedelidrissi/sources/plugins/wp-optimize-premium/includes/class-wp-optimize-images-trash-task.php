<?php

if (!defined('ABSPATH')) die('Access denied.');

if (!class_exists('Updraft_Task_1_1')) require_once(WPO_PLUGIN_MAIN_PATH . 'vendor/team-updraft/common-libs/src/updraft-tasks/class-updraft-task.php');

if (!class_exists('WP_Optimize_Images_Trash_Task')) :

class WP_Optimize_Images_Trash_Task extends Updraft_Task_1_1 {

	/**
	 * Runs the task
	 *
	 * @return bool - true if complete, false otherwise
	 */
	public function run() {

		$this->set_status('active');

		// move to trash task.
		if ('move_image_to_trash' == $this->get_type()) {
			$original = $this->get_option('original');
			$image_id = $this->get_option('image_id');
			$file = $this->get_option('file');
			$blog_id = $this->get_option('blog_id');

			$result = WP_Optimize_Images_Trash_Manager::instance()->move_image_to_trash($image_id, $file, $blog_id);

			if (!is_wp_error($result)) {
				$success_count = WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('move_image_to_trash_success', 0) + 1;
				WP_Optimize_Images_Trash_Manager::instance()->update_stat_value('move_image_to_trash_success', $success_count);
				$moved_images = WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('moved_images', array());
				$moved_images[] = $original;
				WP_Optimize_Images_Trash_Manager::instance()->update_stat_value('moved_images', $moved_images);
			} else {
				$failure_count = WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('move_image_to_trash_failure', 0) + 1;
				WP_Optimize_Images_Trash_Manager::instance()->update_stat_value('move_image_to_trash_failure', $failure_count);
			}
		}

		// restore from trash task.
		if ('restore_image_from_trash' == $this->get_type()) {
			$filename = $this->get_option('file');

			$result = WP_Optimize_Images_Trash_Manager::instance()->restore_from_trash($filename);

			if (!is_wp_error($result)) {
				$success_count = WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('restore_image_from_trash_success', 0) + 1;
				WP_Optimize_Images_Trash_Manager::instance()->update_stat_value('restore_image_from_trash_success', $success_count);
				$restored_image_info = WP_Optimize_Images_Trash_Manager::instance()->get_last_restored_image();
				$restored_images = WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('restored_images', array());
				$restored_images[] = array_key_exists('size', $restored_image_info) ? array($filename, $restored_image_info['size']) : $filename;
				WP_Optimize_Images_Trash_Manager::instance()->update_stat_value('restored_images', $restored_images);
			} else {
				$failure_count = WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('restore_image_from_trash_failure', 0) + 1;
				WP_Optimize_Images_Trash_Manager::instance()->update_stat_value('restore_image_from_trash_failure', $failure_count);
			}
		}

		// delete images from trash
		if ('remove_trash_images' == $this->get_type()) {
			$filename = $this->get_option('file');

			$result = WP_Optimize_Images_Trash_Manager::instance()->remove_from_trash($filename);

			if (!is_wp_error($result)) {
				$success_count = WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('remove_trash_images_success', 0) + 1;
				WP_Optimize_Images_Trash_Manager::instance()->update_stat_value('remove_trash_images_success', $success_count);
			} else {
				$failure_count = WP_Optimize_Images_Trash_Manager::instance()->get_stat_value('remove_trash_images_failure', 0) + 1;
				WP_Optimize_Images_Trash_Manager::instance()->update_stat_value('remove_trash_images_failure', $failure_count);
			}
		}

		return true;
	}
}
endif;

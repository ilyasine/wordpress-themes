<?php
/**
 *  Extends the generic task manager to manage images trash actions
 */

if (!defined('ABSPATH')) die('Access denied.');

if (!class_exists('Updraft_Task_Manager_1_2')) require_once(WPO_PLUGIN_MAIN_PATH . 'vendor/team-updraft/common-libs/src/updraft-tasks/class-updraft-task-manager.php');

if (!class_exists('WP_Optimize_Images_Trash_Manager')) :

class WP_Optimize_Images_Trash_Manager extends Updraft_Task_Manager_1_2 {

	protected $options;

	protected $statistics;

	static protected $_instance = null;

	private $_last_restored_image = array();

	/**
	 * The Task Manager constructor
	 */
	public function __construct() {
		parent::__construct();

		if (!class_exists('WP_Optimize_Images_Trash_Manager_Commands')) include_once('class-wp-optimize-images-trash-manager-commands.php');

		$this->commands = new WP_Optimize_Images_Trash_Manager_Commands($this);
		$this->options = WP_Optimize()->get_options();
	}

	/**
	 * Get absolute path to trash directory for current blog.
	 *
	 * @return string
	 */
	public function get_trash_dir() {
		return $this->get_upload_basedir() . '/wpo-unused-images';
	}

	/**
	 * Get url to trash directory.
	 *
	 * @return string
	 */
	public function get_trash_url() {
		$upload_dir = $this->get_upload_dir();
		return $upload_dir['baseurl'] . '/wpo-unused-images';
	}

	/**
	 * Get absolute path to upload base directory for current blog.
	 *
	 * @return string
	 */
	private function get_upload_basedir() {
		$upload_dir = $this->get_upload_dir();
		return $upload_dir['basedir'];
	}

	/**
	 * Get information about WP upload directory.
	 *
	 * @return array
	 */
	private function get_upload_dir() {
		$upload_dir = function_exists('wp_get_upload_dir') ? wp_get_upload_dir() : wp_upload_dir(null, false);
		return $upload_dir;
	}

	/**
	 * Get statistics information.
	 *
	 * @return array
	 */
	public function get_statistics() {
		return $this->statistics;
	}

	/**
	 * Get single value from statistics array.
	 *
	 * @param string $key
	 * @param string $default
	 * @return mixed|string
	 */
	public function get_stat_value($key, $default = '') {
		if (is_array($this->statistics) && array_key_exists($key, $this->statistics)) return $this->statistics[$key];
		return $default;
	}

	/**
	 * Update single value in statistics array.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function update_stat_value($key, $value) {
		$this->statistics[$key] = $value;
	}

	/**
	 * Move single image to trash. Two cases possible here:
	 *  - Image is defined in WP database then all files related to this image will be moved to the trash folder
	 *	  with the name like {blog_id}_{image_id}-{original filename} and the status for the attachment post will set to 'trash'
	 *  - Image is not exists in WP database then single image file will be moved from the upload folder with the
	 *    name {blog_id}_x-{original filename}
	 *
	 * @param int 	 $image_id attachment post id or zero
	 * @param string $file     path to image file if this file isn't in the database.
	 * @param int    $blog_id
	 * @return bool|WP_Error
	 */
	public function move_image_to_trash($image_id, $file = '', $blog_id = 1) {
		global $wpdb;

		// change blog if for multisite installations
		if (is_multisite()) {
			switch_to_blog($blog_id);
		}

		if ($image_id > 0) {
			// move all files related to the $image_id from upload folder to trash folder
			$success = $this->move_image_files_to_trash($image_id, $blog_id);

			// if operation has finished successfully we update post status
			if (!is_wp_error($success)) {
				// get information about current attachment post status.
				$image_info = $wpdb->get_row($wpdb->prepare("SELECT post_status FROM {$wpdb->posts} WHERE ID = %d", $image_id), ARRAY_A);

				// store old post_status value and set to 'trash'
				update_post_meta($image_id, '_old_post_status', $image_info['post_status']);
				$wpdb->update($wpdb->posts, array('post_status' => 'trash'), array('ID' => $image_id));
			}

		} elseif ('' != $file) {
			// we are moving this single file to the trash directory
			$upload_basedir = $this->get_upload_basedir();
			// get file path info
			$file = str_replace($upload_basedir, '', $file);
			$file_info = pathinfo($file);
			$relative_path = $file_info['dirname'];

			// get absolute path to the image source
			$source = trailingslashit($upload_basedir) . $file;
			// get absolute path to the destination
			$destination_dir = trailingslashit($this->get_trash_dir()) . $relative_path;
			$destination = $destination_dir . '/'. $this->get_trash_filename($file_info['basename']);

			// create destination dir
			wp_mkdir_p($destination_dir);

			// move file to the trash
			$success = $this->move_file($source, $destination);
		} else {
			$success = new WP_Error('move_image_to_trash', __('Image not defined', 'wp-optimize'));
		}

		// restore current blog for multisite installations
		if (is_multisite()) {
			restore_current_blog();
		}

		return $success;
	}

	/**
	 * Get list of images from trash.
	 *
	 * @param int $length
	 * @param int $offset
	 * @param int $blog_id
	 * @return array list of relative paths to image files in the trash folder
	 */
	public function get_trash_images($length = 99, $offset = 0, $blog_id = 1) {

		$cache_key = $length.'_'.$offset.'_'.$blog_id;
		$cached = $this->get_from_cache($cache_key, $blog_id);
		$total_cached = $this->get_from_cache('trash_images_count', $blog_id);

		if (!empty($cached) && $total_cached) {
			$cached['total'] = $total_cached;
			return $cached;
		};

		// switch to the selected blog if need.
		if (is_multisite()) {
			switch_to_blog($blog_id);
		}

		// count of currently found image files
		$found = 0;

		$in_result = array();
		$result = array();

		// get path for the trash directory
		$trash_dir = $this->get_trash_dir();

		// list of directories for scan.
		$directories = array(
			$trash_dir,
		);

		// scan all subdirectories in the trash directory
		while (!empty($directories) && (count($result) < $length)) {
			// get the next directory for scan
			$dir = array_shift($directories);
			// get relative path to the directory
			$relative_path = str_replace($trash_dir, '', $dir);

			if (!is_dir($dir)) continue;

			// scan the directory
			$handle = opendir($dir);

			if (false === $handle) continue;

			while ($file = readdir($handle)) {
				if ('.' == $file || '..' == $file) continue;

				// push found directory to the directories list for scan in the next iterations
				if (is_dir($dir . '/' . $file)) {
					$directories[] = $dir . '/' . $file;
					continue;
				}

				// if the file is not a trashed image then we pass it
				if (!preg_match('/^(\d+)_([x\d]+)\-/U', $file, $match)) continue;

				if ('x' != $match[2]) {
					// image file has the attachment post in the database
					$image_id = $match[2];

					// if we didn't add it to the result then we are adding it
					if (!array_key_exists($image_id, $in_result)) {
						$in_result[$image_id] = count($result);
						$found++;
						if ($offset < $found && count($result) < $length) {
							$result[] = array($relative_path .'/' .$file, $match[2]);
						}
					}
				} else {
					// the image without attachment post in the database
					// we are adding just relative path to the image file
					$found++;
					if ($offset < $found && count($result) < $length) {
						$result[] = array($relative_path .'/' .$file);
					}
				}

				// if we have found enough images then break loop
				if (count($result) >= $length && $total_cached) break;
			}

			closedir($handle);
		}

		// restore current blog if need
		if (is_multisite()) {
			restore_current_blog();
		}

		$this->save_to_cache($cache_key, $result, $blog_id);

		if ($total_cached) {
			$result['total'] = $total_cached;
		} else {
			$this->save_to_cache('trash_images_count', $found, $blog_id);
			$result['total'] = $found;
		}

		return $result;
	}

	/**
	 * Move all image files related to attachment post with $image_id to WP-O trash directory
	 *
	 * @param int $image_id
	 * @param int $blog_id
	 * @return bool|WP_Error
	 */
	private function move_image_files_to_trash($image_id, $blog_id) {
		// get relative paths to the image (original and resized) files
		$image_files = $this->get_all_image_files($image_id);

		if (empty($image_files)) return true;

		$files_for_move = array();

		$file_info = pathinfo($image_files[0]);

		// get absolute paths to source and destination directories
		$source_dir = trailingslashit($this->get_upload_basedir()) . $file_info['dirname'];
		$destination_dir = trailingslashit($this->get_trash_dir()) . $file_info['dirname'];

		// create destination directory if need
		wp_mkdir_p($destination_dir);

		// build list of files for moving
		foreach ($image_files as $image_file) {
			$file_info = pathinfo($image_file);
			$filename = $file_info['basename'];
			$files_for_move[] = array(
				$filename,
				$this->get_trash_filename($filename, $image_id, $blog_id),
			);
		}

		// move selected files from the source to the destination directory
		return $this->move_files($files_for_move, $source_dir, $destination_dir);
	}

	/**
	 * Restore image from WP-Optimize trash folder.
	 *
	 * @param string $filename relative or absolute path to the file in the trash directory
	 * @return bool|WP_Error
	 */
	public function restore_from_trash($filename) {
		global $wpdb;

		// get information about image from the filename
		$file_info = pathinfo($filename);
		$basename = $file_info['basename'];

		preg_match('/^(\d+)_([x\d]+)\-/U', $basename, $match);

		$blog_id = $match[1];
		$image_id = $match[2];

		// switch to the correspondent blog if need
		if (is_multisite()) {
			switch_to_blog($blog_id);
		}

		// get relative path to the file
		$filename = str_replace($this->get_trash_dir(), '', $filename);

		$file_info = pathinfo($filename);
		$relative_path = $file_info['dirname'];

		if ('x' != $image_id) {
			// image is defined in the database and we search all image files with provided image id
			$mask = trailingslashit($this->get_trash_dir() . $relative_path) . $blog_id.'_'.$image_id.'-*';
			$files = glob($mask);

			// prepare list of filenames for move here
			$files_for_move = array();

			foreach ($files as $file) {
				$file_info = pathinfo($file);

				$files_for_move[] = array(
					$file_info['basename'],
					substr($file_info['basename'], strpos($file_info['basename'], '-') + 1),
				);
			}

			// move image files from trash directory to the upload directory
			$success = $this->move_files($files_for_move, $this->get_trash_dir() . $relative_path, $this->get_upload_basedir() . $relative_path);

			// if operation finished successfully restore post status for the attachment post
			if (!is_wp_error($success)) {
				// restore old post_status
				$old_post_status = get_post_meta($image_id, '_old_post_status', true);
				delete_post_meta($image_id, '_old_post_status');
				$wpdb->update($wpdb->posts, array('post_status' => $old_post_status), array('ID' => $image_id));

				$this->_last_restored_image = array(
					'blog_id' => $blog_id,
					'image_id' => $image_id,
				);
			}
		} else {
			// image is not in the database we just move single file without changes in the database
			$original_name = substr($file_info['basename'], strpos($file_info['basename'], '-') + 1);
			// move image file from trash to the upload folder
			$success = $this->move_file($this->get_trash_dir() . $relative_path . '/' . $file_info['basename'], $this->get_upload_basedir() . $relative_path . '/' . $original_name);
			if ($success) {
				$this->_last_restored_image = array(
					'blog_id' => $blog_id,
					'file' => $this->get_upload_basedir() . $relative_path . '/' . $original_name,
					'size' => filesize($this->get_upload_basedir() . $relative_path . '/' . $original_name),
				);
			}
		}

		// restore current blog for multisite if need
		if (is_multisite()) {
			restore_current_blog();
		}

		return $success;
	}

	/**
	 * Get information about the last successfully restored image.
	 *
	 * @return array
	 */
	public function get_last_restored_image() {
		return $this->_last_restored_image;
	}

	/**
	 * Remove single image from trash.
	 *
	 * @param string $filename
	 * @return bool
	 */
	public function remove_from_trash($filename) {
		// get information about image from the filename
		$file_info = pathinfo($filename);
		$basename = $file_info['basename'];

		preg_match('/^(\d+)_([x\d]+)\-/U', $basename, $match);

		$blog_id = $match[1];
		$image_id = $match[2];

		// switch to the correspondent blog if need
		if (is_multisite()) {
			switch_to_blog($blog_id);
		}

		// get relative path to the file
		$filename = str_replace($this->get_trash_dir(), '', $filename);

		$file_info = pathinfo($filename);
		$relative_path = $file_info['dirname'];

		if ('x' != $image_id) {
			// image is defined in the database and we search all image files with provided image id
			$mask = trailingslashit($this->get_trash_dir() . $relative_path) . $blog_id.'_'.$image_id.'-*';
			$files = glob($mask);

			$success = true;

			// delete attachment in the media library
			wp_delete_attachment($image_id, true);

			// delete files in trash folder
			foreach ($files as $file) {
				$success = $success && unlink($file);
			}
		} else {
			// delete file from the trash folder
			$success = unlink($this->get_trash_dir() . $relative_path . '/' . $file_info['basename']);
		}

		// restore current blog for multisite if need
		if (is_multisite()) {
			restore_current_blog();
		}

		return $success;
	}

	/**
	 * Get image file name before moving to trash, i.e. add blog id and image id to filename prefix
	 *
	 * @param string   $image_filename
	 * @param int|null $image_id
	 * @param int      $blog_id
	 * @return string
	 */
	private function get_trash_filename($image_filename, $image_id = null, $blog_id = 1) {
		return $blog_id.'_'.($image_id ? $image_id : 'x').'-'.$image_filename;
	}

	/**
	 * Move list of files from the source to the destination directory. If operation fails function rolling
	 * back any changes.
	 *
	 * @param array  $files		  array with list of files for move [ [source1, dest1], [source2, dest2], ... ]
	 * @param string $source	  source directory
	 * @param string $destination destination directory
	 *
	 * @return bool|WP_Error
	 */
	private function move_files($files, $source, $destination) {

		$source = trailingslashit($source);
		$destination = trailingslashit($destination);

		$success = true;
		$moved_files = array();

		// move files from source to destination folder
		foreach ($files as $file) {
			$original_name = $file[0];
			$new_name = $file[1];

			$source_file = $source . $original_name;
			$destination_file = $destination . $new_name;

			$success = $this->move_file($source_file, $destination_file);
			if (is_wp_error($success)) break;

			$moved_files[] = array(
				$source_file,
				$destination_file,
			);
		}

		// rolling back changes if error happened
		if (is_wp_error($success) && !empty($moved_files)) {
			foreach ($moved_files as $file) {
				$source_file = $file[1];
				$destination_file = $file[0];

				$this->move_file($source_file, $destination_file);
			}
		}

		return $success;
	}

	/**
	 * Move single file.
	 *
	 * @param string $source	  source file name
	 * @param string $destination destination file name
	 * @return bool|WP_Error
	 */
	private function move_file($source, $destination) {
		$success = true;

		if (!rename($source, $destination)) {
			if (copy($source, $destination)) {
				if (!unlink($source)) {
					unlink($destination);
					$success = new WP_Error('wpo_trash_rename_error', __('Attempt to move file to trash directory failed', 'wp-optimize') . " ($source,$destination)");
				}
			} else {
				$success = new WP_Error('wpo_trash_rename_error', __('Attempt to move file to trash directory failed', 'wp-optimize') . " ($source,$destination)");
			}
		}

		return $success;
	}

	/**
	 * Get list of relative paths to all image files for selected attachment (original and resized).
	 *
	 * @param int $image_id
	 * @return array
	 */
	private function get_all_image_files($image_id) {
		$upload_dir = $this->get_upload_dir();

		// get original image source
		$src = get_attached_file($image_id);

		if (false === $src) return array();

		$path_info = pathinfo($src);

		// Stores the file
		$img_base_name = $path_info['dirname'].'/'.$path_info['filename'];

		// Search all matching images
		$list = glob($img_base_name.'-*.'.(isset($path_info['extension']) ? $path_info['extension'] : ''));

		// `glob()`'s regex capabilities don't allow very complex checks, so we need to check the results.
		// Possible values: image-name-1500x200.ext or image-name-scaled.ext or image-name-updraft-pre-smush-original.ext or image-name-scaled-updraft-pre-smush-original.ext
		$pattern = '/'. preg_quote($img_base_name, '/').'-(\d+x\d+|scaled|updraft-pre-smush-original|scaled-updraft-pre-smush-original)\..*/';

		// Store the values in a new array, as we also need to reset the indexes.
		$final_list = array(str_replace($upload_dir['basedir'], '', $src));

		// Check if the files belong to that attachment, and delete basedir part for found image files
		foreach ($list as $filename) {
			if (preg_match($pattern, $filename)) $final_list[] = str_replace($upload_dir['basedir'], '', $filename);
		}

		return $final_list;
	}

	/**
	 * Save information to cache.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param int $blog_id
	 */
	private function save_to_cache($key, $value, $blog_id) {
		$key = 'wpo_images_trash_'.$key;

		// save cache keys to the options, we use it then for clear cached data.
		$cached_keys = $this->options->get_option('wpo_images_trash_keys_'.$blog_id, array());
		$cached_keys = array_merge($cached_keys, array($key));
		$this->options->update_option('wpo_images_trash_keys_'.$blog_id, $cached_keys);

		WP_Optimize_Transients_Cache::get_instance()->set_transient($key, $value);
	}

	/**
	 * Get data from cache.
	 *
	 * @param string $key
	 * @param int $blog_id
	 * @return mixed
	 */
	private function get_from_cache($key, $blog_id) {
		$key = 'wpo_images_trash_'.$key;

		// check if for some reason (deleted, broken) key isn't in the cached trash keys array
		// then we don't try to get data from cache and return false
		$cached_keys = $this->options->get_option('wpo_images_trash_keys_'.$blog_id, array());
		if (false === array_search($key, $cached_keys)) return false;

		return WP_Optimize_Transients_Cache::get_instance()->get_transient($key);
	}

	/**
	 * Clear cached data about trashed images for the selected blog.
	 *
	 * @param int $blog_id
	 */
	public function clear_cached_data($blog_id) {
		$cached_keys = $this->options->get_option('wpo_images_trash_keys_'.$blog_id, array());

		foreach ($cached_keys as $key) {
			WP_Optimize_Transients_Cache::get_instance()->delete_transient($key);
		}

		$this->options->update_option('wpo_images_trash_keys_'.$blog_id, array());
	}

	/**
	 * Returns true if multisite
	 *
	 * @return bool
	 */
	public function is_multisite_mode() {
		return WP_Optimize()->is_multisite_mode();
	}

	/**
	 * Instance of WP_Optimize_Page_Cache_Preloader.
	 *
	 * @return self
	 */
	public static function instance() {
		if (empty(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}

/**
 * Returns a WP_Optimize_Images_Trash_Manager instance
 */
function WP_Optimize_Images_Trash_Manager() {
	return WP_Optimize_Images_Trash_Manager::instance();
}

endif;

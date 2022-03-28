<?php

if (!defined('WPO_VERSION')) die('No direct access allowed');

if (!class_exists('Updraft_Abstract_Logger')) require_once(WPO_PLUGIN_MAIN_PATH.'/includes/class-updraft-abstract-logger.php');
if (!class_exists('Updraft_PHP_Logger')) require_once(WPO_PLUGIN_MAIN_PATH.'/includes/class-updraft-php-logger.php');
if (!class_exists('WP_Optimization_Images_Shutdown')) require_once(WPO_PLUGIN_MAIN_PATH.'/includes/class-wp-optimization-images-shutdown.php');

/**
 * Class WP_Optimization_images
 */
class WP_Optimization_images extends WP_Optimization {

	const DETECT_IMAGES = 'detect_unused_images';
	const DETECT_SIZES = 'detect_images_sizes';
	const DETECT_BOTH = 'detect_both';

	private static $instance;

	private static $work_mode = self::DETECT_BOTH;

	public $available_for_auto = false;

	public $auto_default = false;

	public $ui_sort_order = 5000;

	protected $support_ajax_get_info = true;

	// regexp for splitting on parts image filename from uploads folder
	protected $image_filename_regexp = '/^(.+)(\-([1-9]\d*x[1-9]\d*|scaled|rotated)?(\@\dx)?)?(\.\w+)$/U';

	private $_attachments_meta_data = array();

	/**
	 * How many posts check per one request.
	 *
	 * @var int
	 */
	private $_posts_per_request = 500;

	/**
	 * Information about sites in multisite mode grouped by blog_id key. Used to show information about sites in frontend.
	 *
	 * @var array
	 */
	private $_sites;

	/**
	 * Images extensions for check.
	 *
	 * @var array
	 */
	private $_images_extensions = array('jpg', 'jpeg', 'jpe', 'png', 'gif', 'bmp', 'tiff', 'svg');

	/**
	 * Used to break process.
	 *
	 * @var boolean
	 */
	private $_done = false;

	private $_logger;

	/**
	 * Optimization constructor.
	 *
	 * @param array $data initial optimization data.
	 */
	public function __construct($data = array()) {
		parent::__construct($data);

		$this->_logger = new Updraft_PHP_Logger();
		$this->_attachments_meta_data = array();

		if ($this->is_multisite_mode()) {
			$_sites = WP_Optimize()->get_sites();

			foreach ($_sites as $site) {
				$this->_sites[$site->blog_id] = $site;
			}
		}
	}

	/**
	 * Get WP_Optimization_images instance.
	 *
	 * @return WP_Optimization_images
	 */
	public static function instance() {
		if (!self::$instance) {
			self::$instance = new WP_Optimization_images();
		}

		return self::$instance;
	}

	/**
	 * Display or hide optimization in optimizations list.
	 *
	 * @return bool
	 */
	public function display_in_optimizations_list() {
		return false;
	}

	/**
	 * Set mode for optimization process. We use work mode to separate getting unused images information
	 * and getting image sizes informtion process.
	 *
	 * There are three possible modes:
	 *  DETECT_IMAGES - detect only unused images
	 *  DETECT_SIZES  - get information
	 *  DETECT_BOTH   - get both unused images and sizes
	 *
	 * @param string $mode one of constants DETECT_IMAGES, DETECT_SIZES, DETECT_BOTH.
	 */
	public function set_work_mode($mode) {
		self::$work_mode = $mode;
	}

	/**
	 * Get current work mode.
	 *
	 * @return string
	 */
	public function get_work_mode() {
		return self::$work_mode;
	}

	/**
	 * Returns WP_Optimize_Tasks_Queue instance.
	 *
	 * @return WP_Optimize_Tasks_Queue
	 */
	private function _tasks_queue() {
		return WP_Optimize_Tasks_Queue::this($this->get_work_mode());
	}

	/**
	 * Do optimization.
	 */
	public function optimize() {
		// All operations we do in after_optimize().
	}

	/**
	 * Get last preload time.
	 *
	 * @param string $key self::DETECT_IMAGES or self::DETECT_SIZES
	 * @return int|bool
	 */
	public function get_last_scan_time($key) {
		$time = $this->options->get_option('unused_images_last_scan_'.$key, false);

		if ($time) {
			$time = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $time + (get_option('gmt_offset') * HOUR_IN_SECONDS));
		}

		return $time;
	}

	/**
	 * Update last preload time.
	 *
	 * @param string        $mode (optional)
	 * @param int|bool|null $time (optional)
	 */
	public function update_last_preload_time($mode = '', $time = null) {
		$mode = '' == $mode ? $this->get_work_mode() : $mode;
		$time = is_null($time) ? time() : $time;

		if (self::DETECT_BOTH == $mode) {
			if (false !== $time) {
				$this->options->update_option('unused_images_last_scan_'.self::DETECT_IMAGES, $time);
				$this->options->update_option('unused_images_last_scan_'.self::DETECT_SIZES, $time);
			} else {
				$this->options->delete_option('unused_images_last_scan_'.self::DETECT_IMAGES);
				$this->options->delete_option('unused_images_last_scan_'.self::DETECT_SIZES);
			}
		} else {
			if (false !== $time) {
				$this->options->update_option('unused_images_last_scan_'.$mode, $time);
			} else {
				$this->options->delete_option('unused_images_last_scan_'.$mode);
			}
		}


	}

	/**
	 * Called after optimize() called for all sites.
	 */
	public function after_optimize() {

		$this->log('after_optimize()');

		// if nothing posted then run default optimization, i.e. remove all unused images.
		if (!isset($this->data['selected_images']) && !isset($this->data['selected_sizes'])) {
			$this->data['selected_images'] = 'all';
			$default_optimization = true;
		} else {
			$default_optimization = false;
		}

		// if selected images posted selected images.
		if (array_key_exists('selected_images', $this->data)) {
			$removed = $this->remove_selected_images($this->data['selected_images']);
			if ($default_optimization) {
				$this->build_get_info_output($this->data, $removed, true);
			} else {
				$this->build_get_info_output($this->data, $removed);
			}
		}

		// if posted from images tab then return information about sizes.
		if (!empty($this->data['selected_sizes'])) {
			$removed = $this->remove_images_sizes(array('remove_sizes' => $this->data['selected_sizes']));
			$this->build_get_info_output(array(), $removed, true);
		}

		// flush cached values.
		WP_Optimize_Transients_Cache::get_instance()->flush();
	}

	/**
	 * Output CSV with list of unused images.
	 */
	public function output_csv() {
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=unused-images-'.date('Y-m-d-H-m-s').'.csv');

		$output = fopen('php://output', 'w');

		fputcsv($output, array('Blog ID', 'Attachment ID', 'Image URL', 'File Size'));

		// output information about unused images into output stream.
		foreach ($this->blogs_ids as $blog_id) {
			$unused_posts_images = $this->get_from_cache('unused_posts_images', $blog_id);
			$unused_images_files = $this->get_from_cache('unused_images_files', $blog_id);

			$base_dir = $this->get_upload_base_dir();
			$base_url = $this->get_upload_base_url();

			if (!empty($unused_posts_images)) {
				foreach ($unused_posts_images as $id) {
					$attachment = $this->wp_get_attachment_metadata($id);

					if (!is_array($attachment)) continue;

					$image_file = $base_dir.'/'.$attachment['file'];
					$image_url = $base_url.'/'.$attachment['file'];

					$sub_dir = '';
					if (preg_match('/[0-9]{4}\/[0-9]{1,2}/', $image_file, $match)) {
						$sub_dir = $match[0];
					}

					if (is_file($image_file)) {
						fputcsv($output, array($blog_id, $id, $image_url, filesize($image_file)));
					}

					if (!empty($attachment['sizes'])) {
						foreach ($attachment['sizes'] as $resized) {
							$image_file = $base_dir.'/'.$sub_dir.'/'.$resized['file'];
							$image_url = $base_url.'/'.$sub_dir.'/'.$attachment['file'];

							if (is_file($image_file)) {
								fputcsv($output, array($blog_id, $id, $image_url, filesize($image_file)));
							}
						}
					}
				}
			}

			if (!empty($unused_images_files)) {
				foreach ($unused_images_files as $url => $size) {
					if ('' != $url) fputcsv($output, array($blog_id, '', $url, $size));
				}
			}
		}

		fclose($output);
		die();
	}

	/**
	 * Encode image url to support filenames in with different characters.
	 *
	 * @param string $url
	 * @return string
	 */
	private function prepare_image_url($url) {
		$url_parts = explode('/', $url);
		if (count($url_parts) > 0) {
			$url_parts[count($url_parts)-1] = rawurlencode($url_parts[count($url_parts)-1]);
		}
		return implode('/', $url_parts);
	}

	/**
	 * Save information about unused images to response meta and generate output message.
	 *
	 * @param array      $params
	 * @param array|null $removed 				 ['files' => count of files, 'size' => total size int value] is passed then messages will for optimization, not for get info.
	 * @param boolean    $output_removed_message Put message about removed images into output.
	 * @return void
	 */
	private function build_get_info_output($params = array(), $removed = null, $output_removed_message = false) {
		$default = array(
			'blog_id' => 0,
			'offset' => 0,
			/**
			 * Filter the number of images per page shown in the unused image list.
			 *
			 * @param $images_per_pages - The number of images per page - Default: 99
			 */
			'length' => apply_filters('wpo_unused_images_per_page', 99),
		);

		$this->log('build_get_info_output()');

		$params = wp_parse_args($params, $default);

		// let know in ajax that info prepared.
		$this->register_meta('finished', true);

		$images_information_cached = $sizes_information_cached = true;
		$total_files = $total_size = 0;

		// save blog ids to meta.
		$this->register_meta('blogs_ids', $this->blogs_ids);

		// if multisite then save additional information about multisite.
		if ($this->is_multisite_mode()) {
			$this->register_meta('multisite', true);
			$this->register_meta('network_adminurl', network_admin_url());
			$this->register_meta('sites', $this->_sites);
		}

		$unused_images = $image_sizes = array();

		$mode = $this->get_work_mode();
		$return_images = self::DETECT_IMAGES == $mode || self::DETECT_BOTH == $mode;
		$return_sizes = self::DETECT_SIZES == $mode || self::DETECT_BOTH == $mode;

		// get summary info for all sites.
		foreach ($this->blogs_ids as $blog_id) {

			// calculate information about unused images when current mode require us to return it.
			if ($return_images) {

				$unused_posts_images = $this->get_from_cache('unused_posts_images', $blog_id);
				$unused_images_files = $this->get_from_cache('unused_images_files', $blog_id);

				if (!is_array($unused_posts_images) || !is_array($unused_images_files)) {
					$images_information_cached = false;
				}

				$unused_images[$blog_id] = array();

				if (!empty($unused_posts_images)) {
					foreach ($unused_posts_images as $id) {
						$unused_images[$blog_id][] = array(
							'id' => $id,
						);
					}
				}

				if (!empty($unused_images_files)) {
					foreach ($unused_images_files as $url => $size) {
						$url_encoded =$this->prepare_image_url($url);

						if ('' == $url_encoded) continue;

						$unused_images[$blog_id][] = array(
							'id' => 0,
							'url' => $url_encoded
						);
					}
				}

				$total_files += count($unused_images[$blog_id]);
			}

			$this->switch_to_blog($blog_id);
			$this->register_meta('adminurl_'.$blog_id, admin_url());
			$this->register_meta('baseurl_'.$blog_id, $this->get_upload_base_url());

			// calculate information about unused images when current mode require us to return it.
			if ($return_sizes) {

				$all_image_sizes = $this->get_from_cache('all_image_sizes', $blog_id);
				$registered_image_sizes = get_intermediate_image_sizes();

				if (!is_array($all_image_sizes)) {
					$sizes_information_cached = false;
				}

				// build info about image sizes.
				if (!empty($all_image_sizes)) {
					foreach ($all_image_sizes as $image_size => $info) {
						if ('original' === $image_size) continue;

						$used = $this->image_size_in_use($image_size, $registered_image_sizes);

						if (array_key_exists($image_size, $image_sizes)) {
							$image_sizes[$image_size]['used'] = $used ? $used : $image_sizes[$image_size]['used'];
							$image_sizes[$image_size]['files'] += $info['files'];
							$image_sizes[$image_size]['size'] += $info['size'];
						} else {
							$image_sizes[$image_size] = array(
								'used' => $used,
								'files' => $info['files'],
								'size' => $info['size']
							);
						}
					}
				}

				// make sure that all registered sizes added.
				if (!empty($registered_image_sizes)) {
					foreach ($registered_image_sizes as $image_size) {
						if (is_array($image_sizes) && array_key_exists($image_size, $image_sizes)) continue;

						$image_sizes[$image_size] = array(
							'used' => true,
							'files' => 0,
							'size' => 0
						);
					}
				}
			}

			$this->restore_current_blog();
		}

		if ($return_images) {
			$this->register_meta('files', $total_files);
			$this->register_meta('size', $total_size);
			$this->register_meta('size_formatted', $this->size_format($total_size));

			$images_loaded_text = array();

			if ($params['blog_id'] > 0) {
				foreach (array_keys($unused_images) as $blog_id) {
					if ($params['blog_id'] != $blog_id) unset($unused_images[$blog_id]);
				}
			}

			foreach (array_keys($unused_images) as $blog_id) {

				$total_images_count = count($unused_images[$blog_id]);
				// get items with requested offset and length.
				$unused_images[$blog_id] = array_slice($unused_images[$blog_id], $params['offset'], $params['length']);

				// get urls for found unused images
				if (!empty($unused_images[$blog_id])) {
					$this->switch_to_blog($blog_id);
					$posts_images_ids = array();

					// get list of images ids for preload attachments info.
					foreach ($unused_images[$blog_id] as $image) {
						if (!array_key_exists('url', $image)) {
							$posts_images_ids[] = $image['id'];
						}
					}

					if (!empty($posts_images_ids)) {
						$this->preload_attachments_metadata($posts_images_ids);

						foreach ($unused_images[$blog_id] as &$image) {
							if (!array_key_exists('url', $image)) {
								$image_metadata = $this->get_attachment_info($image['id'], false);
								$image['url'] = $this->prepare_image_url($image_metadata['url']);
							}
						}
					}

					$this->restore_current_blog();
				}

				// get correct images loaded count.
				$images_loaded = isset($params['images_loaded']) && isset($removed[$blog_id]) ? $params['images_loaded'][$blog_id] - $removed[$blog_id] : $params['offset'] + count($unused_images[$blog_id]);
				// save text to display in admin.
				$images_loaded_text[$blog_id] = sprintf(__('%s of %s images loaded', 'wp-optimize'), $images_loaded, $total_images_count);
			}

			$this->register_meta('unused_images', $unused_images);
			$this->register_meta('images_loaded_text', $images_loaded_text);
		}

		if ($return_sizes) {
			if (!empty($image_sizes)) {
				foreach ($image_sizes as $image_size => $info) {
					$image_sizes[$image_size]['size_formatted'] = $this->size_format($info['size']);
				}
			}

			$this->register_meta('image_sizes', $image_sizes);
		}

		// get last preload time
		$images_last_scan_time = $this->get_last_scan_time(self::DETECT_IMAGES);
		$sizes_last_scan_time = $this->get_last_scan_time(self::DETECT_SIZES);

		// if information about unused images already in cache and last scan time value is empty for some reason
		// then update last preload time value
		if (!$images_last_scan_time && $images_information_cached) {
			$this->update_last_preload_time(self::DETECT_IMAGES);
			$images_last_scan_time = $this->get_last_scan_time(self::DETECT_IMAGES);
		}

		// if we have saved last scan time but we have no information for output
		// then reset last scan time value
		if ($images_last_scan_time && !$images_information_cached) {
			$this->update_last_preload_time(self::DETECT_IMAGES, false);
			$images_last_scan_time = false;
		}

		// if information about unused image sizes already in cache and last scan time value is empty for some reason
		// then update last preload time value
		if (!$sizes_last_scan_time && $sizes_information_cached) {
			$this->update_last_preload_time(self::DETECT_SIZES);
			$sizes_last_scan_time = $this->get_last_scan_time(self::DETECT_SIZES);
		}

		// if we have saved last scan time but we have no information for output
		// then reset last scan time value
		if ($sizes_last_scan_time && !$sizes_information_cached) {
			$this->update_last_preload_time(self::DETECT_SIZES, false);
			$sizes_last_scan_time = false;
		}

		// return last scan times
		$this->register_meta('last_scan_'.self::DETECT_IMAGES, $images_last_scan_time);
		$this->register_meta('last_scan_'.self::DETECT_SIZES, $sizes_last_scan_time);

		// if message for optimization.
		if (null !== $removed) {
			$total_files = $removed['files'];
			$total_size = $removed['size'];

			$message = sprintf(_n('%s unused image removed with a total size of ', '%s unused images removed with a total size of ', $total_files, 'wp-optimize') . $this->size_format($total_size), number_format_i18n($total_files), 'wp-optimize');
			$this->register_meta('removed_message', $message);
		}

		if ($total_files > 0) {
			if (null !== $removed && $output_removed_message) {
				$message = sprintf(_n('%s unused image removed with a total size of ', '%s unused images removed with a total size of ', $total_files, 'wp-optimize') . $this->size_format($total_size), number_format_i18n($total_files), 'wp-optimize');
			} else {
				$message = sprintf(_n('%s unused image found with a total size of ', '%s unused images found with a total size of ', $total_files, 'wp-optimize') . $this->size_format($total_size), number_format_i18n($total_files), 'wp-optimize');
			}
		} else {
			$message = __('No unused images found', 'wp-optimize');
		}

		if ($this->is_multisite_mode()) {
			$message .= ' '.sprintf(_n('across %s site', 'across %s sites', count($this->blogs_ids), 'wp-optimize'), count($this->blogs_ids));
		}

		$this->register_output($message);
	}

	/**
	 * Check if requested information alreayd prepared and stored in the cache.
	 *
	 * @return bool
	 */
	private function is_requested_information_cached() {

		$work_mode = $this->get_work_mode();

		foreach ($this->blogs_ids as $blog_id) {

			if (self::DETECT_IMAGES == $work_mode || self::DETECT_BOTH == $work_mode) {
				// check if posts images already in the cache.
				$unused_posts_images = $this->get_from_cache('unused_posts_images', $blog_id);
				if (!is_array($unused_posts_images)) return false;

				// check if upload directory images already in the cache.
				$unused_images_files = $this->get_from_cache('unused_images_files', $blog_id);
				if (!is_array($unused_images_files)) return false;
			}

			if (self::DETECT_SIZES == $work_mode || self::DETECT_BOTH == $work_mode) {
				// check if information about images sizes already in the cache.
				$all_image_sizes = $this->get_from_cache('all_image_sizes', $blog_id);
				if (!is_array($all_image_sizes)) return false;
			}
		}

		return true;
	}

	/**
	 * Returns true if image size used.
	 *
	 * @param string $image_size
	 * @param array  $registered_image_sizes
	 * @return bool
	 */
	public function image_size_in_use($image_size, $registered_image_sizes = array()) {

		$registered_image_sizes = empty($registered_image_sizes) ? get_intermediate_image_sizes() : $registered_image_sizes;

		if (in_array($image_size, $registered_image_sizes)) return true;

		// MetaSlider doesn't register sizes correctly and just add tem to meta.
		if (class_exists('MetaSliderPlugin') && false !== strpos($image_size, 'meta-slider-resized')) return true;

		return false;
	}

	/**
	 * Do actions before get_info called.
	 */
	public function before_get_info() {

		$this->log('before_get_info()');

		// if mode posted then set selected mode.
		if (isset($this->data['mode']) && in_array($this->data['mode'], array(self::DETECT_IMAGES, self::DETECT_SIZES, self::DETECT_BOTH))) {
			$this->set_work_mode($this->data['mode']);
		}

		// return current mode in response
		$this->register_meta('mode', $this->get_work_mode());

		// if sent quickinfo parameter just return it.
		if (!empty($this->data['quickinfo'])) {
			$this->build_get_info_output($this->data);
			$this->_done = true;
			return;
		} elseif (!isset($this->data['forced']) && $this->is_requested_information_cached()) {
			$this->build_get_info_output($this->data);
			$this->_done = true;
		}

		$this->_tasks_queue()->lock();

		// Clear task queue when 'cancel' parameter sent.
		if (isset($this->data['cancel'])) {
			$this->_done = true;
			$this->_tasks_queue()->delete_queue();
			WP_Optimization_Images_Shutdown::get_instance()->reset_values();
			return;
		}

		// if forced option posted then clear cached data.
		if ($this->_tasks_queue()->is_locked() && (!empty($this->data['forced']) || $this->is_debug_mode())) {
			$this->clear_cached_data();
			$this->_tasks_queue()->delete_queue();
			WP_Optimization_Images_Shutdown::get_instance()->reset_values();
		}

		// store in meta support_ajax_get_info flag if posted.
		if (isset($this->data['support_ajax_get_info'])) {
			$this->register_meta('support_ajax_get_info', true);
		}

		// if task queue is empty then set queue meta to create new queue.
		if (0 == $this->_tasks_queue()->length()) {
			$this->_tasks_queue()->set_meta('new_queue', true);
		}
	}

	/**
	 * Do get_info actions for each site.
	 */
	public function get_info() {
		// if output already prepared.
		if ($this->_done) return;

		$this->log('get_info()');

		$blog_id = get_current_blog_id();

		// if queue is not started then add task to get info about current site.
		if ($this->_tasks_queue()->get_meta('new_queue')) {
			$this->_tasks_queue()->add_task(new WP_Optimize_Queue_Task(array(get_class($this), 'task_get_info'), array($blog_id), '', $this->calc_priority($blog_id, 1)));
		}
	}

	/**
	 * Do actions after all get_info completed.
	 */
	public function after_get_info() {
		// if output already prepared.
		if ($this->_done) return;

		$this->log('after_get_info()');

		// Activate shutdown action for handle fatal errors.
		WP_Optimization_Images_Shutdown::get_instance()->activate();
		WP_Optimization_Images_Shutdown::get_instance()->set_meta($this->get_meta());
		// Save queue id for unlock if fatal error happens.
		WP_Optimization_Images_Shutdown::get_instance()->set_value('queue_id', $this->get_work_mode());

		// Remove new queue meta flag.
		$this->_tasks_queue()->set_meta('new_queue', false);

		if (isset($this->data['support_ajax_get_info']) && !$this->is_debug_mode()) {
			// if sent support_ajax_get_info parameter then walk step-by-step and return messages.
			$time_start = microtime(true);
			$time_limit = defined('WPO_IMAGES_REQUEST_TIME_LIMIT') ? WPO_IMAGES_REQUEST_TIME_LIMIT : 2; // limit time seconds.

			// do as many tasks as we can per request.
			while (!$this->_tasks_queue()->is_empty() && ($time_limit > (microtime(true) - $time_start))) {
				$this->_tasks_queue()->do_next_task();
			}

			// if all tasks done then build results.
			if ($this->_tasks_queue()->is_empty()) {
				// wait until queue is free before generate output.
				$this->_tasks_queue()->wait();

				$this->update_last_preload_time();
				$this->build_get_info_output($this->data);
			} else {
				$message = $this->_tasks_queue()->get_meta('message');
				$message = ('' == $message) ? '...' : $message;
				$this->register_output($message);
			}
		} else {
			// if called without ajax info then do all tasks and return results.
			while (!$this->_tasks_queue()->is_empty()) {
				$this->_tasks_queue()->do_next_task();
			}

			// wait until queue is free before generate output.
			$this->_tasks_queue()->wait();

			$this->update_last_preload_time();
			$this->build_get_info_output($this->data);
		}

		// deactivate shutdown action for handle fatal errors.
		WP_Optimization_Images_Shutdown::get_instance()->deactivate();
		// flush cached values.
		WP_Optimize_Transients_Cache::get_instance()->flush();
		// flush queue.
		$this->_tasks_queue()->flush();
		// unlock queue.
		$this->_tasks_queue()->unlock();
	}

	/**
	 * Save message to tasks queue meta, used in build_get_info_output.
	 *
	 * @param string $message text message.
	 * @param int 	 $blog_id blog id.
	 */
	public function message($message, $blog_id) {
		if ($this->is_multisite_mode()) {
			$message = $message .' ['.$this->_sites[$blog_id]->domain.$this->_sites[$blog_id]->path.']';
		}

		$this->_tasks_queue()->set_meta('message', $message);
	}

	/**
	 * Main task for get info, checks cached values and add needed tasks to queue.
	 *
	 * @param int $blog_id
	 */
	public function task_get_info($blog_id) {

		$this->log('task_get_info()');

		$this->message(__('Getting information...', 'wp-optimize'), $blog_id);

		$mode = $this->get_work_mode();

		if (self::DETECT_BOTH == $mode || self::DETECT_IMAGES == $mode) {
			// check if posts images already in the cache.
			$unused_posts_images = $this->get_from_cache('unused_posts_images', $blog_id);
			if (!is_array($unused_posts_images)) {
				$this->_tasks_queue()->add_task(new WP_Optimize_Queue_Task(array(get_class($this), 'task_get_posts_images'), array(0, $this->_posts_per_request, $blog_id), array(get_class($this), 'process_get_posts_images_result'), $this->calc_priority($blog_id, 5)));
			}

			// check if upload directory images already in the cache.
			$unused_images_files = $this->get_from_cache('unused_images_files', $blog_id);
			if (!is_array($unused_images_files)) {
				$this->_tasks_queue()->add_task(new WP_Optimize_Queue_Task(array(get_class($this), 'task_get_unused_images_files'), array($blog_id), '', $this->calc_priority($blog_id, 10)));
			}
		}

		if (self::DETECT_BOTH == $mode || self::DETECT_SIZES == $mode) {
			// check if information about images sizes already in the cache.
			$all_image_sizes = $this->get_from_cache('all_image_sizes', $blog_id);
			if (!is_array($all_image_sizes)) {
				$this->_tasks_queue()->add_task(new WP_Optimize_Queue_Task(array(get_class($this), 'task_get_all_image_sizes'), array(0, 1000, $blog_id), array(get_class($this), 'process_get_all_image_sizes_results'), $this->calc_priority($blog_id, 15)));
			}
		}
	}

	/**
	 * Returns list of attachment ids used in posts.
	 *
	 * @param int $offset
	 * @param int $limit
	 * @param int $blog_id
	 * @return array ['processed' => how many posts processed, 'images_ids' => list of attachment ids used in posts, ...]
	 */
	public function task_get_posts_images($offset = 0, $limit = 500, $blog_id = 1) {
		global $wpdb;

		$this->log('task_get_posts_images()');

		$this->switch_to_blog($blog_id);

		// gets posts ids with post_content
		$posts = $wpdb->get_results($wpdb->prepare("SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type NOT IN ('revision', 'attachment', 'inherit') AND post_status IN ('publish', 'draft') ORDER BY ID LIMIT %d, %d", $offset, $limit));

		// use different functions to get images info from the posts.
		$images_ids = $this->get_posts_content_images($posts, $blog_id);
		$images_ids = array_merge($images_ids, $this->get_posts_wc_galleries_and_thumbnails($posts));

		$this->restore_current_blog();

		return array(
			'blog_id' => $blog_id,
			'offset' => $offset,
			'limit' => $limit,
			'processed' => count($posts),
			'images_ids' => array_unique($images_ids)
		);
	}

	/**
	 * Returns posts images placed in post content.
	 *
	 * @param array $posts
	 * @param int   $blog_id
	 * @return array
	 */
	private function get_posts_content_images(&$posts, $blog_id) {

		if (empty($posts)) return array();

		$this->log('get_posts_content_images()');

		// save blog id into shutdown handler.
		WP_Optimization_Images_Shutdown::get_instance()->set_value('blog_id', $blog_id);

		$this->init_visual_composer();

		$found_images = array();

		// prevent unwanted output by do_shortcode()
		ob_start();

		foreach ($posts as $post) {
			// save post id into shutdown handler.
			WP_Optimization_Images_Shutdown::get_instance()->set_value('last_post_id', $post->ID);

			// if post in "bad posts" list then we don't use do_shortcode.
			if (WP_Optimization_Images_Shutdown::get_instance()->is_bad_post($blog_id, $post->ID)) {
				$post_content = $post->post_content;
			} else {
				$post_content = do_shortcode($post->post_content);
			}

			// delete post id from shutdown handler.
			WP_Optimization_Images_Shutdown::get_instance()->delete_value('last_post_id');
			// get all images in the post
			$images = $this->parse_images_in_content($post_content);

			if (!empty($images)) {
				foreach ($images as $image) {
					$original_image = $this->get_original_image_file_name($image);
					// before 5.4 wp_unique_filename() function doesn't add `-number` suffix for the image filename that possible was resized by WordPress (i.e. with siffix -nxn)
					// this cause the issue with detecting used/unused images thatswhy we add information about filename found in the post and later check both filenames in get_image_attachment_id_bulk().
					$fname  = pathinfo($image, PATHINFO_FILENAME);
					if ($fname && preg_match('/\-([1-9]\d*x[1-9]\d*)$/', $fname)) {
						$found_images[$original_image.':/:'.$image] = 1;
					} else {
						$found_images[$original_image] = 1;
					}
				}
			}
		}

		ob_end_clean();

		if (!empty($found_images)) {
			// get images attachment ids.
			return array_values($this->get_image_attachment_id_bulk(array_keys($found_images)));
		} else {
			return $found_images;
		}
	}

	/**
	 * Call VC function that add shortcodes.
	 */
	public function init_visual_composer() {
		global $shortcode_tags;

		$this->log('init_visual_composer()');

		// if already have VC shortcodes exit.
		if (array_key_exists('vc_row', $shortcode_tags)) return;

		$vc_shortcodes = array(
			'WPBMap',
			'addAllMappedShortcodes',
		);

		if (is_callable($vc_shortcodes)) {
			call_user_func($vc_shortcodes);
		}
	}

	/**
	 * Returns posts images placed in Woo Commerce and post featured images.
	 *
	 * @param array $posts
	 * @return array
	 */
	private function get_posts_wc_galleries_and_thumbnails(&$posts) {
		global $wpdb;

		if (empty($posts) || !class_exists('WooCommerce')) return array();

		$this->log('get_posts_wc_galleries_and_thumbnails()');

		$images_ids = array();

		// Get featured images and Woo Commerce galleries.
		$post_ids = wp_list_pluck($posts, 'ID');
		$posts_meta = $wpdb->get_col("SELECT meta_value FROM {$wpdb->postmeta} WHERE (meta_key = '_thumbnail_id' OR meta_key = '_product_image_gallery') AND (meta_value != '') AND post_id IN ('".join("','", $post_ids)."')");

		if (!empty($posts_meta)) {
			foreach ($posts_meta as $ids) {
				$ids = explode(',', $ids);
				foreach ($ids as $image_id) {
					$images_ids[$image_id] = 1;
				}
			}
		}

		return array_keys($images_ids);
	}

	/**
	 * Return images found in options.
	 *
	 * @return array
	 */
	private function get_images_from_options() {
		global $wpdb;

		$this->log('get_images_from_options()');
		$reg = preg_quote($this->get_upload_base_url(), '/').'\/([^\\\'\"]+\.('.join('|', $this->_images_extensions).'))';
		$option_values = $wpdb->get_col($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name NOT REGEXP %s AND option_value REGEXP %s", '^_', $reg));
		$found_images = array();

		foreach ($option_values as $option_value) {
			// get all images in the post
			$images = $this->parse_images_in_content($option_value);

			if (!empty($images)) {
				foreach ($images as $image) {
					$image = $this->get_original_image_file_name($image);
					$found_images[$image] = 1;
				}
			}
		}

		if (!empty($found_images)) {
			// get images attachment ids.
			return array_values($this->get_image_attachment_id_bulk(array_keys($found_images)));
		} else {
			return $found_images;
		}
	}

	/**
	 * Get list of images moved into WP-Optimize trash
	 *
	 * @return array
	 */
	private function get_images_in_trash() {
		global $wpdb;

		$this->log('get_images_in_trash()');

		$result = $wpdb->get_col("SELECT DISTINCT(pm.post_id) FROM {$wpdb->postmeta} pm WHERE pm.meta_key = '_old_post_status'");

		return $result;
	}

	/**
	 * Get list of attachment ids used as featured images in posts.
	 *
	 * @return array
	 */
	private function get_featured_images() {
		global $wpdb;

		$this->log('get_featured_images()');

		$result = $wpdb->get_col("SELECT DISTINCT(pm.meta_value) FROM {$wpdb->postmeta} pm WHERE pm.meta_key = '_thumbnail_id'");

		return $result;
	}

	/**
	 * Get list of attachment ids used by MetaSlider plugin.
	 *
	 * @return array
	 */
	private function get_metaslider_images() {
		global $wpdb;

		$this->log('get_metaslider_images()');

		$suppress = $wpdb->suppress_errors(true);

		$result = $wpdb->get_col("SELECT pm.meta_value FROM {$wpdb->posts} p JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key='_thumbnail_id' WHERE p.post_type IN ('ml-slide') AND p.post_status IN ('publish', 'inherit')");

		$wpdb->suppress_errors($suppress);

		return $result;
	}

	/**
	 * Scan post meta values for Oxygen builder images.
	 *
	 * @return array
	 */
	private function get_oxygen_images() {
		global $wpdb;

		$this->log('get_oxygen_images()');

		$found_images = array();

		$offset = 0;
		$limit = 500;

		do {
			$posts = $wpdb->get_results("SELECT meta_value FROM {$wpdb->postmeta} WHERE `meta_key` = 'ct_builder_shortcodes' OR `meta_key` = 'ct_builder_shortcodes_revisions' LIMIT {$offset}, {$limit};");

			foreach ($posts as $post) {
				$images = $this->parse_images_in_content($post->meta_value);

				if (!empty($images)) {
					foreach ($images as $image) {
						$image = $this->get_original_image_file_name($image);
						$found_images[$image] = 1;
					}
				}
			}

			$offset += $limit;

		} while (count($posts) == $limit);

		if (!empty($found_images)) {
			// get images attachment ids.
			return array_values($this->get_image_attachment_id_bulk(array_keys($found_images)));
		} else {
			return $found_images;
		}
	}

	/**
	 * Scan post meta values for Oxygen builder images.
	 *
	 * @return array
	 */
	private function get_revslider_slides() {
		global $wpdb;

		$this->log('get_revslider_slides()');

		$found_images = array();

		$offset = 0;
		$limit = 500;

		do {
			$records = $wpdb->get_results("SELECT params, layers FROM {$wpdb->prefix}revslider_slides LIMIT {$offset}, {$limit};");

			foreach ($records as $item) {
				// The slide's background image is stored in 'params'
				$params = json_decode($item->params);
				if ('image' === $params->bg->type) {
					if (property_exists($params->bg, 'imageId')) {
						// If the id is stored, use it
						$found_images[] = $params->bg->imageId;
					} elseif (property_exists($params->bg, 'image')) {
						// Otherwise, find it using the image URL
						$base_upload_url = $this->get_upload_base_url();
						$image_record_value = str_replace($base_upload_url.'/', '', $params->bg->image);
						$image_id = $this->get_image_attachment_id($image_record_value);
						if ($image_id) $found_images[] = $image_id;
					}
				}

				// Get the layers
				$layers = json_decode($item->layers, true);
				if (is_array($layers)) {
					foreach ($layers as $layer) {
						if (isset($layer['media']) && isset($layer['media']['imageId'])) {
							$found_images[] = $layer['media']['imageId'];
						}
					}
				}
			}

			$offset += $limit;

		} while (count($records) == $limit);

		return $found_images;
	}

	/**
	 * Get list of attachment ids used in post meta, including Advanced Custom Fields image fields.
	 * Use this when the post meta is known to only store one ID value
	 *
	 * @return array
	 */
	private function get_single_image_ids_in_post_meta() {
		global $wpdb;

		$this->log('get_single_image_ids_in_post_meta()');

		$post_meta_names = $this->get_acf_field_names();

		/**
		 * Filter wpo_find_used_images_in_post_meta - List of post meta fields containing images
		 *
		 * @param array $post_meta_names The array of field names
		 */
		$post_meta_names = apply_filters('wpo_find_used_images_in_post_meta', $post_meta_names);

		if (empty($post_meta_names)) return array();

		// Select meta values where the Key is in $fields_name, and not empty.
		$posts_meta_values = $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ('".join("','", $post_meta_names)."') AND (meta_value != '')");

		return $posts_meta_values;
	}

	/**
	 * Get list of attachment ids used in post meta, including Advanced Custom Fields Gallery fields.
	 * Use this when the post meta is known to only store an array of IDs
	 *
	 * @return array
	 */
	private function get_multiple_image_ids_in_post_meta() {
		global $wpdb;

		$post_meta_names = apply_filters('wpo_get_multiple_image_ids_in_post_meta', array_merge(
			$this->get_acf_field_names('gallery'), // ACF
			array('_eg_in_gallery') // Envira Gallery
		));

		if (empty($post_meta_names)) return array();

		// Select meta values where the Key is in $fields_name, and not empty.
		$posts_meta_values = $wpdb->get_col("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ('".join("','", $post_meta_names)."') AND (meta_value != '')");

		$found_images_ids = array();

		foreach ($posts_meta_values as $value) {
			$values = maybe_unserialize($value);
			if (is_array($values)) {
				$found_images_ids = array_merge($found_images_ids, $values);
			}
		}

		return $found_images_ids;
	}

	/**
	 * Get the acf meta field names
	 *
	 * @param string $field_type
	 * @return array
	 */
	private function get_acf_field_names($field_type = 'image') {
		if (!function_exists('acf_get_raw_fields')) return array();
		$this->acf_field_type = $field_type;
		static $acf_image_fields = array();
		// Get all ACF fields
		if (empty($acf_image_fields)) $acf_image_fields = acf_get_raw_fields($field_type);
		if (!is_array($acf_image_fields)) return array();
		// Pluck the meta names and types
		return array_keys(array_filter(wp_list_pluck($acf_image_fields, 'type', 'name'), array($this, 'filter_acf_fields_per_type')));
	}

	/**
	 * Filters the ACFields array
	 * Called by in get_acf_field_names byarray_filter
	 *
	 * @param string $type
	 * @return boolean
	 */
	public function filter_acf_fields_per_type($type) {
		return $type == $this->acf_field_type;
	}

	/**
	 * Get source html by $url and parse content for images.
	 * Returns array with found images.
	 *
	 * @param  string $url
	 * @return array|bool
	 */
	public function get_images_from_url($url, $timeout = 5) {
		$response = wp_safe_remote_get($url, array('timeout' => $timeout, 'stream' => false));

		if (is_array($response)) {
			return $this->parse_images_in_content($response['body']);
		}

		return false;
	}

	/**
	 * Get images from homepage and returns list of attachment ids for them.
	 *
	 * @param int  $blog_id
	 * @param bool $reload  if true then don't use cached values.
	 * @return array
	 */
	public function get_homepage_images($blog_id, $reload = false) {

		$this->log('get_homepage_images({blog_id})', array('blog_id' => $blog_id));

		$this->switch_to_blog($blog_id);

		// try to get information about images from cache.
		if (false === $reload) {
			$cached = $this->get_from_cache('homepage_images', $blog_id);
			if (is_array($cached)) return $cached;
		}

		// try to load images from url.
		$images = $this->get_images_from_url(site_url('/'));

		$found_images = array();

		if (!empty($images)) {
			foreach ($images as $image) {
				$image = $this->get_original_image_file_name($image);
				$found_images[$image] = 1;
			}
		}

		if (!empty($found_images)) {
			// get images attachment ids.
			$found_images = array_values($this->get_image_attachment_id_bulk(array_keys($found_images)));
		}

		// if images loaded successfully then save information to cache.
		if (is_array($images)) {
			$this->save_to_cache('homepage_images', $found_images, $blog_id);
		}

		$this->restore_current_blog();

		return $found_images;
	}

	/**
	 * Process get posts images task result.
	 *
	 * @param array $result
	 */
	public function process_get_posts_images_result($result) {
		$blog_id = $result['blog_id'];

		$this->log('process_get_posts_images_result({blog_id})', array('blog_id' => $blog_id));

		$found_images_ids = $this->get_from_cache('unused_posts_images_part', $blog_id);
		if (!is_array($found_images_ids)) $found_images_ids = array();

		// if some unused images found then merge with current result.
		if (!empty($result['images_ids'])) {
			$found_images_ids = array_merge($found_images_ids, $result['images_ids']);
		}

		// if all posts processed then save results and go to the next step.
		if ($result['processed'] < $result['limit']) {
			$this->switch_to_blog($blog_id);

			// get images from trash.
			$found_images_ids = array_merge($found_images_ids, $this->get_images_in_trash());
			// get images from options table.
			$found_images_ids = array_merge($found_images_ids, $this->get_images_from_options());
			// get featured images.
			$found_images_ids = array_merge($found_images_ids, $this->get_featured_images());
			// get MetaSlider images.
			$found_images_ids = array_merge($found_images_ids, $this->get_metaslider_images());
			// get Oxygen images.
			if (defined('CT_VERSION')) {
				$found_images_ids = array_merge($found_images_ids, $this->get_oxygen_images());
			}
			// Slider revolution images
			if (class_exists('RevSliderFront')) $found_images_ids = array_merge($found_images_ids, $this->get_revslider_slides());
			// add homepage images ids.
			$found_images_ids = array_merge($found_images_ids, $this->get_homepage_images($blog_id));
			// Get images from postmeta fields (unique INT values) e.g. ACF images
			$found_images_ids = array_merge($found_images_ids, $this->get_single_image_ids_in_post_meta());
			// Get images from postmeta fields (serialized values) e.g. ACF galleries
			$found_images_ids = array_merge($found_images_ids, $this->get_multiple_image_ids_in_post_meta());

			$all_image_ids = $this->get_image_attachments_post_ids();
			$unused_images_ids = array_diff($all_image_ids, $found_images_ids);

			// delete partially cached data.
			$this->delete_from_cache('unused_posts_images_part', $blog_id);

			// save unused attachment ids.
			$unused_images_ids = apply_filters('wpo_unused_images_ids', $unused_images_ids, $blog_id);

			$this->save_to_cache('unused_posts_images', $unused_images_ids, $blog_id);

			$this->message(__('Posts checked.', 'wp-optimize'), $blog_id);

			$this->restore_current_blog();
		} else {
			// partially processed posts.
			$this->save_to_cache('unused_posts_images_part', $found_images_ids, $blog_id);

			$new_offset = $result['offset'] + $result['processed'];
			$this->_tasks_queue()->add_task(new WP_Optimize_Queue_Task(array(get_class($this), 'task_get_posts_images'), array($new_offset, $result['limit'], $blog_id), array(get_class($this), 'process_get_posts_images_result'), $this->calc_priority($blog_id, 5)));

			$this->message(sprintf(_n('%s post processed...', '%s posts processed...', $new_offset, 'wp-optimize'), $new_offset), $blog_id);
		}
	}

	/**
	 * Add needed tasks for checking upload directory to queue.
	 *
	 * @param int $blog_id
	 */
	public function task_get_unused_images_files($blog_id = 1) {

		$this->log('task_get_unused_images_files({blog_id})', array('blog_id' => $blog_id));

		$this->message(__('Checking upload directory...', 'wp-optimize'), $blog_id);

		$sub_dirs = apply_filters('wpo_unused_images_sub_dirs', $this->get_upload_sub_dirs($blog_id), $blog_id, $this);

		if (!empty($sub_dirs)) {
			foreach ($sub_dirs as $sub_dir) {
				$this->_tasks_queue()->add_task(new WP_Optimize_Queue_Task(array(get_class($this), 'get_orphaned_images_in_sub_directory'), array($sub_dir, $blog_id), array(get_class($this), 'process_get_orphaned_images_in_sub_directory'), $this->calc_priority($blog_id, 11)));
			}
		}

		$this->_tasks_queue()->add_task(new WP_Optimize_Queue_Task(array(get_class($this), 'process_get_unused_images_files_result'), array($blog_id), '', $this->calc_priority($blog_id, 12)));
	}

	/**
	 * Called after upload directory scanned.
	 *
	 * @param int $blog_id
	 */
	public function process_get_unused_images_files_result($blog_id) {

		$this->log('process_get_unused_images_files_result({blog_id})', array('blog_id' => $blog_id));

		$this->message(__('Process results...', 'wp-optimize'), $blog_id);

		$unused_images_files = $this->get_from_cache('unused_images_files_part', $blog_id);

		if (empty($unused_images_files)) $unused_images_files = array();

		$this->save_to_cache('unused_images_files', $unused_images_files, $blog_id);

		$this->delete_from_cache('unused_images_files_part', $blog_id);
	}

	/**
	 * Scans a sub directory and returns image files which are not associated to any media record.
	 *
	 * @param string $sub_directory upload sub directory.
	 * @param int    $blog_id
	 * @return array
	 */
	public function get_orphaned_images_in_sub_directory($sub_directory, $blog_id = 1) {
		$unused_images = $_image_files = array();

		$this->log('get_orphaned_images_in_sub_directory({sub_directory}, {blog_id})', array('sub_directory' => $sub_directory, 'blog_id' => $blog_id));

		// minimal set of images to check.
		$min_images_per_check = 100;

		// how much memory keep free to avoid exceeding memory limit.
		$keep_free_memory = 8 * 1024 * 1024;

		// currently free memory variable.
		$free_memory = WP_Optimize()->get_free_memory();
		// how often refresh free memory variable.
		$refresh_free_memory_freq = 1000;
		$refresh_free_memory_counter = 0;

		// max DB packet size.
		$max_packet_size = WP_Optimize()->get_max_packet_size();

		// counter for SQL query length.
		$current_query_length = 0;
		// static content in query.
		$static_query_length = 2048;

		$this->message(__('Checking upload directory...', 'wp-optimize').' ['.$sub_directory.']', $blog_id);

		$this->switch_to_blog($blog_id);

		$base_upload_dir = $this->get_upload_base_dir();

		if ($handle = opendir($base_upload_dir.'/'.$sub_directory)) {

			$file = readdir($handle);

			while (false !== $file) {

				// check if this is an image file.
				if ('.' == $file || '..' == $file || is_dir($base_upload_dir.'/'.$sub_directory.'/'.$file) || !$this->is_image_file($file)) {
					$file = readdir($handle);
					continue;
				}

				$image_file_name = $sub_directory.'/'.$file;

				// get original filename for image.
				$original_file_name = $this->get_original_image_file_name($image_file_name);

				// if this is smush backup then delete smush suffix.
				if (preg_match('/^(.+)\-updraft\-pre\-smush\-original(\.\w+)$/', $image_file_name, $parts)) {
					$original_file_name = $parts[1] . $parts[2];
				}

				// add to list.
				if (array_key_exists($original_file_name, $_image_files)) {
					$_image_files[$original_file_name][] = $image_file_name;
				} else {
					$_image_files[$original_file_name] = array($image_file_name);
					$current_query_length += strlen($image_file_name) + 3; // filename length + quotes and comma.
				}

				// read next filename.
				$file = readdir($handle);

				// if last file or get max packet size for db or we have low memory and picked at least minimal amount for check.
				if (false === $file || (($current_query_length + $static_query_length) >= $max_packet_size) || ($free_memory < $keep_free_memory && count($_image_files[$original_file_name]) > $min_images_per_check)) {
					
					// replace array keys from original_file_name to original_file_name:/:source_file_name when it has just one relation
					// in this case original image possible has -nxn suffix and we need check both names in the database
					foreach ($_image_files as $original_file_name => $files) {
						if (1 != count($files) || false !== strpos($original_file_name, ':/:')) continue;

						$fname  = pathinfo($files[0], PATHINFO_FILENAME);
						if ($fname && preg_match('/\-([1-9]\d*x[1-9]\d*)$/', $fname)) {
							$new_key = $original_file_name.':/:'.$files[0];
							$_image_files[$new_key] = $files;
							unset($_image_files[$original_file_name]);
						}
					}

					// get attachment ids for image files.
					$found_images = $this->get_image_attachment_id_bulk(array_keys($_image_files), true);

					// walk through found image files and check if there relation in database found.
					foreach ($_image_files as $key => $files) {
						// if $key consist of multiple filenames then split it
						if (false !== strpos($key, ':/:')) {
							$files_to_check = explode(':/:', $key);
						} else {
							$files_to_check = array($key);
						}

						$found = false;
						$image_filename = '';
						foreach ($files_to_check as $filename) {
							if (array_key_exists($filename, $found_images) && false !== $found_images[$filename]) {
								// if filename exists in the database then we mark it as found.
								$found = true;
							} elseif (is_file($base_upload_dir.'/'.$filename)) {
								// if filename doesn't exist in the database and file exists
								// then we store it. possible current image is unused.
								$image_filename = $filename;
								// add filename to related files list for possible push it to unused images list
								$files[] = $filename;
							}
						}

						// if image file not found and file exists then we store it as unused.
						if (!$found && '' != $image_filename) {
							// as we added root filename(s) to $files we need avoid duplicates.
							$files = array_unique($files);

							// add files to unused images list.
							foreach ($files as $filename) {
								if (is_file($base_upload_dir.'/'.$filename)) {
									$unused_images[htmlentities($filename)] = filesize($base_upload_dir.'/'.$filename);
								}
							}
						}
					}

					unset($_image_files);
					$current_query_length = 0;
					$_image_files = array();
				}

				$refresh_free_memory_counter++;
				if ($refresh_free_memory_counter >= $refresh_free_memory_freq) {
					$refresh_free_memory_counter = 0;
					$free_memory = WP_Optimize()->get_free_memory();
				}
			}

			closedir($handle);
		}

		$this->restore_current_blog();

		return array(
			'blog_id' => $blog_id,
			'base_upload_dir' => $base_upload_dir,
			'sub_dir' => $sub_directory,
			'unused_images' => $unused_images
		);
	}

	/**
	 * Called after upload subdirectory checked.
	 *
	 * @param array $result
	 */
	public function process_get_orphaned_images_in_sub_directory($result) {
		$blog_id = $result['blog_id'];

		$this->log('process_get_orphaned_images_in_sub_directory({blog_id})', array('blog_id' => $blog_id));

		$unused_images = $this->get_from_cache('unused_images_files_part', $blog_id);

		if (empty($unused_images)) {
			$unused_images = $result['unused_images'];
		} else {
			$unused_images = array_merge($unused_images, $result['unused_images']);
		}

		$this->save_to_cache('unused_images_files_part', $unused_images, $blog_id);

	}

	/**
	 * Get images sizes information.
	 *
	 * @param int $offset
	 * @param int $limit
	 * @param int $blog_id
	 * @return array
	 */
	public function task_get_all_image_sizes($offset = 0, $limit = 1000, $blog_id = 1) {

		$this->log('task_get_all_image_sizes(offset: {offset}, limit: {limit}, blog_id: {blog_id})', array('offset' => $offset, 'limit' => $limit, 'blog_id' => $blog_id));

		$this->message(__('Get information about image sizes...', 'wp-optimize'), $blog_id);

		$this->switch_to_blog($blog_id);

		$image_ids = $this->get_image_attachments_post_ids($offset, $limit);

		$this->restore_current_blog();

		return array(
			'image_ids' => $image_ids,
			'offset' => $offset,
			'limit' => $limit,
			'blog_id' => $blog_id
		);
	}

	/**
	 * Process result from task_get_all_image_sizes.
	 *
	 * @param array $result
	 */
	public function process_get_all_image_sizes_results($result) {
		$blog_id = $result['blog_id'];

		$this->log('process_get_all_image_sizes_results(blog_id: {blog_id})', array('blog_id' => $blog_id));

		$all_image_sizes = $this->get_from_cache('all_image_sizes_part', $blog_id);

		if (empty($all_image_sizes)) {
			$all_image_sizes = array();
		}

		$this->switch_to_blog($blog_id);

		if (!empty($result['image_ids'])) {
			// walk through all images and get image sizes with file sizes.
			foreach ($result['image_ids'] as $image_id) {

				// if data for attachment is not loaded from database then preload data for the next portion.
				if (!$this->is_attachment_metadata_loaded($image_id)) {
					$this->preload_attachments_metadata($result['image_ids']);
				}

				$image_info = $this->get_attachment_info($image_id);

				// we don't need this info in memory so release.
				if (isset($this->_attachments_meta_data[$image_id])) unset($this->_attachments_meta_data[$image_id]);

				if (!empty($image_info['sizes'])) {
					foreach ($image_info['sizes'] as $size_id => $file_size) {

						if (is_array($all_image_sizes) && array_key_exists($size_id, $all_image_sizes)) {
							$all_image_sizes[$size_id]['files']++;
							$all_image_sizes[$size_id]['size'] += $file_size;
						} else {
							$all_image_sizes[$size_id]['files'] = 1;
							$all_image_sizes[$size_id]['size'] = $file_size;
						}
					}
				}
			}
		}

		$this->restore_current_blog();

		if (count($result['image_ids']) == $result['limit']) {
			// if not all images scanned then save partially information to cache and add task to scan next images.
			$this->save_to_cache('all_image_sizes_part', $all_image_sizes, $blog_id);
			$new_offset = $result['offset'] + $result['limit'];
			$this->_tasks_queue()->add_task(new WP_Optimize_Queue_Task(array(get_class($this), 'task_get_all_image_sizes'), array($new_offset, $result['limit'], $this->calc_priority($blog_id, 15))));
		} else {
			// all images scanned, save results to cache.
			$this->delete_from_cache('all_image_sizes_part', $blog_id);
			$this->save_to_cache('all_image_sizes', $all_image_sizes, $blog_id);
		}
	}

	/**
	 * Returns settings label.
	 *
	 * @return string
	 */
	public function settings_label() {
		return __('Remove unused images', 'wp-optimize');
	}

	/**
	 * Remove images by images paths list.
	 *
	 * @param array|string $images 'all' to remove all unused images or list of images in format [blog_id]_[image_id|relative_path_to_url].
	 * @return array
	 */
	public function remove_selected_images($images) {
		if (empty($images)) return;

		$this->log('remove_selected_images()');

		$remove_all_images = ('all' === $images);

		$removed = array('files' => 0, 'size' => 0);

		if ($remove_all_images) {
			$blog_ids = $this->blogs_ids;
		} else {
			$images = $this->group_posted_images_by_blogs($images);
			$blog_ids = array_keys($images);
		}

		if (!empty($blog_ids)) {
			foreach ($blog_ids as $blog_id) {
				$this->switch_to_blog($blog_id);

				$removed[$blog_id] = 0;

				$base_upload_dir = $this->get_upload_base_dir();

				// get information about unused images from cache.
				$unused_posts_images = $this->get_from_cache('unused_posts_images', $blog_id);
				$unused_images_files = $this->get_from_cache('unused_images_files', $blog_id);
				$all_image_sizes = $this->get_from_cache('all_image_sizes', $blog_id);

				if ($remove_all_images) {
					// remove all unused images here.
					if (!empty($unused_posts_images)) {
						foreach ($unused_posts_images as $i => $image_id) {
							$attachment_info = $this->get_attachment_info($image_id);
							$this->remove_attachment($image_id);
							// update information about sizes in cache.
							$this->remove_sizes_info($all_image_sizes, $attachment_info);

							$removed['files']++;
							$removed[$blog_id]++;
							$removed['size'] += $attachment_info['size'];

							unset($unused_posts_images[$i]);
						}
					}

					if (!empty($unused_images_files)) {
						foreach (array_keys($unused_images_files) as $image_file) {
							$this->remove_file($base_upload_dir . '/' . $image_file);

							$removed['files']++;
							$removed[$blog_id]++;
							$removed['size'] += $unused_images_files[$image_file];

							unset($unused_images_files[$image_file]);
						}
					}
				} else {
					// if posted images id or urls.
					if (array_key_exists($blog_id, $images)) {
						// get all posted images for current blog.
						foreach ($images[$blog_id] as $image) {
							if (is_numeric($image)) {
								$attachment_info = $this->get_attachment_info($image);
								// if image id posted then remove attachment.
								$this->remove_attachment($image);
								// update information about sizes in cache.
								$this->remove_sizes_info($all_image_sizes, $attachment_info);

								$removed['files']++;
								$removed[$blog_id]++;
								$removed['size'] += $attachment_info['size'];

								$image_i = array_search($image, $unused_posts_images);
								unset($unused_posts_images[$image_i]);
							} else {
								// if posted url then remove file from upload directory.
								$this->remove_file($base_upload_dir.'/'.html_entity_decode($image));

								$removed['files']++;
								$removed[$blog_id]++;
								$removed['size'] += $unused_images_files[$image];

								unset($unused_images_files[$image]);
							}
						}
					}
				}

				// save updated info to cache.
				$this->save_to_cache('unused_posts_images', $unused_posts_images, $blog_id);
				$this->save_to_cache('unused_images_files', $unused_images_files, $blog_id);
				$this->save_to_cache('all_image_sizes', $all_image_sizes, $blog_id);

				$this->restore_current_blog();
			}
		}

		return $removed;
	}

	/**
	 * Remove for sizes info array ( [size_id => ['files' => files count, 'size' => total size, ...] )
	 *
	 * @param array $sizes_info sizes info array ( [size_id => ['files' => files count, 'size' => total size, ...] )
	 * @param array $image_info
	 */
	private function remove_sizes_info(&$sizes_info, $image_info) {
		if (!is_array($sizes_info) || empty($image_info['sizes'])) return;

		$this->log('remove_sizes_info()');

		foreach ($image_info['sizes'] as $size_id => $size) {
			if (!array_key_exists($size_id, $sizes_info)) continue;
			$sizes_info[$size_id]['files']--;
			$sizes_info[$size_id]['size'] -= $size;
		}
	}

	/**
	 * Get posted image values from frontend and group it by blog id, we post it like [blog_id]_[image_id | url].
	 *
	 * @param  array $images
	 * @return array
	 */
	private function group_posted_images_by_blogs($images) {
		$result = array();

		if (empty($images)) return $result;

		foreach ($images as $image_id) {
			preg_match('/^(\d+)_(.+)$/', $image_id, $image_id_parts);
			$blog_id = $image_id_parts[1];
			if (!array_key_exists($blog_id, $result)) $result[$blog_id] = array();
			$result[$blog_id][] = $image_id_parts[2];
		}

		return $result;
	}

	/**
	 * Returns list of attachment ids
	 *
	 * @param int      $offset
	 * @param int|null $limit
	 * @return array
	 */
	public function get_image_attachments_post_ids($offset = 0, $limit = null) {
		global $wpdb;

		$this->log('get_image_attachments_post_ids(offset: {offset}, limit: {limit})', array('offset' => $offset, 'limit' => $limit));

		$ids = array();
		$one_iteration = (null === $limit);

		// Get attachments by parts.
		do {
			if ($one_iteration) {
				$query = $wpdb->prepare(
					"SELECT p.ID FROM {$wpdb->posts} p ".
					" JOIN {$wpdb->postmeta} pm".
					" ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file' ".
					"WHERE p.post_type=%s AND p.post_mime_type LIKE %s;",
					'attachment',
					'image/%'
				);
			} else {
				$query = $wpdb->prepare(
					"SELECT p.ID FROM {$wpdb->posts} p ".
					" JOIN {$wpdb->postmeta} pm".
					" ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file' ".
					"WHERE p.post_type=%s AND p.post_mime_type LIKE %s ".
					"LIMIT %d, %d;",
					'attachment',
					'image/%',
					$offset,
					$limit
				);
			}
			$found = $wpdb->get_col($query);
			$offset += $limit;
			if (!empty($found))	$ids = array_merge($ids, $found);
		} while (count($found) === $limit && !$one_iteration);

		$wpdb->flush();

		return $ids;
	}

	/**
	 * Remove file.
	 *
	 * @param string $filename filename.
	 * @return bool
	 */
	public function remove_file($filename) {
		return @unlink($filename);// phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
	}

	/**
	 * Remove attachment and save statistic.
	 *
	 * @param int $attachment_id wordpress attachment id.
	 * @return bool
	 */
	public function remove_attachment($attachment_id) {
		if (wp_delete_attachment($attachment_id, true)) return true;

		return false;
	}

	/**
	 * Remove images by posted sizes info.
	 *
	 * @param array $args parameters for remove.
	 *
	 * @return array
	 */
	public function remove_images_sizes($args) {
		$result = array(
			'files' => 0,
			'size' => 0
		);

		$defaults = array(
			'remove_sizes' => array(), // list of sizes ids which we want to remove.
			'keep_sizes' => array(), // list of sizes ids which we want to keep and remove other.
			'ids' => array() // attachment ids which will we check.
		);

		$r = wp_parse_args($args, $defaults);

		$keep_size = $remove_size = array();

		// if some data passed to remove_sizes or keep_sizes then check attachments.
		if (!empty($r['remove_sizes']) || !empty($r['keep_sizes'])) {

			if (!empty($r['remove_sizes'])) {
				foreach ($r['remove_sizes'] as $size) {
					$remove_size[$size] = true;
				}
			}

			if (!empty($r['keep_sizes'])) {
				foreach ($r['keep_sizes'] as $size) {
					$keep_size[$size] = true;
				}
			}

			foreach ($this->blogs_ids as $blog_id) {
				$this->switch_to_blog($blog_id);

				// get information about unused images from cache.
				$all_image_sizes = $this->get_from_cache('all_image_sizes', $blog_id);

				$base_upload_dir = $this->get_upload_base_dir();

				// if ids passed ids then use these values otherwise get all image attachments ids.
				$ids = !empty($r['ids']) ? $r['ids'] : $this->get_image_attachments_post_ids();

				if (!empty($ids)) {
					foreach ($ids as $id) {
						$meta = $_meta = wp_get_attachment_metadata($id, true);

						// if meta data found for attachment then check resized images.
						if (!empty($meta) && !empty($meta['sizes'])) {

							if (!preg_match('/^\d{4}\/\d{2}/', $meta['file'], $sub_dir)) continue;

							$updated = false;

							$file_sub_dir = $base_upload_dir . '/' . $sub_dir[0];

							foreach ($meta['sizes'] as $size => $info) {
								if ((!empty($keep_size) && !array_key_exists($size, $keep_size)) || (!empty($remove_size) && array_key_exists($size, $remove_size))) {
									$full_file_name = $file_sub_dir . '/' . $info['file'];
									if (is_file($full_file_name)) {
										$filesize = filesize($full_file_name);
										if ($this->remove_file($full_file_name)) {
											$updated = true;

											// reduce information in cache.
											$all_image_sizes[$size]['files']--;
											$all_image_sizes[$size]['size'] -= $filesize;

											$result['files']++;
											$result['size'] += $filesize;

											unset($_meta['sizes'][$size]);
										}
									} else {
										$updated = true;
										unset($_meta['sizes'][$size]);
									}
								}
							}

							if ($updated) {
								// if something was updated then update metadata.
								wp_update_attachment_metadata($id, $_meta);
							}
						}
					}
				}

				// save updated info to cache.
				$this->save_to_cache('all_image_sizes', $all_image_sizes, $blog_id);

				$this->restore_current_blog();
			}
		}

		return $result;
	}

	/**
	 * Get upload base dir.
	 *
	 * @return mixed
	 */
	public function get_upload_base_url() {
		$upload_dir = function_exists('wp_get_upload_dir') ? wp_get_upload_dir() : wp_upload_dir(null, false);
		return $upload_dir['baseurl'];
	}

	/**
	 * Get upload relative dir.
	 *
	 * @return mixed
	 */
	public function get_upload_relative_url() {
		static $dir = '';
		if ($dir) return $dir;
		$base = $this->get_upload_base_url();
		$dir = parse_url($base, PHP_URL_PATH);
		return $dir;
	}

	/**
	 * Get upload base dir.
	 *
	 * @return mixed
	 */
	public function get_upload_base_dir() {
		$upload_dir = function_exists('wp_get_upload_dir') ? wp_get_upload_dir() : wp_upload_dir(null, false);
		return $upload_dir['basedir'];
	}

	/**
	 * Returns upload folder subdirectories in format YYYY/MM.
	 *
	 * @param int $blog_id
	 * @return array
	 */
	public function get_upload_sub_dirs($blog_id = 1) {

		$this->log('get_upload_sub_dirs(blog_id: {blog_id})', array('blog_id' => $blog_id));

		$this->switch_to_blog($blog_id);

		$base_upload_dir = $this->get_upload_base_dir();

		$years_dirs = $this->get_sub_dirs($base_upload_dir, '/\d{4}/');
		$years_month_dirs = array();

		if (!empty($years_dirs)) {
			foreach ($years_dirs as $year) {
				$sub_dirs = $this->get_sub_dirs($base_upload_dir.'/'.$year, '/\d{2}/');
				if (!empty($sub_dirs)) {
					foreach ($sub_dirs as $sub_dir) {
						$years_month_dirs[] = $year.'/'.$sub_dir;
					}
				}
			}
		}

		$this->restore_current_blog();

		return $years_month_dirs;
	}

	/**
	 * Returns list of subdirectories in $path folder matched with $pattern regexp.
	 *
	 * @param string $path    path to directory.
	 * @param string $pattern regexp to match with subdirectories.
	 * @return array
	 */
	private function get_sub_dirs($path, $pattern = '') {
		$sub_dirs = array();
		if (!is_dir($path)) return $sub_dirs;

		$this->log('get_sub_dirs(path: {path}, pattern: {pattern})', array('path' => $path, 'pattern' => $pattern));

		$handle = opendir($path);

		if (false === $handle) return $sub_dirs;

		while ($file = readdir($handle)) {
			if ('.' == $file || '..' == $file || !is_dir($path.'/'.$file)) continue;
			if ('' == $pattern || preg_match($pattern, $file)) {
				$sub_dirs[] = $file;
			}
		}

		closedir($handle);

		return $sub_dirs;
	}

	/**
	 * Returns attachment ID by image filename.
	 *
	 * @param string $filename filename with upload sub folder, for ex. 2017/01/image.jpg
	 * @return null|int
	 */
	public function get_image_attachment_id($filename) {
		global $wpdb;
		static $last_post_id = 0, $last_original_file_name = '';

		// check if file name for resized image.
		$original_file_name = $this->get_original_image_file_name($filename);

		if ($original_file_name == $last_original_file_name) return $last_post_id;

		$query = "SELECT post_id FROM {$wpdb->postmeta} pm WHERE pm.meta_key=%s AND pm.meta_value=%s LIMIT 1";
		$post_id = $wpdb->get_var($wpdb->prepare($query, '_wp_attached_file', $original_file_name));

		$last_post_id = $post_id;
		$last_original_file_name = $original_file_name;

		return $post_id;
	}

	/**
	 * Get images attachment ids by filenames list.
	 *
	 * @param array  $filenames          list of image filenames (for original files, i.e. not resized -[width]x[height].[ext]).
	 * @param string $return_nonexistent if true then non existent attachments will returned too with id = false.
	 * @return array assoc array with filename in key and attachment id in value.
	 */
	public function get_image_attachment_id_bulk($filenames, $return_nonexistent = false) {
		global $wpdb;

		$found_attachments = array();
		if (empty($filenames)) return $found_attachments;

		// walk through $filenames and check if there any with resized filename
		// store this info into separate array and replace $filenames element
		// just with original image filename (i.e. without -nxn size suffix)
		$resized_filenames = array();
		foreach ($filenames as $key => $filename) {
			if (false === strpos($filename, ':/:')) continue;
			$image_filenames = explode(':/:', $filename);
			$resized_filenames[$image_filenames[0]] = $image_filenames[1];
			$filenames[$key] = $image_filenames[0];
		}

		$query = "SELECT post_id, meta_value FROM {$wpdb->postmeta} pm WHERE pm.meta_key='_wp_attached_file' AND pm.meta_value IN (\"".join('","', esc_sql($filenames))."\")";
		$query_result = $wpdb->get_results($query, ARRAY_A);

		if (!empty($query_result)) {
			foreach ($query_result as $row) {
				$found_attachments[$row['meta_value']] = $row['post_id'];
			}
		}

		// check if some images was not found then build list with resized file names.
		$search = array();
		foreach ($resized_filenames as $original => $resized) {
			if (!array_key_exists($original, $found_attachments)) {
				$search[] = $resized;
			}
		}

		// search resized image file names in the database
		if (!empty($search)) {
			$query = "SELECT post_id, meta_value FROM {$wpdb->postmeta} pm WHERE pm.meta_key='_wp_attached_file' AND pm.meta_value IN (\"".join('","', esc_sql($search))."\")";
			$query_result = $wpdb->get_results($query, ARRAY_A);

			if (!empty($query_result)) {
				foreach ($query_result as $row) {
					$found_attachments[$row['meta_value']] = $row['post_id'];
				}
			}
		}

		// if some images was not found in the database then we try to find images with `-scaled`, `-rotated` suffix in the database
		// build new filenames with `-scaled`, `-rotated` suffix here for all not found images.
		$search = array();
		foreach ($filenames as $filename) {
			if (!array_key_exists($filename, $found_attachments) && !(array_key_exists($filename, $resized_filenames) && array_key_exists($resized_filenames[$filename], $found_attachments))) {
				preg_match($this->image_filename_regexp, $filename, $match);
				if ('scaled' != $match[3] && 'rotated' != $match[3]) {
					$search[] = $match[1] . '-scaled' . $match[5];
					$search[] = $match[1] . '-rotated' . $match[5];
				}
			}
		}

		// trying to find in the database images with `-scaled`, `-rotated` suffix.
		if (!empty($search)) {
			$query = "SELECT post_id, meta_value FROM {$wpdb->postmeta} pm WHERE pm.meta_key='_wp_attached_file' AND pm.meta_value IN (\"".join('","', esc_sql($search))."\")";
			$query_result = $wpdb->get_results($query, ARRAY_A);

			if (!empty($query_result)) {
				foreach ($query_result as $row) {
					$found_attachments[$this->get_original_image_file_name($row['meta_value'])] = $row['post_id'];
				}
			}
		}

		if ($return_nonexistent) {
			// fill nonexisting filenames with false.
			foreach ($filenames as $filename) {
				if (!array_key_exists($filename, $found_attachments) && !(array_key_exists($filename, $resized_filenames) && array_key_exists($resized_filenames[$filename], $found_attachments))) $found_attachments[$filename] = false;
			}
		}

		return $found_attachments;
	}

	/**
	 * Returns information about attachment files and total size.
	 *
	 * @param int  $attachment_id attachment_id
	 * @param bool $extended      if true then return additional information about sizes.
	 * @return array
	 */
	public function get_attachment_info($attachment_id, $extended = true) {
		$attachment_info = array('url' => '#', 'files' => 0, 'size' => 0);
		$base_upload_dir = $this->get_upload_base_dir();
		$meta = $this->wp_get_attachment_metadata($attachment_id);

		$thumb_size = 0;

		// get info about original image.
		if ($meta) {
			$pinfo = pathinfo($meta['file']);
			$sub_dir = $pinfo['dirname'];
			$file_sub_dir = $base_upload_dir . '/' . $sub_dir;

			$original_file = $base_upload_dir . '/' . $meta['file'];
			if (is_file($original_file)) {
				$filesize = filesize($original_file);

				$thumb_size = $filesize;

				$attachment_info['url'] = $meta['file'];
				$attachment_info['sizes']['original'] = $filesize;
				$attachment_info['size'] += $filesize;
				$attachment_info['files']++;
			}

			// get info about resized images.
			if (!empty($meta['sizes'])) {
				foreach ($meta['sizes'] as $size_id => $info) {
					$full_file_name = $file_sub_dir . '/' . $info['file'];
					// if file isn't exists then continue.
					if (!is_file($full_file_name)) continue;

					$filesize = filesize($full_file_name);

					// save to 'url' little thumb image.
					if ((0 === $thumb_size || $thumb_size > $filesize) && ($info['width'] >= 120)) {
						$thumb_size = $filesize;
						$attachment_info['url'] = $sub_dir . '/'. $info['file'];
					}

					$attachment_info['sizes'][$size_id] = $filesize;
					$attachment_info['size'] += $filesize;
					$attachment_info['files']++;
				}
			}

			// Fallback to the meta info (e.g. the above may fail if PHP doesn't have the right permissions, as seen on some WPEngine users)
			if (!isset($attachment_info['url']) || empty($attachment_info['url'])) {
				$attachment_info['url'] = $meta['file'];
			}
		}

		if (false === $extended) unset($attachment_info['sizes']);

		return $attachment_info;
	}

	/**
	 * Returns original filename for resized image.
	 *
	 * @param string $filename filename.
	 * @return string
	 */
	public function get_original_image_file_name($filename) {
		if (preg_match($this->image_filename_regexp, $filename, $parts)) {
			return $parts[1].$parts[5];
		} else {
			return $filename;
		}
	}

	/**
	 * Check if given file is an image.
	 *
	 * @param string $filename
	 * @return bool
	 */
	public function is_image_file($filename) {
		$check = wp_check_filetype($filename);
		if (empty($check['ext'])) {
			return false;
		}
		$ext = strtolower($check['ext']);

		$image_exts = $this->_images_extensions;
		return in_array($ext, $image_exts);
	}

	/**
	 * Save value to cache.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param int    $blog_id
	 */
	private function save_to_cache($key, $value, $blog_id = 1) {
		$transient_limit = 3600 * 24;
		$key = 'wpo_images_cache_' . $blog_id . '_'. $key;

		$this->log('save_to_cache(key: {key})', array('key' => $key));

		return WP_Optimize_Transients_Cache::get_instance()->set($key, $value, $transient_limit);
	}

	/**
	 * Get value from cache.
	 *
	 * @param string $key
	 * @param int    $blog_id
	 * @return mixed
	 */
	private function get_from_cache($key, $blog_id = 1) {
		$key = 'wpo_images_cache_' . $blog_id . '_'. $key;

		$this->log('get_from_cache(key: {key})', array('key' => $key));

		$value = WP_Optimize_Transients_Cache::get_instance()->get($key);

		return $value;
	}

	/**
	 * Delete selected images from unused images cache (used with unused images trash functionality).
	 *
	 * @param array $images - array with values <blog_id>_<image_id> or <blog_id>_<relative_path>
	 */
	public function delete_selected_images_from_cache($images) {

		if (empty($images)) return;

		$_images = array();

		foreach ($images as $image) {
			// possible one of two cases
			// 1. $image = <blog_id>_<image_id>
			// 2. $image = <blog_id>_<relative_path>
			$image = explode('_', $image);
			if (!array_key_exists($image[0], $_images)) $_images[$image[0]] = array();
			$_images[$image[0]][$image[1]] = 1;
		}

		foreach (array_keys($_images) as $blog_id) {
			$update_images = false;
			$update_files = false;

			foreach (array_keys($_images[$blog_id]) as $image) {
				if (preg_match('/^\d+$/i', $image)) {
					$update_images = true;
				} else {
					$update_files = true;
				}
			}

			$unused_posts_images = $update_images ? WP_Optimization_images::instance()->get_from_cache('unused_posts_images', $blog_id) : array();
			$unused_images_files = $update_files ? WP_Optimization_images::instance()->get_from_cache('unused_images_files', $blog_id) : array();

			if ($update_images) {
				foreach ($unused_posts_images as $i => $image) {
					if (array_key_exists($image, $_images[$blog_id])) unset($unused_posts_images[$i]);
				}
			}

			if ($unused_images_files) {
				foreach ($unused_images_files as $file => $size) {
					if (array_key_exists($file, $_images[$blog_id])) unset($unused_images_files[$file]);
				}
			}

			if ($update_images) WP_Optimization_images::instance()->save_to_cache('unused_posts_images', $unused_posts_images, $blog_id);
			if ($update_files) WP_Optimization_images::instance()->save_to_cache('unused_images_files', $unused_images_files, $blog_id);

			WP_Optimize_Transients_Cache::get_instance()->flush();
		}
	}

	/**
	 * Delete selected images from unused images cache (used with unused images trash functionality).
	 *
	 * @param array $images - array with values <blog_id>_<image_id> or [<blog_id>_<relative_path>, image_file_size]
	 */
	public function add_selected_images_to_cache($images) {

		if (empty($images)) return;

		$_images = array();

		foreach ($images as $image) {
			if (is_array($image)) {
				$image_file_size = $image[1];
				$image = $image[0];
			}

			$path_parts = explode('/', $image);
			$basename = array_pop($path_parts);
			preg_match('/^(\d+)_([x\d]+)\-/U', $basename, $match);

			$blog_id = $match[1];
			$image_id = $match[2];

			// $image_id can be int or 'x'.
			if ('x' == $image_id) {
				// remove from base name prefix with information <blog_id>_<image_id>_
				$basname_parts = explode('-', $basename);
				$path_parts[] = implode('-', array_slice($basname_parts, 1));
				$image = implode('/', $path_parts);
				// delete leading slash
				if ('/' == $image[0]) $image = substr($image, 1);
				$_images[$blog_id][$image] = $image_file_size;
			} else {
				$_images[$blog_id][$image_id] = 1;
			}
		}

		foreach (array_keys($_images) as $blog_id) {
			$update_images = false;
			$update_files = false;

			foreach (array_keys($_images[$blog_id]) as $image) {
				if (preg_match('/^\d+$/i', $image)) {
					$update_images = true;
				} else {
					$update_files = true;
				}
			}

			$unused_posts_images = $update_images ? WP_Optimization_images::instance()->get_from_cache('unused_posts_images', $blog_id) : array();
			$unused_images_files = $update_files ? WP_Optimization_images::instance()->get_from_cache('unused_images_files', $blog_id) : array();

			foreach (array_keys($_images[$blog_id]) as $image) {
				if (preg_match('/^\d+$/i', $image)) {
					$unused_posts_images[] = $image;
				} else {
					$size = $_images[$blog_id][$image];
					$unused_images_files[$image] = $size;
				}
			}

			if ($update_images) WP_Optimization_images::instance()->save_to_cache('unused_posts_images', $unused_posts_images, $blog_id);
			if ($update_files) WP_Optimization_images::instance()->save_to_cache('unused_images_files', $unused_images_files, $blog_id);

			WP_Optimize_Transients_Cache::get_instance()->flush();
		}
	}

	/**
	 * Delete value from cache.
	 *
	 * @param string $key
	 * @param int    $blog_id
	 */
	private function delete_from_cache($key, $blog_id = 1) {
		$key = 'wpo_images_cache_' . $blog_id . '_'. $key;

		$this->log('delete_from_cache(key: {key})', array('key' => $key));

		WP_Optimize_Transients_Cache::get_instance()->delete($key);

		$this->delete_transient($key);
	}

	/**
	 * Delete transient wrapper.
	 *
	 * @param string $key
	 */
	private function delete_transient($key) {
		if ($this->is_multisite_mode()) {
			delete_site_transient($key);
		} else {
			delete_transient($key);
		}
	}

	/**
	 * Remove all cached data stored by image optimization.
	 */
	private function clear_cached_data() {
		global $wpdb;

		$this->log('clear_cached_data()');

		$unused_images_keys = array(
			'homepage_images',
			'unused_posts_images',
			'unused_images_files',
			'homepage_images',
		);

		$image_sizes_keys = array(
			'all_image_sizes',
		);

		$cache_keys = array();

		switch ($this->get_work_mode()) {
			case self::DETECT_IMAGES:
				$cache_keys = $unused_images_keys;
				break;
			case self::DETECT_SIZES:
				$cache_keys = $image_sizes_keys;
				break;
			case self::DETECT_BOTH:
				$cache_keys = array_merge($unused_images_keys, $image_sizes_keys);
				break;
		}

		$field = $this->is_multisite_mode() ? 'meta_key' : 'option_name';
		$where_parts = array();

		foreach ($cache_keys as $key) {
			$where_parts[] = "({$field} LIKE '%wpo_images_cache_%_{$key}%')";
		}

		$where = implode(' OR ', $where_parts);

		// get list of cached data by optimization.
		if ($this->is_multisite_mode()) {
			$keys = $wpdb->get_col("SELECT meta_key FROM {$wpdb->sitemeta} WHERE {$where}");
		} else {
			$keys = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE {$where}");
		}

		if (!empty($keys)) {
			$transient_keys = array();
			foreach ($keys as $key) {
				preg_match('/wpo_images_cache_.+/', $key, $option_name);
				$option_name = $option_name[0];
				$transient_keys[] = $option_name;
			}

			// get unique keys.
			$transient_keys = array_unique($transient_keys);

			// delete transients.
			foreach ($transient_keys as $key) {
				$this->delete_transient($key);
			}
		}
	}

	/**
	 * Get image filenames from html $content.
	 *
	 * @param string $content
	 * @return array
	 */
	private function parse_images_in_content($content) {
		$base = $this->get_upload_relative_url();
		$pat = '/'.preg_quote($base, '/').'\/([^\\\'\"]+\.('.join('|', $this->_images_extensions).'))/Ui';
		preg_match_all($pat, $content, $images);
		// Return the first group
		return $images[1];
	}

	/**
	 * Format int to size string.
	 *
	 * @param int $size
	 * @param int $decimals
	 * @return string
	 */
	private function size_format($size, $decimals = 1) {
		return size_format($size, $size < 1024 ? 0 : $decimals);
	}

	/**
	 * Returns true if attachment metadata preloaded into $this->_attachments_meta_data.
	 *
	 * @param int $attachment_id
	 * @return bool
	 */
	private function is_attachment_metadata_loaded($attachment_id) {
		return array_key_exists((int) $attachment_id, $this->_attachments_meta_data);
	}

	/**
	 * Preload attachments metadata info by posted attachment ids.
	 *
	 * @param array $attachment_ids
	 */
	private function preload_attachments_metadata(&$attachment_ids) {
		global $wpdb;

		$this->log('preload_attachments_metadata()');

		$item_size = 1024 * 60; // ~5-10kb is a memory size used by one attachment record, we get 5x to be safe with memory limit.

		if (empty($attachment_ids)) return;

		// Reduce the array to what's not loaded
		$already_loaded = array_keys($this->_attachments_meta_data);
		$attachment_ids = array_diff($attachment_ids, $already_loaded);

		// calculate how many items we can load per time.
		$preload_batch_limit = floor(WP_Optimize()->get_free_memory() / $item_size);

		// load some data anyway.
		if ($preload_batch_limit < 500) {
			$preload_batch_limit = 500;
		}

		if (count($attachment_ids) <= $preload_batch_limit) {
			// load all attachment info.
			$metadata = $wpdb->get_results("SELECT `post_id` as `id`, `meta_value` FROM {$wpdb->postmeta} WHERE (`meta_key` = '_wp_attachment_metadata') AND `post_id` IN ('" . join("','", $attachment_ids) . "')", ARRAY_A);
			$loaded_meta_ids = $attachment_ids;
		} else {
			$loaded_meta_ids = array_splice($attachment_ids, 0, $preload_batch_limit);
			$metadata = $wpdb->get_results("SELECT `post_id` as `id`, `meta_value` FROM {$wpdb->postmeta} WHERE (`meta_key` = '_wp_attachment_metadata') AND `post_id` IN ('" . join("','", $loaded_meta_ids) . "')", ARRAY_A);
		}

		if (!empty($metadata)) {
			foreach ($metadata as $data) {
				$this->_attachments_meta_data[$data['id']] = unserialize($data['meta_value']);
			}
		}
		// fill not exists data false values.
		foreach ($loaded_meta_ids as $attachment_id) {
			if (!array_key_exists($attachment_id, $this->_attachments_meta_data)) $this->_attachments_meta_data[$attachment_id] = false;
		}
	}

	/**
	 * Returns attachment meta data form preloaded data or call wp_get_attachment_metadata().
	 *
	 * @param int $attachment_id
	 * @return array|false
	 */
	private function wp_get_attachment_metadata($attachment_id) {
		if ($this->is_attachment_metadata_loaded($attachment_id)) return $this->_attachments_meta_data[$attachment_id];
		$this->_attachments_meta_data[$attachment_id] = wp_get_attachment_metadata($attachment_id);
		return $this->_attachments_meta_data[$attachment_id];
	}

	/**
	 * Get size information for all currently-registered image sizes.
	 * https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
	 *
	 * @global $_wp_additional_image_sizes
	 * @uses   get_intermediate_image_sizes()
	 * @return array $sizes Data for all currently-registered image sizes.
	 */
	private function get_image_sizes() {
		global $_wp_additional_image_sizes;

		$this->log('get_image_sizes()');

		$sizes = array();
		foreach (get_intermediate_image_sizes() as $_size) {
			if (in_array($_size, array('thumbnail', 'medium', 'medium_large', 'large'))) {
				$sizes[$_size]['width']  = get_option("{$_size}_size_w");
				$sizes[$_size]['height'] = get_option("{$_size}_size_h");
				$sizes[$_size]['crop']   = (bool) get_option("{$_size}_crop");
			} elseif (isset($_wp_additional_image_sizes[$_size])) {
				$sizes[$_size] = array(
					'width'  => $_wp_additional_image_sizes[$_size]['width'],
					'height' => $_wp_additional_image_sizes[$_size]['height'],
					'crop'   => $_wp_additional_image_sizes[$_size]['crop'],
				);
			}
		}

		return $sizes;
	}

	/**
	 * Returns assoc array with different values for width and height for registered sizes.
	 *
	 * @return array
	 */
	private function get_image_sizes_wh() {
		$image_sizes = $this->get_image_sizes();

		$image_sizes_wh = array(
			'width' => array(),
			'height' => array()
		);

		foreach ($image_sizes as $size) {
			$image_sizes_wh['width'][] = $size['width'];
			$image_sizes_wh['height'][] = $size['height'];
		}

		return array(
			'width' => array_unique($image_sizes_wh['width']),
			'height' => array_unique($image_sizes_wh['height']),
		);
	}

	/**
	 * Calculate task priority by blog id and internal priority.
	 * used priorities:
	 *   task_get_info - 1
	 *   task_get_posts_images - 5
	 *   task_get_unused_images_files - 10
	 *   get_unused_images_in_sub_directory - 11
	 *   process_get_unused_images_files_result - 12
	 *   task_get_all_image_sizes - 15
	 *
	 * @param  int $blog_id
	 * @param  int $priority
	 * @return int
	 */
	private function calc_priority($blog_id, $priority) {
		return ($blog_id-1) * 100 + $priority;
	}

	/**
	 * Returns true if set debug mode constant.
	 *
	 * @return bool
	 */
	private function is_debug_mode() {
		return (defined('WP_OPTIMIZE_DEBUG_OPTIMIZATIONS') && WP_OPTIMIZE_DEBUG_OPTIMIZATIONS);
	}

	/**
	 * Log message into PHP log.
	 *
	 * @param string $message
	 * @param array  $context
	 */
	private function log($message, $context = array()) {

		if (defined('WP_OPTIMIZE_UNUSED_IMAGES_LOG') && WP_OPTIMIZE_UNUSED_IMAGES_LOG) {
			$this->_logger->debug($message, $context);
		}

	}
}

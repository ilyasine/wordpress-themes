<?php
if (!defined('ABSPATH')) die('No direct access allowed');

if (!class_exists('WP_Optimize_Premium')) {

class WP_Optimize_Premium {

	protected static $_instance = null;

	protected $cron_scheduler = null;

	/**
	 * WP_Optimize_Premium constructor
	 */
	public function __construct() {

		if (class_exists('WP_CLI_Command')) {
			include_once(WPO_PLUGIN_MAIN_PATH . '/includes/class-wp-optimize-cli-command.php');
		}

		// load task manager.
		WP_Optimize()->get_task_manager();
		include_once(WPO_PLUGIN_MAIN_PATH . '/includes/class-wp-optimize-queue-task.php');

		include_once(WPO_PLUGIN_MAIN_PATH.'/includes/class-semaphore.php');

		if (!class_exists('WP_Optimize_Cron_Scheduler')) {
			include_once(WPO_PLUGIN_MAIN_PATH . '/includes/class-wp-optimize-cron-scheduler.php');
			$this->cron_scheduler = WP_Optimize_Cron_scheduler::get_instance();
		}

		include_once(WPO_PLUGIN_MAIN_PATH.'/includes/class-wp-optimize-lazy-load.php');
		add_filter('wp_optimize_loggers_classes', array($this, 'loggers_classes'));
		add_filter('additional_options_updraft_slack_logger', array($this, 'additional_options_updraft_slack_logger'), 20, 3);

		// Add custom capabilities.
		$this->setup_capabilities();

		// Single table optimize feature.
		add_filter('wpo_tables_list_additional_column_data', array($this, 'tables_list_additional_column_data'), 10, 2);
		add_action('wpo_tables_list_before', array($this, 'wpo_tables_list_before'));
		add_filter('wpo_get_tables_data', array($this, 'show_innodb_force_optimize'));

		if (is_multisite()) {
			add_action('wpo_additional_options', array($this, 'wpo_additional_options'));
			add_action('wpo_additional_options_cron', array($this, 'wpo_additional_options_cron'));
		}

		add_action('wpo_premium_scripts_styles', array($this, 'enqueue_scripts_styles'), 15, 3);

		add_filter('wp_optimize_sub_menu_items', array($this, 'change_premium_page_title'), 20);
		add_filter('wp_optimize_admin_page_wpo_mayalso_tabs', array($this, 'check_premium_tab_title'));
		add_filter('wp_optimize_admin_page_wpo_images_tabs', array($this, 'admin_page_images_tabs'));

		add_action('auto_option_settings', array($this, 'auto_option_settings'));
		add_filter('wp_optimize_option_keys', array($this, 'wp_optimize_option_keys'));
		add_filter('wpo_cron_next_event', array($this->cron_scheduler, 'wpo_cron_next_event'));
		add_filter('wpo_js_translations', array($this, 'wpo_js_translations'));
		add_filter('wpo_default_auto_options', array($this, 'default_auto_options'));

		add_action('wpo_after_general_settings', array($this, 'after_general_settings'));

		add_filter('wpo_faq_url', array($this, 'wpo_faq_url'));

		/**
		 * Add action for display Images > Unused images and sizes tab.
		 */
		add_action('wp_optimize_admin_page_wpo_images_unused', array($this, 'admin_page_wpo_images_unused'));

		/**
		 * Add action for display Dashboard > Lazyload tab.
		 */
		add_action('wp_optimize_admin_page_wpo_images_lazyload', array($this, 'admin_page_wpo_images_lazyload'));

		add_action('admin_init', array($this, 'handle_unused_images_csv'));

		$this->schedule_image_optimization_jobs();
		$this->include_lazy_load();
		$this->include_images_trash();
		$this->include_cache_premium();
		$this->include_power_tweaks();

		if (!class_exists('Updraft_Manager_Updater_1_8')) {
			include_once(WPO_PLUGIN_MAIN_PATH.'/vendor/davidanderson684/simba-plugin-manager-updater/class-udm-updater.php');
		}
		
		try {
			new Updraft_Manager_Updater_1_8('https://getwpo.com/plugin-info/', 1, 'wp-optimize-premium/wp-optimize.php', array('require_login' => false));
		} catch (Exception $e) {
			error_log($e->getMessage().' at '.$e->getFile().' line '.$e->getLine());
		}
	}

	/**
	 * Plugin activation actions.
	 */
	public function plugin_activation_actions() {
		// reschedule scheduled events.
		$this->cron_scheduler->wpo_cron_deactivate();
		$this->cron_scheduler->cron_activate();
	}

	/**
	 * Runs upon the WP action admin_page_wpo_images_unused
	 */
	public function admin_page_wpo_images_unused() {
		WP_Optimize()->include_template('images/unused.php');
	}

	/**
	 * Runs upon the WP action wp_optimize_admin_page_wpo_images_lazyload
	 */
	public function admin_page_wpo_images_lazyload() {
		WP_Optimize()->include_template('images/lazyload.php', false, array('lazyload_already_provided_by' => implode(', ', $this->lazyload_already_provided_by())));
	}

	/**
	 * Returns WP_Optimize_Premium instance
	 *
	 * @return null|WP_Optimize_Premium
	 */
	public static function instance() {
		if (empty(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * Enqueue scripts and styles required for premium version
	 *
	 * @param string $min_or_not_internal - The .min-version-number suffix to use on internal assets
	 * @param string $min_or_not          - The .min suffix to use on third party assets
	 * @param string $enqueue_version     - The enqueued version
	 * @return void
	 */
	public function enqueue_scripts_styles($min_or_not_internal, $min_or_not, $enqueue_version) {
		wp_enqueue_script('wp-optimize-lazy-load', WPO_PLUGIN_URL . 'js/wpo-lazy-load' . $min_or_not_internal . '.js', array(), $enqueue_version);

		wp_enqueue_style('wp-optimize-premium-css', WPO_PLUGIN_URL . 'css/wpo-premium' . $min_or_not_internal . '.css', array(), $enqueue_version);
		wp_enqueue_script('wp-optimize-tablesorter-pager', WPO_PLUGIN_URL . 'js/jquery.tablesorter.pager' . $min_or_not_internal . '.js', array('jquery'), $enqueue_version);

		wp_enqueue_script('select2', WPO_PLUGIN_URL . 'js/select2/select2' . $min_or_not . '.js', array('jquery'), $enqueue_version);
		wp_enqueue_style('select2', WPO_PLUGIN_URL . 'css/select2/select2' . $min_or_not . '.css', array(), $enqueue_version);
		wp_enqueue_script('wp-optimize-images-view-js', WPO_PLUGIN_URL . 'js/wpo-images-view' . $min_or_not_internal . '.js', array('jquery'), $enqueue_version);
		wp_enqueue_script('wp-optimize-premium-js', WPO_PLUGIN_URL . 'js/wpo-premium' . $min_or_not_internal . '.js', array('jquery', 'jquery-ui-dialog', 'wp-optimize-send-command', 'wp-optimize-admin-js', 'wp-optimize-images-view-js', 'jquery-ui-datepicker', 'select2'), $enqueue_version);

		wp_enqueue_script('wp-optimize-modernizr-js', WPO_PLUGIN_URL . 'js/modernizr/modernizr-custom' . $min_or_not . '.js', array(), $enqueue_version);
		wp_enqueue_script('wp-optimize-timepicker-js', WPO_PLUGIN_URL . 'js/jquery-timepicker/jquery.timepicker' . $min_or_not . '.js', array('jquery'), $enqueue_version);
		wp_enqueue_style('wp-optimize-timepicker-css', WPO_PLUGIN_URL . 'js/jquery-timepicker/jquery.timepicker'. $min_or_not . '.css', array(), $enqueue_version);

		// Defeat other plugins/themes which dump their jQuery UI CSS onto our settings page
		wp_deregister_style('jquery-ui');
		wp_enqueue_style('jquery-ui', WPO_PLUGIN_URL.'css/jquery-ui.custom' . $min_or_not_internal . '.css', array(), $enqueue_version);

		wp_enqueue_script('handlebars', WPO_PLUGIN_URL . 'js/handlebars/handlebars' . $min_or_not . '.js', array(), '4.1.2');
		wp_enqueue_script('wp-optimize-compiled-handlebars', WPO_PLUGIN_URL . 'templates/handlebars-compiled-'.str_replace('.', '-', WPO_VERSION).'.js', array(), $enqueue_version);
	}

	/**
	 * Add WP-Optimize custom capabilities.
	 */
	public function setup_capabilities() {
		global $wp_roles;

		if (!isset($wp_roles)) {
			$wp_roles = new WP_Roles();
		}

		$wpo_capabilities = array(
			'wpo_manage_settings',
			'wpo_run_optimizations',
		);

		$old_use_db = $wp_roles->use_db;
		$wp_roles->use_db = true;

		if (!isset($wp_roles->roles['administrator'])) {
			return;
		}

		$administrator = $wp_roles->role_objects['administrator'];

		foreach ($wpo_capabilities as $capability) {
			if (!$administrator->has_cap($capability)) {
				$administrator->add_cap($capability);
			}
		}

		$wp_roles->use_db = $old_use_db;
	}

	/**
	 * Add tabs to Dashboard > Images pages.
	 *
	 * @param array $tabs
	 * @return mixed
	 */
	public function admin_page_images_tabs($tabs) {
		$tabs['unused'] = __('Unused images and sizes', 'wp-optimize');
		$tabs['lazyload'] = __('Lazy-load', 'wp-optimize');

		return $tabs;
	}

	/**
	 * Add premium logger classes
	 *
	 * @param  array $loggers_classes
	 * @return array
	 */
	public function loggers_classes($loggers_classes) {
		$premium_classes = array(
			'Updraft_Syslog_Logger'         => WPO_PLUGIN_MAIN_PATH.'includes/class-updraft-syslog-logger.php',
			'Updraft_Simple_History_Logger' => WPO_PLUGIN_MAIN_PATH.'includes/class-updraft-simple-history-logger.php',
			'Updraft_Slack_Logger'          => WPO_PLUGIN_MAIN_PATH.'includes/class-updraft-slack-logger.php',
		);

		if (empty($loggers_classes)) return $premium_classes;

		return array_merge($loggers_classes, $premium_classes);
	}

	/**
	 * Additional options for Slack filter
	 *
	 * @param  string $additional_options_html
	 * @param  string $logger_form_name
	 * @param  array  $logger_additional_options
	 * @return string
	 */
	public function additional_options_updraft_slack_logger($additional_options_html, $logger_form_name, $logger_additional_options) {
		$slack_webhook_url = !empty($logger_additional_options['slack_webhook_url']) ? $logger_additional_options['slack_webhook_url'] : '';

		return $additional_options_html.'<input type="text" size="50" name="'.$logger_form_name.'[slack_webhook_url]" value="'.esc_attr($slack_webhook_url).'" placeholder="'.esc_attr__('Slack Webhook URL', 'wp-optimize').'" />';
	}

	/**
	 * Save/update auto options and call event deactivation and activation as needed
	 *
	 * @param  array $settings Array of information with the schedule parameters
	 * @return void
	 */
	public function auto_option_settings($settings) {

		$options = WP_Optimize()->get_options();
		$options_from_user = isset($settings['wp-optimize-auto']) ? array_values($settings['wp-optimize-auto']) : array();
		if (!is_array($options_from_user)) $options_from_user = array();

		// Set an option to determine whether to optimize innodb automatically or not based on user choice.
		$options->update_option('auto-innodb', !empty($settings['auto-innodb']) ? 'true' : 'false');
		$options->update_option('auto-updated', $options_from_user);

		$this->cron_scheduler->wpo_cron_deactivate();
		$this->cron_scheduler->cron_activate();

		// Save purge cache permissions settings.
		if (isset($settings['purge_cache_permissions'])) {
			$options->update_option('purge-cache-permissions', $settings['purge_cache_permissions']);
		}
	}

	/**
	 * Adds premium options keys to array of option keys
	 *
	 * @param  array $keys Array of existing option keys
	 * @return array Array of updated option keys
	 */
	public function wp_optimize_option_keys($keys) {
		array_push($keys, 'auto-updated', 'locks', 'auto-innodb');
		return $keys;
	}

	/**
	 * Action wpo_tables_list_additional_column_data. Output button Optimize in the action column.
	 *
	 * @param string $content    String for output to column
	 * @param object $table_info Object with table info.
	 *
	 * @return string
	 */
	public function tables_list_additional_column_data($content, $table_info) {
		// If table type is supported, then show optimize button.
		$content .= '<div class="wpo_button_wrap '.((!$table_info->is_type_supported || $table_info->is_needing_repair) ? 'wpo_hidden' : '').'">'
			.'<button class="button button-secondary run-single-table-optimization" data-table="'.esc_attr($table_info->Name).'" data-type="'.esc_attr($table_info->Engine).'" data-disabled="'.($table_info->is_optimizable ? 0 : 1).'" '.($table_info->is_optimizable ? '' : 'disabled').'>'.__('Optimize', 'wp-optimize').'</button>'
			.'<img class="optimization_spinner visibility-hidden" src="'.esc_attr(admin_url('images/spinner-2x.gif')).'" width="20" height="20" alt="...">'
			.'<span class="optimization_done_icon dashicons dashicons-yes visibility-hidden"></span>'
			.'</div>';
		return $content;
	}

	/**
	 * Action wpo_tables_list_before to output custom content before tables list
	 */
	public function wpo_tables_list_before() {
		WP_Optimize()->include_template('database/tables-list-before.php');
	}

	/**
	 * Wether to show the innodb_force_optimize_single checkbox
	 *
	 * @param array $data - The data passed to get_table_list()
	 * @return boolean
	 */
	public function show_innodb_force_optimize($data) {
		$data['show_innodb_force_optimize'] = WP_Optimize()->get_optimizer()->show_innodb_force_optimize();
		return $data;
	}

	/**
	 * Add options with sites list in multisite admin
	 * Called by WP action wpo_additional_options
	 */
	public function wpo_additional_options() {
		$sites = WP_Optimize()->get_sites();

		$options = WP_Optimize()->get_options();
		$sites_options = $options->get_wpo_sites_option();

		$is_all_selected = (is_array($sites_options) && in_array('all', $sites_options)) ? true : false;
		?>
		<ul id="wpo_settings_sites_list">
			<li>
				<input id="wpo_all_sites" type="checkbox" value="all" <?php checked($is_all_selected, true); ?>>
				<label for="wpo_all_sites" data-label="<?php esc_attr_e('Optimize all sites', 'wp-optimize'); ?>"><?php _e('Optimize all sites', 'wp-optimize'); ?></label>
			</li>
			<li>
				<div id="wpo_sitelist_moreoptions" class="wpo_always_visible">
					<ul>
						<?php
						foreach ($sites as $site) {
							$dom_id = 'site-'.$site->blog_id;
							?>
							<li>
								&nbsp;
								<input id="<?php echo $dom_id; ?>" type="checkbox" name="wpo-sites[]" value="<?php echo $site->blog_id; ?>" <?php checked($is_all_selected || (is_array($sites_options) && in_array($site->blog_id, $sites_options)), true); ?>>
								<label for="<?php echo $dom_id; ?>"><?php echo $site->domain.$site->path . (1 == $site->blog_id ? '<i>('.__('The network-wide tables will be optimized with this site', 'wp-optimize').')</i>' : ''); ?></label>
							</li>
						<?php } ?>
					</ul>
				</div>
			</li>
		</ul>
		<?php
	}

	/**
	 * Add site list options for multisite cron settings
	 * Called by WP action wpo_additional_options_cron
	 */
	public function wpo_additional_options_cron() {
		$sites = WP_Optimize()->get_sites();

		$options       = WP_Optimize()->get_options();
		$sites_options = $options->get_option('wpo-sites-cron', array('all'));

		$is_all_selected = (is_array($sites_options) && in_array('all', $sites_options)) ? true : false;
		?>

		<ul id="wpo_settings_sites_list_cron">
			<li>
				<input id="wpo_all_sites_cron" name="wpo-sites-cron[]" type="checkbox"
					value="all" <?php checked($is_all_selected, true); ?>>
				<label for="wpo_all_sites_cron"
					data-label="<?php esc_attr_e('Optimize all sites', 'wp-optimize'); ?>"><?php _e('Optimize all sites', 'wp-optimize'); ?></label>
				(<a href="#" id="wpo_sitelist_show_moreoptions_cron"
					title="<?php esc_attr_e('Follow this link to view a list of all sites', 'wp-optimize'); ?>">...</a>)
			</li>
			<li>
				<div id="wpo_sitelist_moreoptions_cron" class="wpo_hidden">
					<ul>
						<?php
						foreach ($sites as $site) {
							$dom_id = 'site-cron-' . $site->blog_id;
							?>
							<li>
								<input id="<?php echo $dom_id; ?>" name="wpo-sites-cron[]" type="checkbox"
									value="<?php echo $site->blog_id; ?>" <?php checked($is_all_selected || in_array($site->blog_id, $sites_options), true); ?>>
								<label for="<?php echo $dom_id; ?>"><?php echo $site->domain . $site->path; ?></label>
							</li>
						<?php } ?>
					</ul>
				</div>
			</li>
		</ul>
		<?php
	}

	/**
	 * Check if Premium is present and amend the tab title
	 * If Free, Tab is: Premium / Plugin family
	 * If Premium, Tab is: Plugin family
	 *
	 * @param array $admin_page_tabs all admin page tab in $slug => $title format
	 * @return array $admin_page_tab modified $admin_page_tabs which doesn't havemay_also key element
	 */
	public function check_premium_tab_title($admin_page_tabs) {
		$admin_page_tabs['may_also'] = __('Plugin family', 'wp-optimize');
		return $admin_page_tabs;
	}

	/**
	 * If Free, Page name is: Premium / Plugin family
	 * If Premium, Page name is: Plugin family
	 *
	 * @param array $sub_menu_items all admin page tab in $slug => $title format
	 * @return array
	 */
	public function change_premium_page_title($sub_menu_items) {
		foreach ($sub_menu_items as $index => $menu_item) {
			if (!isset($menu_item['menu_slug'])) continue;
			if ('wpo_mayalso' != $menu_item['menu_slug']) continue;
			$sub_menu_items[$index]['page_title'] = __('Plugin family', 'wp-optimize');
			$sub_menu_items[$index]['menu_title'] = __('Plugin family', 'wp-optimize');
		}
		return $sub_menu_items;
	}

	/**
	 * Get available auto optimizations
	 *
	 * @return array An array of available auto optimizations
	 */
	public static function get_auto_optimizations() {
		$optimizer = WP_Optimize::get_optimizer();
		$optimizations = $optimizer->sort_optimizations($optimizer->get_optimizations());
		$auto_optimizations = array();

		foreach ($optimizations as $id => $optimization) {
			if (empty($optimization->available_for_auto)) continue;
			$auto_optimizations[$id] = array(
				'id' => $id,
				'optimization' => $optimization->get_auto_option_description(),
				'selected' => ''
			);
		}
		return $auto_optimizations;
	}

	/**
	 * Return list of scheduled optimizations.
	 *
	 * @return array
	 */
	public function get_scheduled_optimizations() {
		$options = WP_Optimize::get_options();
		$auto_options = $options->get_option('auto-updated');
		if (!is_array($auto_options)) $auto_options = array();

		return $auto_options;
	}

	/**
	 * An array of schedule types
	 *
	 * @param boolean $placeholder Determines whether placeholder select option should be included or not
	 *
	 * @return array An array of schedule types
	 */
	public static function get_schedule_types($placeholder = false) {
		$schedule_types = array(
			'wpo_once' => __('Once', 'wp-optimize'),
			'wpo_daily' => __('Daily', 'wp-optimize'),
			'wpo_weekly' => __('Weekly', 'wp-optimize'),
			'wpo_fortnightly' => __('Fortnightly', 'wp-optimize'),
			'wpo_monthly' => __('Monthly', 'wp-optimize'),
		);
		if (true === $placeholder) {
			$schedule_types = array_merge(array('' => __('Select schedule', 'wp-optimize')), $schedule_types);
		}
		return $schedule_types;
	}

	/**
	 * An array of week days
	 *
	 * @return array An array of week days
	 */
	public static function get_week_days() {
		$week_days = array();
		global $wp_locale;
		for ($day_index = 1; $day_index < 8; $day_index++) {
			$week_days[$day_index] = $wp_locale->get_weekday($day_index - 1);
		}
		return $week_days;
	}

	/**
	 * Month days
	 *
	 * @return array An array of available days across all months
	 */
	public static function get_days() {
		$days = array();
		for ($day=1; $day<=28; $day++) {
			$days[$day] = $day;
		}
		return $days;
	}

	/**
	 * Add translation strings to existing array
	 *
	 * @param array $translations An array of translation strings
	 *
	 * @return array An array of translation strings
	 */
	public function wpo_js_translations($translations) {
		$premium_translations = array(
			'no_unused_images_found' => __('You have no unused images found.', 'wp-optimize'),
			'no_unused_trash_images_found' => __('You have no unused images in the trash.', 'wp-optimize'),
			'no_registered_image_sizes' => __('You have no registered image sizes.', 'wp-optimize'),
			'no_unsed_image_sizes' => __('You have no unused image sizes.', 'wp-optimize'),
			'deleting_selected_unused_images' => __('Deleting selected unused images...', 'wp-optimize'),
			'moving_selected_unused_images_to_trash' => __('Moving selected unused images to trash...', 'wp-optimize'),
			'deleting_unused_images_from_trash' => __('Deleting unused images from trash...', 'wp-optimize'),
			'restoring_selected_unused_images_from_trash' => __('Restoring selected unused images from trash...', 'wp-optimize'),
			'auto_optimizations' => $this->get_auto_optimizations(),
			'schedule_types' => $this->get_schedule_types(true),
			'week_days' => $this->get_week_days(),
			'week' => array('1st' => __('1st', 'wp-optimize'), '2nd' => __('2nd', 'wp-optimize')),
			'days' => $this->get_days(),
			'select_optimizations' => __('Press to add as many scheduled optimization tasks as you wish.', 'wp-optimize'),
			'select_roles' => __('Select roles', 'wp-optimize'),
			'no_schedule' => __('There are no scheduled tasks.', 'wp-optimize'),
			'note_save_settings' => __('Note: Your settings have changed; remember to save them.', 'wp-optimize'),
			'time' => __('Time', 'wp-optimize'),
			'date' => __('Date', 'wp-optimize'),
			'day' => __('Day', 'wp-optmize'),
			'day_number' => __('Day Number', 'wp-optimize'),
			'inactive' => __('Inactive', 'wp-optimize'),
			'active' => __('Active', 'wp-optimize'),
			'edit' => __('Edit', 'wp-optimize'),
			'remove_task' => __('Remove this task', 'wp-optimize'),
			'fill_all_fields' => __('Before saving, you need to complete the currently incomplete scheduled time settings (or remove them).', 'wp-optimize'),
			'confirm_remove_task' => __('Are you sure you want to remove this scheduled task?', 'wp-optimize'),
			'close_btn' => __('Close', 'wp-optimize'),
			'delete_selected_items_btn' => __('Delete selected items', 'wp-optimize'),
			'view_image_link_text' => __('View image', 'wp-optimize'),
			'trash' => __('Trash', 'wp-optimize'),
			'move_to_trash' => __('Move to trash', 'wp-optimize'),
			'delete' => __('Delete', 'wp-optimize'),
			'permanently_delete' => __('Delete permanently', 'wp-optimize'),
			'restore' => __('Restore', 'wp-optimize'),
			'restore_from_trash' => __('Restore from trash', 'wp-optimize'),
			'no_unused_images' => __('no unused images', 'wp-optimize'),
			'x_of_x_images_loaded' => __('%s of %s images loaded', 'wp-optimize'),
			'cancel_scan' => __('Abort scan', 'wp-optimize'),
			'unused_images_per_page' => apply_filters('wpo_unused_images_per_page', 99),
		);
		return array_merge($translations, $premium_translations);
	}

	public function default_auto_options($new_auto_options) {
		$options = WP_Optimize()->get_options();
		$auto_options = $options->get_option('auto');
		if (empty($auto_options)) {
			$new_auto_options = array();
		}
		return $new_auto_options;
	}

	/**
	 * Schedule cron jobs for image optimization.
	 */
	public function schedule_image_optimization_jobs() {
		add_action('wpo_preload_homepage_images', array($this, 'preload_homepage_images'));
		if (!wp_next_scheduled('wpo_preload_homepage_images')) {
			wp_schedule_event(time(), 'twicedaily', 'wpo_preload_homepage_images');
		}
	}

	/**
	 * Preload images from homepage for current site.
	 */
	public function preload_homepage_images() {
		$images_optimization = WP_Optimize()->get_optimizer()->get_optimization('images');
		// this function load images from homepage and save information to cache.
		$images_optimization->get_homepage_images(get_current_blog_id(), true);
	}

	/**
	 * Return url to FAQ page for the premium version, used by wpo_faq_url
	 *
	 * @return string
	 */
	public function wpo_faq_url() {
		return 'https://getwpo.com/faqs/';
	}

	/**
	 * Include lazy load files if need and add action to output script.
	 */
	public function include_lazy_load() {
		$options = WP_Optimize()->get_options();

		$lazyload_options = wp_parse_args($options->get_option('lazyload'), array('images' => false, 'iframes' => false, 'backgrounds' => false));
		if ($lazyload_options['images'] || $lazyload_options['iframes'] || $lazyload_options['backgrounds']) {

			include_once(WPO_PLUGIN_MAIN_PATH.'/includes/class-wp-optimize-lazy-load.php');

			new WP_Optimize_Lazy_Load();

			add_action('wp_footer', array($this, 'wp_footer'));
		
		}
	}

	/**
	 * Include images trash functionality.
	 */
	public function include_images_trash() {
		include_once(WPO_PLUGIN_MAIN_PATH . '/includes/class-wp-optimize-images-trash-manager.php');
		include_once(WPO_PLUGIN_MAIN_PATH . '/includes/class-wp-optimize-images-trash-manager-commands.php');
		include_once(WPO_PLUGIN_MAIN_PATH . '/includes/class-wp-optimize-images-trash-task.php');

		WP_Optimize_Images_Trash_Manager();
	}

	/**
	 * Show additional settings in the genaral settings page.
	 */
	public function after_general_settings() {
		$this->display_purge_cache_permissions();
	}

	/**
	 * Show purge cache settings permissions.
	 */
	private function display_purge_cache_permissions() {
		$roles = $this->get_wp_roles();
		$permissions = $this->get_purge_cache_permissions();
		foreach ($roles as &$role) {
			$role['selected'] = in_array($role['role'], $permissions);
		}
		WP_Optimize()->include_template('settings/purge-cache-permissions.php', false, array('roles' => $roles));
	}

	/**
	 * Returns list with WordPress user roles who can purge the cache.
	 *
	 * @return array
	 */
	public function get_purge_cache_permissions() {
		$options = WP_Optimize()->get_options();
		return $options->get_option('purge-cache-permissions', array('administrator'));
	}

	/**
	 * Check if current user can purge the cache.
	 *
	 * @return bool
	 */
	public function can_purge_the_cache() {
		global $current_user;

		if (!is_user_logged_in()) return false;

		$permissions = $this->get_purge_cache_permissions();

		foreach ($current_user->roles as $user_role) {
			if (in_array($user_role, $permissions)) return true;
		}

		return false;
	}

	/**
	 * Get list of WordPress roles.
	 *
	 * @return array
	 */
	public function get_wp_roles() {
		$roles = array();
		$wp_roles = get_editable_roles();
		foreach ($wp_roles as $role => $info) {
			$roles[] = array(
				'role' => $role,
				'name' => translate_user_role($info['name']),
			);
		}
		return $roles;
	}

	/**
	 * Include the extra cache functionalities
	 *
	 * @return void
	 */
	private function include_cache_premium() {
		include_once(WPO_PLUGIN_MAIN_PATH.'/cache/class-wpo-cache-premium.php');

		new WPO_Cache_Premium();
	}

	/**
	 * Include the extra cache functionalities
	 *
	 * @return void
	 */
	private function include_power_tweaks() {
		include_once WPO_PLUGIN_MAIN_PATH.'/includes/power-tweaks/class-wp-optimize-power-tweaks.php';

		$this->power_tweaks = new WP_Optimize_Power_Tweaks();
	}

	/**
	 * Check if requested unused images CSV file then return it.
	 */
	public function handle_unused_images_csv() {
		if (isset($_GET['wpo_unused_images_csv'])) {
			$nonce = isset($_REQUEST['_nonce']) ? $_REQUEST['_nonce'] : '';

			if (wp_verify_nonce($nonce, 'wpo_unused_images_csv') && current_user_can('manage_options')) {
				WP_Optimize()->get_optimizer()->get_optimization('images')->output_csv();
			} else {
				die('Security check');
			}
		}
	}
	
	/**
	 * If registered, runs upon the action wp_footer
	 */
	public function wp_footer() {
		$min_or_not_internal = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '-'. str_replace('.', '-', WPO_VERSION). '.min';
		echo '<script>';
		echo file_get_contents(trailingslashit(WPO_PLUGIN_MAIN_PATH) . 'js/wpo-lazy-load' . $min_or_not_internal . '.js');
		echo '</script>';
	}

	/**
	 * Check if there is a known component already providing lazy-load
	 *
	 * @return Array - list of existing components
	 */
	public function lazyload_already_provided_by() {
	
		$provided_by = array();
	
		global $shortname;

		if (function_exists('et_setup_theme') && 'divi' == $shortname) $provided_by[] = 'Divi';
		if (defined('AUTOPTIMIZE_PLUGIN_VERSION')) $provided_by[] = 'Autoptimize';
		
		return $provided_by;
	}
}

}


/**
 * Instantiate premium features class
 *
 * @return null|WP_Optimize_Premium
 */
function WP_Optimize_Premium() {
	wp_clear_scheduled_hook('wpo_cron_event2');
	return WP_Optimize_Premium::instance();
}

require_once(WPO_PLUGIN_MAIN_PATH.'includes/class-wp-optimize-tasks-queue.php');
require_once(WPO_PLUGIN_MAIN_PATH.'/includes/class-wp-optimize-transients-cache.php');

$GLOBALS['wp_optimize_premium'] = WP_Optimize_Premium();

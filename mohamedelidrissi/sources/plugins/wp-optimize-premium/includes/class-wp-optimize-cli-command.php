<?php

if (!defined('WPO_PLUGIN_MAIN_PATH')) die('No direct access allowed');

/**
 * Implements example command.
 */
class WP_Optimize_CLI_Command extends WP_CLI_Command {

	/**
	 * Command line params.
	 *
	 * @var array
	 */
	private $args;

	/**
	 * Controls WP-Optimize. Run 'wp optimize' to get a list of the available subcommands.
	 * Requires PHP 5.3+; but then, so does WP-CLI (The first line appears in the help section of WP_CLI, when running `wp`)
	 *
	 * @param array $args 		command line params.
	 * @param array $assoc_args command line params in associative array.
	 */
	public function __invoke($args, $assoc_args) { // phpcs:ignore PHPCompatibility.FunctionNameRestrictions.NewMagicMethods.__invokeFound

		$this->args = $args;

		// change underscores to hypes in command.
		if (isset($args[0])) {
			$args[0] = str_replace('-', '_', $args[0]);
		}

		if (!empty($args) && is_callable(array($this, $args[0]))) {
			call_user_func(array($this, $args[0]), $assoc_args);
			return;
		}

		WP_CLI::log('usage: wp optimize <command> [--optimization-id=<optimization-id>] [--site-id=<site-id>] [--param1=value1] [--param2=value2] ...');
		WP_CLI::log("\n".__('These are common WP-Optimize commands used in various situations:', 'wp-optimize')."\n");

		$commands = array(
			'version' => __('Display version of WP-Optimize', 'wp-optimize'),
			'sites' => __('Display list of sites in a WP multisite installation.', 'wp-optimize'),
			'optimizations' => __('Display available optimizations', 'wp-optimize'),
			'do-optimization' => __('Do selected optimization', 'wp-optimize'),
			// Page cache
			'cache enable' => __('Enable the page cache', 'wp-optimize'),
			'cache disable' => __('Disable the page cache', 'wp-optimize'),
			'cache purge' => __('Purge contents from the page cache', 'wp-optimize'),
			'cache preload' => __('Preload contents into the page cache', 'wp-optimize'),
			'cache status' => __('Get the current page cache status', 'wp-optimize'),
			// Minification
			'minify enable' => __('Enable minification.', 'wp-optimize'). ' ' .sprintf(__('%s can be used to enable a specific minification feature.', 'wp-optimize'), '--feature=xxx'),
			'minify disable' => __('Disable minification.', 'wp-optimize'). ' ' .sprintf(__('%s can be used to disable a specific minification feature.', 'wp-optimize'), '--feature=xxx'),
			'minify status' => __('Get the current minification status.', 'wp-optimize'),
			'minify regenerate' => __('Regenerate the minified files, and purge any supported page cache.', 'wp-optimize'),
			'minify delete' => __('Removed all created minified files created, and purge any supported page caches.', 'wp-optimize')
		);

		foreach ($commands as $command => $description) {
			WP_CLI::log(sprintf("     %-25s %s", $this->colorize($command, 'bright'), $description));
		}
	}

	/**
	 * Display WP-Optimize version.
	 */
	public function version() {
		WP_CLI::log(WPO_VERSION);
	}

	/**
	 * Display list of optimizations.
	 */
	public function optimizations() {
		$optimizer = WP_Optimize()->get_optimizer();
		$optimizations = $optimizer->sort_optimizations($optimizer->get_optimizations());

		foreach ($optimizations as $id => $optimization) {

			if (false === $optimization->display_in_optimizations_list()) continue;

			// This is an array, with attributes dom_id, activated, settings_label, info; all values are strings.
			$html = $optimization->get_settings_html();

			WP_CLI::log(sprintf("     %-25s %s", $id, $html['settings_label']));
		}
	}

	/**
	 * Display list of sites when on a multisite install
	 */
	public function sites() {
		if (!is_multisite()) {
			WP_CLI::error(__('This command is only available on a WP multisite installation.', 'wp-optimize'));
		}

		$sites = WP_Optimize()->get_sites();

		WP_CLI::log(sprintf("     %-15s %s", __('Site ID', 'wp-optimize'), __('Path', 'wp-optimize')));
		foreach ($sites as $site) {
			WP_CLI::log(sprintf("     %-15s %s", $site->blog_id, $site->domain.$site->path));
		}
	}

	/**
	 * Call do optimization command.
	 *
	 * @param array $assoc_args array with params for optimization, optimization_id item required.
	 */
	public function do_optimization($assoc_args) {

		if (!isset($assoc_args['optimization-id'])) {
			WP_CLI::error(__('Please, select optimization.', 'wp-optimize'));
			return;
		}

		if (isset($assoc_args['site-id'])) {
			$assoc_args['site_id'] = array_values(array_map('trim', explode(',', $assoc_args['site-id'])));
		}

		if (isset($assoc_args['include-ui'])) {
			$assoc_args['include_ui_elements'] = array_values(array_map('trim', explode(',', $assoc_args['include-ui'])));
		} else {
			$assoc_args['include_ui_elements'] = false;
		}

		// save posted parameters in data item to make them available in optimization.
		$assoc_args['data'] = $assoc_args;

		// get array with optimization ids.
		$optimizations_ids = array_values(array_map('trim', explode(',', $assoc_args['optimization-id'])));

		foreach ($optimizations_ids as $optimization_id) {
			$assoc_args['optimization_id'] = $optimization_id;
			$results = $this->get_commands()->do_optimization($assoc_args);

			if (is_wp_error($results)) {
				WP_CLI::error($results);
			} elseif (!empty($results['errors'])) {
				$message = implode("\n", $results['errors']);
				WP_CLI::error($message);
			} else {
				$message = implode("\n", $results['result']->output);
				WP_CLI::success($message);
			}
		}
	}

	/**
	 * Handle cache commands.
	 */
	public function cache() {
		$available_commands = array(
			'enable' => 'enable',
			'disable' => 'disable',
			'purge' => 'purge_page_cache',
			'preload' => 'run_cache_preload_cli',
			'status' => 'get_status_info',
		);

		$command = isset($this->args[1]) ? $this->args[1] : '';

		if (!array_key_exists($command, $available_commands)) {
			WP_CLI::error(__('Undefined command', 'wp-optimize'));
		}

		if (!class_exists('WP_Optimize_Cache_Commands')) include_once(WPO_PLUGIN_MAIN_PATH . 'cache/class-cache-commands.php');
		$cache_commands = new WP_Optimize_Cache_Commands();

		$result = call_user_func(array($cache_commands, $available_commands[$command]));

		if (isset($result['error'])) {
			WP_CLI::error($result['error']);
		}

		WP_CLI::success($result['message']);
	}

	/**
	 * Return instance of WP_Optimize_Commands.
	 *
	 * @return WP_Optimize_Commands
	 */
	private function get_commands() {
		// Other commands, available for any remote method.
		if (!class_exists('WP_Optimize_Commands')) include_once(WPO_PLUGIN_MAIN_PATH.'includes/class-commands.php');

		return new WP_Optimize_Commands();
	}

	/**
	 * Handle minification commands
	 */
	public function minify($params) {
		
		$available_commands = array(
			'enable' => array(
				'description' => __('Enable minification.', 'wp-optimize'). ' ' .sprintf(__('%s can be used to enable a specific minification feature.', 'wp-optimize'), '--feature=xxx')
			),
			'disable' => array(
				'description' => __('Disable minification.', 'wp-optimize'). ' ' .sprintf(__('%s can be used to disable a specific minification feature.', 'wp-optimize'), '--feature=xxx')
			),
			'status' => array(
				'description' => __('Get the current minification status.', 'wp-optimize')
			),
			'regenerate' => array(
				'method_name' => 'purge_minify_cache',
				'description' => __('Regenerate the minified files, and purge any supported page cache.', 'wp-optimize'),
			),
			'delete' => array(
				'method_name' => 'purge_all_minify_cache',
				'description' => __('Removed all created minified files created, and purge any supported page caches.', 'wp-optimize')
			),
		);

		$command = isset($this->args[1]) ? $this->args[1] : '';

		// if no command was specified, send the list of available commands
		if (!$command) {
			WP_CLI::log(__('The following commands are available for the minification feature:', 'wp-optimize'));
			foreach ($available_commands as $command_name => $command_data) {
				WP_CLI::log(sprintf("     %-20s %s", $this->colorize($command_name, 'bright'), $command_data['description']));
			}
			return;
		}

		if (!array_key_exists($command, $available_commands)) {
			WP_CLI::error(__('Undefined command', 'wp-optimize'));
		}

		if (!class_exists('WP_Optimize_Minify_Commands')) include_once(WPO_PLUGIN_MAIN_PATH . 'minify/class-wp-optimize-minify-commands.php');
		$minify_commands = new WP_Optimize_Minify_Commands();

		// Handle activating / deactivating
		if ('enable' === $command || 'disable' === $command) {
			// Enable or disable minify
			if (!isset($params['feature'])) {
				$saved = $minify_commands->save_minify_settings(array('enabled' => 'enable' === $command));
				if ($saved['success']) {
					WP_CLI::success(__('WP-Optimize minification status was successfully changed.', 'wp-optimize'));
				} else {
					WP_CLI::error(__('WP-Optimize minification status could not be changed.', 'wp-optimize'));
				}
				return;
			} else {
				switch($params['feature']) {
					case 'js':
						$saved = $minify_commands->save_minify_settings(array('enable_js' => 'enable' === $command));
						if ($saved['success']) {
							WP_CLI::success(__('JavaScript minification status was successfully changed.', 'wp-optimize'));
						} else {
							WP_CLI::error(__('JavaScript minification status could not be changed.', 'wp-optimize'));
						}
						break;
					case 'css':
						$saved = $minify_commands->save_minify_settings(array('enable_css' => 'enable' === $command));
						if ($saved['success']) {
							WP_CLI::success(__('CSS minification status was successfully changed.', 'wp-optimize'));
						} else {
							WP_CLI::error(__('CSS minification status could not be changed.', 'wp-optimize'));
						}
						break;
					case 'html':
						$saved = $minify_commands->save_minify_settings(array('html_minification' => 'enable' === $command));
						if ($saved['success']) {
							WP_CLI::success(__('HTML minification status was successfully changed.', 'wp-optimize'));
						} else {
							WP_CLI::error(__('HTML minification status could not be changed.', 'wp-optimize'));
						}
						break;
					default:
						WP_CLI::error(sprintf(__('"%s" is not a feature.', 'wp-optimize'), $params['feature']));
						break;
				}
				return;
			}
		}

		// Show the current status
		if ('status' == $command) {
			$status = $minify_commands->get_status();
			WP_CLI::log(__('WP-Optimize minification status:', 'wp-optimize'));
			WP_CLI::log(' - '.sprintf(_x('<inification is %s', '%s is replaced by a colored version of enabled or disabled (WP_CLI)', 'wp-optimize'), ($status['enabled'] ? $this->colorize(__('enabled', 'wp-optimize'), 'green_bright') : $this->colorize(__('disabled', 'wp-optimize'), 'red_bright'))));
			WP_CLI::log(' - '.sprintf(__('JavaScript minification: %s', 'wp-optimize'), ($status['js'] ? $this->colorize(__('ON', 'wp-optimize'), 'green_bright') : $this->colorize(__('OFF', 'wp-optimize'), 'red_bright'))));
			WP_CLI::log(' - '.sprintf(__('CSS minification: %s', 'wp-optimize'), ($status['css'] ? $this->colorize(__('ON', 'wp-optimize'), 'green_bright') : $this->colorize(__('OFF', 'wp-optimize'), 'red_bright'))));
			WP_CLI::log(' - '.sprintf(__('HTML minification: %s', 'wp-optimize'), ($status['html'] ? $this->colorize(__('ON', 'wp-optimize'), 'green_bright') : $this->colorize(__('OFF', 'wp-optimize'), 'red_bright'))));
			WP_CLI::log(' - '.sprintf(__('Size on disk: %s', 'wp-optimize'), $this->colorize($status['stats']['cachesize'], 'bright')));
			WP_CLI::log(' - '.sprintf(__('Cache date: %s', 'wp-optimize'), $this->colorize($status['stats']['cacheTime'], 'bright')));
			WP_CLI::log(' - '.sprintf(__('Number of CSS files: %s', 'wp-optimize'), $this->colorize(count($status['stats']['css']), 'bright')));
			WP_CLI::log(' - '.sprintf(__('Number of JavaScript files: %s', 'wp-optimize'), $this->colorize(count($status['stats']['js']), 'bright')));
			WP_CLI::log(' - '.sprintf(__('Cache path: %s', 'wp-optimize'), $this->colorize(count($status['stats']['cachePath']), 'bright')));
			return;
		}

		// Other commands
		if (isset($available_commands[$command]['method_name']) && method_exists($minify_commands, $available_commands[$command]['method_name'])) {
			$result = call_user_func(array($minify_commands, $available_commands[$command]['method_name']));
			if (isset($result['error'])) {
				WP_CLI::error($result['error']);
			}

			if (isset($result['message'])) {
				WP_CLI::success($result['message']);
			}
		}
	}

	private function colorize($string, $color) {
		$tokens = array(
			'yellow' => '%y', // ['color' => 'yellow',
			'green' => '%g', // ['color' => 'green'],
			'blue' => '%b', // ['color' => 'blue'],
			'red' => '%r', // ['color' => 'red'],
			'magenta' => '%p', // ['color' => 'magenta'],
			'magenta' => '%m', // ['color' => 'magenta',
			'cyan' => '%c', // ['color' => 'cyan',
			'grey' => '%w', // ['color' => 'grey',
			'black' => '%k', // ['color' => 'black',
			'reset' => '%n', // ['color' => 'reset',
			'yellow_bright' => '%Y', // ['color' => 'yellow', 'style' => 'bright',
			'green_bright' => '%G', // ['color' => 'green', 'style' => 'bright',
			'blue_bright' => '%B', // ['color' => 'blue', 'style' => 'bright',
			'red_bright' => '%R', // ['color' => 'red', 'style' => 'bright',
			'magenta_bright' => '%P', // ['color' => 'magenta', 'style' => 'bright',
			'magenta_bright_2' => '%M', // ['color' => 'magenta', 'style' => 'bright',
			'cyan_bright' => '%C', // ['color' => 'cyan', 'style' => 'bright',
			'grey_bright' => '%W', // ['color' => 'grey', 'style' => 'bright',
			'black_bright' => '%K', // ['color' => 'black', 'style' => 'bright',
			'reset_bright' => '%N', // ['color' => 'reset', 'style' => 'bright',
			'yellow_bg' => '%3', // ['background' => 'yellow',
			'green_bg' => '%2', // ['background' => 'green',
			'blue_bg' => '%4', // ['background' => 'blue',
			'red_bg' => '%1', // ['background' => 'red',
			'magenta_bg' => '%5', // ['background' => 'magenta',
			'cyan_bg' => '%6', // ['background' => 'cyan',
			'grey_bg' => '%7', // ['background' => 'grey',
			'black_bg' => '%0', // ['background' => 'black',
			'blink' => '%F', // ['style' => 'blink',
			'underline' => '%U', // ['style' => 'underline',
			'inverse' => '%8', // ['style' => 'inverse',
			'bright' => '%9', // ['style' => 'bright',
			'bright_2' => '%_' // ['style' => 'bright']
		);

		$token = isset($tokens[$color]) ? $tokens[$color] : $tokens['bright'];
		return WP_CLI::colorize($token.$string.'%n');
	}
}

WP_CLI::add_command('optimize', 'WP_Optimize_CLI_Command');

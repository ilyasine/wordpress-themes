<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (class_exists('Updraft_Slack_Logger')) return;

/**
 * Class Updraft_Slack_Logger
 */
class Updraft_Slack_Logger extends Updraft_Abstract_Logger {

	protected $allow_multiple = true;

	/**
	 * Updraft_Slack_Logger constructor
	 *
	 * @param string $webhook_url
	 */
	public function __construct($webhook_url = '') {
		$this->set_webhook_url($webhook_url);
	}

	/**
	 * Set Webhook URL
	 *
	 * @param string $webhook_url Setting the webhook url.
	 */
	public function set_webhook_url($webhook_url) {
		$this->set_option('slack_webhook_url', $webhook_url);
	}

	/**
	 * Get Webhook URL
	 *
	 * @return null
	 */
	public function get_webhook_url() {
		return $this->get_option('slack_webhook_url');
	}

	/**
	 * Returns logger description
	 *
	 * @return string|void
	 */
	public function get_description() {
		return __('Log events into Slack', 'wp-optimize');
	}

	/**
	 * Returns list of logger options.
	 *
	 * @return array
	 */
	public function get_options_list() {
		return array(
			'slack_webhook_url' => array(
				__('Slack webhook URL', 'wp-optimize'),
				'url', // validator
			)
		);
	}

	/**
	 * Emergency message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return null|void
	 */
	public function emergency($message, array $context = array()) {
		$this->log(Updraft_Log_Levels::EMERGENCY, $message, $context);
	}

	/**
	 * Alert message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return null|void
	 */
	public function alert($message, array $context = array()) {
		$this->log(Updraft_Log_Levels::ALERT, $message, $context);
	}

	/**
	 * Critical message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return null|void
	 */
	public function critical($message, array $context = array()) {
		$this->log(Updraft_Log_Levels::CRITICAL, $message, $context);
	}

	/**
	 * Error message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return null|void
	 */
	public function error($message, array $context = array()) {
		$this->log(Updraft_Log_Levels::ERROR, $message, $context);
	}

	/**
	 * Warning message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return null|void
	 */
	public function warning($message, array $context = array()) {
		$this->log(Updraft_Log_Levels::WARNING, $message, $context);
	}

	/**
	 * Notice message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return null|void
	 */
	public function notice($message, array $context = array()) {
		$this->log(Updraft_Log_Levels::NOTICE, $message, $context);
	}

	/**
	 * Info message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return null|void
	 */
	public function info($message, array $context = array()) {
		$this->log(Updraft_Log_Levels::INFO, $message, $context);
	}

	/**
	 * Debug message
	 *
	 * @param  string $message
	 * @param  array  $context
	 * @return null|void
	 */
	public function debug($message, array $context = array()) {
		$this->log(Updraft_Log_Levels::DEBUG, $message, $context);
	}

	/**
	 * Log message with any level
	 *
	 * @param  mixed  $level
	 * @param  string $message
	 * @param  array  $context
	 * @return null|void
	 */
	public function log($level, $message, array $context = array()) {
		if (!$this->is_enabled()) return false;

		$prefix  = '['.Updraft_Log_Levels::to_text($level).']: ';
		$message = $prefix.$this->interpolate($message, $context);
		$this->post_message($message);
	}

	/**
	 * Post message to Slack
	 *
	 * @param  string $message
	 * @return null
	 */
	protected function post_message($message) {
		$webhook_url = $this->get_webhook_url();
		$logger_name = $this->get_option('logger_name', 'Updraft Logger');

		if (!$webhook_url) return false;

		$params = array(
			'username' => $logger_name,
			'text'     => $message,
		);

		wp_remote_post(
			$webhook_url,
			array(
				'body' => array('payload' => json_encode($params)),
			)
		);
	}
}

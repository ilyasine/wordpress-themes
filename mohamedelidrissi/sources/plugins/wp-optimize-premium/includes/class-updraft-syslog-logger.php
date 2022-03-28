<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (class_exists('Updraft_Syslog_Logger')) return;

/**
 * Class Updraft_Syslog_Logger
 */
class Updraft_Syslog_Logger extends Updraft_Abstract_Logger {

	protected $log_ident;

	protected $log_facility;

	protected $syslog = null;

	/**
	 * Updraft_Syslog_Logger constructor
	 *
	 * @param string $log_ident
	 * @param null   $log_facility
	 */
	public function __construct($log_ident = 'updraft-syslog', $log_facility = null) {
		if (!function_exists('openlog') || !function_exists('syslog')) return;

		$this->log_ident    = $log_ident;
		$this->log_facility = (!empty($log_facility) ? $log_facility : LOG_USER);
		$this->syslog       = openlog($this->log_ident, (LOG_ODELAY | LOG_PID), $this->log_facility);
	}

	/**
	 * Returns logger description
	 *
	 * @return string|void
	 */
	public function get_description() {
		return __('Log events in syslog', 'wp-optimize');
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
		if (!$this->is_enabled() || !$this->syslog) return false;

		$message = $this->interpolate($message, $context);
		if ($this->syslog) syslog($this->syslog_level($level), $message);
	}

	/**
	 * Return syslog level constant value by Updraft_Log_Levels level
	 *
	 * @param  string $level
	 * @return integar
	 */
	private function syslog_level($level) {
		switch ($level) {
			case Updraft_Log_Levels::EMERGENCY:
				return LOG_EMERG;
			case Updraft_Log_Levels::ALERT:
				return LOG_ALERT;
			case Updraft_Log_Levels::CRITICAL:
				return LOG_CRIT;
			case Updraft_Log_Levels::ERROR:
				return LOG_ERR;
			case Updraft_Log_Levels::WARNING:
				return LOG_WARNING;
			case Updraft_Log_Levels::NOTICE:
				return LOG_NOTICE;
			case Updraft_Log_Levels::INFO:
				return LOG_INFO;
			case Updraft_Log_Levels::DEBUG:
				return LOG_DEBUG;
		}

		return LOG_INFO;
	}
}

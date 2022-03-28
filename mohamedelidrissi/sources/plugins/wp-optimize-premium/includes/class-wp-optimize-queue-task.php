<?php

if (!defined('ABSPATH')) die('No direct access allowed');

class WP_Optimize_Queue_Task extends Updraft_Task_1_1 {

	private $queue_id;

	/**
	 * Function to run.
	 *
	 * @var array|int
	 */
	public $task;

	/**
	 * Params for $task.
	 *
	 * @var array
	 */
	public $params;

	/**
	 * Function to run after $task done.
	 *
	 * @var mixed
	 */
	public $callback;

	/**
	 * Task priority.
	 *
	 * @var int
	 */
	public $priority;

	/**
	 * WP_Optimize_Queue_Task constructor.
	 *
	 * @param array|int $task
	 * @param array 	$params
	 * @param callable 	$callback
	 * @param int 		$priority
	 */
	public function __construct($task, $params = array(), $callback = '', $priority = 0) {
		$this->queue_id = 'queue';

		if (is_numeric($task)) {
			$this->load($task);
		} else {
			$this->task = $task;
			$this->params = $params;
			$this->callback = $callback;
			$this->priority = $priority;
		}
	}

	/**
	 * Set queue id.
	 *
	 * @param string $queue_id
	 */
	public function set_queue_id($queue_id) {
		$this->queue_id = $queue_id;
	}

	/**
	 * Get queue id.
	 *
	 * @return string
	 */
	public function get_queue_id() {
		return $this->queue_id;
	}

	/**
	 * Run task.
	 */
	public function run() {
		$task = $this->prepare_function($this->task);

		if (is_callable($task)) {
			$result = call_user_func_array($task, $this->params);

			if ('' != $this->callback && is_callable($this->callback)) {
				$callback = $this->prepare_function($this->callback);
				call_user_func($callback, $result);
			}
		}
	}

	/**
	 * Save task to database.
	 *
	 * @return bool|int
	 */
	public function save() {
		global $wpdb;

		// we use one task queue for all users.
		$user_id = 0;
		$description = 'Queue #'.$this->queue_id.' task '.(is_array($this->task) ? join('->', $this->task) : $this->task);

		$sql = $wpdb->prepare("INSERT INTO {$wpdb->base_prefix}tm_tasks (user_id, description, status) VALUES (%d, %s, %s)", $user_id, $description, 'active');

		$wpdb->query($sql);

		$task_id = $wpdb->insert_id;

		if (!$task_id)
			return false;

		$_task = $wpdb->get_row("SELECT * FROM {$wpdb->base_prefix}tm_tasks WHERE id = {$task_id} LIMIT 1");

		if (!$_task)
			return false;

		$this->set_id($task_id);

		Updraft_Task_Meta::update_task_meta($task_id, 'queue_id', $this->queue_id);

		Updraft_Task_Meta::update_task_meta($task_id, 'task', array(
			'task' => $this->task,
			'params' => $this->params,
			'callback' => $this->callback
		));

		Updraft_Task_Meta::update_task_meta($task_id, 'priority', $this->priority);

		return $task_id;
	}

	/**
	 * Load task from database by id.
	 *
	 * @param  int $task_id
	 * @return bool
	 */
	public function load($task_id) {
		global $wpdb;

		$task_info = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->base_prefix}tm_tasks t JOIN {$wpdb->base_prefix}tm_taskmeta tm ON t.id = tm.task_id WHERE t.id=%d;", $task_id), ARRAY_A);

		if (empty($task_info)) return false;

		$this->set_id($task_id);

		foreach ($task_info as $info) {
			if ('task' == $info['meta_key']) {
				$_info = maybe_unserialize($info['meta_value']);

				if (is_array($_info)) {
					foreach ($_info as $key => $value) {
						$this->{$key} = $value;
					}
				}
			} else {
				$this->{$info['meta_key']} = maybe_unserialize($info['meta_value']);
			}
		}
	}

	/**
	 * Return information about task as a string.
	 *
	 * @return string
	 */
	public function get_info() {
		return 'Task: '.(is_array($this->task) ? implode('->', $this->task) : $this->task);
	}

	/**
	 * Get function name or array [className|classObj, methodName], and check if it possible to call statically,
	 * if not possible then create instance of the className.
	 *
	 * @param  array|string $function
	 * @return array
	 */
	private function prepare_function($function) {

		if (is_array($function) && (is_string($function[0]) && is_callable($function))) {
			$reflection_method = new ReflectionMethod($function[0], $function[1]);
			if (!$reflection_method->isStatic()) {
				$function[0] = new $function[0];
			}
		}

		return $function;
	}
}

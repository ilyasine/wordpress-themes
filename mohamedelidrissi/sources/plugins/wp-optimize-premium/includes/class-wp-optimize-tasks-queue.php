<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (!class_exists('Updraft_Abstract_Logger')) require_once(WPO_PLUGIN_MAIN_PATH.'/includes/class-updraft-abstract-logger.php');
if (!class_exists('Updraft_PHP_Logger')) require_once(WPO_PLUGIN_MAIN_PATH.'/includes/class-updraft-php-logger.php');

/**
 * Class WP_Optimize_Tasks_Queue
 */
class WP_Optimize_Tasks_Queue {

	private $_queue_id;

	private $_queue_key;

	private $_queue;

	private $_meta = array();

	private static $_instances = array();

	private $_lock;

	private $_locked = null;

	private $_logger;

	/**
	 * WP_Optimize_Tasks_Queue constructor.
	 *
	 * @param string $queue_id
	 */
	public function __construct($queue_id) {
		$this->_queue_id = $queue_id;
		$this->_queue_key = 'wpo_queue_' . $this->_queue_id;
		$this->_lock = new WP_Optimize_Semaphore($queue_id);
		$this->_logger = new Updraft_PHP_Logger();
	}

	/**
	 * Lock queue.
	 */
	public function lock() {
		$this->log('WP_Optimize_Tasks_Queue->lock()');
		$this->_locked = $this->_lock->lock();
		return $this->_locked;
	}

	/**
	 * Unlock queue.
	 */
	public function unlock() {
		$this->log('WP_Optimize_Tasks_Queue->unlock()');
		$this->_lock->unlock();
		$this->_locked = null;
	}

	/**
	 * Wait until queue will free.
	 */
	public function wait() {
		if ($this->is_locked()) return;

		$this->log('WP_Optimize_Tasks_Queue->wait()');

		$time_start = microtime(true);
		$time_limit = 5; // limit time seconds.

		while (false == $this->lock() && ($time_limit > (microtime(true) - $time_start))) {
			$this->sleep(0.5);
		}
	}

	/**
	 * Returns true if queue is locked.
	 *
	 * @return bool
	 */
	public function is_locked() {
		return (null === $this->_locked || true === $this->_locked);
	}

	/**
	 * Return instance of WP_Optimize_Tasks_Queue.
	 *
	 * @param  string $queue_id
	 * @return object WP_Optimize_Tasks_Queue
	 */
	public static function this($queue_id = 'queue') {
		if (!array_key_exists($queue_id, self::$_instances)) {
			self::$_instances[$queue_id] = new WP_Optimize_Tasks_Queue($queue_id);

			self::$_instances[$queue_id]->load();
		}

		return self::$_instances[$queue_id];
	}

	/**
	 * Delete queue form database.
	 */
	public function delete_queue() {
		global $wpdb;

		$this->log('WP_Optimize_Tasks_Queue->delete_queue()');

		// disable action if queue is not locked.
		if (false == $this->is_locked()) return;

		$tasks_ids = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT(tm.task_id) FROM {$wpdb->base_prefix}tm_taskmeta tm WHERE tm.meta_key='queue_id' AND tm.meta_value=%s", $this->_queue_id));

		if (empty($tasks_ids)) return;

		$tasks_ids = join(',', $tasks_ids);

		$wpdb->query("DELETE t, tm FROM {$wpdb->base_prefix}tm_tasks t JOIN {$wpdb->base_prefix}tm_taskmeta tm ON t.id = tm.task_id WHERE t.id IN ({$tasks_ids})");
	}

	public function clear_queue() {
		$this->log('WP_Optimize_Tasks_Queue->clear_queue()');

		$this->_queue = array();
	}

	/**
	 * Returns tru if queue is empty.
	 *
	 * @return bool
	 */
	public function is_empty() {
		return (0 == $this->length());
	}

	/**
	 * Returns count of tasks in queue.
	 *
	 * @return int
	 */
	public function length() {
		return count($this->_queue);
	}

	/**
	 * Add task to queue.
	 *
	 * @param WP_Optimize_Queue_Task $task
	 */
	public function add_task(WP_Optimize_Queue_Task $task) {
		$this->log('WP_Optimize_Tasks_Queue->add_task({task})', array('task' => $task->get_info()));

		// check if queue locked unsuccessfully, you don't need lock queue.
		if (false == $this->is_locked()) return;

		$task->set_queue_id($this->_queue_id);
		// $task->save();
		$this->_queue[] = $task;
	}

	/**
	 * Load queue tasks form database.
	 */
	public function load() {
		global $wpdb;

		$this->log('WP_Optimize_Tasks_Queue->load()');

		// load tasks info from database.
		$tasks = $wpdb->get_results("SELECT task_id, meta_key, meta_value FROM {$wpdb->base_prefix}tm_taskmeta t ORDER BY task_id", ARRAY_A);

		$_tasks = array();

		// arrange tasks info.
		foreach ($tasks as $info) {
			$task_id = $info['task_id'];
			if (!array_key_exists($task_id, $_tasks)) $_tasks[$task_id] = array();

			if ('task' == $info['meta_key']) {
				$_info = maybe_unserialize($info['meta_value']);

				if (is_array($_info)) {
					foreach ($_info as $key => $value) {
						$_tasks[$task_id][$key] = $value;
					}
				}
			} else {
				$_tasks[$task_id][$info['meta_key']] = maybe_unserialize($info['meta_value']);
			}
		}

		$this->_queue = array();

		// build queue.
		foreach ($_tasks as $task_id => $info) {

			// if unsupported task information then don't create task.
			if (empty($info['task'])) continue;

			$task = new WP_Optimize_Queue_Task($info['task'], $info['params'], $info['callback'], $info['priority']);
			$task->set_id($task_id);
			$task->set_queue_id($this->_queue_id);
			$this->_queue[] = $task;
		}

		// sort queue tasks by priority
		usort($this->_queue, array($this, 'cmp_order'));
	}

	/**
	 * Compare function for ordering tasks in queue by priority.
	 *
	 * @param WP_Optimize_Queue_Task $task_a
	 * @param WP_Optimize_Queue_Task $task_b
	 * @return int
	 */
	public function cmp_order(WP_Optimize_Queue_Task $task_a, WP_Optimize_Queue_Task $task_b) {
		if ($task_a->priority == $task_b->priority) {
			return ($task_a->get_id() < $task_b->get_id()) ? -1 : 1;
		}

		return ($task_a->priority < $task_b->priority) ? -1 : 1;
	}

	/**
	 * Save tasks to database and clear queue.
	 */
	public function flush() {
		if (false == $this->is_locked()) return;

		$this->log('WP_Optimize_Tasks_Queue->flush()');

		// save tasks those not exists in DB.
		foreach ($this->_queue as $task) {
			if (!$task->get_id()) $task->save();
		}

		// clear queue variable.
		$this->clear_queue();
	}

	/**
	 * Returns next task from the queue.
	 *
	 * @return bool|WP_Optimize_Queue_Task
	 */
	public function get_next_task() {
		// global $wpdb;

		$this->log('WP_Optimize_Tasks_Queue->get_next_task()');

		// Find task in queue.

		if (empty($this->_queue)) return false;

		$next_task_i = false;

		foreach ($this->_queue as $i => $task) {
			if (false === $next_task_i || $this->_queue[$next_task_i]->priority > $task->priority) {
				$next_task_i = $i;
			}
		}

		// $task = $wpdb->get_results($wpdb->prepare("SELECT t.task_id FROM {$wpdb->base_prefix}tm_tasks t JOIN {$wpdb->base_prefix}tm_taskmeta tm ON t.task_id = tm.task_id WHERE t.status='active' AND tm.meta_key='priority' AND t.task_id IN (SELECT DISTINCT(tm.task_id) FROM {$wpdb->base_prefix}tm_taskmeta tm WHERE tm.meta_key='queue_id' AND tm.meta_value=%s) ORDER BY tm.meta_value, t.task_id LIMIT 1", $this->_queue_id));

		if (false !== $next_task_i) {
			$next_task = $this->_queue[$next_task_i];
			unset($this->_queue[$next_task_i]);

			return $next_task;
		}

		return false;
	}

	/**
	 * Do next task from queue.
	 */
	public function do_next_task() {
		$this->log('WP_Optimize_Tasks_Queue->do_next_task()');

		// disable action if queue is not locked.
		if (false == $this->is_locked()) {
			// short sleep before next try.
			$this->sleep(0.5);
			$this->lock();
			return;
		}

		$task = $this->get_next_task();

		// do task.
		if (is_object($task) && is_a($task, 'WP_Optimize_Queue_Task')) {

			$this->log('WP_Optimize_Tasks_Queue->run({task})', array('task' => $task->get_info()));
			$task->run();

			$task->delete_meta();
			$task->delete();
		}
	}

	/**
	 * Save meta value for queue.
	 *
	 * @param string $name
	 * @param mixed  $value
	 */
	public function set_meta($name, $value) {
		$this->_meta[$name] = $value;
	}

	/**
	 * Return meta value.
	 *
	 * @param string $name
	 * @return mixed|null
	 */
	public function get_meta($name) {
		if (array_key_exists($name, $this->_meta)) return $this->_meta[$name];

		return null;
	}

	/**
	 * Sleep for $seconds.
	 *
	 * @param float $seconds
	 */
	private function sleep($seconds) {
		$second = 1000000; // microseconds in second.
		usleep(floor($seconds * $second));
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

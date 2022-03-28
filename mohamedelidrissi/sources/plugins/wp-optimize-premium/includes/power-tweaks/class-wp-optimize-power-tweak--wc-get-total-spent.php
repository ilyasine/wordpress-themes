<?php

if (!defined('ABSPATH')) die('No direct access allowed');

class WP_Optimize_Power_Tweak__WC_Get_Total_Spent extends WP_Optimize_Power_Tweak {

	/**
	 * Tweak identifier
	 *
	 * @var string
	 */
	protected $tweak_name = 'wc-get-total-spent';

	/**
	 * FAQ link
	 *
	 * @var string
	 */
	protected $tweak_link = 'https://getwpo.com/faqs/speed-up-woocommerces-get-total-spent-query/';

	/**
	 * Action type (`activate` for recurring, `run` for one-shot actions)
	 *
	 * @var string
	 */
	protected $action_type = 'activate';

	/**
	 * Initialize the tweaks
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get the labels
	 *
	 * @return array
	 */
	public function get_labels() {
		return array(
			'title' => __('Speed up WooCommerce\'s "Get total spent" query', 'wp-optimize'),
			'description' => __('When a customer places their first order, WooCommerce calculates the total spent by running a very slow query.', 'wp-optimize') . ' ' . __('This Power Tweak replaces this query by two separate and much more efficient queries.', 'wp-optimize'),
			'details' => __('Uses the filter `woocommerce_customer_get_total_spent` to calculate the total spent when user meta `_money_spent` is not set.', 'wp-optimize')
		);
	}

	/**
	 * Run the tweak
	 *
	 * @return void
	 */
	public function run() {
		add_filter('woocommerce_customer_get_total_spent', array($this, 'get_total_spent'), 10, 2);
	}

	/**
	 * Test the availability of the tweak
	 *
	 * @return boolean
	 */
	public function test_availability() {
		return function_exists('WC');
	}

	/**
	 * Get the total spent
	 *
	 * @param string|float $spent    - The amount spent
	 * @param object       $customer - The customer object
	 * @return string|float
	 */
	public function get_total_spent($spent, $customer) {
		global $wpdb;
		// If $spent is not an empty string, it was already set, so return it directly.
		if ('' !== $spent) return $spent;
		$orders = wc_get_orders(array(
			'customer_id' => $customer->get_id(),
			'status' => wc_get_is_paid_statuses(),
			'limit' => -1,
			'return' => 'ids'
		));

		$total_spent = $wpdb->get_var(
			"SELECT SUM(meta_value) FROM $wpdb->postmeta"
			." WHERE post_id IN (".implode(',', array_map('absint', $orders)).")"
			." AND meta_key = '_order_total';"
		);
	
		if (is_numeric($total_spent)) {
			update_user_meta($customer->get_id(), '_money_spent', $total_spent);
			return $total_spent;
		}

		return $spent;
	}
}

return new WP_Optimize_Power_Tweak__WC_Get_Total_Spent();

<?php

namespace MatthiasWeb\RealMediaLibrary\folder;

use WP_Query;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Get the count of WP_Query resultset instead of all the rows.
 */
class QueryCount extends \WP_Query {
    /**
     * C'tor.
     *
     * @param string[] $args
     */
    public function __construct($args = []) {
        add_filter('posts_request', [$this, 'posts_request'], 999999);
        add_filter('posts_orderby', [$this, 'posts_orderby'], 999999);
        add_filter('post_limits', [$this, 'post_limits'], 999999);
        add_action('pre_get_posts', [$this, 'pre_get_posts'], 999999);
        parent::__construct($args);
    }
    // Documented in \WP_Query
    public function count() {
        if (isset($this->posts[0])) {
            return $this->posts[0];
        }
        return '';
    }
    // Documented in \WP_Query
    public function posts_request($request) {
        remove_filter(current_filter(), [$this, __FUNCTION__], 999999);
        $sql = \sprintf('SELECT COUNT(*) FROM ( %s ) as t', $request);
        return $sql;
    }
    // Documented in \WP_Query
    public function pre_get_posts($q) {
        $q->query_vars['fields'] = 'ids';
        remove_action(current_filter(), [$this, __FUNCTION__], 999999);
    }
    // Documented in \WP_Query
    public function post_limits($limits) {
        remove_filter(current_filter(), [$this, __FUNCTION__], 999999);
        return '';
    }
    // Documented in \WP_Query
    public function posts_orderby($orderby) {
        remove_filter(current_filter(), [$this, __FUNCTION__], 999999);
        return '';
    }
}

<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Server;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Add `no-store` cache control directive to WP REST API requests.
 *
 * @see https://core.trac.wordpress.org/ticket/51831
 */
class ServiceNoStore {
    const CACHE_CONTROL_HEADER = 'Cache-Control';
    /**
     * Add `no-store` to default content type.
     *
     * @see https://developer.wordpress.org/reference/functions/wp_get_nocache_headers/
     */
    const CACHE_CONTROL_VALUE_NO_STORE = 'no-cache, no-store, must-revalidate, max-age=0';
    private $namespace;
    /**
     * C'tor.
     *
     * @param string $namespace
     * @codeCoverageIgnore
     */
    private function __construct($namespace) {
        $this->namespace = $namespace;
    }
    /**
     * Check if a given REST API should send the nocache headers in general and add a filter
     * to modify the header.
     *
     * @param bool $rest_send_nocache_headers Whether to send no-cache headers.
     */
    public function rest_send_nocache_headers($rest_send_nocache_headers) {
        if ($rest_send_nocache_headers) {
            add_filter('rest_post_dispatch', [$this, 'rest_post_dispatch'], 10, 3);
        }
        return $rest_send_nocache_headers;
    }
    /**
     * Add `no-store` to nocache headers, but only once and only if not already modified through endpoint.
     *
     * @param WP_HTTP_Response $result Result to send to the client. Usually a WP_REST_Response.
     * @param WP_REST_Server $server Server instance.
     * @param WP_REST_Request $request Request used to generate the response.
     */
    public function rest_post_dispatch($result, $server, $request) {
        $headers = $result->get_headers();
        $shouldNamespace = $this->getNamespace();
        // Already set, skip
        if (isset($headers[self::CACHE_CONTROL_HEADER])) {
            return $result;
        }
        // Check if route begins with expected namespace
        if (\substr($request->get_route(), 0, \strlen($shouldNamespace)) === $shouldNamespace) {
            $result->headers[self::CACHE_CONTROL_HEADER] = self::CACHE_CONTROL_VALUE_NO_STORE;
        }
        return $result;
    }
    /**
     * Get namespace.
     *
     * @codeCoverageIgnore
     */
    public function getNamespace() {
        return $this->namespace;
    }
    /**
     * Create and hook a `no-store` service for a given namespace starting with.
     *
     * @param string $namespace Must start with a leading slash!
     */
    public static function hook($namespace) {
        add_filter('rest_send_nocache_headers', [self::instance($namespace), 'rest_send_nocache_headers']);
    }
    /**
     * Get a new instance of ServiceNoStore.
     *
     * @param string $namespace Must start with a leading slash!
     * @return ServiceNoStore
     * @codeCoverageIgnore Instance getter
     */
    public static function instance($namespace) {
        return new \MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\ServiceNoStore($namespace);
    }
}

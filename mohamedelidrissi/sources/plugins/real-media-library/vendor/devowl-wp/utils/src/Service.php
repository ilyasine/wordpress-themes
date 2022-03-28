<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

use WP_REST_Response;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Create a base REST Service needed for boilerplate development. Please do not remove it.
 */
class Service {
    private $core;
    const NOTICE_CORRUPT_REST_API_ID = 'notice-corrupt-rest-api';
    const SECURITY_PLUGINS_BLOCKING_REST_API = [
        'better-wp-security',
        'all-in-one-wp-security-and-firewall',
        'sucuri-scanner',
        'anti-spam',
        'wp-cerber',
        'wp-simple-firewall',
        'wp-hide-security-enhancer',
        'bulletproof-security',
        'disable-json-api',
        'ninjafirewall',
        'hide-my-wp',
        'perfmatters',
        'swift-performance',
        'clearfy',
        'password-protected'
    ];
    /**
     * C'tor.
     *
     * @param PluginReceiver $core
     * @codeCoverageIgnore
     */
    private function __construct($core) {
        $this->core = $core;
    }
    /**
     * Register endpoints.
     */
    public function rest_api_init() {
        $namespace = \MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Service::getNamespace($this->getCore());
        register_rest_route($namespace, '/plugin', [
            'methods' => 'GET',
            'callback' => [$this, 'routePlugin'],
            'permission_callback' => '__return_true'
        ]);
    }
    /**
     * Response for /plugin route.
     */
    public function routePlugin() {
        return new \WP_REST_Response($this->getCore()->getPluginData());
    }
    /**
     * Get core instance.
     *
     * @return PluginReceiver
     * @codeCoverageIgnore
     */
    public function getCore() {
        return $this->core;
    }
    /**
     * Show a notice for `corruptRestApi.tsx`.
     */
    public function admin_notices() {
        if (!isset($GLOBALS[self::NOTICE_CORRUPT_REST_API_ID])) {
            $GLOBALS[self::NOTICE_CORRUPT_REST_API_ID] = \true;
            $securityPlugins = $this->getSecurityPlugins();
            echo \sprintf(
                '<div id="notice-corrupt-rest-api" class="hidden notice notice-warning"><p>%s</p><ul></ul><p>%s</p></div>',
                \sprintf(
                    // translators:
                    __(
                        'One or more WordPress plugins tried to call the WordPress REST API, which failed. Most likely a security plugin%s or a web server configuration disabled the REST API. Please make sure that the following REST API namespaces are reachable to use your plugin without problems:',
                        'devowl-wp-utils'
                    ),
                    \count($securityPlugins) > 0 ? ' <strong>(' . \join(', ', $securityPlugins) . ')</strong>' : ''
                ),
                \sprintf(
                    // translators:
                    __('What is the WordPress REST API and how to enable it? %1$sLearn more%2$s.', 'devowl-wp-utils'),
                    '<a href="' .
                        esc_attr(
                            __(
                                'https://devowl.io/knowledge-base/wordpress-rest-api-does-not-respond/',
                                'devowl-wp-utils'
                            )
                        ) .
                        '" target="_blank" rel="noreferrer">',
                    '</a>'
                )
            );
        }
    }
    /**
     * Get all active security plugins which can limit the WP REST API.
     *
     * @return string[]
     */
    public function getSecurityPlugins() {
        $result = [];
        $plugins = get_option('active_plugins');
        // @codeCoverageIgnoreStart
        if (!\defined('PHPUNIT_FILE')) {
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
        }
        // @codeCoverageIgnoreEnd
        foreach ($plugins as $pluginFile) {
            foreach (self::SECURITY_PLUGINS_BLOCKING_REST_API as $slug) {
                if (\strpos($pluginFile, $slug, 0) === 0) {
                    $result[] = get_plugin_data(\constant('WP_PLUGIN_DIR') . '/' . $pluginFile)['Name'];
                }
            }
        }
        return $result;
    }
    /**
     * Get the wp-json URL for a defined REST service.
     *
     * @param string $instance The plugin class instance, so we can determine the slug from
     * @param string $namespace The prefix for REST service
     * @param string $endpoint The path appended to the prefix
     * @return string Example: https://wordpress.org/wp-json
     */
    public static function getUrl($instance, $namespace = null, $endpoint = '') {
        $path =
            ($namespace === null
                ? \MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Service::getNamespace($instance)
                : $namespace) .
            '/' .
            $endpoint;
        return rest_url($path);
    }
    /**
     * Get the default namespace of this plugin generated from the slug.
     *
     * @param mixed $instance The plugin class instance, so we can determine the slug from
     * @param string $version The version used for this namespace
     * @return string
     */
    public static function getNamespace($instance, $version = 'v1') {
        $slug = $instance->getPluginConstant(
            \MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\PluginReceiver::$PLUGIN_CONST_SLUG
        );
        $slug = \preg_replace('/-(lite|pro)$/', '', $slug);
        return $slug . '/' . $version;
    }
    /**
     * Get the (backend) API URL for the current running environment for another container.
     *
     * @param int $developmentPort E.g. 10004
     * @param string $reviewPrefix E.g. commerce-
     * @param string $productionUrl E.g. https://commerce.devowl.io/
     * @codeCoverageIgnore Ignored due to the fact that it depends on too much server variables
     */
    public static function getExternalContainerUrl($developmentPort, $reviewPrefix, $productionUrl) {
        if (\defined('DEVOWL_WP_DEV') && \constant('DEVOWL_WP_DEV')) {
            // It is definitively in our development stack (local, traefik)
            $protocol = \strtolower(\substr($_SERVER['SERVER_PROTOCOL'], 0, 5)) === 'https' ? 'https' : 'http';
            if ($_SERVER['SERVER_PORT'] === \strval(10000)) {
                // Development stack is running in containers with exposed ports
                return $protocol . '://' . $_SERVER['SERVER_NAME'] . ':' . $developmentPort . '/';
            } else {
                // Review applications are running in via `nip.io` and traefik
                return $protocol . '://' . $reviewPrefix . $_SERVER['SERVER_NAME'] . '/';
            }
        } else {
            return $productionUrl;
        }
    }
    /**
     * Get a new instance of Service.
     *
     * @param PluginReceiver $core
     * @return Service
     * @codeCoverageIgnore Instance getter
     */
    public static function instance($core) {
        return new \MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Service($core);
    }
}

<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\Freemium;

use Exception;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Base;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
trait CorePro {
    // Documented in ICore
    public function overrideConstructFreemium() {
        add_filter('http_request_args', [$this, 'http_request_args_lite'], 10, 2);
        add_filter('site_transient_update_plugins', [$this, 'site_transient_update_plugins']);
        add_filter('plugin_row_meta', [$this, 'plugin_row_meta_lite'], 10, 2);
        /**
         * This trait always needs to be used along with base trait.
         *
         * @var Base
         */
        $base = $this;
        register_activation_hook(plugin_basename($base->getPluginConstant('FILE')), [$this, 'deactivate_lite_version']);
    }
    /**
     * Deactivate lite version of this plugin. Warning: You need also implement this functionality in your
     * `feedback-already.php` file to avoid sorting issues on Windows servers!
     */
    public function deactivate_lite_version() {
        /**
         * This trait always needs to be used along with base trait.
         *
         * @var Base
         */
        $base = $this;
        $currentSlug = plugin_basename($base->getPluginConstant('FILE'));
        $liteSlug =
            $base->getPluginConstant(
                \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\Freemium\FreemiumProvider::$PLUGIN_CONST_SLUG_LITE
            ) . '/index.php';
        if ($currentSlug !== $liteSlug) {
            deactivate_plugins($liteSlug);
        }
    }
    /**
     * We want to know the lite version so it can be shown right to the pro version.
     *
     * @param array $args
     * @param string $url
     * @return array
     * @see https://developer.wordpress.org/reference/functions/wp_update_plugins/
     */
    public function http_request_args_lite($args, $url) {
        /**
         * This trait always needs to be used along with base trait.
         *
         * @var Base
         */
        $base = $this;
        if (set_url_scheme($url, 'http') === 'http://api.wordpress.org/plugins/update-check/1.1/') {
            $basename = plugin_basename($base->getPluginConstant('FILE'));
            $liteSlug =
                $base->getPluginConstant(
                    \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\Freemium\FreemiumProvider::$PLUGIN_CONST_SLUG_LITE
                ) . '/index.php';
            $plugins = \json_decode($args['body']['plugins'], ARRAY_A);
            // We are sure that our plugin is in it, but recheck...
            if (isset($plugins['plugins'][$basename])) {
                // Create the installed plugin (do not replace)
                $lite = $plugins['plugins'][$basename];
                $lite['Title'] .= ' (Free)';
                $lite['Name'] .= ' (Free)';
                $plugins['plugins'][$liteSlug] = $lite;
                // Create the active plugin (do not replace)
                $plugins['active'][] = $liteSlug;
                // Write to the arguments so it is sent to the wp.org server
                $args['body']['plugins'] = \json_encode($plugins);
            }
        }
        return $args;
    }
    /**
     * As we do need the current lite version for debugging purposes, we should definitely show
     * only the installed lite version in the plugin row meta. So, we need to filter out the lite version.
     *
     * @param object $update_plugins
     */
    public function site_transient_update_plugins($update_plugins) {
        if (
            $update_plugins !== \false &&
            \is_object($update_plugins) &&
            isset($update_plugins->response, $update_plugins->no_update)
        ) {
            /**
             * This trait always needs to be used along with base trait.
             *
             * @var Base
             */
            $base = $this;
            $liteSlug =
                $base->getPluginConstant(
                    \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\Freemium\FreemiumProvider::$PLUGIN_CONST_SLUG_LITE
                ) . '/index.php';
            $response = &$update_plugins->response;
            // Lite version has update
            if (isset($response[$liteSlug])) {
                unset($response[$liteSlug]);
            }
        }
        return $update_plugins;
    }
    /**
     * Add an "Lite version" title to the plugin row links. For debugging purposes.
     *
     * @param string[] $links
     * @param string $file
     * @return string[]
     */
    public function plugin_row_meta_lite($links, $file) {
        /**
         * This trait always needs to be used along with base trait.
         *
         * @var Base
         */
        $base = $this;
        // Completely ignore errors
        try {
            if (\false !== \strpos($file, plugin_basename($base->getPluginConstant('FILE')))) {
                remove_filter('site_transient_update_plugins', [$this, 'site_transient_update_plugins']);
                $update_plugins = get_site_transient('update_plugins');
                add_filter('site_transient_update_plugins', [$this, 'site_transient_update_plugins']);
                if (
                    $update_plugins !== \false &&
                    \is_object($update_plugins) &&
                    isset($update_plugins->response, $update_plugins->no_update)
                ) {
                    $liteSlug =
                        $base->getPluginConstant(
                            \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\Freemium\FreemiumProvider::$PLUGIN_CONST_SLUG_LITE
                        ) . '/index.php';
                    $no_update = $update_plugins->no_update;
                    $response = $update_plugins->response;
                    $version = 'n/a';
                    // Lite version has update
                    if (isset($response[$liteSlug])) {
                        $version = $response[$liteSlug]->new_version;
                    }
                    // No update, still show the current lite version
                    if (isset($no_update[$liteSlug])) {
                        $version = $no_update[$liteSlug]->new_version;
                    }
                    // Use tooltip in first version string
                    $links[0] = '<span title="Lite version is currently ' . $version . '">' . $links[0] . '</span>';
                }
            }
        } catch (\Exception $e) {
            // Silence is golden.
        }
        return $links;
    }
    // Documented in ICore
    public function isLiteNoticeDismissed($set = null) {
        return \true;
    }
}

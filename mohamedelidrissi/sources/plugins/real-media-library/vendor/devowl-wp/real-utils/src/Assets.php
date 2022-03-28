<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils;

use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Assets as UtilsAssets;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Assets handling.
 */
class Assets {
    use UtilsProvider;
    use UtilsAssets;
    /**
     * C'tor.
     *
     * @codeCoverageIgnore
     */
    private function __construct() {
        // Silence is golden.
    }
    /**
     * Localize the plugin with additional options.
     *
     * @param string $context
     * @return array
     */
    public function overrideLocalizeScript($context) {
        $ratingHandler = \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()->getRatingHandler();
        // Get names of plugins
        $names = [];
        foreach (
            \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()->getInitiators()
            as $initiator
        ) {
            $names[$initiator->getPluginSlug()] = get_plugin_data($initiator->getPluginFile())['Name'];
        }
        return [
            'canBeRated' => $ratingHandler->getCanBeRated(),
            'rateLinks' => $ratingHandler->getLinks(),
            'cross' => \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()
                ->getCrossSellingHandler()
                ->getAvailable(),
            'names' => $names
        ];
    }
    /**
     * Enqueue scripts and styles depending on the type. This function is called
     * from both admin_enqueue_scripts and wp_enqueue_scripts. You can check the
     * type through the $type parameter. In this function you can include your
     * external libraries from public/lib, too.
     *
     * Note: The scripts are loaded only on backend (`admin_enqueue_scripts`). If your plugin
     * is also loaded on frontend you need to make sure to enqueue via `wp_enqueue_scripts`, too.
     * See also https://app.clickup.com/t/4rknyh for more information about this (commits).
     *
     * @param string $type The type (see Assets constants)
     * @param string $hook_suffix The current admin page
     */
    public function enqueue_scripts_and_styles($type, $hook_suffix = null) {
        if ($type !== 'admin_enqueue_scripts') {
            return;
        }
        $handle = $this->enqueueHelper();
        $this->enqueueFeedback();
        $this->enqueueWelcome();
        $this->enqueueCrossSelling();
        // Localize jQuery as it is surely enqueued already and before our scripts
        wp_localize_script($handle, REAL_UTILS_SLUG_CAMELCASE, $this->localizeScript($this));
    }
    /**
     * Enqueue script for plugins page so "Deactivate" opens a popup.
     *
     * @deprecated Use package `real-product-manager-wp-client` instead
     */
    protected function enqueueFeedback() {
        $screen = \function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen !== null && $screen->id === 'plugins') {
            $assets = $this->getFirstAssetsToEnqueueComposer();
            $scriptDeps = $assets->enqueueUtils();
            \array_push($scriptDeps, 'wp-pointer');
            $assets->enqueueComposerScript(REAL_UTILS_SLUG, $scriptDeps, 'feedback.js');
            $assets->enqueueComposerStyle(REAL_UTILS_SLUG, [], 'feedback.css');
        }
    }
    /**
     * Enqueue script for welcome page.
     */
    protected function enqueueWelcome() {
        $initiator = $this->isSomeWelcomePage();
        if ($initiator) {
            $assets = $initiator->getPluginAssets();
            $scriptDeps = $assets->enqueueUtils();
            $assets->enqueueComposerScript(REAL_UTILS_SLUG, $scriptDeps, 'welcome.js');
            $assets->enqueueComposerStyle(REAL_UTILS_SLUG, [], 'welcome.css');
            wp_enqueue_script('updates');
            wp_enqueue_script('plugin-install');
        }
    }
    /**
     * Enqueue cross selling script if possible.
     */
    protected function enqueueCrossSelling() {
        if (
            \count(
                \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()
                    ->getCrossSellingHandler()
                    ->getAvailable()
            ) > 0
        ) {
            $assets = $this->getFirstAssetsToEnqueueComposer();
            $scriptDeps = $assets->enqueueUtils();
            \array_push($scriptDeps, 'wp-pointer');
            $assets->enqueueComposerScript(REAL_UTILS_SLUG, $scriptDeps, 'cross.js');
            $assets->enqueueComposerStyle(REAL_UTILS_SLUG, ['wp-pointer'], 'cross.css');
        }
    }
    /**
     * Enqueue helper from each initiator until one valid is found.
     */
    protected function enqueueHelper() {
        $assets = $this->getFirstAssetsToEnqueueComposer();
        $enqueuePointer =
            \count(
                \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()
                    ->getRatingHandler()
                    ->getCanBeRated()
            ) > 0;
        $handle = $assets->enqueueComposerScript(
            REAL_UTILS_SLUG,
            $enqueuePointer ? ['wp-pointer', REAL_UTILS_ROOT_SLUG . '-utils'] : [REAL_UTILS_ROOT_SLUG . '-utils'],
            'helper.js'
        );
        $assets->enqueueComposerStyle(REAL_UTILS_SLUG, [], 'helper.css');
        if (!empty($handle)) {
            // Do not enqueue until required
            wp_dequeue_script($handle);
            wp_dequeue_style($handle);
        }
        return $handle;
    }
    /**
     * Get first found instance of utils' Assets class. This is needed to we can enqueue assets from their.
     */
    public function getFirstAssetsToEnqueueComposer() {
        foreach (
            \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()->getInitiators()
            as $initiator
        ) {
            $assets = $initiator->getPluginAssets();
            if (isset($assets::$ASSETS_BUMP) && $assets::$ASSETS_BUMP >= 4) {
                return $assets;
            }
        }
    }
    /**
     * Get initiator if the current page is a welcome page.
     */
    public function isSomeWelcomePage() {
        foreach (
            \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()->getInitiators()
            as $initiator
        ) {
            if ($initiator->getWelcomePage()->isCurrentPage()) {
                return $initiator;
            }
        }
    }
    /**
     * New instance.
     *
     * @codeCoverageIgnore
     */
    public static function instance() {
        return new \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Assets();
    }
}

<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils;

use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Base;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Assets;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This is the main class. You need to create an own class extending from
 * this one to initiate the ads system. The configuration is done by an
 * abstract schema. That means, all configurations need to be implemented through
 * methods.
 */
abstract class AbstractInitiator {
    // use UtilsProvider; Never do this, because the extended class needs to use their plugins' UtilsProvider
    /**
     * Welcome page.
     *
     * @var WelcomePage
     */
    private $welcomePage;
    /**
     * Get the plugin's base instance. It is needed so our initiator can
     * access dynamically constants and configurations.
     *
     * @return Base
     */
    abstract public function getPluginBase();
    /**
     * Get the plugin's assets instance. It is need to enqueue scripts and styles.
     *
     * @return Assets
     */
    abstract public function getPluginAssets();
    /**
     * Get link to rate the plugin.
     *
     * @return string
     */
    abstract public function getRateLink();
    /**
     * Get three key features. See example implementation in real plugins for more information.
     *
     * @return array[]
     */
    abstract function getKeyFeatures();
    /**
     * Get the hero button link [0] and text [1] for the welcome page.
     *
     * @return string[]|null
     * @codeCoverageIgnore
     */
    public function getHeroButton() {
        return null;
    }
    /**
     * Get the image height for the welcome page key features.
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getWelcomePageImageHeight() {
        return 200;
    }
    /**
     * Get support link. Visible in welcome page.
     *
     * @codeCoverageIgnore
     */
    public function getSupportLink() {
        return 'https://devowl.io/support/';
    }
    /**
     * Get the timestamp when the next rating popup should be shown.
     *
     * @param boolean $isFirstTime This is true if the popup should be shown the first time
     * @codeCoverageIgnore
     */
    public function getNextRatingPopup($isFirstTime) {
        return \strtotime('+30 days');
    }
    /**
     * Get the external URL to assets. The default implementation relies on
     * "wp-{TD}". Why TD? The text domain is currently always the same as the slug,
     * even if we are using the lite version. Please ensure a trailing slash, if you override it!
     *
     * @param string $path
     */
    public function getAssetsUrl($path = '') {
        return \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()->getBaseAssetsUrl(
            \sprintf('wp-%s/%s', $this->getPluginBase()->getPluginConstant('TD'), $path)
        );
    }
    /**
     * Initialize all available things depending on the configuration.
     */
    public function start() {
        \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()->addInitiator($this);
        $this->welcomePage = \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\WelcomePage::instance($this);
        add_filter('plugin_row_meta', [$this->welcomePage, 'plugin_row_meta'], 10, 2);
        add_action('activated_plugin', [$this->welcomePage, 'activated_plugin'], \PHP_INT_MAX);
        add_action('admin_menu', [$this->welcomePage, 'admin_menu']);
    }
    // Self-explaining
    public function getPluginSlug() {
        return $this->getPluginBase()->getPluginConstant('SLUG');
    }
    // Self-explaining
    public function getPluginFile() {
        return $this->getPluginBase()->getPluginConstant('FILE');
    }
    /**
     * Get welcome page.
     *
     * @codeCoverageIgnore
     */
    public function getWelcomePage() {
        return $this->welcomePage;
    }
}

<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Cross-selling handler.
 */
class CrossSellingHandler {
    use UtilsProvider;
    const ALL_VERSIONS = [
        // Pro versions
        'real-thumbnail-generator/index.php',
        \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\CrossRealCategoryLibrary::FILE_PRO,
        \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\CrossRealPhysicalMedia::FILE_PRO,
        \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\CrossRealMediaLibrary::FILE_PRO,
        \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\CrossRealCookieBanner::FILE_PRO,
        // Lite versions
        'real-thumbnail-generator-lite/index.php',
        \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\CrossRealCategoryLibrary::FILE_LITE,
        \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\CrossRealMediaLibrary::FILE_LITE,
        \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\CrossRealCookieBanner::FILE_LITE
    ];
    const PRO_VERSIONS = [
        'real-thumbnail-generator/index.php',
        \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\CrossRealCategoryLibrary::FILE_PRO,
        \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\CrossRealPhysicalMedia::FILE_PRO,
        \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\CrossRealMediaLibrary::FILE_PRO,
        \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\CrossRealCookieBanner::FILE_PRO
    ];
    /**
     * C'tor.
     *
     * @codeCoverageIgnore
     */
    private function __construct() {
        // Silence is golden.
    }
    /**
     * Check if cross-selling pointers can be shown.
     *
     * @codeCoverageIgnore
     */
    public function canBeShown() {
        // We have decided to (temporarily) deactivate cross selling, see also https://app.clickup.com/t/ajyaar
        return \false;
        /* if (!Options::isCrossSellingActive() || $this->isDeactivatedThroughEdgeCase()) {
                    return false;
                }
        
                $ts = intval(
                    TransientHandler::get(
                        TransientHandler::TRANSIENT_INITIATOR_CROSS,
                        TransientHandler::TRANSIENT_NEXT_CROSS_SELLING,
                        0
                    )
                );
        
                // Set initial value
                if ($ts === 0) {
                    $ts = strtotime('+7 days');
                    TransientHandler::set(
                        TransientHandler::TRANSIENT_INITIATOR_CROSS,
                        TransientHandler::TRANSIENT_NEXT_CROSS_SELLING,
                        $ts
                    );
                }
        
                return time() >= $ts; */
    }
    /**
     * Check if a plugin is installed (not depending on active status).
     *
     * @param string $file Main plugin file, e. g. real-media-library/index.php
     * @param boolean $returnFile
     */
    public function isInstalled($file, $returnFile = \false) {
        $plugins = get_plugins();
        if (isset($plugins[$file])) {
            return $returnFile ? \constant('WP_PLUGIN_DIR') . '/' . $file : \true;
        }
        // Fallback to folder-name only
        $keys = \array_keys($plugins);
        foreach ($keys as $pluginKey) {
            if (\substr($pluginKey, 0, \strlen($file . '/')) === $file . '/') {
                return $returnFile ? \constant('WP_PLUGIN_DIR') . '/' . $pluginKey : \true;
            }
        }
        return \false;
    }
    /**
     * Check if any pro version of us is installed.
     */
    public function isAnyProInstalled() {
        foreach (self::PRO_VERSIONS as $file) {
            if ($this->isInstalled($file)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Check if cross-selling is deactivated through a given Edge case. E. g.
     * do not show any cross-sellings when "Real Cookie Banner" is the only plugin active.
     */
    public function isDeactivatedThroughEdgeCase() {
        $our = $this->getOurPluginsInstalled();
        $rcb = 'real-cookie-banner';
        $foundNonRcb = \false;
        foreach ($our as $file) {
            if (\substr($file, 0, \strlen($rcb)) !== $rcb) {
                $foundNonRcb = \true;
            }
        }
        return !$foundNonRcb;
    }
    /**
     * Get an array of our installed plugins.
     */
    public function getOurPluginsInstalled() {
        $result = [];
        foreach (self::ALL_VERSIONS as $file) {
            if ($this->isInstalled($file)) {
                $result[] = $file;
            }
        }
        return $result;
    }
    /**
     * Localize frontend only when needed.
     */
    public function getAvailable() {
        if (!$this->canBeShown()) {
            return [];
        }
        $result = [];
        foreach (
            \array_values(\MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()->getCrossSellings())
            as $product
        ) {
            if (!$product->skip()) {
                $meta = $product->getMeta();
                // Append dismissed count so the "Never show again" checkbox can be shown
                foreach ($meta as $action => &$value) {
                    // Check if this action is skipped and should not be showed again
                    if ($product->forceHide($action)) {
                        unset($meta[$action]);
                        continue;
                    }
                    $value['link'] = add_query_arg('feature', $action, $value['link']);
                    $value['dismissed'] = $product->actionCounter($action);
                }
                if (\count($meta) > 0) {
                    $result[$product->getSlug()] = $meta;
                }
            }
        }
        return $result;
    }
    /**
     * New instance.
     *
     * @codeCoverageIgnore
     */
    public static function instance() {
        return new \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\CrossSellingHandler();
    }
}

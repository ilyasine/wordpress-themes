<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils;

use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Base;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Let our package act as own "plugin".
 */
trait UtilsProvider {
    use Base;
    /**
     * Get the prefix of this package so we can utils package natively.
     *
     * @return string
     */
    public function getPluginConstantPrefix() {
        self::setupConstants();
        return 'REAL_UTILS';
    }
    /**
     * Make sure the REAL_UTILS constants are available.
     */
    public static function setupConstants() {
        if (\defined('REAL_UTILS_SLUG')) {
            return;
        }
        \define('REAL_UTILS_SLUG', 'real-utils');
        \define('REAL_UTILS_ROOT_SLUG', 'devowl-wp');
        \define('REAL_UTILS_TD', REAL_UTILS_ROOT_SLUG . '-' . REAL_UTILS_SLUG);
        \define('REAL_UTILS_SLUG_CAMELCASE', \lcfirst(\str_replace('-', '', \ucwords(REAL_UTILS_SLUG, '-'))));
        \define('REAL_UTILS_VERSION', \filemtime(__FILE__));
        // as we do serve assets through the consumer plugin we can safely use file modified time
    }
}

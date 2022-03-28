<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\Freemium;

use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Base;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request.
// @codeCoverageIgnoreEnd
/**
 * Extends the UtilsProvider with freemium provider.
 */
trait FreemiumProvider {
    public static $PLUGIN_CONST_IS_PRO = 'IS_PRO';
    public static $PLUGIN_CONST_OVERRIDES_INC = 'OVERRIDES_INC';
    public static $PLUGIN_CONST_SLUG_LITE = 'SLUG_LITE';
    public static $PLUGIN_CONST_PRO_VERSION = 'PRO_VERSION';
    /**
     * Is the current using plugin Pro version?
     *
     * @return boolean
     */
    public function isPro() {
        /**
         * This trait always needs to be used along with base trait.
         *
         * @var Base
         */
        $base = $this;
        return $base->getPluginConstant(self::$PLUGIN_CONST_IS_PRO);
    }
}

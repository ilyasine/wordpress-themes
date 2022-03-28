<?php

namespace MatthiasWeb\RealMediaLibrary;

use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Localization as UtilsLocalization;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * i18n management for backend and frontend.
 */
class Localization {
    use UtilsProvider;
    use UtilsLocalization;
    /**
     * Get the directory where the languages folder exists.
     *
     * @param string $type
     * @return string[]
     */
    protected function getPackageInfo($type) {
        if ($type === \MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Localization::$PACKAGE_INFO_BACKEND) {
            return [path_join(RML_PATH, 'languages'), RML_TD];
        } else {
            return [path_join(RML_PATH, \MatthiasWeb\RealMediaLibrary\Assets::$PUBLIC_JSON_I18N), RML_TD];
        }
    }
}

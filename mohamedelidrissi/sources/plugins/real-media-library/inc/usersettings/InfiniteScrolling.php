<?php

namespace MatthiasWeb\RealMediaLibrary\usersettings;

use MatthiasWeb\RealMediaLibrary\api\IUserSettings;
use MatthiasWeb\RealMediaLibrary\attachment\Filter;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use WP_Query;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Add an option so the user can disable the WordPress infinite scrolling.
 *
 * @see https://core.trac.wordpress.org/ticket/40330
 */
class InfiniteScrolling implements \MatthiasWeb\RealMediaLibrary\api\IUserSettings {
    use CommonUserSettingsTrait;
    use UtilsProvider;
    const MIN_WP_VERSION = '5.8';
    const FIELD_NAME = 'infiniteScrolling';
    const OPTION_NAME = 'rmlInfiniteScrolling';
    /**
     * C'tor.
     */
    public function __construct() {
        if (self::isEnabled()) {
            add_filter('media_library_infinite_scrolling', '__return_true');
        }
    }
    // Documented in IMetadata
    public function content($content, $user) {
        $content .=
            '<label><input name="' .
            self::FIELD_NAME .
            '" type="checkbox" value="1" ' .
            checked(1, self::isEnabled(), \false) .
            ' /> ' .
            __('Enable infinite scrolling in grid view', RML_TD) .
            '</label>
            <p class="description">' .
            __(
                'Instead of displaying a "Load More" button, all files are loaded automatically as you scroll down. This is not supported in list view.',
                RML_TD
            ) .
            '</p>';
        return $content;
    }
    // Documented in IMetadata
    public function save($response, $user, $request) {
        $param = $request->get_param(self::FIELD_NAME);
        if (self::isEnabled($param === '1') !== \false) {
            $this->hardReloadAfterSaveIfBodyHasClass($response, 'upload-php');
        }
        return $response;
    }
    // Documented in IMetadata
    public function scripts($assets) {
        // Silence is golden.
    }
    /**
     * Check if this setting should be available to the current WordPress instance.
     */
    public function isAvailable() {
        global $wp_version;
        return \version_compare($wp_version, self::MIN_WP_VERSION, '>=');
    }
    // Documented in CommonUserSettingsTrait
    public static function isEnabled($persist = null) {
        return self::is(self::OPTION_NAME, $persist);
    }
}

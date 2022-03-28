<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\view;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core;
use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Add real-utils specific options to frontend.
 */
class Options {
    use UtilsProvider;
    const FIELD_NAME_CROSS_SELLING = 'real-utils-cross-selling';
    /**
     * C'tor.
     *
     * @codeCoverageIgnore
     */
    private function __construct() {
        // Silence is golden.
    }
    /**
     * Register settings.
     */
    public function admin_init() {
        if (
            \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()
                ->getCrossSellingHandler()
                ->isAnyProInstalled() &&
            current_user_can('activate_plugins')
        ) {
            add_option(self::FIELD_NAME_CROSS_SELLING, \true);
            register_setting('general', self::FIELD_NAME_CROSS_SELLING, ['type' => 'boolean']);
            add_settings_field(
                self::FIELD_NAME_CROSS_SELLING,
                '<label for="' .
                    self::FIELD_NAME_CROSS_SELLING .
                    '">' .
                    \sprintf(
                        // translators:
                        __('Products of %s', REAL_UTILS_TD),
                        '<a href="https://devowl.io/" target="_blank">devowl.io</a>'
                    ) .
                    '</label>',
                [$this, 'html_cross_selling'],
                'general'
            );
        }
    }
    /**
     * Allow to deactivate real-utils cross-selling functionality.
     */
    public function html_cross_selling() {
        echo '<label>
    <input type="checkbox" name="' .
            self::FIELD_NAME_CROSS_SELLING .
            '" ' .
            checked(self::isCrossSellingActive(), \true, \false) .
            ' value="1" />
    ' .
            \sprintf(
                // translators:
                __('Show advertising for not yet installed %s products in the WordPress backend', REAL_UTILS_TD),
                '<a href="https://devowl.io/" target="_blank">devowl.io</a>'
            ) .
            '
</label>';
    }
    /**
     * Check if cross-selling is activated.
     *
     * @return boolean
     */
    public static function isCrossSellingActive() {
        if (
            \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()
                ->getCrossSellingHandler()
                ->isAnyProInstalled()
        ) {
            return get_option(self::FIELD_NAME_CROSS_SELLING);
        }
        return \true;
    }
    /**
     * New instance.
     *
     * @codeCoverageIgnore
     */
    public static function instance() {
        return new \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\view\Options();
    }
}

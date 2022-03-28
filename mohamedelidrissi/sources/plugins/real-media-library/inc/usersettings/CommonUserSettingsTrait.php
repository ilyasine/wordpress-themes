<?php

namespace MatthiasWeb\RealMediaLibrary\usersettings;

use MatthiasWeb\RealMediaLibrary\metadata\CommonTrait;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Trait common user settings helper methods.
 *
 * @since 4.0.8
 */
trait CommonUserSettingsTrait {
    use CommonTrait;
    /**
     * Gets (and persists) a checkbox to the user (settings) metadata.
     *
     * @param string $meta The meta key
     * @param boolean $persist If setted it will be updated or deleted
     * @return boolean
     */
    protected static function is($meta, $persist = null) {
        if ($persist !== null) {
            if ($persist) {
                return update_user_meta(get_current_user_id(), $meta, $persist);
            } else {
                return delete_user_meta(get_current_user_id(), $meta);
            }
        }
        return (bool) get_user_meta(get_current_user_id(), $meta, \true);
    }
    /**
     * Gets (and persists) a string to the user (settings) metadata.
     *
     * @param string $meta The meta key
     * @param boolean $persist If setted it will be updated or deleted
     * @return string|boolean
     */
    protected static function get($meta, $persist = null) {
        if ($persist !== null) {
            if ($persist) {
                return update_user_meta(get_current_user_id(), $meta, $persist);
            } else {
                return delete_user_meta(get_current_user_id(), $meta);
            }
        }
        return get_user_meta(get_current_user_id(), $meta, \true);
    }
}

<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handle site-wide transients for a specific plugin. It is static-only access and set.
 * Do not use this handler for other data than timestamps and booleans!
 *
 * All your used transient keys should be in constants so they are readable in code and
 * as short as possible in database.
 */
final class TransientHandler {
    const OPTION_NAME = 'real_utils-transients';
    const TRANSIENT_INITIATOR_CROSS = 'cross';
    const TRANSIENT_NEXT_CROSS_SELLING = 'ncs';
    const TRANSIENT_CROSS_SKIP = 'cs';
    const TRANSIENT_CROSS_COUNTER = 'cc';
    const TRANSIENT_REDIRECT_AFTER_ACTIVATE = 'raa';
    const TRANSIENT_NEXT_RATING = 'nr';
    /**
     * Set a value for a given plugin.
     *
     * @param AbstractInitiator $initiator
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public static function set($initiator, $key, $value) {
        $json = self::json();
        $pid = \is_string($initiator) ? $initiator : $initiator->getPluginBase()->getPluginConstantPrefix();
        if (!isset($json[$pid])) {
            $json[$pid] = [];
        }
        $json[$pid][$key] = $value;
        return self::json($json);
    }
    /**
     * Get a value for a given plugin.
     *
     * @param AbstractInitiator|string $initiator
     * @param string $key
     * @param mixed $default
     */
    public static function get($initiator, $key, $default = null) {
        $json = self::json();
        $pid = \is_string($initiator) ? $initiator : $initiator->getPluginBase()->getPluginConstantPrefix();
        if (!isset($json[$pid]) || !isset($json[$pid][$key])) {
            return $default;
        }
        return isset($json[$pid][$key]) ? $json[$pid][$key] : $default;
    }
    /**
     * Get the JSON from database option.
     *
     * @param array $set Write back to database
     */
    protected static function json($set = null) {
        if ($set !== null) {
            return update_site_option(self::OPTION_NAME, \json_encode($set));
        }
        return \json_decode(get_site_option(self::OPTION_NAME, '[]'), ARRAY_A);
    }
}

<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * WordPress itself has a so-called "Transient API" which allows you to save custom
 * results of e. g. database queries in the database `wp_options` table. While developing the
 * plugin Real Media Library we experienced a lot of issues with Transients as there are a lot
 * of performance and optimization plugins which clears the transients.
 *
 * Note: You should not use this for huge query results as this option gets autoloaded if non-site-wide.
 *
 * @see https://developer.wordpress.org/apis/handbook/transients/
 */
class ExpireOption {
    const EXPIRE_SUFFIX = '-expire';
    const TRANSIENT_MIGRATION_DISABLED = 0;
    const TRANSIENT_MIGRATION_NON_SITE_WIDE = 1;
    const TRANSIENT_MIGRATION_SITE_WIDE = 2;
    private $name;
    private $siteWide;
    private $expiration;
    private $transientMigration;
    /**
     * C'tor.
     *
     * @param string $name Your option name
     * @param boolean $siteWide Use e. g. `get_site_transient` instead of `get_transient`
     * @param int $expiration Time until expiration in seconds
     * @codeCoverageIgnore
     */
    public function __construct($name, $siteWide, $expiration) {
        $this->name = $name;
        $this->siteWide = $siteWide;
        $this->expiration = $expiration;
        $this->transientMigration = self::TRANSIENT_MIGRATION_DISABLED;
    }
    /**
     * Get option value. Returns `$fallback` if not found or expired.
     *
     * @param mixed $fallback
     * @param boolean $respectExpire If `false`, you will always get the value from database (useful if you need to merge from previous data)
     * @see https://developer.wordpress.org/reference/functions/get_site_transient/
     * @see https://developer.wordpress.org/reference/functions/get_transient/
     * @see https://developer.wordpress.org/reference/functions/get_site_option/
     * @see https://developer.wordpress.org/reference/functions/get_option/
     */
    public function get($fallback = \false, $respectExpire = \true) {
        if ($respectExpire) {
            // Get time
            $expire = $this->isSiteWide()
                ? get_site_option($this->getExpireName(), 0)
                : get_option($this->getExpireName(), 0);
            if (\time() > $expire) {
                // Fallback to transient migration value
                if ($expire === 0 && $this->getTransientMigration() !== self::TRANSIENT_MIGRATION_DISABLED) {
                    return $this->handleMigration($fallback);
                }
                return $fallback;
            }
        }
        return $this->isSiteWide()
            ? get_site_option($this->getName(), $fallback)
            : get_option($this->getName(), $fallback);
    }
    /**
     * Handle the migration and return the correct value.
     *
     * @param mixed $fallback
     */
    protected function handleMigration($fallback) {
        $siteWide = $this->getTransientMigration() === self::TRANSIENT_MIGRATION_SITE_WIDE;
        $transientValue = $siteWide ? get_site_transient($this->getName()) : get_transient($this->getName());
        if ($transientValue === \false) {
            // Save in database as `NULL` for further autoloading queries
            $this->set(null);
            return $fallback;
        } else {
            // Re-save in database
            $this->set($transientValue);
            // Delete transient as no longer needed (only non-site-wide as site-wide can be used for further site-wide to non-site-wide migrations)
            if (!$siteWide) {
                delete_transient($this->getName());
            }
            return $transientValue;
        }
    }
    /**
     * Set option value.
     *
     * @param mixed $value
     * @see https://developer.wordpress.org/reference/functions/set_site_transient/
     * @see https://developer.wordpress.org/reference/functions/set_transient/
     * @see https://developer.wordpress.org/reference/functions/set_site_option/
     * @see https://developer.wordpress.org/reference/functions/set_option/
     */
    public function set($value) {
        $expire = \time() + $this->getExpiration();
        if ($this->isSiteWide()) {
            update_site_option($this->getExpireName(), $expire);
            return update_site_option($this->getName(), $value);
        } else {
            update_option($this->getExpireName(), $expire);
            return update_option($this->getName(), $value);
        }
    }
    /**
     * Delete option.
     *
     * @see https://developer.wordpress.org/reference/functions/delete_site_transient/
     * @see https://developer.wordpress.org/reference/functions/delete_transient/
     * @see https://developer.wordpress.org/reference/functions/delete_site_option/
     * @see https://developer.wordpress.org/reference/functions/delete_option/
     */
    public function delete() {
        if ($this->isSiteWide()) {
            delete_site_option($this->getExpireName());
            return delete_site_option($this->getName());
        } else {
            delete_option($this->getExpireName());
            return delete_option($this->getName());
        }
    }
    /**
     * If you are migrating from a transient, use this method to allow reading the value
     * via `get` from the transient value, when no option was found.
     *
     * @param int $type See class constants `TRANSIENT_MIGRATION*`
     * @codeCoverageIgnore
     */
    public function enableTransientMigration($type) {
        $this->transientMigration = $type;
        return $this;
    }
    /**
     * Get "expire" option name.
     *
     * @codeCoverageIgnore
     */
    public function getExpireName() {
        return $this->getName() . self::EXPIRE_SUFFIX;
    }
    /**
     * Get option name.
     *
     * @codeCoverageIgnore
     */
    public function getName() {
        return $this->name;
    }
    /**
     * Get if option is site-wide.
     *
     * @codeCoverageIgnore
     */
    public function isSiteWide() {
        return $this->siteWide;
    }
    /**
     * Get expiration in seconds.
     *
     * @codeCoverageIgnore
     */
    public function getExpiration() {
        return $this->expiration;
    }
    /**
     * Get value of `transientMigration`.
     *
     * @codeCoverageIgnore
     */
    public function getTransientMigration() {
        return $this->transientMigration;
    }
}

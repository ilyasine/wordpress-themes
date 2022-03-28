<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross;

use MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Cross-selling for Real Cookie Banner.
 *
 * @see https://app.clickup.com/2088/v/dc/218-92/218-1114
 */
class CrossRealCookieBanner extends \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\cross\AbstractCrossSelling {
    const SLUG = 'real-cookie-banner';
    const FILE_LITE = 'real-cookie-banner/index.php';
    const FILE_PRO = 'real-cookie-banner-pro/index.php';
    const NON_GDPR_PLUGINS = [
        'luckywp-cookie-notice-gdpr',
        'ninja-gdpr-compliance',
        'ninja-gdpr',
        'gdpr-compliance-by-supsystic',
        'surbma-gdpr-proof-google-analytics',
        'easy-wp-cookie-popup',
        'smart-cookie-kit',
        'italy-cookie-choices',
        'shapepress-dsgvo',
        'uk-cookie-consent'
    ];
    const GDPR_PLUGINS = [
        'cookiebot',
        'iubenda-cookie-law-solution',
        'gdpr-cookie-compliance',
        'cookie-law-info',
        'gdpr-cookie-consent',
        'cookie-notice',
        'pixelmate'
    ];
    const SALE_READY_GDPR_PLUGINS = ['borlabs-cookie', 'complianz-gdpr', 'complianz-gdpr-premium'];
    const GDPR_LANGUAGES = ['de'];
    /**
     * Documented in AbstractCrossSelling.
     *
     * @codeCoverageIgnore
     */
    public function getSlug() {
        return self::SLUG;
    }
    /**
     * Documented in AbstractCrossSelling.
     */
    public function skip() {
        // Always skip when RCB is installed
        $handler = \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()->getCrossSellingHandler();
        if ($handler->isInstalled(self::FILE_LITE) || $handler->isInstalled(self::FILE_PRO)) {
            return \true;
        }
        // Do not skip when a GDPR plugin is installed
        $hasGdprPluginInstalled = $this->hasGdprPluginInstalled(
            \array_merge(self::NON_GDPR_PLUGINS, self::GDPR_PLUGINS, self::SALE_READY_GDPR_PLUGINS)
        );
        if ($hasGdprPluginInstalled !== \false) {
            return \false;
        }
        // Do not skip when the blog needs a GDPR solution
        return !$this->hasGdprBlog();
    }
    /**
     * Documented in AbstractCrossSelling.
     *
     * @codeCoverageIgnore
     */
    public function getMeta() {
        return [
            'gdpr-compliant' => [
                'title' => __('Is your website GDPR compliant?', REAL_UTILS_TD),
                'image' => $this->getAssetsUrl(__('cookie-banner-frontend.png', REAL_UTILS_TD)),
                'description' => __(
                    'Websites targeting EU users and setting non-essential cookies need an opt-in cookie banner. We recommend Real Cookie Banner as an ePrivacy Policy and GDPR compliant solution for WordPress.',
                    REAL_UTILS_TD
                ),
                'link' => __('https://devowl.io/go/real-cookie-banner?source=cross-rcb', REAL_UTILS_TD)
            ]
        ];
    }
    /**
     * Check if the current WordPress instance has a plugin installed, which
     * is not GDPR-compliant.
     *
     * @param string[] $plugins Use one of the class constants
     */
    public function hasGdprPluginInstalled($plugins) {
        foreach ($plugins as $slug) {
            $file = \MatthiasWeb\RealMediaLibrary\Vendor\DevOwl\RealUtils\Core::getInstance()
                ->getCrossSellingHandler()
                ->isInstalled($slug, \true);
            if ($file !== \false) {
                return get_plugin_data($file)['Name'];
            }
        }
        return \false;
    }
    /**
     * Check if the current WordPress instance generally needs a GDPR solution.
     */
    public function hasGdprBlog() {
        $current = get_locale();
        foreach (self::GDPR_LANGUAGES as $lang) {
            if (\substr($current, 0, \strlen($lang)) === $lang) {
                return \true;
            }
        }
        return \false;
    }
}

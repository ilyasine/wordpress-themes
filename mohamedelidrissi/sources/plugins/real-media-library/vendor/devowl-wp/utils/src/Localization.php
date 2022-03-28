<?php

namespace MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils;

\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
/**
 * Base i18n management for backend and frontend.
 */
trait Localization {
    public static $PACKAGE_INFO_FRONTEND = 'frontend';
    public static $PACKAGE_INFO_BACKEND = 'backend';
    /**
     * Get the directory where the languages folder exists.
     *
     * The returned string array should look like this:
     * [0] = Full path to the "languages" folder
     * [1] = Used textdomain
     * [2]? = Use different prefix domain in filename
     *
     * @param string $type
     * @return string[]
     */
    abstract protected function getPackageInfo($type);
    /**
     * Obtain language key from a file name.
     *
     * @param string $file
     */
    public function getLanguageFromFile($file) {
        $availableLanguages = get_available_languages();
        $availableLanguages[] = 'en_US';
        \preg_match_all(
            '/-(' . \join('|', $availableLanguages) . ')-/m',
            \basename($file),
            $matches,
            \PREG_SET_ORDER,
            0
        );
        if (\count($matches) === 0) {
            return \false;
        }
        return $matches[0][1];
    }
    /**
     * Never load `.mo` files from `wp-content/plugins/languages` as we do manage all our translations ourself.
     *
     * TODO: make this configurable per plugin?
     *
     * @param string $mofile
     * @param string $domain
     */
    public function load_textdomain_mofile($mofile, $domain) {
        list($packagePath, $packageDomain) = $this->getPackageInfo(self::$PACKAGE_INFO_BACKEND);
        $avoidPath = \constant('WP_LANG_DIR') . '/plugins/';
        // Path to the language file within our plugin
        $packageFilePath = path_join($packagePath, \basename($mofile));
        if (
            $domain === $packageDomain &&
            \substr($mofile, 0, \strlen($avoidPath)) === $avoidPath &&
            \file_exists($packageFilePath)
        ) {
            // Always use our internal translation instead of the `wp-content/languages` folder
            return $packageFilePath;
        }
        return $mofile;
    }
    /**
     * Add filters to WordPress runtime.
     */
    public function hooks() {
        add_filter('load_textdomain_mofile', [$this, 'load_textdomain_mofile'], 10, 2);
    }
    /**
     * Get the languages which are available in the POT file. Why multiple? Imagine you want to
     * use the pot file for `en_US` and `en_GB`. This can be useful for the `@devowl-wp/multilingual`
     * package, especially the `TemporaryTextDomain` feature.
     */
    public function getPotLanguages() {
        return ['en_US', 'en_GB', 'en_CA', 'en_NZ', 'en_AU', 'en'];
    }
}

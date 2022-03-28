<?php

namespace MatthiasWeb\RealMediaLibrary\usersettings;

use MatthiasWeb\RealMediaLibrary\api\IUserSettings;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\lite\usersettings\DefaultFolder as LiteDefaultFolder;
use MatthiasWeb\RealMediaLibrary\overrides\interfce\usersettings\IOverrideDefaultFolder;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Add an option so the user can set the default startup folder.
 * It replaces this plugin: https://wordpress.org/plugins/default-startup-folder-for-real-media-library/
 *
 * @since 4.6.0
 */
class DefaultFolder implements
    \MatthiasWeb\RealMediaLibrary\api\IUserSettings,
    \MatthiasWeb\RealMediaLibrary\overrides\interfce\usersettings\IOverrideDefaultFolder {
    use CommonUserSettingsTrait;
    use UtilsProvider;
    use LiteDefaultFolder;
    const FIELD_NAME = 'defaultFolder';
    const OPTION_NAME = 'rmlDefaultFolder';
    const ID_NONE = -2;
    const ID_LAST_QUERIED = -3;
    /**
     * C'tor.
     */
    public function __construct() {
        $this->overrideConstruct();
    }
    // Documented in IMetadata
    public function content($content, $user) {
        $default = $this->getDefaultFolder();
        $selectedNone = $default === self::ID_NONE ? 'selected="selected"' : '';
        $selectedLastQueried = $default === self::ID_LAST_QUERIED ? 'selected="selected"' : '';
        $disabled = $this->isPro() ? '' : 'disabled="disabled" ';
        $content .=
            '<label>' .
            __('Default startup folder', RML_TD) .
            '</label><select ' .
            $disabled .
            ' name="' .
            self::FIELD_NAME .
            '"><option value="' .
            self::ID_NONE .
            '" ' .
            $selectedNone .
            '>' .
            __('No folder at startup', RML_TD) .
            '</option><option value="' .
            self::ID_LAST_QUERIED .
            '" ' .
            $selectedLastQueried .
            '>' .
            __('Last opened folder', RML_TD) .
            '</option>' .
            wp_rml_dropdown($default, []) .
            '</select>';
        if (!$this->isPro()) {
            $content .=
                '<p class="description">' .
                __(
                    'Your media library can always open in the last opened folder or a folder you choose. This saves you time every time you open the media library!',
                    RML_TD
                ) .
                ' <a href="' .
                (RML_PRO_VERSION . '&feature=start-up-folder') .
                '" target="_blank">' .
                __('Learn more about PRO', RML_TD) .
                '</a></p>';
        }
        return $content;
    }
    // Documented in IMetadata
    public function save($response, $user, $request) {
        return $this->overrideSave($response, $user, $request);
    }
    // Documented in IMetadata
    public function scripts($assets) {
        // Silence is golden.
    }
    // Documented in CommonUserSettingsTrait
    public static function getDefaultFolder($persist = null) {
        $default = self::get(self::OPTION_NAME, $persist);
        return empty($default) ? '' : \intval($default);
    }
}

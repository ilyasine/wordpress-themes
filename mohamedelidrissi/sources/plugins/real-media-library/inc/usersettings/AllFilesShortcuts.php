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
 * Add an option so the user can hide shortcuts in "All files" view.
 *
 * @since 4.0.8
 */
class AllFilesShortcuts implements \MatthiasWeb\RealMediaLibrary\api\IUserSettings {
    use CommonUserSettingsTrait;
    use UtilsProvider;
    const FIELD_NAME = 'hideAllFilesShortcuts';
    const OPTION_NAME = 'rmlHideAllFilesShortcuts';
    /**
     * C'tor.
     */
    public function __construct() {
        if (self::isEnabled()) {
            add_filter('RML/Filter/PostsClauses', [$this, 'posts_clauses'], 10, 3);
        }
    }
    /**
     * Modify posts clauses to hide shortcuts.
     *
     * @param array $clauses
     * @param WP_Query $query
     * @param int $folderId
     * @return array
     */
    public function posts_clauses($clauses, $query, $folderId) {
        if (\MatthiasWeb\RealMediaLibrary\attachment\Filter::getInstance()->isQuerying() && $folderId === 0) {
            $clauses['where'] .= ' AND IFNULL(rmlposts.isShortcut, 0) = 0 ';
        }
        return $clauses;
    }
    // Documented in IMetadata
    public function content($content, $user) {
        $content .=
            '<label><input name="' .
            self::FIELD_NAME .
            '" type="checkbox" value="1" ' .
            checked(1, self::isEnabled(), \false) .
            ' /> ' .
            __('Hide shortcuts in "All files"', RML_TD) .
            '</label>
            <p class="description">' .
            __('The count always includes shortcuts', RML_TD) .
            '</p>';
        return $content;
    }
    // Documented in IMetadata
    public function save($response, $user, $request) {
        $param = $request->get_param(self::FIELD_NAME);
        if (self::isEnabled($param === '1') !== \false) {
            $this->reloadAfterSave($response);
        }
        return $response;
    }
    // Documented in IMetadata
    public function scripts($assets) {
        // Silence is golden.
    }
    // Documented in CommonUserSettingsTrait
    public static function isEnabled($persist = null) {
        return self::is(self::OPTION_NAME, $persist);
    }
}

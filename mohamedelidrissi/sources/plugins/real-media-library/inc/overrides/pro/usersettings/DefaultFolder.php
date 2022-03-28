<?php

namespace MatthiasWeb\RealMediaLibrary\lite\usersettings;

use MatthiasWeb\RealMediaLibrary\usersettings\DefaultFolder as UsersettingsDefaultFolder;
use WP_Query;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
trait DefaultFolder {
    // Documented in IOverrideDefaultFolder
    public function overrideConstruct() {
        add_filter('RML/Localize', [$this, 'localize']);
        add_filter('RML/Filter/PostsClauses', [$this, 'posts_clauses'], 10, 4);
        if (
            isset($_GET['rml_folder']) &&
            \intval($_GET['rml_folder']) === \MatthiasWeb\RealMediaLibrary\usersettings\DefaultFolder::ID_NONE
        ) {
            add_filter('gettext', [$this, 'gettext'], 10, 3);
        }
    }
    // Documented in IMetadata
    public function overrideSave($response, $user, $request) {
        \MatthiasWeb\RealMediaLibrary\usersettings\DefaultFolder::getDefaultFolder(
            $request->get_param(\MatthiasWeb\RealMediaLibrary\usersettings\DefaultFolder::FIELD_NAME)
        );
        $response['data']['lastQueried'] = wp_rml_last_queried_folder();
        return $response;
    }
    /**
     * Modify text in list table.
     *
     * @param string[] $translation
     * @param string $text
     * @param string $domain
     * @return string
     */
    public function gettext($translation, $text, $domain) {
        return $text === 'No media files found.' && $domain === 'default'
            ? __('Please select a folder to show items.', RML_TD)
            : $translation;
    }
    /**
     * Modify fields to a fast select query when the setting "None at startup" is configured.
     *
     * @param array $clauses
     * @param WP_Query $query
     * @param int $folderId
     * @param int $root
     * @return array
     */
    public function posts_clauses($clauses, $query, $folderId, $root) {
        if ($folderId === \MatthiasWeb\RealMediaLibrary\usersettings\DefaultFolder::ID_NONE) {
            $clauses['where'] = ' AND 1=0';
            $clauses['limits'] = 'LIMIT 0, 1';
            $clauses['_restrictRML'] = \true;
        }
        return $clauses;
    }
    /**
     * Localize frontend.
     *
     * @param array $arr
     * @return array
     */
    public function localize($arr) {
        $arr['defaultFolder'] = self::getDefaultFolder();
        return $arr;
    }
}

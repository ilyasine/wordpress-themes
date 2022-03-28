<?php

namespace MatthiasWeb\RealMediaLibrary\metadata;

use MatthiasWeb\RealMediaLibrary\api\IMetadata;
use MatthiasWeb\RealMediaLibrary\attachment\Structure;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\Core;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Create general functionality for the custom folder fields.
 *
 * For an example see the function-doc of this::content_general and this::save_general
 */
class Meta implements \MatthiasWeb\RealMediaLibrary\api\IMetadata {
    use UtilsProvider;
    private static $me = null;
    private $view = null;
    private $boxes = [];
    /**
     * C'tor.
     */
    private function __construct() {
        // Add our folder meta table to wpdb
        global $wpdb;
        if (!isset($wpdb->realmedialibrary_meta)) {
            $wpdb->realmedialibrarymeta = \MatthiasWeb\RealMediaLibrary\Core::tableName('meta');
        }
        $this->view = \MatthiasWeb\RealMediaLibrary\attachment\Structure::getInstance()->getView();
    }
    // Documented in IMetadata
    public function content($content, $folder) {
        $type = $folder->getType();
        if ($type !== RML_TYPE_ROOT) {
            $content .= '<label>' . __('Path', RML_TD) . '</label>' . $folder->getPath(' > ') . '';
        }
        $content .=
            '<label>' .
            __('Folder type', RML_TD) .
            '</label>' .
            $folder->getTypeName() .
            ' <i>' .
            $folder->getTypeDescription() .
            '</i>';
        return $content;
    }
    // Documented in IMetadata
    public function save($response, $folder, $request) {
        return $response;
    }
    // Documented in IMetadata
    public function scripts($assets) {
        // Silence is golden.
    }
    /**
     * Prepare the whole content for a single meta box.
     *
     * @param int $fid
     */
    public function prepare_content($fid) {
        $folder = null;
        $inputID = 'all';
        $type = RML_TYPE_ALL;
        if (!empty($fid)) {
            $folder = wp_rml_get_object_by_id($fid);
            $inputID = $folder->getId();
            $type = $folder->getType();
            if ($folder === null) {
                return '404';
            }
        }
        /**
         * Add a tab group to the folder details or user settings dialog.
         * You cam use this function together with add_rml_meta_box()
         * or add_rml_user_settings_box(). Allowed $types: "User/Settings"|"Folder/Meta".
         *
         * @param {array} $tabs The tabs with key (unique tab name) and value (display text)
         * @hook RML/$type/Groups
         * @return {array} The tabs
         * @since 3.3
         */
        $tabs = apply_filters('RML/' . ($type === RML_TYPE_ALL ? 'User/Settings' : 'Folder/Meta') . '/Groups', [
            'general' => __('General', RML_TD)
        ]);
        // Create content form
        $content =
            '<form method="POST" action="">
            <input type="hidden" name="folderId" value="' .
            $inputID .
            '" />
            <input type="hidden" name="folderType" value="' .
            $type .
            '" />
            <ul class="rml-meta-errors"></ul>';
        // Create groups
        foreach ($tabs as $key => $value) {
            $content .= '<h3>' . $value . '</h3>';
            $hookAddition = $key === 'general' ? '' : '/' . $key;
            // Create group content
            if ($type === RML_TYPE_ALL) {
                /**
                 * Add content to the general settings. Do not use this filter directly instead use the
                 * add_rml_user_settings_box() function! If you want to add content to the "General" tab you
                 * can use the filter `RML/User/Settings/Content`.
                 *
                 * @param {string} $content The HTML content
                 * @param {int} $user The current user id
                 * @hook RML/User/Settings/Content/$tabGroup
                 * @return {string} The HTML content
                 * @since 3.2
                 */
                $content .= apply_filters('RML/User/Settings/Content' . $hookAddition, '', get_current_user_id());
            } else {
                /**
                 * Add content to the folder metabox. Do not use this filter directly instead use the
                 * add_rml_meta_box() function! If you want to add content to the "General" tab you
                 * can use the filter `RML/Folder/Meta/Content`.
                 *
                 * @param {string} $content The HTML content
                 * @param {IFolder} $folder The folder object
                 * @hook RML/Folder/Meta/Content/$tabGroup
                 * @return {string} The HTML content
                 * @since 3.3.1 $folder can never be null
                 */
                $content .= apply_filters('RML/Folder/Meta/Content' . $hookAddition, '', $folder);
            }
        }
        $content .= '</form>';
        return $content;
    }
    /**
     * Add meta box.
     *
     * @param string $name
     * @param IMetadata $instance
     * @return boolean
     */
    public function add($name, $instance) {
        if ($this->get($name) !== null) {
            return \false;
        } else {
            $this->boxes[$name] = $instance;
            return \true;
        }
    }
    /**
     * Get meta box instance.
     *
     * @param string $name
     * @return IMetadata|null
     */
    public function get($name) {
        foreach ($this->boxes as $key => $value) {
            if ($key === $name) {
                return $value;
            }
        }
        return null;
    }
    /**
     * Check if a meta box exists by name.
     *
     * @param string $name
     * @return boolean
     */
    public function exists($name) {
        return $this->get($name) !== null;
    }
    /**
     * When a folder gets deleted, also delete meta.
     *
     * @param int $fid
     * @param mixed $oldData
     */
    public function folder_deleted($fid, $oldData) {
        truncate_media_folder_meta($fid);
    }
    /**
     * Get instance.
     *
     * @return Meta
     */
    public static function getInstance() {
        return self::$me === null ? (self::$me = new \MatthiasWeb\RealMediaLibrary\metadata\Meta()) : self::$me;
    }
}

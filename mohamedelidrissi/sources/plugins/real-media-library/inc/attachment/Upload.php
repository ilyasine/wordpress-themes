<?php

namespace MatthiasWeb\RealMediaLibrary\attachment;

use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\exception\OnlyInProVersionException;
use MatthiasWeb\RealMediaLibrary\usersettings\DefaultFolder;
use MatthiasWeb\RealMediaLibrary\view\Options;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handling uploader, so files can be uploaded directly to a folder.
 */
class Upload {
    use UtilsProvider;
    private static $me = null;
    /**
     * C'tor.
     */
    private function __construct() {
        // Silence is golden.
    }
    /**
     * Handles the upload to move an attachment directly to a given folder.
     *
     * @param int $postID
     */
    public function add_attachment($postID) {
        $folder = $this->getFolderFromRequest();
        if ($folder !== null) {
            wp_rml_move($folder, [$postID]);
        } else {
            _wp_rml_synchronize_attachment($postID, _wp_rml_root());
        }
    }
    /**
     * Get the folder id from the current page request.
     */
    public function getFolderFromRequest() {
        $rmlFolder = isset($_REQUEST['rmlFolder']) ? $_REQUEST['rmlFolder'] : null;
        // Recursively creating folders is only allowed in PRO version as in Lite version there are no subfolders supported
        $allowRecursively = $this->isPro();
        // Do not allow recursively creation of folders for non-folder types
        if ($rmlFolder !== null && $allowRecursively) {
            $folder = wp_rml_get_object_by_id($rmlFolder);
            $allowRecursively =
                $folder === null
                    ? $allowRecursively
                    : \in_array($folder->getType(), [RML_TYPE_FOLDER, RML_TYPE_ROOT], \true);
        }
        // Check if relative path is given an create folders
        $rmlCreateFolder = isset($_REQUEST['rmlCreateFolder']) ? $_REQUEST['rmlCreateFolder'] : null;
        if ($rmlCreateFolder !== null && $allowRecursively) {
            // Create folder recursively
            $rmlFolder = $rmlFolder === null ? _wp_rml_root() : $rmlFolder;
            try {
                $created = wp_rml_create_p(\dirname($rmlCreateFolder), $rmlFolder, RML_TYPE_FOLDER);
                if (!\is_array($created)) {
                    $rmlFolder = $created;
                }
            } catch (\MatthiasWeb\RealMediaLibrary\exception\OnlyInProVersionException $e) {
                // Silence is golden.
            }
        }
        return $rmlFolder;
    }
    /**
     * The grid view and post editor upload can be modified through the left
     * tree view to upload files directly.
     */
    public function pre_upload_ui() {
        global $pagenow;
        if (wp_rml_active()) {
            if (!is_admin() && !\MatthiasWeb\RealMediaLibrary\view\Options::load_frontend()) {
                return;
            }
            // Get the options depending on the current page
            $options = [
                'name' => 'rmlFolder',
                'disabled' => [RML_TYPE_COLLECTION],
                'title' => __('Select destination folder', RML_TD)
            ];
            $defaultFolder = \MatthiasWeb\RealMediaLibrary\usersettings\DefaultFolder::getDefaultFolder();
            if (!empty($defaultFolder)) {
                $options['selected'] = $defaultFolder;
            }
            if (isset($_GET['rml_preselect'])) {
                $options['selected'] = $_GET['rml_preselect'];
            }
            if ($pagenow === 'media-new.php') {
                $label = __(
                    'You can simply upload files directly to a folder. Select a folder and upload files.',
                    RML_TD
                );
            } else {
                $label = __('upload to folder', RML_TD);
            }
            echo '<p class="attachments-filter-upload-chooser">' .
                $label .
                '</p><p>' .
                wp_rml_selector($options) .
                '</p>';
        }
    }
    /**
     * Get instance.
     *
     * @return Upload
     */
    public static function getInstance() {
        return self::$me === null ? (self::$me = new \MatthiasWeb\RealMediaLibrary\attachment\Upload()) : self::$me;
    }
}

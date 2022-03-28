<?php

namespace MatthiasWeb\RealMediaLibrary\attachment;

use MatthiasWeb\RealMediaLibrary\api\IFolder;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Folders permission handling.
 */
class Permissions {
    use UtilsProvider;
    private static $me = null;
    const RESTRICTION_PARENT = 'par';
    const RESTRICTION_REARRANGE = 'rea';
    const RESTRICTION_CREATE = 'cre';
    const RESTRICTION_INSERT = 'ins';
    const RESTRICTION_RENAME = 'ren';
    const RESTRICTION_DELETE = 'del';
    const RESTRICTION_MOVE = 'mov';
    const RESTRICTION_ALL = ['par', 'rea', 'cre', 'ins', 'ren', 'del', 'mov'];
    /**
     * Restrict to insert/upload new attachments, automatically moved to root if upload
     * Restrict to move files outside of a folder.
     *
     * @param string[] $errors
     * @param int $id
     * @param IFolder $folder
     * @return string[]
     */
    public static function insert($errors, $id, $folder) {
        if (is_rml_folder($folder) && $folder->isRestrictFor('ins')) {
            $errors[] = __('You are not allowed to insert files here.', RML_TD);
            return $errors;
        }
        // Check if "mov" of current folder is allowed
        $otherFolder = wp_attachment_folder($id);
        if ($otherFolder !== '') {
            $otherFolder = wp_rml_get_by_id($otherFolder, null, \true);
            if (is_rml_folder($otherFolder) && $otherFolder->isRestrictFor('mov')) {
                $errors[] = __('You are not allowed to move the file.', RML_TD);
            }
        }
        return $errors;
    }
    /**
     * Restrict to create new subfolders.
     *
     * @param string[] $errors
     * @param string $name
     * @param int $parent
     * @param int $type
     * @return string[]
     */
    public static function create($errors, $name, $parent, $type) {
        $folder = wp_rml_get_by_id($parent, null, \true);
        if (is_rml_folder($folder) && $folder->isRestrictFor('cre')) {
            $errors[] = __('You are not allowed to create a subfolder here.', RML_TD);
        }
        return $errors;
    }
    /**
     * Restrict to create new subfolders.
     *
     * @param string[] $errors
     * @param int $id
     * @param IFolder $folder
     * @return string[]
     */
    public static function deleteFolder($errors, $id, $folder) {
        if (is_rml_folder($folder) && $folder->isRestrictFor('del')) {
            $errors[] = __('You are not allowed to delete this folder.', RML_TD);
        }
        return $errors;
    }
    /**
     * Restrict to rename a folder.
     *
     * @param string[] $errors
     * @param string $name
     * @param IFolder $folder
     * @return string[]
     */
    public static function setName($errors, $name, $folder) {
        if (is_rml_folder($folder) && $folder->isRestrictFor('ren')) {
            $errors[] = __('You are not allowed to rename this folder.', RML_TD);
        }
        return $errors;
    }
    /**
     * Get instance.
     *
     * @return Permissions
     */
    public static function getInstance() {
        return self::$me === null
            ? (self::$me = new \MatthiasWeb\RealMediaLibrary\attachment\Permissions())
            : self::$me;
    }
}

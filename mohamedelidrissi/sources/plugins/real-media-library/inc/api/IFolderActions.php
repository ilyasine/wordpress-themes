<?php

namespace MatthiasWeb\RealMediaLibrary\api;

use MatthiasWeb\RealMediaLibrary\exception\OnlyInProVersionException;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This interface provides elementary action methods for folder objects. All folder
 * types (Folder, Collection, Gallery, ...) have implemented this interface.
 * Also the root ("Unorganized") is a folder and implements this interface.
 *
 * @since 3.3.1
 */
interface IFolderActions {
    /**
     * Fetch all attachment ids currently in this folder. It uses the
     * default WP_Query to fetch the ids. You can also use the WP_Query like:
     * ```php
     * $query = new \WP_Query([
     *  	'post_status' => 'inherit',
     *  	'post_type' => 'attachment',
     *  	'rml_folder' => 4,
     *      'rml_include_children' => false // (optional) Include files of subfolder, you have to use wp_rml_all_children_sql_supported(false, 'function') for checking support
     * ]);
     * ```
     *
     * @param string $order The order "ASC" or "DESC"
     * @param string $orderby Use "rml" to get ids ordered by custom order
     * @return int[] Post ids
     */
    public function read($order = null, $orderby = null);
    /**
     * Relocate a folder to a given place in the folder structure.
     *
     * @param integer $parentId The parent id
     * @param integer $nextFolderId The next folder id it should be prepend or false for the end
     * @throws Exception
     * @return boolean|string[] true or array with errors
     * @since 4.12.1 This function ignores the `$parentId` parameter in Lite version as creating subfolders is no longer supported
     */
    public function relocate($parentId, $nextFolderId = \false);
    /**
     * (Pro only) Start to order the given folder subfolders by a given order type.
     *
     * @param string $orderby The order type key
     * @param boolean $writeMetadata
     * @return boolean
     * @throws OnlyInProVersionException
     * @since 4.4
     */
    public function orderSubfolders($orderby, $writeMetadata = \true);
    /**
     * (Pro only) Reset the subfolders order'.
     *
     * @return boolean
     * @throws OnlyInProVersionException
     * @since 4.4
     */
    public function resetSubfolderOrder();
    /**
     * (Pro only) Reindex the children folders so the "ord" number is set right.
     *
     * @since 4.12.1 This function is only available in PRO-Version.
     * @param boolean $resetData If true, the structure is reset
     * @return boolean
     * @since 4.12.1 This function is only available in PRO-Version
     */
    public function reindexChildrens($resetData = \false);
    /**
     * Insert an amount of post ID's (attachments) to this folder.
     *
     * @param int[] $ids Array of post ids
     * @param boolean $supress_validation Suppress the permission validation
     * @param boolean $isShortcut Determines, if the post's should be "copied" to the folder (no physical copy)
     * @throws \Exception
     * @return true
     * @see wp_rml_move()
     * @see wp_rml_create_shortcuts()
     */
    public function insert($ids, $supress_validation = \false, $isShortcut = \false);
    /**
     * Iterate all children of this folder recursively and
     * update the absolute path. Use this function with caution because it can be
     * time intensive.
     */
    public function updateThisAndChildrensAbsolutePath();
    /**
     * For internal usage only!
     *
     * @param object $children
     * @internal
     */
    public function addChildren($children);
    /**
     * Sets the folders visibility to the user.
     *
     * @param boolean $visible
     */
    public function setVisible($visible);
    /**
     * Set restrictions for this folder. Allowed restrictions for folders:
     *
     * <ul>
     *  <li><strong>par</strong> Restrict to change the parent id</li>
     *  <li><strong>rea</strong> Restrict to rearrange the hierarchical levels of all subfolders (it is downwards all subfolders!) and cannot be inherited</li>
     *  <li><strong>cre</strong> Restrict to create new subfolders</li>
     *  <li><strong>ins</strong> Restrict to insert/upload new attachments, automatically moved to root if upload</li>
     *  <li><strong>ren</strong> Restrict to rename the folder</li>
     *  <li><strong>del</strong> Restrict to delete the folder</li>
     *  <li><strong>mov</strong> Restrict to move files outside the folder</li>
     * </ul>
     *
     * You can append a ">" after each permission so it is inherited in each created subfolder: "cre>", "ins>", ...
     *
     * @param string[] $restrictions Array with restrictions
     * @return boolean
     */
    public function setRestrictions($restrictions = []);
    /**
     * Changes the parent folder of this folder.
     *
     * @param integer $id The new parent (use -1 for root)
     * @param int $ord The order number
     * @param boolean $force If true no permission checks are executed
     * @throws \Exception
     * @since 4.12.1 This function ignores the `$id` parameter in Lite version as creating subfolders is no longer supported
     * @return boolean
     */
    public function setParent($id, $ord = -1, $force = \false);
    /**
     * Renames a folder and then checks, if there is no duplicate folder in the
     * parent folder.
     *
     * @param string $name String New name of the folder
     * @param boolean $supress_validation Suppress the permission validation
     * @throws \Exception
     * @return boolean
     */
    public function setName($name, $supress_validation = \false);
}

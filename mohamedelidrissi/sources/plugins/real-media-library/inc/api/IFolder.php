<?php

namespace MatthiasWeb\RealMediaLibrary\api;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This interface provides elementary getter and setter methods for folder objects. All folder
 * types (Folder, Collection, Gallery, ...) have implemented this interface.
 * Also the root ("Unorganized") is a folder and implements this interface. Usually,
 * the root acts as "-1" but you should use the _wp_rml_root function to get the
 * root id. If this interface does not provide an expected method, yet, have a look at the
 * other API files. For example to create a folder use wp_rml_create.
 *
 * <strong>Check if a variable is surely a IFolder interface object:</strong>
 * ```php
 * $folder = wp_rml_get_object_by_id(5);
 * if (is_rml_folder($folder)) {
 *      // It is an interface implementation of IFolder
 * }
 * ```
 *
 * <h3>Register own folder type:</h3>
 * You can create your own implementation of a folder type (Gallery, Collection, Root, ...)
 * just have a look at the wp-content/plugins/real-media-library/inc/folder files.
 *
 * Also have a look at the wp_rml_register_creatable function to register your class
 * (RML_TYPE_FOLDER is an unique defined integer for your folder type):
 * ```php
 * wp_rml_register_creatable(Folder::class, RML_TYPE_FOLDER);
 * ```
 *
 * @see wp_rml_root_childs
 * @see wp_rml_get_object_by_id
 * @see wp_rml_get_by_id
 * @see wp_rml_get_by_absolute_path
 * @see wp_rml_objects
 * @see is_rml_folder
 * @see IFolderActions
 */
interface IFolder extends
    \MatthiasWeb\RealMediaLibrary\api\IFolderActions,
    \MatthiasWeb\RealMediaLibrary\api\IFolderContent {
    /**
     * Get all parents which meets a given column value or column value is not empty.
     *
     * @param string $column The column name for the wp_realmedialibrary SQL table. "slug", "name", "absolutePath", ... This string is not escaped when you pass it through this function
     * @param mixed $value The value the column should have
     * @param string $valueFormat The value format for $value ($wpdb->prepare) This string is not escaped when you pass it through this function
     * @param boolean $includeSelf Set true to add self to list
     * @param int $until The highest allowed folder id. If null _wp_rml_root() is used
     * @return array folderId => columnValue, first id is the first found parent
     * @since 3.3
     */
    public function anyParentHas($column, $value = null, $valueFormat = '%s', $includeSelf = \false, $until = null);
    /**
     * Get all parents which meets a given meta key value or meta key value is not empty.
     *
     * @param string $meta_key The meta key name for the wp_realmedialibrary_meta SQL table. This string is not escaped when you pass it through this function
     * @param mixed $meta_value The value the meta key should have
     * @param string $valueFormat The value format for $value ($wpdb->prepare) This string is not escaped when you pass it through this function
     * @param boolean $includeSelf Set true to add self to list
     * @param int $until The highest allowed folder id. If null _wp_rml_root() is used
     * @return array Array with keys: id (meta_id), folderId, value (meta_value), first id is the first found parent
     * @since 3.3
     */
    public function anyParentHasMetadata(
        $meta_key,
        $meta_value = null,
        $valueFormat = '%s',
        $includeSelf = \false,
        $until = null
    );
    /**
     * Get all children which meets a given column value or column value is not empty.
     *
     * @param string $column The column name for the wp_realmedialibrary SQL table. "slug", "name", "absolutePath", ... This string is not escaped when you pass it through this function
     * @param mixed $value The value the column should have
     * @param string $valueFormat The value format for $value ($wpdb->prepare) This string is not escaped when you pass it through this function
     * @param boolean $includeSelf Set true to add self to list
     * @return array folderId => columnValue, first id is the first found child
     * @since 3.3
     */
    public function anyChildrenHas($column, $value = null, $valueFormat = '%s', $includeSelf = \false);
    /**
     * Get all chilren which meets a given meta key value or meta key value is not empty.
     *
     * @param string $meta_key The meta key name for the wp_realmedialibrary_meta SQL table. This string is not escaped when you pass it through this function
     * @param mixed $meta_value The value the meta key should have
     * @param string $valueFormat The value format for $value ($wpdb->prepare) This string is not escaped when you pass it through this function
     * @param boolean $includeSelf Set true to add self to list
     * @return array Array with keys: id (meta_id), folderId, value (meta_value), first id is the first found child
     * @since 3.3
     */
    public function anyChildrenHasMetadata($meta_key, $meta_value = null, $valueFormat = '%s', $includeSelf = \false);
    /**
     * Checks if this folder has a children with a given name.
     *
     * @param string $name Name of folder
     * @param boolean $returnObject If set to true and a children with this name is found, then return the object for this folder
     * @return boolean
     * @since 3.3 Now it checks for a given folder name instead the slug
     */
    public function hasChildren($name, $returnObject = \false);
    /**
     * Return the type for the given folder. For example: 0 = Folder, 1 = Collection, 2 = Gallery
     *
     * @return int
     */
    public function getType();
    /**
     * Get all allowed children folder types.
     *
     * @return boolean|int[] Array with allowed types or TRUE for all types allowed
     */
    public function getAllowedChildrenTypes();
    /**
     * Get the folder id.
     *
     * @return int
     */
    public function getId();
    /**
     * Get the parent folder id.
     *
     * @return int
     */
    public function getParent();
    /**
     * Get all parents of this folder.
     *
     * @param int $until The highest allowed folder id. If null _wp_rml_root() is used
     * @param int $colIdx The index returning for the wp_rml_create_all_parents_sql() query
     * @return int[] Folder ids, first id is the first parent
     * @since 3.3
     */
    public function getAllParents($until = null, $colIdx = 0);
    /**
     * Get the folder name.
     *
     * @param boolean $htmlentities If true the name is returned htmlentitied for output
     * @return string
     */
    public function getName($htmlentities = \false);
    /**
     * Returns a sanitized title for the folder. If the slug is empty
     * or forced to, it will be updated in the database, too.
     *
     * @param boolean $force Forces to regenerate the slug
     * @param boolean $fromSetName For internal usage only
     * @return string
     */
    public function getSlug($force = \false, $fromSetName = \false);
    /**
     * Creates a absolute path without slugging' the names.
     *
     * ```php
     * // Get valid physical folder name
     * $folder->getPath("/", "_wp_rml_sanitize_filename");
     * ```
     *
     * @param string $implode Delimiter for the folder names
     * @param callable $map Map the names with this function. Pass null to skip this map function
     * @param callable $filter Filter folders
     * @return string htmlentitied path
     */
    public function getPath($implode = '/', $map = 'htmlentities', $filter = null);
    /**
     * Get the creator/owner of the folder.
     *
     * @return int ID of the user
     * @since 3.3
     */
    public function getOwner();
    /**
     * Creates a absolute path. If the absolute path is empty
     * or forced to, it will be updated in the database, too.
     *
     * @param boolean $force Forces to regenerate the absolute path
     * @param boolean $fromSetName For internal usage only
     * @return string
     */
    public function getAbsolutePath($force = \false, $fromSetName = \false);
    /**
     * Gets the count of the files in this folder.
     *
     * @param boolean $forceReload If true the count cache gets reloaded
     * @return int
     * @since 3.3.1
     */
    public function getCnt($forceReload = \false);
    /**
     * Get children of this folder.
     *
     * @return IFolder
     */
    public function getChildren();
    /**
     * Get the order number.
     *
     * @return int
     * @since 3.3.1
     */
    public function getOrder();
    /**
     * Get the maximal order number of the children.
     *
     * @return integer Max order number
     * @since 3.3.1
     */
    public function getMaxOrder();
    /**
     * Get the restrictions of this folder.
     *
     * @return string[]
     */
    public function getRestrictions();
    /**
     * Get the count of the restrictions.
     *
     * @return int
     */
    public function getRestrictionsCount();
    /**
     * Gets a plain array with folder properties.
     *
     * @param boolean $deep Return the children as plain object array
     * @return array or null when not visible
     */
    public function getPlain($deep = \false);
    /**
     * Get the full row of the SQL query.
     *
     * @param string $field The field name
     * @return mixed Any object or false
     * @since 3.3
     */
    public function getRowData($field = null);
    /**
     * Get the type name for this folder. For example: Folder, Collection, Gallery, Unorganized.
     *
     * @param string $default The default (if null "Folder" is used as default)
     * @return string
     * @since 3.3.1
     * @see Filter RML/Folder/Type/Name
     */
    public function getTypeName($default = null);
    /**
     * Get the type description for this folder.
     *
     * @param string $default The default (if null folder description is used as default)
     * @return string
     * @since 3.3.1
     * @see Filter RML/Folder/Type/Description
     */
    public function getTypeDescription($default = null);
    /**
     * Check if the folder object is a given type.
     *
     * @param int $folder_type The folder type
     * @return boolean
     */
    public function is($folder_type);
    /**
     * Check if the folder object is visible to the user.
     *
     * @return boolean
     */
    public function isVisible();
    /**
     * Checks if this folder has a special restriction.
     *
     * @param string $restriction The restriction to check
     * @return boolean
     * @see IFolder::setRestrictions()
     */
    public function isRestrictFor($restriction);
    /**
     * Checks if a given folder type is allowed in this folder.
     *
     * @param int $type The type
     * @return boolean
     * @see IFolder::getAllowedChildrenTypes()
     * @since 4.12.1 This function returns always `true` in Lite version
     */
    public function isValidChildrenType($type);
}

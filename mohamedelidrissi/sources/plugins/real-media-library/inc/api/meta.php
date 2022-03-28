<?php

/*
 * Meta tags for folders.
 *
 * @table wp_realmedialibrary_meta
 * $wpdb->realmedialibrarymeta
 *
 * (C) add_media_folder_meta(...)
 * (R) get_media_folder_meta(...)
 * (U) update_media_folder_meta(...)
 * (D) delete_folder_meta(...)
 *
 * delete_media_folder_meta_by_key(...): Delete everything from folder meta matching meta key.
 *
 * Here you can use the default meta data hooks like:
 *  add_{$meta_type}_meta
 *  => add_realmedialibrary_meta
 *
 * @see RML_Meta
 *         metadata\Meta:content_general
 *         metadata\Meta:save_general
 * @see assets/js/meta.js
 *
 * PREDEFINED META KEYS:
 *  description
 *  coverImage
 */
use MatthiasWeb\RealMediaLibrary\api\IMetadata;
use MatthiasWeb\RealMediaLibrary\api\IUserSettings;
use MatthiasWeb\RealMediaLibrary\metadata\Meta;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
if (!\function_exists('get_media_folder_meta')) {
    /**
     * Retrieve folder meta field for a folder.
     *
     * @param int $folder_id Folder ID.
     * @param string $key The meta key to retrieve. By default, returns data for all keys.
     * @param boolean $single Whether to return a single value. Default false.
     * @return mixed[]|mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
     */
    function get_media_folder_meta($folder_id, $key = '', $single = \false) {
        \MatthiasWeb\RealMediaLibrary\metadata\Meta::getInstance();
        // Necessary checks to prepare metas
        return \get_metadata('realmedialibrary', \_wp_rml_meta_fix_absint($folder_id), $key, $single);
    }
    //var_dump(get_media_folder_meta(108, "test", true));
}
if (!\function_exists('add_media_folder_meta')) {
    /**
     * Add meta data field to a folder.
     *
     * Folder meta data is called "Custom Fields" on the Administration Screen.
     *
     * @param int $folder_id Folder ID.
     * @param string $meta_key Metadata name.
     * @param mixed $meta_value Metadata value. Must be serializable if non-scalar.
     * @param boolean $unique Whether the same key should not be added.
     * @return int|false
     */
    function add_media_folder_meta($folder_id, $meta_key, $meta_value, $unique = \false) {
        \MatthiasWeb\RealMediaLibrary\metadata\Meta::getInstance();
        // Necessary checks to prepare meta
        return \add_metadata('realmedialibrary', \_wp_rml_meta_fix_absint($folder_id), $meta_key, $meta_value, $unique);
    }
}
if (!\function_exists('update_media_folder_meta')) {
    /**
     * Update folder meta field based on folder ID.
     *
     * Use the $prev_value parameter to differentiate between meta fields with the
     * same key and folder ID.
     *
     * If the meta field for the folder does not exist, it will be added.
     *
     * @param int $folder_id Folder ID.
     * @param string $meta_key Metadata key.
     * @param mixed $meta_value Metadata value. Must be serializable if non-scalar.
     * @param mixed $prev_value Previous value to check before removing.
     * @return int|boolean
     */
    function update_media_folder_meta($folder_id, $meta_key, $meta_value, $prev_value = '') {
        \MatthiasWeb\RealMediaLibrary\metadata\Meta::getInstance();
        // Necessary checks to prepare meta
        return \update_metadata(
            'realmedialibrary',
            \_wp_rml_meta_fix_absint($folder_id),
            $meta_key,
            $meta_value,
            $prev_value
        );
    }
}
if (!\function_exists('delete_media_folder_meta')) {
    /**
     * Remove metadata matching criteria from a folder.
     *
     * You can match based on the key, or key and value. Removing based on key and
     * value, will keep from removing duplicate metadata with the same key. It also
     * allows removing all metadata matching key, if needed.
     *
     * @param int $folder_id Folder ID.
     * @param string $meta_key Metadata name.
     * @param mixed $meta_value Metadata value. Must be serializable if non-scalar.
     * @return boolean True on success, false on failure.
     */
    function delete_media_folder_meta($folder_id, $meta_key, $meta_value = '') {
        \MatthiasWeb\RealMediaLibrary\metadata\Meta::getInstance();
        // Necessary checks to prepare meta
        return \delete_metadata('realmedialibrary', \_wp_rml_meta_fix_absint($folder_id), $meta_key, $meta_value);
    }
}
if (!\function_exists('delete_media_folder_meta_by_key')) {
    /**
     * Delete everything from folder meta matching meta key.
     *
     * @param string $folder_meta_key Key to search for when deleting.
     * @return boolean Whether the post meta key was deleted from the database.
     */
    function delete_media_folder_meta_by_key($folder_meta_key) {
        \MatthiasWeb\RealMediaLibrary\metadata\Meta::getInstance();
        // Necessery checks to prepare metas
        return \delete_metadata('realmedialibrary', null, $folder_meta_key, '', \true);
    }
}
if (!\function_exists('truncate_media_folder_meta')) {
    /**
     * Remove all meta of a folder. Use this with caution!!
     *
     * @param int $folder_id Folder ID
     * @return int
     */
    function truncate_media_folder_meta($folder_id) {
        \MatthiasWeb\RealMediaLibrary\metadata\Meta::getInstance();
        // Necessary checks to prepare meta
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare(
            'DELETE FROM ' . $wpdb->realmedialibrarymeta . ' WHERE realmedialibrary_id=%d',
            \_wp_rml_meta_fix_absint($folder_id)
        );
        return $wpdb->query($sql);
        // phpcs:enable WordPress.DB.PreparedSQL
    }
}
if (!\function_exists('add_rml_user_settings_box')) {
    /**
     * Add a visible content to the general user settings dialog.
     *
     * Example: Adding a new tab "Physical" group to user settings dialog (or RML/Folder/Meta/Groups for folder details)
     * ```php
     * add_filter("RML/User/Settings/Groups", function($groups) {
     *  $groups["physical"] = __("Physical");
     *  return $groups;
     * });
     * ```
     *
     * @param string $name Unique name for this meta box
     * @param IUserSettings $obj The object which implements IUserSettings
     * @param boolean $deprecated boolean Load the resources if exists (since 4.3.0 deprecated, scripts method is always called)
     * @param int $priority Priority for actions and filters
     * @param string $contentGroup The tab group for the meta settings, see example for adding a new group
     * @return boolean
     */
    function add_rml_user_settings_box($name, $obj, $deprecated = \false, $priority = 10, $contentGroup = '') {
        if (!\MatthiasWeb\RealMediaLibrary\metadata\Meta::getInstance()->add($name, $obj)) {
            return \false;
        }
        \add_filter(
            'RML/User/Settings/Content' . (empty($contentGroup) ? '' : '/' . $contentGroup),
            [$obj, 'content'],
            $priority,
            2
        );
        \add_filter('RML/User/Settings/Save', [$obj, 'save'], $priority, 3);
        \add_action('RML/Scripts', [$obj, 'scripts'], $priority);
        return \true;
    }
}
if (!\function_exists('add_rml_meta_box')) {
    /**
     * Add a visible content to the folder details dialog.
     *
     * Example: Adding a new tab "Physical" group to meta dialog (or RML/User/Settings/Groups for user settings)
     * ```php
     * add_filter("RML/Folder/Meta/Groups", function($groups) {
     *  $groups["physical"] = __("Physical");
     *  return $groups;
     * });
     * ```
     *
     * @param string $name Unique name for this meta box
     * @param IMetadata $obj The object which implements IMetadata
     * @param boolean $hasScripts boolean Load the resources if exists
     * @param int $priority Priority for actions and filters
     * @param string $contentGroup The tab group for the meta settings, see example for adding a new group
     * @return boolean
     */
    function add_rml_meta_box($name, $obj, $hasScripts = \false, $priority = 10, $contentGroup = '') {
        if (!\MatthiasWeb\RealMediaLibrary\metadata\Meta::getInstance()->add($name, $obj)) {
            return \false;
        }
        \add_filter(
            'RML/Folder/Meta/Content' . (empty($contentGroup) ? '' : '/' . $contentGroup),
            [$obj, 'content'],
            $priority,
            2
        );
        \add_filter('RML/Folder/Meta/Save', [$obj, 'save'], $priority, 3);
        \add_action('RML/Scripts', [$obj, 'scripts'], $priority);
        return \true;
    }
}
if (!\function_exists('_wp_rml_meta_fix_absint')) {
    /**
     * Fix absint() in WordPress
     *
     * @param int $folder_id
     * @return int
     * @internal
     */
    function _wp_rml_meta_fix_absint($folder_id) {
        return $folder_id === -1 ? 100000000000 : $folder_id;
    }
}

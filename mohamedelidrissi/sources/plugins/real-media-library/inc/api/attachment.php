<?php

/**
 * In this file you will find attachment relevant functions.
 *
 * DEFINED POST TYPES
 *
 *      define('RML_TYPE_FOLDER', 0);
 *      define('RML_TYPE_COLLECTION', 1);
 *      define('RML_TYPE_GALLERY', 2);
 *
 * ==========================================
 *
 * Example scenario #1:
 *   1. User navigates to /rml/collection1
 *   2. Use wp_rml_get_by_absolute_path("/collection1") to get the api\IFolder Object
 *   3. (Additional check) $folder->is(RML_TYPE_COLLECTION) to check, if it is a collection.
 *   4. Iterate the childrens with foreach ($folder->getChildren() as $value) { }
 *   5. In collection can only be other collections or galleries.
 *
 *   6. (Additional check) $value->is(RML_TYPE_GALLERY) to check, if it is a gallery.
 *   7. Fetch the IDs with $value->read();
 *
 * ==========================================
 *
 * If you want to use more functions look into the attachment\Structure Class.
 * You easily get it with attachment\Structure::getInstance() (Singleton).
 *
 * Meaning: Root = Unorganized Pictures
 *
 * ==========================================
 *
 * ORDER QUERY
 *
 * Using the custom order of galleries: In your get_posts()
 * query args use the option "orderby" => "rml" to get the
 * images ordered by custom user order.
 *
 * ==========================================
 *
 * CUSTOM FIELDS FOR FOLDERS, COLLECTIONS, GALLERIES, ....
 *
 * You want create your own custom fields for a rml object?
 * Have a look at the metadata\Meta class.
 */
use MatthiasWeb\RealMediaLibrary\attachment\Filter;
use MatthiasWeb\RealMediaLibrary\attachment\Shortcut;
use MatthiasWeb\RealMediaLibrary\Core;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
if (!\function_exists('wp_rml_get_attachments')) {
    /**
     * Reads content of a folder.
     *
     * @param int $fid The folder id
     * @param string $order The order statement
     * @param string $orderby The order by statement
     * @return null|int[] Null if folder not exists or array of post ids
     */
    function wp_rml_get_attachments($fid, $order = null, $orderby = null) {
        $folder = \wp_rml_get_object_by_id($fid);
        return \is_rml_folder($folder) ? $folder->read($order, $orderby) : null;
    }
}
if (!\function_exists('wp_attachment_folder')) {
    /**
     * Returns the folder id of an given attachment or more than one attachment (array). If you pass an array
     * as attachment ids, then the default value does not work, only for single queries. When you pass a
     * shortcut attachment id, the folder id for the shortcut is returned.
     *
     * @param int $attachmentId The attachment ID, if you pass an array you get an array of folder IDs
     * @param int $default If no folder was found for this, this value is returned for the attachment
     * @return int|mixed Folder ID or $default or Array
     */
    function wp_attachment_folder($attachmentId, $default = null) {
        return \MatthiasWeb\RealMediaLibrary\attachment\Filter::getInstance()->getAttachmentFolder(
            $attachmentId,
            $default
        );
    }
}
if (!\function_exists('wp_attachment_order_update')) {
    /**
     * (Pro only) Moves an attachment before another given attachment in the order table.
     *
     * @param int $folderId The folder id where the attachment exists
     * @param int $attachmentId The attachment which should be moved
     * @param int $nextId The attachment next to the currentId, if it is false the currentId should be moved to the end of table.
     * @param int $lastIdInView If you have pagination, you can pass the last id from this view
     * @return boolean True or array with error strings
     */
    function wp_attachment_order_update($folderId, $attachmentId, $nextId, $lastIdInView = \false) {
        // Get folder
        $folder = \wp_rml_get_object_by_id($folderId);
        if (\is_rml_folder($folder)) {
            // Try to insert
            try {
                $folder->contentOrder($attachmentId, $nextId, $lastIdInView);
                return \true;
            } catch (\Exception $e) {
                \MatthiasWeb\RealMediaLibrary\Core::getInstance()->debug($e->getMessage(), __FUNCTION__);
                return [$e->getMessage()];
            }
        } else {
            \MatthiasWeb\RealMediaLibrary\Core::getInstance()->debug(
                "Could not find the folder with id {$folderId}",
                __FUNCTION__
            );
            return [\__('The given folder was not found.', \RML_TD)];
        }
    }
}
if (!\function_exists('wp_rml_move')) {
    /**
     * Move or create shortcuts of a set of attachments to a specific folder.
     *
     * If you copy attachments, the action called is also "RML/Item/Move"... but
     * there is a paramter $isShortcut.
     *
     * @param int $to Folder ID
     * @param int[] $ids Array of attachment ids
     * @param boolean $supress_validation Supress the permission validation
     * @param boolean $isShortcut Determines, if the ID's are copies
     * @return boolean|string[] True or Array with errors
     */
    function wp_rml_move($to, $ids, $supress_validation = \false, $isShortcut = \false) {
        if ($to === \false || !\is_numeric($to)) {
            // No movement
            return [\__('The given folder was not found.', \RML_TD)];
        }
        // Get folder
        $folder = \wp_rml_get_object_by_id($to);
        if (\is_rml_folder($folder)) {
            // Try to insert
            try {
                $folder->insert($ids, $supress_validation, $isShortcut);
                return \true;
            } catch (\Exception $e) {
                \MatthiasWeb\RealMediaLibrary\Core::getInstance()->debug($e->getMessage(), __FUNCTION__);
                return [$e->getMessage()];
            }
        } else {
            \MatthiasWeb\RealMediaLibrary\Core::getInstance()->debug(
                "Could not find the folder with id {$to}",
                __FUNCTION__
            );
            return [\__('The given folder was not found.', \RML_TD)];
        }
    }
}
/*
 * Shortcut relevant API.
 */
if (!\function_exists('wp_rml_create_shortcuts')) {
    /**
     * Link/Copy a set of attachments to a specific folder. When the folder
     * has already a given shortcut, the movement for the given attachment will be skipped.
     *
     * If you want to receive the last created shortcut ID's you can use the
     * wp_rml_created_shortcuts_last_ids() function.
     *
     * @param int $to Folder ID, if folder not exists then root will be
     * @param int[] $ids Array of attachment ids
     * @param boolean $supress_validation Supress the permission validation
     * @return boolean|string[] True or Array with errors
     */
    function wp_rml_create_shortcuts($to, $ids, $supress_validation = \false) {
        return \wp_rml_move($to, $ids, $supress_validation, \true);
    }
}
if (!\function_exists('wp_rml_created_shortcuts_last_ids')) {
    /**
     * If you create shortcuts you can get the ids for those shortcuts with this function.
     *
     * @return int[]
     */
    function wp_rml_created_shortcuts_last_ids() {
        return \MatthiasWeb\RealMediaLibrary\attachment\Shortcut::getInstance()->getLastIds();
    }
}
if (!\function_exists('wp_attachment_ensure_source_file')) {
    /**
     * Checks if a given attachment has already a shortcut in a given folder id
     * or has generally shortcuts.
     *
     * @param int|WP_Post $post The attachment id or a WP_Post object
     * @return int|WP_Post
     */
    function wp_attachment_ensure_source_file($post) {
        $isShortcut = \wp_attachment_is_shortcut($post, \true);
        if ($isShortcut > 0) {
            return $post instanceof \WP_Post ? \get_post($isShortcut) : $isShortcut;
        }
        return $post;
    }
}
if (!\function_exists('wp_attachment_has_shortcuts')) {
    /**
     * Checks if a given attachment has already a shortcut in a given folder id
     * or has generally shortcuts.
     *
     * @param int $postId The attachment id
     * @param int $fid The folder id, if false, it checks if there generally exists shortcuts
     * @return boolean
     */
    function wp_attachment_has_shortcuts($postId, $fid = \false) {
        global $wpdb;
        $table_name = \MatthiasWeb\RealMediaLibrary\Core::getInstance()->getTableName('posts');
        // phpcs:disable WordPress.DB.PreparedSQL
        if ($fid !== \false) {
            $sql = $wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE isShortcut=%d AND fid=%d", $postId, $fid);
        } else {
            $sql = $wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE isShortcut=%d", $postId);
        }
        return $wpdb->get_var($sql) > 0;
        // phpcs:enable WordPress.DB.PreparedSQL
    }
}
if (!\function_exists('wp_attachment_get_shortcuts')) {
    /**
     * Checks if a given attachment ID has shortcut and returns the shortcut IDs as array.
     *
     * @param int $postId The attachment id
     * @param int $fid The folder id, if false, it checks if there generally exists shortcuts
     * @param boolean $extended If true the result is an array with all information about the associated folder
     * @return mixed
     */
    function wp_attachment_get_shortcuts($postId, $fid = \false, $extended = \false) {
        global $wpdb;
        $table_name = \MatthiasWeb\RealMediaLibrary\Core::getInstance()->getTableName('posts');
        $table_name_rml = \MatthiasWeb\RealMediaLibrary\Core::getInstance()->getTableName();
        $join = $extended ? "LEFT JOIN {$table_name_rml} AS rml ON rml.id = p.fid" : '';
        $select = $extended ? ', rml.*' : '';
        $orderby = $extended ? 'ORDER BY name' : '';
        // phpcs:disable WordPress.DB.PreparedSQL
        if ($fid !== \false) {
            $sql = $wpdb->prepare(
                "SELECT p.attachment, p.fid AS folderId {$select} FROM {$table_name} AS p {$join} WHERE p.isShortcut=%d AND p.fid=%d {$orderby}",
                $postId,
                $fid
            );
        } else {
            $sql = $wpdb->prepare(
                "SELECT p.attachment, p.fid AS folderId {$select} FROM {$table_name} AS p {$join} WHERE p.isShortcut=%d {$orderby}",
                $postId
            );
        }
        return $extended ? $wpdb->get_results($sql, \ARRAY_A) : $wpdb->get_col($sql);
        // phpcs:enable WordPress.DB.PreparedSQL
    }
}
if (!\function_exists('wp_attachment_is_shortcut')) {
    /**
     * Checks if a given attachment is a shortcut, use the $returnSourceId
     * parameter to get the source attachment id.
     *
     * @param int|WP_Post $post The attachment id or a WP_Post object
     * @param boolean $returnSourceId If true, the return will be the source attachment id or 0 if it is no shortcut
     * @return boolean|int
     */
    function wp_attachment_is_shortcut($post, $returnSourceId = \false) {
        $guid = \get_the_guid($post);
        \preg_match('/\\?sc=([0-9]+)$/', $guid, $matches);
        if (isset($matches) && \is_array($matches) && isset($matches[1])) {
            return $returnSourceId ? (int) $matches[1] : \true;
        } else {
            return $returnSourceId ? 0 : \false;
        }
    }
}
if (!\function_exists('_wp_rml_synchronize_attachment')) {
    /**
     * Synchronizes a result with the realmedialibrary_posts table so on this
     * base there can be made the folder content. It also creates shortcuts, if the
     * given $isShortcut parameter is true.
     *
     * Do not use this directly, instead use the wp_rml_move function.
     *
     * @param int $postId The post ID
     * @param int $fid The folder ID
     * @param boolean $isShortcut true = Is shortcut in the given folder, false = Is no shortcut, mainly in this folder
     * @return boolean
     */
    function _wp_rml_synchronize_attachment($postId, $fid, $isShortcut = \false) {
        return \MatthiasWeb\RealMediaLibrary\attachment\Shortcut::getInstance()->create($postId, $fid, $isShortcut);
    }
}

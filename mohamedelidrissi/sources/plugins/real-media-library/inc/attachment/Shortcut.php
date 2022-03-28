<?php

namespace MatthiasWeb\RealMediaLibrary\attachment;

use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use Exception;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handle the metadata and attached file for shortcuts.
 */
class Shortcut {
    use UtilsProvider;
    private static $me = null;
    /**
     * Avoid recursive creation of shortcuts.
     */
    private $lockCreate = \false;
    /**
     * The last generated shortcut ids.
     */
    private $lastIds = \false;
    /**
     * C'tor.
     */
    private function __construct() {
        // Silence is golden.
    }
    /**
     * Creates a shortcut.
     *
     * @param int $postId
     * @param int $fid
     * @param boolean $isShortcut
     * @return boolean
     * @see wp_rml_create_shortcuts
     * @see _wp_rml_synchronize_attachment
     */
    public function create($postId, $fid, $isShortcut = \false) {
        global $wpdb;
        // Is locked?
        if ($this->lockCreate === \true) {
            $this->lockCreate = \false;
            return \false;
        }
        // Collect data
        $table_name = $this->getTableName('posts');
        $oldFolder = wp_attachment_folder($postId);
        $isShortcut = $isShortcut ? 1 : 0;
        $attachmentId = $postId;
        // The id for the realmedialibrary_posts table
        // Check if attachment exists
        if (get_post_type($postId) !== 'attachment') {
            return \false;
        }
        // Process
        if ($isShortcut > 0) {
            // Ensure, that we are working with the source file and not create a shortcut from a shortcut...
            $postId = wp_attachment_ensure_source_file($postId);
            // Prepare the new post
            $wp_post = get_post($postId);
            $new_post = [
                'guid' => $wp_post->guid . '?sc=' . $postId,
                'post_mime_type' => $wp_post->post_mime_type,
                'post_title' => $wp_post->post_title,
                'post_content' => '',
                'post_excerpt' => $wp_post->post_excerpt,
                // Caption
                'post_content' => $wp_post->post_content,
                // Description
                'post_status' => 'inherit'
            ];
            $attachedFile = get_attached_file($postId);
            // Create new post
            $this->lockCreate = \true;
            try {
                $scId = wp_insert_attachment($new_post, $attachedFile);
                // Copy alt text if present
                $altText = get_post_meta($postId, '_wp_attachment_image_alt', \true);
                if (!empty($altText)) {
                    update_post_meta($scId, '_wp_attachment_image_alt', $altText);
                }
                $this->debug('Shortcut for ' . $postId . ' created in posts table with id ' . $scId, __METHOD__);
                $this->lockCreate = \false;
            } catch (\Exception $e) {
                $this->lockCreate = \false;
                return \false;
            }
            $attachmentId = $scId;
            $this->lastIds[] = $attachmentId;
            $isShortcut = $postId;
        } else {
            $isShortcut = wp_attachment_is_shortcut($postId, \true);
        }
        // Insert or update the new attachment relationship
        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare(
            "INSERT INTO {$table_name} (`attachment`, `fid`, `isShortcut`)\n            VALUES (%d, %d, %d) ON DUPLICATE KEY UPDATE fid=VALUES(fid), isShortcut=VALUES(isShortcut), nr=0, oldCustomNr=0",
            $attachmentId,
            $fid,
            $isShortcut
        );
        $wpdb->query($sql);
        // phpcs:enable WordPress.DB.PreparedSQL
        /**
         * An attachment is moved to a specific folder.
         *
         * @param {int} $postId The post id of the attachment
         * @param {int} $oldFolder The old folder id of the attachment
         * @param {int} $fid The new folder id of the attachment
         * @param {boolean} $isShortcut If true the attachment was copied to a folder
         * @hook RML/Item/Moved
         */
        do_action('RML/Item/Moved', $postId, $oldFolder, $fid, $isShortcut);
        return \true;
    }
    /**
     * Check if a meta key is inheritable.
     *
     * @param string $meta_key
     * @param boolean $withAttached
     * @return boolean
     */
    private function isInheritableMetaKey($meta_key, $withAttached = \true) {
        return $meta_key === '_wp_attachment_metadata' ||
            ($meta_key === '_wp_attached_file' && $withAttached) ||
            $meta_key === '_wp_attachment_backup_sizes';
    }
    /**
     * If it is a shortcut, read the metadata from the source file.
     * It also handles the wp_delete_attachment process to avoid to delete
     * the source files if shortcut.
     *
     * @param mixed $check
     * @param int $object_id
     * @param string $meta_key
     * @param boolean $single
     * @return mixed
     */
    public function get_post_metadata($check, $object_id, $meta_key, $single) {
        if ($this->isInheritableMetaKey($meta_key) && ($source_id = wp_attachment_is_shortcut($object_id, \true))) {
            // Check if we want to delete the attachment
            // phpcs:disable
            $backtrace = \debug_backtrace();
            // phpcs:enable
            foreach ($backtrace as $value) {
                if ($value['function'] === 'wp_delete_attachment') {
                    $this->debug(
                        'Tried to delete an attachment shortcut... Avoid to delete the physical files (' .
                            $meta_key .
                            ')',
                        __METHOD__
                    );
                    return $single ? '' : [[]];
                }
            }
            // Return main file data
            $meta = get_post_meta($source_id, $meta_key, $single);
            return $single ? [$meta] : $meta;
        }
        return $check;
    }
    /**
     * Avoids to generate own meta data for shortcuts.
     *
     * @param mixed $check
     * @param int $object_id
     * @param string $meta_key
     * @param string $meta_value
     * @param boolean $unique
     * @return mixed
     */
    public function add_post_metadata($check, $object_id, $meta_key, $meta_value, $unique) {
        if ($this->isInheritableMetaKey($meta_key) && ($source_id = wp_attachment_is_shortcut($object_id, \true))) {
            $add = add_post_meta($source_id, $meta_key, $meta_value, $unique);
            return \is_bool($add) ? $add : $add > 0;
        }
        return $check;
    }
    /**
     * Avoids to generate own meta data for shortcuts.
     *
     * @param mixed $check
     * @param int $object_id
     * @param string $meta_key
     * @param string $meta_value
     * @param string $prev_value
     * @return mixed
     */
    public function update_post_metadata($check, $object_id, $meta_key, $meta_value, $prev_value) {
        if ($this->isInheritableMetaKey($meta_key) && ($source_id = wp_attachment_is_shortcut($object_id, \true))) {
            $this->debug(
                'Probably the image gets regenerated, save the new metadata to the source file...',
                __METHOD__
            );
            $update = update_post_meta($source_id, $meta_key, $meta_value, $prev_value);
            return \is_bool($update) ? $update : $update > 0;
        }
        return $check;
    }
    /**
     * Get last generated shortcut ids.
     *
     * @see wp_rml_created_shortcuts_last_ids()
     * @return int[]
     */
    public function getLastIds() {
        return \is_array($this->lastIds) ? $this->lastIds : ($this->lastIds = []);
    }
    /**
     * Delete all associated shortcuts.
     *
     * @param int $postId
     */
    public function delete_attachment($postId) {
        $shortcuts = wp_attachment_get_shortcuts($postId);
        if (\count($shortcuts) > 0) {
            $this->debug('Found shortcuts for this postid (' . $postId . '): ' . \json_encode($shortcuts), __METHOD__);
            foreach ($shortcuts as $value) {
                wp_delete_attachment($value, \true);
            }
        }
    }
    /**
     * This function should only be used in the Creatable::insert() function.
     */
    public function _resetLastIds() {
        $this->lastIds = [];
    }
    /**
     * Get instance.
     *
     * @return Shortcut
     */
    public static function getInstance() {
        return self::$me === null ? (self::$me = new \MatthiasWeb\RealMediaLibrary\attachment\Shortcut()) : self::$me;
    }
}

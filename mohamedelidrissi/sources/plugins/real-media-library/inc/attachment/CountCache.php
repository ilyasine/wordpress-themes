<?php

namespace MatthiasWeb\RealMediaLibrary\attachment;

use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This class handles the count cache for the folder structure.
 */
class CountCache {
    use UtilsProvider;
    private static $me = null;
    /**
     * An array of new attachment ID's which should be updated
     * with the this::updateCountCache method. This includes also
     * deleted attachments. The "new" means the attachments which are changed,
     * but new for the update.
     */
    private $newAttachments = [];
    /**
     * A collection of folder ids which gets reset on wp_die event.
     */
    private $folderIdsOnWpDie = [];
    /**
     * C'tor.
     */
    private function __construct() {
        // Silence is golden.
    }
    /**
     * Handle the count cache for the folders. This should avoid
     * a lack SQL subquery which loads data from the posts table.
     *
     * @param int[] $folders Array of folders ID, if null then all folders with cnt = NULL are updated
     * @param int[] $attachments Array of attachments ID, is merged with $folders if given
     * @param boolean $onlyReturn Set to true if you only want the SQL query
     * @return string|null
     */
    public function updateCountCache($folders = null, $attachments = null, $onlyReturn = \false) {
        global $wpdb;
        if ($folders !== null) {
            $this->debug('Update count cache for this folders: ' . \json_encode($folders), __METHOD__);
        }
        if ($attachments !== null) {
            $this->debug('Update count cache for this attachments: ' . \json_encode($attachments), __METHOD__);
        }
        $table_name = $this->getTableName();
        // Create where statement
        $where = '';
        // Update by specific folders
        if (\is_array($folders) && \count($folders) > 0) {
            $folders = \array_unique($folders);
            $where .= ' tn.id IN (' . \implode(',', $folders) . ') ';
        }
        // Update by attachment IDs, catch all touched
        if (\is_array($attachments) && \count($attachments) > 0) {
            $attachments = \array_unique($attachments);
            $attachments_in = \implode(',', $attachments);
            $table_posts = $this->getTableName('posts');
            $where .=
                ($where === '' ? '' : ' OR') .
                " tn.id IN (SELECT DISTINCT(rmlposts.fid) FROM {$table_posts} AS rmlposts WHERE rmlposts.attachment IN ({$attachments_in})) ";
        }
        // Default where statement
        if ($where === '') {
            $where = 'tn.cnt IS NULL';
        }
        // Create statement
        $sqlStatement = "UPDATE {$table_name} AS tn SET cnt = (" . $this->getSingleCountSql() . ") WHERE {$where}";
        if ($onlyReturn) {
            return $sqlStatement;
        } elseif (has_action('RML/Count/Update')) {
            /**
             * The folder needs to be updated. If minimum one hook exists the update on the main table
             * is no longer executed. This action is used for example for the WPML compatibility.
             *
             * @param {array} $folders Array of folders ID, if null then all folders with cnt = NULL are updated
             * @param {array} $attachments Array of attachments ID, is merged with $folders if given
             * @param {string} $where The where statement used for the main table
             * @hook RML/Count/Update
             */
            do_action('RML/Count/Update', $folders, $attachments, $where);
        } else {
            // phpcs:disable WordPress.DB.PreparedSQL
            $wpdb->query($sqlStatement);
            // phpcs:enable WordPress.DB.PreparedSQL
        }
    }
    /**
     * Get the single SQL for the subquery of count getter.
     *
     * @return string
     */
    public function getSingleCountSql() {
        /**
         * Get the posts clauses for the count cache.
         *
         * @param {string[]} $clauses The posts clauses with "from", "where"
         * @return {string[]} The posts clauses
         * @hook RML/Count/PostsClauses
         */
        $sql = apply_filters('RML/Count/PostsClauses', [
            'from' => $this->getTableName('posts') . ' AS rmlpostscnt',
            'where' => 'rmlpostscnt.fid = tn.id',
            'afterWhere' => ''
        ]);
        return 'SELECT COUNT(*) FROM ' . $sql['from'] . ' WHERE ' . $sql['where'] . ' ' . $sql['afterWhere'];
    }
    /**
     * Reset the count cache for the current blog id. The content of the array is not prepared for the statement
     *
     * @param int $folderId Array If you pass folder id/ids array, only this one will be reset.
     * @return CountCache
     */
    public function resetCountCache($folderId = null) {
        global $wpdb;
        $table_name = $this->getTableName();
        // phpcs:disable WordPress.DB.PreparedSQL
        if (\is_array($folderId)) {
            $wpdb->query("UPDATE {$table_name} SET cnt=NULL WHERE id IN (" . \implode(',', $folderId) . ')');
        } else {
            $wpdb->query("UPDATE {$table_name} SET cnt=NULL");
        }
        // phpcs:enable WordPress.DB.PreparedSQL
        /**
         * Reset a count cache for a folder or all folders.
         *
         * @param {int} $folderId Can be `null` if all folders need to be reset
         * @hook RML/Count/Reset
         * @since 4.10.3
         */
        do_action('RML/Count/Reset', $folderId);
        return $this;
    }
    /**
     * Is fired with wp_die event.
     *
     * @param int $folderId The folder id
     */
    public function resetCountCacheOnWpDie($folderId) {
        if (!\in_array($folderId, $this->folderIdsOnWpDie, \true)) {
            $this->folderIdsOnWpDie[] = $folderId;
        }
    }
    /**
     * Update at the end of the script execution the count of the given added / deleted attachments.
     */
    public function wp_die() {
        if (\count($this->newAttachments) > 0) {
            $this->debug('Update count cache on wp die...', __METHOD__);
            $this->updateCountCache(null, $this->newAttachments);
        }
        if (\count($this->folderIdsOnWpDie) > 0) {
            $this->debug('Update count cache on wp die...', __METHOD__);
            $this->updateCountCache($this->folderIdsOnWpDie);
        }
        // Reset because this function can be called multiple
        $this->newAttachments = [];
        $this->folderIdsOnWpDie = [];
    }
    /**
     * Add an attachment to the update queue.
     *
     * @param int $id The attachment id
     */
    public function addNewAttachment($id) {
        $this->newAttachments[] = $id;
        return $this;
    }
    /**
     * Get instance.
     *
     * @return CountCache
     */
    public static function getInstance() {
        return self::$me === null ? (self::$me = new \MatthiasWeb\RealMediaLibrary\attachment\CountCache()) : self::$me;
    }
}

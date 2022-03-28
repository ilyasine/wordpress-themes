<?php

namespace MatthiasWeb\RealMediaLibrary\lite\order;

use MatthiasWeb\RealMediaLibrary\api\IFolder;
use MatthiasWeb\RealMediaLibrary\attachment\Filter;
use MatthiasWeb\RealMediaLibrary\Core;
use WP_Query;
use Exception;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
trait Sortable {
    // Documented in IFolderContent
    public function contentDeleteOrder() {
        if ($this->getContentCustomOrder() !== 1) {
            return \false;
        }
        delete_media_folder_meta($this->id, 'orderby');
        $this->debug("Deleted order of the folder {$this->id}", __METHOD__);
        return \true;
    }
    // Documented in IFolderContent
    public function contentRestoreOldCustomNr() {
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        $wpdb->query(
            $wpdb->prepare('UPDATE ' . $this->getTableName('posts') . ' SET nr = oldCustomNr WHERE fid=%d;', $this->id)
        );
        // phpcs:enable WordPress.DB.PreparedSQL
        delete_media_folder_meta($this->id, 'orderAutomatically');
        $this->debug("Restored the order of folder {$this->id} to the old custom order", __METHOD__);
        return \true;
    }
    // Documented in IFolderContent
    public function contentOrder($attachmentId, $nextId, $lastIdInView = \false) {
        // Check, if the folder needs the order enabled first
        $contentCustomOrder = $this->getContentCustomOrder();
        if ($contentCustomOrder === 0 && !$this->contentEnableOrder()) {
            throw new \Exception(__('The given folder does not allow to reorder the files.', RML_TD));
        } elseif ($contentCustomOrder === 1) {
            // Reindex
            $this->contentReindex();
        }
        $orderAutomatically = $this->getRowData('orderAutomatically');
        if (!empty($orderAutomatically)) {
            throw new \Exception(__('This folder has an automatic order. Please deactivate that first.', RML_TD));
        }
        /**
         * (Pro only) Allow changing the post ids for the content order process. This is for example
         * necessary when using a multilingual plugin like WPML so the order is always synced
         * when using RML/Sortable/PostsClauses.
         *
         * @param {array} $ids The ids for attachment, next (can be false) and lastInView (can be false)
         * @return {array} $ids
         * @hook RML/Sortable/Ids
         * @since 4.0.8
         */
        $changed = apply_filters('RML/Sortable/Ids', [
            'attachment' => $attachmentId,
            'next' => $nextId,
            'lastInView' => $lastIdInView
        ]);
        $attachmentId = $changed['attachment'];
        $nextId = $changed['next'];
        $lastIdInView = $changed['lastInView'];
        // Process
        global $wpdb;
        $table_name = $this->getTableName('posts');
        $this->debug(
            "The folder {$this->id} wants to move {$attachmentId} before {$nextId} (lastIdInView: {$lastIdInView})",
            __METHOD__
        );
        // Is it the real end?
        if ($nextId === \false && $lastIdInView !== \false) {
            $this->debug(
                'Want to move to the end of the list and there is a pagination system with the lastIdInView...',
                __METHOD__
            );
            $nextIdTo = $this->getAttachmentNextTo($lastIdInView);
            if ($nextIdTo > 0) {
                $nextId = $nextIdTo;
            }
        }
        // Push to end
        if ($nextId === \false) {
            $newOrder = $this->getContentAggregationNr('MAX') + 1;
            $this->debug("Order the attachment to the end and use the new order value {$newOrder}...", __METHOD__);
        } else {
            $_newOrder = $this->getContentNrOf($nextId);
            // Temp save in this, because the query can fail
            if ($_newOrder === \false) {
                $_newOrder = $this->getContentAggregationNr('MAX') + 1;
                // Put to last
            }
            $this->debug(
                "Order the attachment before {$nextId} and change the order value {$_newOrder} for the moved attachment....",
                __METHOD__
            );
            // Count up the next ids
            // phpcs:disable WordPress.DB.PreparedSQL
            $wpdb->query("UPDATE {$table_name} SET nr = nr + 1 WHERE fid = {$this->id} AND nr >= {$_newOrder}");
            // phpcs:enable WordPress.DB.PreparedSQL
            $newOrder = $_newOrder;
        }
        // Update the new order number
        if (isset($newOrder) && $newOrder > 0) {
            // phpcs:disable WordPress.DB.PreparedSQL
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$table_name} SET nr=%d WHERE fid=%d AND attachment=%d",
                    $newOrder,
                    $this->id,
                    $attachmentId
                )
            );
            // phpcs:enable WordPress.DB.PreparedSQL
            $this->debug('Successfully updated the order of the attachment', __METHOD__);
            // Save to old custom order
            // phpcs:disable WordPress.DB.PreparedSQL
            $wpdb->query($wpdb->prepare('UPDATE ' . $table_name . ' SET oldCustomNr = nr WHERE fid = %d;', $this->id));
            // phpcs:enable WordPress.DB.PreparedSQL
            $this->debug('Successfully updated the old custom nr of the folder', __METHOD__);
            /**
             * (Pro only) This action is fired after an item in a folder got ordered by drag&drop.
             *
             * @param {int} $fid The folder id
             * @param {int} $attachmentId The attachment id which got updated
             * @param {int} $newOrder The new "nr" value
             * @hook RML/Item/DragDrop
             * @since 4.5.3
             */
            do_action('RML/Item/DragDrop', $this->id, $attachmentId, $newOrder);
        } else {
            throw new \Exception(__('Something went wrong.', RML_TD));
        }
    }
    // Documented in IFolderContent
    public function contentOrderBy($orderby, $writeMetadata = \true) {
        $orders = self::getAvailableContentOrders();
        $fid = $this->getId();
        $this->debug("Try to order the folder {$fid} by {$orderby}...", __METHOD__);
        if (\in_array($orderby, \array_keys($orders), \true)) {
            global $wpdb;
            // Get order
            $split = \explode('_', $orderby);
            $order = $orders[$orderby];
            $direction = $split[1];
            $table_name = $this->getTableName('posts');
            // Run SQL
            // phpcs:disable WordPress.DB.PreparedSQL
            $sql = $wpdb->prepare(
                "UPDATE {$table_name} AS rmlo2\n                LEFT JOIN (\n                \tSELECT @rownum := @rownum + 1 AS nr, t.ID\n                \tFROM ( SELECT wp.ID\n                \t\tFROM {$table_name} AS rmlo\n                \t\tINNER JOIN {$wpdb->posts} AS wp ON rmlo.attachment = wp.id AND wp.post_type = \"attachment\"\n                \t\tWHERE rmlo.fid = %d\n                \t\tORDER BY " .
                    $order['sqlOrder'] .
                    " {$direction} ) AS t, (SELECT @rownum := 0) AS r\n                ) AS rmlonew ON rmlo2.attachment = rmlonew.ID\n                SET rmlo2.nr = rmlonew.nr\n                WHERE rmlo2.fid = %d",
                $fid,
                $fid
            );
            $wpdb->query(
                $wpdb->prepare('UPDATE ' . $this->getTableName() . ' SET contentCustomOrder=1 WHERE id = %d', $fid)
            );
            $wpdb->query($sql);
            // phpcs:enable WordPress.DB.PreparedSQL
            // Save in the metadata
            if ($writeMetadata) {
                update_media_folder_meta($fid, 'orderby', $orderby);
            }
            $this->debug('Successfully ordered folder', __METHOD__);
            /**
             * (Pro only) This action is fired after items ins a folder got ordered by criteria.
             *
             * @param {int} $fid The folder id
             * @param {string} $orderby Orderby column
             * @param {string} $order 'ASC' or 'DESC'
             * @hook RML/Folder/OrderBy
             * @since 4.5.3
             */
            do_action('RML/Folder/OrderBy', $fid, $split[0], $split[1]);
            return \true;
        } else {
            $this->debug("'{$orderby}' is not a valid order...", __METHOD__);
            return \false;
        }
    }
    // Documented in IFolderContent
    public function contentIndex($delete = \true) {
        // Check, if this folder is allowed for custom content order
        if (!$this->isContentCustomOrderAllowed()) {
            return \false;
        }
        // First, delete the old entries from this folder
        if ($delete && !$this->contentDeleteOrder()) {
            return \false;
        }
        // Create INSERT-SELECT statement for this folder
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare(
            'UPDATE ' .
                $this->getTableName('posts') .
                " AS rmlp2\n                LEFT JOIN (\n                    SELECT\n                    \twpp2.ID AS attachment,\n                    \twpp2.fid AS fid,\n                    \t@rownum := @rownum + 1 AS nr,\n                    \t@rownum AS oldCustomNr\n                    FROM (SELECT @rownum := 0) AS r,\n                    \t(SELECT wpp.ID, rmlposts.fid\n                    \t\tFROM {$wpdb->posts} AS wpp\n                    \t\tINNER JOIN " .
                $this->getTableName('posts') .
                " AS rmlposts ON ( wpp.ID = rmlposts.attachment )\n                    \t\tWHERE rmlposts.fid = %d\n                    \t\tAND wpp.post_type = 'attachment'\n                    \t\tAND wpp.post_status = 'inherit'\n                    \t\tGROUP BY wpp.ID ORDER BY wpp.post_date DESC, wpp.ID DESC) \n                    \tAS wpp2\n                ) AS rmlnew ON rmlp2.attachment = rmlnew.attachment\n                SET rmlp2.nr = rmlnew.nr, rmlp2.oldCustomNr = rmlnew.oldCustomNr\n                WHERE rmlp2.fid = %d",
            $this->id,
            $this->id
        );
        $wpdb->query($sql);
        // phpcs:enable WordPress.DB.PreparedSQL
        $this->debug("Indexed the content order of {$this->id}", __METHOD__);
        return \true;
    }
    // Documented in IFolderContent
    public function contentReindex() {
        if ($this->getContentCustomOrder() !== 1) {
            return \false;
        }
        global $wpdb;
        $table_name = $this->getTableName('posts');
        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = "UPDATE {$table_name} AS rml2\n                LEFT JOIN (\n                \tSELECT @rownum := @rownum + 1 AS nr, t.attachment\n                    FROM ( SELECT rml.attachment\n                        FROM {$table_name} AS rml\n                        WHERE rml.fid = {$this->id}\n                        ORDER BY rml.nr ASC )\n                        AS t, (SELECT @rownum := 0) AS r\n                ) AS rmlnew ON rml2.attachment = rmlnew.attachment\n                SET rml2.nr = rmlnew.nr\n                WHERE rml2.fid = {$this->id}";
        $wpdb->query($sql);
        // phpcs:enable WordPress.DB.PreparedSQL
        $this->debug("Reindexed the content order of {$this->id}", __METHOD__);
        return \true;
    }
    // Documented in IFolderContent
    public function contentEnableOrder() {
        // Check, if this folder is allowed for custom content order
        if (!$this->contentIndex(\false)) {
            return \false;
        }
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        $wpdb->query(
            $wpdb->prepare('UPDATE ' . $this->getTableName() . ' SET contentCustomOrder=1 WHERE id = %d', $this->id)
        );
        // phpcs:enable WordPress.DB.PreparedSQL
        $this->contentCustomOrder = 1;
        return \true;
    }
    // Documented in IFolderContent
    public function getAttachmentNextTo($attachmentId) {
        if ($this->getContentCustomOrder() !== 1) {
            return \false;
        }
        global $wpdb;
        $query = new \WP_Query([
            'post_status' => 'inherit',
            'post_type' => 'attachment',
            'rml_folder' => $this->id,
            'orderby' => 'rml',
            'order' => 'ASC'
        ]);
        $sql = \str_replace('SQL_CALC_FOUND_ROWS', '', $query->request);
        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare(
            'SELECT * FROM (' .
                $sql .
                ') tmpnext
		    WHERE orderNr > (SELECT orderNr FROM (' .
                $sql .
                ') tmpnext2 WHERE ID = %d)
		    LIMIT 1',
            $attachmentId
        );
        $result = $wpdb->get_row($sql, ARRAY_A);
        // phpcs:enable WordPress.DB.PreparedSQL
        return $result;
    }
    // Documented in IFolderContent
    public function getContentAggregationNr($function = 'MAX') {
        if (!\in_array($function, ['MAX', 'MIN'], \true)) {
            throw new \Exception('Only max or min aggregation function allowed!');
        }
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        $max = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT ' . $function . '(o.nr) FROM ' . $this->getTableName('posts') . ' AS o WHERE o.fid = %d',
                $this->id
            )
        );
        // phpcs:enable WordPress.DB.PreparedSQL
        return !($max > 0) ? \false : $max;
    }
    // Documented in IFolderContent
    public function getContentNrOf($attachmentId) {
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        $nextNr = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT o.nr FROM ' . $this->getTableName('posts') . ' AS o WHERE o.attachment = %d AND o.fid = %d',
                $attachmentId,
                $this->id
            )
        );
        // phpcs:enable WordPress.DB.PreparedSQL
        return !($nextNr > 0) ? \false : $nextNr;
    }
    // Documented in IFolderContent
    public function getContentOldCustomNrCount() {
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        $result = $wpdb->get_col(
            $wpdb->prepare(
                'SELECT COUNT(oldCustomNr) FROM ' . $this->getTableName('posts') . ' WHERE fid=%d',
                $this->id
            )
        );
        // phpcs:enable WordPress.DB.PreparedSQL
        return $result[0];
    }
    /**
     * When moving to a folder with content custom order, reindex the folder content.
     *
     * @param int $folderId
     * @param int[] $ids
     * @param IFolder $folder
     * @param boolean $isShortcut
     * @hooked RML/Item/MoveFinished
     */
    public static function item_move_finished($folderId, $ids, $folder, $isShortcut) {
        $core = \MatthiasWeb\RealMediaLibrary\Core::getInstance();
        // Apply automatic order
        $orderAutomatically = (bool) $folder->getRowData('orderAutomatically');
        if ($orderAutomatically) {
            $order = $folder->getRowData('lastOrderBy');
            if (!empty($order)) {
                $core->debug("{$folderId} detected some new files, synchronize with automatic orderby...", __METHOD__);
                return $folder->contentOrderBy($order, \false);
            }
        }
        if ($folder->getContentCustomOrder() === 1) {
            $core->debug("{$folderId} detected some new files, synchronize with custom content order...", __METHOD__);
            $folder->contentReindex();
        }
    }
    /**
     * JOIN the order table and orderby the nr.
     * It is only affected when
     * $query = new \WP_Query([
     *      'post_status' => 'inherit',
     *      'post_type' => 'attachment',
     *      'rml_folder' => 4,
     *      'orderby' => 'rml'
     * ));
     *
     * @param string[] $pieces
     * @param WP_Query $query
     * @return string[]
     */
    public static function posts_clauses($pieces, $query) {
        global $wpdb;
        $folderId = !empty($query->query_vars['parsed_rml_folder']) ? $query->query_vars['parsed_rml_folder'] : 0;
        $applyOrder =
            $folderId > 0 &&
            (empty($query->query['orderby']) ||
                (isset($query->query['orderby']) && $query->query['orderby'] === 'rml'));
        if ($query->get('use_rml_folder') !== \true) {
            return $pieces;
        }
        if ($applyOrder) {
            $pieces['orderby'] = $wpdb->posts . '.post_date DESC, ' . $wpdb->posts . '.ID DESC';
        }
        // Get folder
        if ($folderId !== 0) {
            $folder = wp_rml_get_object_by_id($query->query_vars['parsed_rml_folder']);
            if ($folder === null) {
                return $pieces;
            }
        } else {
            $folder = wp_rml_get_object_by_id(-1);
        }
        $applyOrder = $applyOrder && !($folder->getContentCustomOrder() !== 1 && !$folder->forceContentCustomOrder());
        // left join and order by
        $pieces['fields'] = \trim($pieces['fields'], ',') . ', IFNULL(rmlorder.nr, -1) orderNr';
        $pieces['join'] .=
            ' LEFT JOIN ' .
            \MatthiasWeb\RealMediaLibrary\Core::getInstance()->getTableName('posts') .
            ' AS rmlorder ON rmlorder.fid=IFNULL(rmlposts.fid, 0) AND rmlorder.attachment = ' .
            $wpdb->posts .
            '.ID ';
        if ($folder->postsClauses($pieces) === \false && $applyOrder) {
            $pieces['orderby'] = 'rmlorder.nr, ' . $wpdb->posts . '.post_date DESC, ' . $wpdb->posts . '.ID DESC';
            /**
             * (Pro only) Modify the filter expression for the sortable content. You can str_replace the
             * "rmlorder.nr" for the main order column. Use this filter in conjunction with
             * RML/Sortable/Ids so you can modify the ids for the content order process.
             *
             * @param {array} $pieces The list of clauses for the query
             * @param {WP_Query} $query The WP_Query instance
             * @param {IFolder} $folder The folder to query
             * @return {array} $pieces
             * @hook RML/Sortable/PostsClauses
             * @see https://developer.wordpress.org/reference/hooks/posts_clauses/
             * @since 4.0.8
             */
            $pieces = apply_filters('RML/Sortable/PostsClauses', $pieces, $query, $folder);
        }
        return $pieces;
    }
    /**
     * Media Library Assistant extension.
     *
     * @param WP_Query $query
     * @return WP_Query
     */
    public static function mla_media_modal_query_final_terms($query) {
        $folderId = \MatthiasWeb\RealMediaLibrary\attachment\Filter::getInstance()->getFolder(null, \true);
        if ($folderId !== null) {
            $folder = wp_rml_get_object_by_id($folderId);
            if ($folder !== null && $folder->getContentCustomOrder() === 1) {
                $query['orderby'] = 'rml';
                $query['order'] = 'asc';
            }
        }
        return $query;
    }
    /**
     * Deletes the complete order. Use it with CAUTION!
     */
    public static function delete_all_order() {
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        $wpdb->query(
            'UPDATE ' .
                \MatthiasWeb\RealMediaLibrary\Core::getInstance()->getTableName('posts') .
                ' SET nr=null, oldCustomNr=NULL'
        );
        $wpdb->query(
            'UPDATE ' . \MatthiasWeb\RealMediaLibrary\Core::getInstance()->getTableName() . ' SET contentCustomOrder=0'
        );
        // phpcs:enable WordPress.DB.PreparedSQL
        \MatthiasWeb\RealMediaLibrary\Core::getInstance()->debug('Deleted the whole order of all folders', __METHOD__);
    }
}

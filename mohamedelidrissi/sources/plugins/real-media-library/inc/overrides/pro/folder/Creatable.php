<?php

namespace MatthiasWeb\RealMediaLibrary\lite\folder;

use Exception;
use MatthiasWeb\RealMediaLibrary\Core;
use MatthiasWeb\RealMediaLibrary\exception\FolderAlreadyExistsException;
use MatthiasWeb\RealMediaLibrary\Util;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
trait Creatable {
    // Documented in IFolderActions
    public function resetSubfolderOrder() {
        delete_media_folder_meta($this->id, 'lastSubOrderBy');
        $this->debug("Deleted subfolder order of the folder {$this->id}", __METHOD__);
        return \true;
    }
    // Documented in IFolderActions
    public function orderSubfolders($orderby, $writeMetadata = \true) {
        $orders = self::getAvailableSubfolderOrders();
        $core = \MatthiasWeb\RealMediaLibrary\Core::getInstance();
        $core->debug("Try to order the subfolders of {$this->id} by {$orderby}...", __METHOD__);
        if (\in_array($orderby, \array_keys($orders), \true)) {
            global $wpdb;
            // Get order
            $split = \explode('_', $orderby);
            $order = $orders[$orderby];
            $direction = $split[1];
            $table_name = $core->getTableName();
            // Run SQL
            // phpcs:disable WordPress.DB.PreparedSQL
            $sql = $wpdb->prepare(
                "UPDATE {$table_name} AS rmlo2\n                LEFT JOIN (\n                \tSELECT @rownum := @rownum + 1 AS ord, t.id\n                \tFROM ( SELECT rmlo.id\n                \t\tFROM {$table_name} AS rmlo\n                \t\tWHERE rmlo.parent = %d\n                \t\tORDER BY " .
                    $order['sqlOrder'] .
                    " {$direction} ) AS t, (SELECT @rownum := 0) AS r\n                ) AS rmlonew ON rmlo2.id = rmlonew.id\n                SET rmlo2.ord = rmlonew.ord\n                WHERE rmlo2.parent = %d",
                $this->id,
                $this->id
            );
            $wpdb->query($sql);
            // phpcs:enable WordPress.DB.PreparedSQL
            // Save in the metadata
            if ($writeMetadata) {
                update_media_folder_meta($this->id, 'lastSubOrderBy', $orderby);
            }
            $core->debug('Successfully ordered folder', __METHOD__);
            return \true;
        } else {
            $core->debug("'{$orderby}' is not a valid order...", __METHOD__);
            return \false;
        }
    }
    /**
     * Check if the current folders parent is automatically ordered by a criteria so
     * the order can be applied. This should be called when the hierarchy of the
     * folder is changed or when a new folder is added to that parent.
     *
     * @return boolean
     */
    protected function applySubfolderOrderBy() {
        $parent = wp_rml_get_object_by_id($this->getParent());
        if (!is_rml_folder($parent)) {
            return \false;
        }
        $orderAutomatically = (bool) $parent->getRowData('subOrderAutomatically');
        if ($orderAutomatically) {
            $order = $parent->getRowData('lastSubOrderBy');
            if (!empty($order)) {
                $this->debug(
                    'New subfolder ' .
                        $this->getId() .
                        ' in folder ' .
                        $parent->getId() .
                        ", automatically order by {$order} ...",
                    __METHOD__
                );
                return $parent->orderSubfolders($order, \false);
            }
        }
        return \false;
    }
    // Documented in IOverrideCreatable
    public function persistCheckParent() {
        // Check, if the parent exists
        $parentObj = wp_rml_get_object_by_id($this->parent);
        if (!is_rml_folder($parentObj)) {
            // translators:
            throw new \Exception(\sprintf(__('The parent %d does not exist.', RML_TD), $this->parent));
        }
        return $parentObj;
    }
    // Documented in IFolderActions
    public function reindexChildrens($resetData = \false) {
        global $wpdb;
        $table_name = $this->getTableName();
        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = "UPDATE {$table_name} AS rml2\n            LEFT JOIN (\n                SELECT @rownum := @rownum + 1 AS nr, t.ID\n                FROM ( SELECT rml.id\n                    FROM {$table_name} AS rml\n                    WHERE rml.parent = {$this->id}\n                    ORDER BY rml.ord )\n                    AS t, (SELECT @rownum := 0) AS r\n            ) AS rmlnew ON rml2.id = rmlnew.id\n            SET rml2.ord = rmlnew.nr\n            WHERE rml2.parent = {$this->id}";
        $wpdb->query($sql);
        // phpcs:enable WordPress.DB.PreparedSQL
        $this->debug("Reindexed the childrens order of {$this->id}", __METHOD__);
        if ($resetData) {
            wp_rml_structure_reset(null, \false);
        }
        return \true;
    }
    // Documented in IFolder
    public function isValidChildrenType($type) {
        $allowed = $this->getAllowedChildrenTypes();
        $this->debug(
            "Check if children type '{$type}' of {$this->id}... is allowed here: " .
                ($allowed === \true ? 'All is allowed here' : 'Only ' . \json_encode($allowed) . ' is allowed here'),
            __METHOD__
        );
        return $allowed === \true ? \true : \in_array($type, $allowed, \true);
    }
    // Documented in IFolderActions
    public function setParent($id, $ord = -1, $force = \false) {
        // Get the parent id
        $this->debug("Try to set parent of {$this->id} from {$this->parent} to {$id}...", __METHOD__);
        // Get the parent object
        $parent = wp_rml_get_object_by_id($id);
        if ($id === $this->parent) {
            $this->debug('The parent is the same, probably only the order is changed...', __METHOD__);
        } else {
            // Check if parent folder is given
            if ($parent === null) {
                throw new \Exception(__('The given parent does not exist to set the parent for this folder.', RML_TD));
            }
            // Check if allowed to change the parent
            if (!$force && $this->isRestrictFor('par')) {
                throw new \Exception(__('You are not allowed to change the parent for this folder.', RML_TD));
            }
            // Check, if the folder type is allowed here
            if (!$force && !$parent->isValidChildrenType($this->getType())) {
                throw new \Exception(__('The given parent does not allow the folder type.', RML_TD));
            }
            // Check, if the parent has already the given folder name
            if ($parent->hasChildren($this->name)) {
                throw new \MatthiasWeb\RealMediaLibrary\exception\FolderAlreadyExistsException($id, $this->name);
            }
        }
        $newOrder = $ord > -1 ? $ord : $parent->getMaxOrder() + 1;
        $isRelocate = $id === $this->parent;
        /**
         * (Pro only) This action is called when a folder was moved in the folder tree. That
         * means the parent and order was changed.
         *
         * @param {IFolder} $folder The folder object
         * @param {int} $id The new parent folder id
         * @param {int} $order The (new) order number
         * @param {boolean} $force If true the relocating was forced
         * @hook RML/Folder/Move
         * @since 4.0.7
         * @since 4.12.1 This hook is only available in PRO-Version
         */
        do_action($isRelocate ? 'RML/Folder/Relocate' : 'RML/Folder/Move', $this, $id, $newOrder, $force);
        $oldData = $this->getRowData();
        $beforeId = $this->parent;
        $this->parent = $id;
        $this->order = $newOrder;
        $this->debug("Use {$this->order} (passed {$ord} as parameter) as new order value", __METHOD__);
        // Save in database
        if ($this->id > -1) {
            global $wpdb;
            // Update childrens
            if ($beforeId !== $this->parent) {
                $this->updateThisAndChildrensAbsolutePath();
            }
            // Update order
            // phpcs:disable WordPress.DB.PreparedSQL
            $table_name = $this->getTableName();
            $wpdb->query(
                $wpdb->prepare("UPDATE {$table_name} SET parent=%d, ord=%d WHERE id = %d", $id, $this->order, $this->id)
            );
            // phpcs:enable WordPress.DB.PreparedSQL
            /**
             * (Pro only) This action is called when a folder was moved in the folder tree. That
             * means the parent and order was changed.
             *
             * @param {IFolder} $folder The folder object
             * @param {int} $id The new parent folder id
             * @param {int} $order The (new) order number
             * @param {boolean} $force If true the relocating was forced
             * @param {object} $oldData The old SQL row data (raw) of the folder
             * @hook RML/Folder/Moved
             * @since 4.12.1 This hook is only available in PRO-Version
             */
            do_action(
                $isRelocate ? 'RML/Folder/Relocated' : 'RML/Folder/Moved',
                $this,
                $id,
                $this->order,
                $force,
                $oldData
            );
            $this->debug('Successfully moved and saved in database', __METHOD__);
            \MatthiasWeb\RealMediaLibrary\Util::getInstance()->doActionAnyParentHas($this, 'Folder/RelocatedOrMoved', [
                $this,
                $id,
                $this->order,
                $force,
                $oldData
            ]);
            $this->applySubfolderOrderBy();
        } else {
            $this->debug('Successfully setted the new parent', __METHOD__);
            $this->getAbsolutePath(\true, \true);
        }
        return \true;
    }
    // Documented in IFolderActions
    public function relocate($parentId, $nextFolderId = \false) {
        global $wpdb;
        // Collect data
        $table_name = $this->getTableName();
        $this->debug(
            $parentId === $this->id
                ? "Start to relocate folder {$this->id} inside parent..."
                : "Start to relocate folder {$this->id} to parent {$parentId}...",
            __METHOD__
        );
        $this->debug(
            $nextFolderId === \false
                ? 'The folder should take place at the end of the list...'
                : "The folder should take place before folder id {$nextFolderId}...",
            __METHOD__
        );
        $parent = $parentId === $this->id ? $this : wp_rml_get_object_by_id($parentId);
        $next = $nextFolderId === \false ? null : wp_rml_get_object_by_id($nextFolderId);
        // At end of the list
        try {
            if ($next === null && is_rml_folder($parent)) {
                // Only set the parent
                $this->setParent($parent->id);
            } elseif (is_rml_folder($next) && is_rml_folder($parent)) {
                // Reindex and reget
                $parent->reindexChildrens();
                $_this = wp_rml_structure_reset(null, \false, $this->id);
                $next = wp_rml_get_object_by_id($next->id);
                // Get the order of the next folder
                $newOrder = $next->order;
                // Count up the next ids
                // phpcs:disable WordPress.DB.PreparedSQL
                $sql = "UPDATE {$table_name} SET ord = ord + 1 WHERE parent = {$parent->id} AND ord >= {$newOrder}";
                $wpdb->query($sql);
                // phpcs:enable WordPress.DB.PreparedSQL
                // Set the new parent
                $_this->setParent($parent->id, $newOrder);
            } else {
                // There is nothing given
                throw new \Exception(__('Something went wrong.', RML_TD));
            }
            $this->debug('Successfully relocated', __METHOD__);
            return \true;
        } catch (\Exception $e) {
            $this->debug('Error: ' . $e->getMessage(), __METHOD__);
            return [$e->getMessage()];
        }
    }
    /**
     * Delete the subOrderAutomatically metadata when deleting the subfolder
     * order and also reset the subfolder order. It also handles the content order.
     *
     * @param int[] $meta_ids
     * @param int $object_id
     * @param string $meta_key
     */
    public static function deleted_realmedialibrary_meta($meta_ids, $object_id, $meta_key) {
        global $wpdb;
        if (empty($object_id)) {
            return;
        }
        if ($meta_key === 'lastSubOrderBy') {
            // The default is to order by ID ascending
            $folder = wp_rml_get_object_by_id($object_id);
            if (is_rml_folder($folder)) {
                $folder->orderSubfolders('id_asc', \false);
            }
            // phpcs:disable WordPress.DB.PreparedSQL
            $wpdb->query(
                $wpdb->prepare(
                    'UPDATE ' .
                        \MatthiasWeb\RealMediaLibrary\Core::getInstance()->getTableName() .
                        ' SET ord=NULL, oldCustomOrder=NULL WHERE id=%d',
                    $object_id
                )
            );
            // phpcs:enable WordPress.DB.PreparedSQL
            delete_media_folder_meta($object_id, 'subOrderAutomatically');
        }
        if ($meta_key === 'orderby') {
            // phpcs:disable WordPress.DB.PreparedSQL
            $wpdb->query(
                $wpdb->prepare(
                    'UPDATE ' .
                        \MatthiasWeb\RealMediaLibrary\Core::getInstance()->getTableName('posts') .
                        ' SET nr=NULL, oldCustomNr=NULL WHERE fid=%d',
                    $object_id
                )
            );
            $wpdb->query(
                $wpdb->prepare(
                    'UPDATE ' .
                        \MatthiasWeb\RealMediaLibrary\Core::getInstance()->getTableName() .
                        ' SET contentCustomOrder=0 WHERE id=%d',
                    $object_id
                )
            );
            // phpcs:enable WordPress.DB.PreparedSQL
            delete_media_folder_meta($object_id, 'orderAutomatically');
        }
    }
}

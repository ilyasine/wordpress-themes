<?php

namespace MatthiasWeb\RealMediaLibrary\lite\comp;

use MatthiasWeb\RealMediaLibrary\attachment\CountCache;
use MatthiasWeb\RealMediaLibrary\comp\ExImport as CompExImport;
use MatthiasWeb\RealMediaLibrary\Util;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
trait ExImport {
    // Documented in IOverrideExImport
    public function importTaxonomy($tax) {
        global $wpdb;
        // Import taxonomies
        $this->import($this->getCategories($tax));
        $table_name = $this->getTableName();
        $table_name_posts = $this->getTableName('posts');
        // Import posts
        $this->debug('Start importing posts for the taxonomy ' . $tax . '...', __METHOD__);
        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare(
            "INSERT INTO {$table_name_posts} (`attachment`, `fid`, `importData`)\n        SELECT p.ID AS attachment, rml.id AS fid, GROUP_CONCAT(rml.id SEPARATOR ',') AS importData\n        FROM {$wpdb->posts} AS p\n        INNER JOIN {$wpdb->term_relationships} AS tr ON p.ID = tr.object_id\n        INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)\n        INNER JOIN {$wpdb->terms} AS t ON t.term_id = tt.term_id\n        INNER JOIN {$table_name} AS rml ON t.term_id = rml.importId\n        WHERE p.post_type = 'attachment' AND tt.taxonomy = %s\n        GROUP BY p.ID\n        ON DUPLICATE KEY UPDATE importData = VALUES(importData)",
            $tax
        );
        $wpdb->query($sql);
        // phpcs:enable WordPress.DB.PreparedSQL
        // Fix isShortcut parameter
        \MatthiasWeb\RealMediaLibrary\Util::getInstance()->fixIsShortcutInPosts();
        // Import shortcuts
        $this->importShortcuts();
        $this->clearImportData();
        \MatthiasWeb\RealMediaLibrary\attachment\CountCache::getInstance()->resetCountCache();
        // Dismiss notice
        $this->isImportTaxNoticeDismissed(\true);
    }
    // Documented in IOverrideExImport
    public function importMlf() {
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        $result = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT wpp.ID as importId, wpp.post_name as name, wpf.folder_id AS parent\n                FROM {$wpdb->posts} wpp\n                INNER JOIN " .
                    $wpdb->prefix .
                    'mgmlp_folders wpf ON wpp.ID = wpf.post_id
                WHERE wpp.post_type = %s',
                \MatthiasWeb\RealMediaLibrary\comp\ExImport::MLF_POST_TYPE
            ),
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.PreparedSQL
        // Cast
        foreach ($result as &$row) {
            $row['importId'] = \intval($row['importId']);
            $row['parent'] = \intval($row['parent']);
        }
        $tree = \MatthiasWeb\RealMediaLibrary\Util::getInstance()->buildTree(
            $result,
            0,
            'parent',
            'importId',
            '__children'
        );
        $tree = \MatthiasWeb\RealMediaLibrary\Util::getInstance()->clearTree($tree, ['parent'], '__children');
        // Media Library Folders holds the first folder as the main upload folder (e. g. `uploads` from `wp-content/uploads`)
        $tree = $tree[0]['__children'];
        // Import the tree
        $this->import($tree);
        // Import post relations
        $table_name = $this->getTableName();
        $table_name_posts = $this->getTableName('posts');
        // Import posts
        // phpcs:disable WordPress.DB.PreparedSQL
        $wpdb->query(
            "INSERT INTO {$table_name_posts} (`attachment`, `fid`)\n        SELECT wpp.ID AS attachment, rml.id AS fid\n        FROM " .
                $wpdb->prefix .
                "mgmlp_folders wpf\n        INNER JOIN {$wpdb->posts} wpp ON wpp.ID = wpf.post_id\n        INNER JOIN {$table_name} AS rml ON wpf.folder_id = rml.importId\n        WHERE wpp.post_type = 'attachment'\n        ON DUPLICATE KEY UPDATE fid = VALUES(fid)"
        );
        // phpcs:enable WordPress.DB.PreparedSQL
        // Cleanup
        $this->clearImportData();
        \MatthiasWeb\RealMediaLibrary\attachment\CountCache::getInstance()->resetCountCache();
        // Dismiss notice
        $this->isImportTaxNoticeDismissed(\true);
    }
    // Documented in IOverrideExImport
    public function importFileBird() {
        global $wpdb;
        $table_name_fbv = $wpdb->prefix . \MatthiasWeb\RealMediaLibrary\comp\ExImport::FILE_BIRD_TABLE_NAME;
        $table_name_fbv_posts = $wpdb->prefix . \MatthiasWeb\RealMediaLibrary\comp\ExImport::FILE_BIRD_TABLE_NAME_POSTS;
        // phpcs:disable WordPress.DB.PreparedSQL
        $result = $wpdb->get_results(
            "SELECT id as importId, name, parent\n            FROM {$table_name_fbv}",
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.PreparedSQL
        // Cast
        foreach ($result as &$row) {
            $row['importId'] = \intval($row['importId']);
            $row['parent'] = \intval($row['parent']);
        }
        $tree = \MatthiasWeb\RealMediaLibrary\Util::getInstance()->buildTree(
            $result,
            0,
            'parent',
            'importId',
            '__children'
        );
        $tree = \MatthiasWeb\RealMediaLibrary\Util::getInstance()->clearTree($tree, ['parent'], '__children');
        // Import the tree
        $this->import($tree);
        // Import post relations
        $table_name = $this->getTableName();
        $table_name_posts = $this->getTableName('posts');
        // Import posts
        // phpcs:disable WordPress.DB.PreparedSQL
        $wpdb->query(
            "INSERT INTO {$table_name_posts} (`attachment`, `fid`)\n        SELECT attachment_id AS attachment, rml.id AS fid\n        FROM {$table_name_fbv_posts} fbva\n        INNER JOIN {$table_name_fbv} fbv ON fbv.id = fbva.folder_id\n        INNER JOIN {$table_name} AS rml ON fbva.folder_id = rml.importId\n        ON DUPLICATE KEY UPDATE fid = VALUES(fid)"
        );
        // phpcs:enable WordPress.DB.PreparedSQL
        // Cleanup
        $this->clearImportData();
        \MatthiasWeb\RealMediaLibrary\attachment\CountCache::getInstance()->resetCountCache();
        // Dismiss notice
        $this->isImportTaxNoticeDismissed(\true);
    }
    // Documented in IOverrideExImport
    public function importShortcuts() {
        global $wpdb;
        $this->debug('Start importing shortcuts...', __METHOD__);
        $table_name_posts = $this->getTableName('posts');
        // phpcs:disable WordPress.DB.PreparedSQL
        $results = $wpdb->get_results("SELECT * FROM {$table_name_posts} WHERE importData LIKE \"%,%\"", ARRAY_A);
        // phpcs:enable WordPress.DB.PreparedSQL
        // Grouping
        $group = [];
        foreach ($results as $result) {
            $folders = \explode(',', $result['importData']);
            // Iterate all needed folders
            foreach ($folders as $scFid) {
                if ($scFid !== $result['fid']) {
                    $isShortcut = $result['isShortcut'] > 0 ? $result['isShortcut'] : $result['attachment'];
                    $group[] = ['to' => $scFid, 'attachment' => $isShortcut];
                }
            }
        }
        // Create shortcuts
        $group = \MatthiasWeb\RealMediaLibrary\Util::getInstance()->group_by($group, 'to');
        foreach ($group as $to => $attachments) {
            $attachmentIds = [];
            foreach ($attachments as $attachment) {
                $attachmentIds[] = $attachment['attachment'];
            }
            wp_rml_create_shortcuts($to, $attachmentIds, \true);
        }
    }
    // Documented in IOverrideExImport
    public function import($tree) {
        $this->debug('Start importing a tree...', __METHOD__);
        $this->importTreeRecursively($tree);
        \MatthiasWeb\RealMediaLibrary\Util::getInstance()->resetAllSlugsAndAbsolutePathes();
        $this->debug('Importing done', __METHOD__);
        wp_rml_structure_reset();
    }
    /**
     * Import a tree with __children array and __metas array recursively.
     *
     * @param array $tree The tree, for example from $this::getFolders()
     * @param int $idOffset The id offset for inserting unique keys
     * @param int $parent THe parent for the import
     */
    private function importTreeRecursively($tree, $idOffset = null, $parent = null) {
        global $wpdb;
        // Just to get the ID...
        if ($idOffset === null) {
            $this->idOffset = wp_rml_create(
                'Import ' . \gmdate('Y-m-d H:m:s'),
                _wp_rml_root(),
                RML_TYPE_FOLDER,
                [],
                \true
            );
            wp_rml_delete($this->idOffset, \true);
        }
        // Set default parent
        if ($parent === null) {
            $parent = _wp_rml_root();
        }
        if (\is_array($tree) && \count($tree) > 0) {
            $values = [];
            // Flat
            foreach ($tree as $node) {
                $this->idOffset++;
                // Create column values
                $columnValues = [$this->idOffset, $parent, get_current_user_id()];
                foreach ($this->columns as $column) {
                    if (\array_key_exists($column, $node)) {
                        $columnValues[] = $wpdb->prepare('%s', $node[$column]);
                    } else {
                        $columnValues[] = 'DEFAULT';
                    }
                }
                $values[] = \implode(',', $columnValues);
            }
            // SQL Insert
            $sql =
                'INSERT INTO ' .
                $this->getTableName() .
                ' (`id`,`parent`,`owner`,`' .
                \implode('`,`', $this->columns) .
                '`) VALUES (' .
                \implode('),(', $values) .
                ')';
            // phpcs:disable WordPress.DB.PreparedSQL
            if ($wpdb->query($sql) !== \false) {
                // phpcs:enable WordPress.DB.PreparedSQL
                $lastParent = $wpdb->insert_id;
                // It is the last inserted id
                // Next depth and metas
                $metaValues = [];
                // Values for metadata...
                foreach (\array_reverse($tree) as $node) {
                    if (isset($node['__children']) && \is_array($node['__children'])) {
                        $this->importTreeRecursively($node['__children'], $this->idOffset, $lastParent);
                    }
                    if (isset($node['__metas']) && \is_array($node['__metas']) && \count($node['__metas']) > 0) {
                        foreach ($node['__metas'] as $meta) {
                            $metaValues[] = $wpdb->prepare(
                                '%d, %s, %s',
                                $lastParent,
                                $meta['meta_key'],
                                $meta['meta_value']
                            );
                        }
                    }
                    $lastParent--;
                }
                // SQL Insert meta data
                if (\count($metaValues) > 0) {
                    // phpcs:disable WordPress.DB.PreparedSQL
                    $wpdb->query(
                        'INSERT INTO ' .
                            $this->getTableName('meta') .
                            ' (`realmedialibrary_id`,`meta_key`,`meta_value`) VALUES (' .
                            \implode('),(', $metaValues) .
                            ')'
                    );
                    // phpcs:enable WordPress.DB.PreparedSQL
                }
            }
        }
    }
    /**
     * Clear importData and importId in posts and folder table.
     */
    private function clearImportData() {
        global $wpdb;
        $this->debug('Clear importData and importId in posts and folder table...', __METHOD__);
        $table_name = $this->getTableName();
        $table_name_posts = $this->getTableName('posts');
        // phpcs:disable WordPress.DB.PreparedSQL
        $wpdb->query("UPDATE {$table_name} SET importId = NULL");
        $wpdb->query("UPDATE {$table_name_posts} SET importData = NULL");
        // phpcs:enable WordPress.DB.PreparedSQL
    }
    /**
     * Get the folder tree of a taxonomy for import process.
     *
     * @param string $tax
     * @return array
     */
    public function getCategories($tax) {
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        $result = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT t.term_id as importId, t.name, tt.parent\n            FROM {$wpdb->terms} AS t \n            INNER JOIN {$wpdb->term_taxonomy} AS tt ON (t.term_id = tt.term_id) \n            WHERE tt.taxonomy = %s\n            ORDER BY t.name ASC",
                $tax
            ),
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.PreparedSQL
        // Cast
        foreach ($result as &$row) {
            $row['importId'] = \intval($row['importId']);
            $row['parent'] = \intval($row['parent']);
        }
        $tree = \MatthiasWeb\RealMediaLibrary\Util::getInstance()->buildTree(
            $result,
            0,
            'parent',
            'importId',
            '__children'
        );
        return \MatthiasWeb\RealMediaLibrary\Util::getInstance()->clearTree($tree, ['parent'], '__children');
    }
}

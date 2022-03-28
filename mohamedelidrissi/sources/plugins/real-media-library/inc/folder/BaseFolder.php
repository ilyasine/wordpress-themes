<?php

namespace MatthiasWeb\RealMediaLibrary\folder;

use MatthiasWeb\RealMediaLibrary\api\IFolder;
use MatthiasWeb\RealMediaLibrary\attachment\Permissions;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Abstract base class for folders. It handles the available fields and getters / setters.
 * The class is completely documented in the implemented interface.
 */
abstract class BaseFolder implements \MatthiasWeb\RealMediaLibrary\api\IFolder {
    use UtilsProvider;
    protected $id;
    protected $parent;
    protected $name;
    protected $cnt;
    protected $order;
    protected $slug;
    protected $absolutePath;
    protected $row;
    protected $children;
    protected $visible = \true;
    protected $restrictions = [];
    protected $restrictionsCount = 0;
    protected $systemReservedFolders = ['/', '..', '.'];
    protected $contentCustomOrder;
    // Documented in IFolder
    public function anyParentHas($column, $value = null, $valueFormat = '%s', $includeSelf = \false, $until = null) {
        global $wpdb;
        // phpcs:disable WordPress.DB
        $sql = wp_rml_create_all_parents_sql($this, $includeSelf, $until, [
            'fields' => "rmldata.id, rmldata.{$column}",
            'afterWhere' => $wpdb->prepare("AND rmldata.{$column} = {$valueFormat}", $value)
        ]);
        return $sql === \false ? [] : $wpdb->get_results($sql, ARRAY_A);
        // phpcs:enable WordPress.DB
    }
    // Documented in IFolder
    public function anyParentHasMetadata(
        $meta_key,
        $meta_value = null,
        $valueFormat = '%s',
        $includeSelf = \false,
        $until = null
    ) {
        global $wpdb;
        // phpcs:disable WordPress.DB
        $sql = wp_rml_create_all_parents_sql($this, $includeSelf, $until, [
            'fields' => 'rmlmeta.meta_id AS id, rmlmeta.realmedialibrary_id AS folderId, rmlmeta.meta_value AS value',
            'join' => $wpdb->prepare(
                'JOIN ' .
                    $this->getTableName('meta') .
                    ' rmlmeta ON rmlmeta.realmedialibrary_id = rmldata.id AND rmlmeta.meta_key = %s',
                $meta_key
            ),
            'afterWhere' =>
                $meta_value === null
                    ? "AND TRIM(IFNULL(rmlmeta.meta_value,'')) <> ''"
                    : $wpdb->prepare("AND rmlmeta.meta_value = {$valueFormat}", $meta_value)
        ]);
        return $sql === \false ? [] : $wpdb->get_results($sql, ARRAY_A);
        // phpcs:enable WordPress.DB
    }
    // Documented in IFolder
    public function anyChildrenHas($column, $value = null, $valueFormat = '%s', $includeSelf = \false) {
        global $wpdb;
        // phpcs:disable WordPress.DB
        $sql = wp_rml_create_all_children_sql($this, $includeSelf, [
            'fields' => "rmldata.id, rmldata.{$column}",
            'afterWhere' => $wpdb->prepare("AND rmldata.{$column} = {$valueFormat}", $value)
        ]);
        return $sql === \false ? [] : $wpdb->get_results($sql, ARRAY_A);
        // phpcs:enable WordPress.DB
    }
    // Documented in IFolder
    public function anyChildrenHasMetadata($meta_key, $meta_value = null, $valueFormat = '%s', $includeSelf = \false) {
        // phpcs:disable WordPress.DB
        global $wpdb;
        $sql = wp_rml_create_all_children_sql($this, $includeSelf, [
            'fields' => 'rmlmeta.meta_id AS id, rmlmeta.realmedialibrary_id AS folderId, rmlmeta.meta_value AS value',
            'join' => $wpdb->prepare(
                'JOIN ' .
                    $this->getTableName('meta') .
                    ' rmlmeta ON rmlmeta.realmedialibrary_id = rmldata.id AND rmlmeta.meta_key = %s',
                $meta_key
            ),
            'afterWhere' =>
                $meta_value === null
                    ? "AND TRIM(IFNULL(rmlmeta.meta_value,'')) <> ''"
                    : $wpdb->prepare("AND rmlmeta.meta_value = {$valueFormat}", $meta_value)
        ]);
        return $sql === \false ? [] : $wpdb->get_results($sql, ARRAY_A);
        // phpcs:enable WordPress.DB
    }
    // Documented in IFolder
    public function hasChildren($name, $returnObject = \false) {
        foreach ($this->getChildren() as $value) {
            if ($value->getName() === $name) {
                return $returnObject === \true ? $value : \true;
            }
        }
        return \false;
    }
    // Documented in IFolder
    public function getId() {
        return $this->id;
    }
    // Documented in IFolder
    public function getParent() {
        return $this->parent;
    }
    // Documented in IFolder
    public function getAllParents($until = null, $colIdx = 0) {
        global $wpdb;
        $sql = wp_rml_create_all_parents_sql($this, \false, $until);
        // phpcs:disable WordPress.DB.PreparedSQL
        return $sql === \false ? [] : $wpdb->get_col($sql, $colIdx);
        // phpcs:enable WordPress.DB.PreparedSQL
    }
    // Documented in IFolder
    public function getName($htmlentities = \false) {
        return $htmlentities ? \htmlentities($this->name) : $this->name;
    }
    // Documented in IFolder
    public function getSlug($force = \false, $fromSetName = \false) {
        if ($this->slug === '' || $force) {
            $slugBefore = $this->slug;
            $this->slug = _wp_rml_sanitize($this->name, $this->id > -1, $this->id);
            // Update in database
            if ($this->id > -1) {
                if ($slugBefore !== $this->slug) {
                    global $wpdb;
                    $table_name = $this->getTableName();
                    // phpcs:disable WordPress.DB.PreparedSQL
                    $wpdb->query(
                        $wpdb->prepare("UPDATE {$table_name} SET slug=%s WHERE id = %d", $this->slug, $this->id)
                    );
                    // phpcs:enable WordPress.DB.PreparedSQL
                    $this->debug("Successfully changed slug '{$this->slug}' in database", __METHOD__);
                }
                if (!$fromSetName) {
                    $this->updateThisAndChildrensAbsolutePath();
                }
            }
        }
        return $this->slug;
    }
    // Documented in IFolder
    public function getPath($implode = '/', $map = 'htmlentities', $filter = null) {
        $return = [];
        // Add initial folder
        if (!isset($filter) || \call_user_func_array($filter, [$this])) {
            $initial = $this->name;
            // Allow custom map function
            if ($map !== null) {
                if ($map === 'htmlentities') {
                    $initial = \htmlentities($initial);
                } else {
                    $initial = \call_user_func_array($map, [$initial, $this]);
                }
            }
            $return[] = $initial;
        }
        $folder = $this;
        while (\true) {
            $f = wp_rml_get_object_by_id($folder->parent);
            if ($f !== null && $f->getType() !== RML_TYPE_ROOT) {
                $folder = $f;
                if (isset($filter) && !\call_user_func_array($filter, [$folder])) {
                    continue;
                }
                $name = $folder->name;
                // Allow custom map function
                if ($map !== null) {
                    if ($map === 'htmlentities') {
                        $name = \htmlentities($name);
                    } else {
                        $name = \call_user_func_array($map, [$name, $f]);
                    }
                }
                $return[] = $name;
            } else {
                break;
            }
        }
        return \implode($implode, \array_reverse($return));
    }
    // Documented in IFolder
    public function getOwner() {
        return $this->owner;
    }
    // Documented in IFolder
    public function getAbsolutePath($force = \false, $fromSetName = \false) {
        if ($this->absolutePath === '' || $force) {
            $pathBefore = $this->absolutePath;
            $return = [$this->getSlug(\true, \true)];
            $folder = $this;
            while (\true) {
                $f = wp_rml_get_object_by_id($folder->getParent());
                if (is_rml_folder($f) && $f->getId() !== -1) {
                    $folder = $f;
                    $return[] = $folder->getSlug();
                } else {
                    break;
                }
            }
            $this->absolutePath = \implode('/', \array_reverse($return));
            // Update in database
            if ($this->id > -1) {
                if ($pathBefore !== $this->absolutePath) {
                    global $wpdb;
                    $table_name = $this->getTableName();
                    // phpcs:disable WordPress.DB.PreparedSQL
                    $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE {$table_name} SET absolute=%s WHERE id = %d",
                            $this->absolutePath,
                            $this->id
                        )
                    );
                    // phpcs:enable WordPress.DB.PreparedSQL
                    $this->debug("Successfully changed absolute path '{$this->absolutePath}' in database", __METHOD__);
                }
                if (!$fromSetName) {
                    $this->updateThisAndChildrensAbsolutePath();
                }
            }
        }
        return $this->absolutePath;
    }
    // Documented in IFolder
    public function getCnt($forceReload = \false) {
        if ($this->cnt === null || $forceReload) {
            $query = new \MatthiasWeb\RealMediaLibrary\folder\QueryCount(
                /**
                 * Modify the query arguments to count items within a folder.
                 *
                 * @param {array} $query The query with post_status, post_type and rml_folder
                 * @hook RML/Folder/QueryCountArgs
                 * @return {array} The query
                 */
                apply_filters('RML/Folder/QueryCountArgs', [
                    'post_status' => 'inherit',
                    'post_type' => 'attachment',
                    'rml_folder' => $this->id
                ])
            );
            if (isset($query->posts[0])) {
                $this->cnt = $query->posts[0];
            } else {
                $this->cnt = 0;
            }
        }
        return $this->cnt;
    }
    // Documented in IFolder
    public function getChildren() {
        return $this->children;
    }
    // Documented in IFolder
    public function getOrder() {
        return $this->order;
    }
    // Documented in IFolder
    public function getRestrictions() {
        if (!$this->isVisible()) {
            return \MatthiasWeb\RealMediaLibrary\attachment\Permissions::RESTRICTION_ALL;
        }
        return $this->restrictions;
    }
    // Documented in IFolder
    public function getRestrictionsCount() {
        if (!$this->isVisible()) {
            return \count(\MatthiasWeb\RealMediaLibrary\attachment\Permissions::RESTRICTION_ALL);
        }
        return $this->restrictionsCount;
    }
    // Documented in IFolder
    public function getPlain($deep = \false) {
        if (!$this->isVisible()) {
            return null;
        }
        $result = [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'parent' => $this->getParent(),
            'name' => $this->getName(),
            'order' => $this->getOrder(),
            'restrictions' => $this->getRestrictions(),
            'slug' => $this->getSlug(),
            'absolutePath' => $this->getAbsolutePath(),
            'cnt' => $this->getCnt(),
            'contentCustomOrder' => (int) $this->getContentCustomOrder(),
            'forceCustomOrder' => $this->forceContentCustomOrder(),
            'lastOrderBy' => $this->getRowData('lastOrderBy'),
            'orderAutomatically' => $this->getRowData('orderAutomatically'),
            'lastSubOrderBy' => $this->getRowData('lastSubOrderBy'),
            'subOrderAutomatically' => $this->getRowData('subOrderAutomatically')
        ];
        // Add the children
        if ($deep) {
            $children = [];
            foreach ($this->getChildren() as $child) {
                $plain = $child->getPlain($deep);
                if ($plain !== null) {
                    $children[] = $plain;
                }
            }
            $result['children'] = $children;
        }
        return $result;
    }
    // Documented in IFolder
    public function setRestrictions($restrictions = []) {
        $this->debug($restrictions, __METHOD__);
        $this->restrictions = $restrictions;
        $this->restrictionsCount = \count($this->restrictions);
        // Save in Database
        if ($this->id > -1) {
            global $wpdb;
            $table_name = $this->getTableName();
            // phpcs:disable WordPress.DB.PreparedSQL
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$table_name} SET restrictions=%s WHERE id = %d",
                    \implode(',', $restrictions),
                    $this->id
                )
            );
            // phpcs:enable WordPress.DB.PreparedSQL
            $this->debug('Successfully saved restrictions in database', __METHOD__);
        }
    }
    // Documented in IFolder
    public function is($folder_type) {
        return $this->getType() === $folder_type;
    }
    // Documented in IFolder
    public function isVisible() {
        return $this->visible;
    }
    // Documented in IFolder
    public function isRestrictFor($restriction) {
        if (!$this->isVisible()) {
            // When it is not visible, restrict the complete access
            return \true;
        }
        return \in_array($restriction, $this->restrictions, \true) ||
            \in_array($restriction . '>', $this->restrictions, \true);
    }
}

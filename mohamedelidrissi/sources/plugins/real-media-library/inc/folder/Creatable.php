<?php

namespace MatthiasWeb\RealMediaLibrary\folder;

use MatthiasWeb\RealMediaLibrary\attachment\Shortcut;
use MatthiasWeb\RealMediaLibrary\exception\FolderAlreadyExistsException;
use MatthiasWeb\RealMediaLibrary\lite\folder\Creatable as FolderCreatable;
use MatthiasWeb\RealMediaLibrary\order\Sortable;
use MatthiasWeb\RealMediaLibrary\overrides\interfce\folder\IOverrideCreatable;
use MatthiasWeb\RealMediaLibrary\Util;
use Exception;
use WP_Query;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Abstract class for a creatable folder item. It handles all general
 * actions for a folder item. If you want to add an new folder type, have a
 * look at the api function wp_rml_register_creatable();
 *
 * A new folder type MUST have the implementation with class FOLDERTYPE
 * extends order\Sortable because every folder can also be sortable!
 */
abstract class Creatable extends \MatthiasWeb\RealMediaLibrary\folder\BaseFolder implements
    \MatthiasWeb\RealMediaLibrary\overrides\interfce\folder\IOverrideCreatable {
    use FolderCreatable;
    static $cachedOrders = null;
    /**
     * C'tor with the main properties.
     *
     * The constructor does not throw any errors because when it is fully filled with parameters
     * it expects the right properties from the database.
     *
     * Only ::instance and ::create should create instances from this class!
     *
     * Synced with order\Sortable::__construct
     *
     * @param int $id
     * @param int $parent
     * @param string $name
     * @param string $slug
     * @param string $absolute
     * @param int $order
     * @param int $cnt
     * @param array $row
     * @throws \Exception
     */
    public function __construct(
        $id,
        $parent = -1,
        $name = '',
        $slug = '',
        $absolute = '',
        $order = -1,
        $cnt = 0,
        $row = []
    ) {
        // Check, if the folder type is defined in the right way
        if (!$this instanceof \MatthiasWeb\RealMediaLibrary\order\Sortable) {
            $className = \explode('\\', \get_class($this));
            $className = $className[\count($className) - 1];
            throw new \Exception(
                "The folder type is defined in the wrong way! Please use the class definition:\n\n                use " .
                    RML_NS .
                    "\\order; // use namespace\n                class {$className} extends order\\Sortable { ... }\n\n... You can disable the sortable functionality by set the contentCustomOrder to 2 in the database."
            );
        }
        // Set properties
        $this->id = $id;
        $this->parent = $parent;
        $this->name = $name;
        $this->cnt = $cnt >= 0 ? $cnt : 0;
        $this->order = $order;
        $this->children = [];
        $this->slug = $slug;
        $this->absolutePath = $absolute;
        $this->owner = isset($row->owner) ? $row->owner : get_current_user_id();
        $this->row = $row;
        // Parse the restrictions
        if (isset($row->restrictions) && \is_string($row->restrictions) && \strlen($row->restrictions) > 0) {
            $this->restrictions = \explode(',', $row->restrictions);
            $this->restrictionsCount = \count($this->restrictions);
        }
    }
    // Documented in IFolderActions
    public function read($order = null, $orderby = null) {
        return self::xread($this->id, $order, $orderby);
    }
    // Documented in IFolderActions
    public function insert($ids, $supress_validation = \false, $isShortcut = \false) {
        $this->debug('Start moving files ' . \json_encode($ids) . " to {$this->id}...", __METHOD__);
        if (\is_array($ids)) {
            // Reset last shortcut ids
            if ($isShortcut) {
                \MatthiasWeb\RealMediaLibrary\attachment\Shortcut::getInstance()->_resetLastIds();
            }
            // Create posts cache to avoid multiple SQL queries in _wp_rml_synchronize_attachment
            $cacheIds = [];
            foreach ($ids as $value) {
                if (!wp_cache_get($value, 'posts')) {
                    $cacheIds[] = $value;
                }
            }
            if (\count($cacheIds) > 0) {
                $this->debug('Get and cache the following post ids: ' . \implode(',', $cacheIds), __METHOD__);
                get_posts(['numberposts' => -1, 'include' => $cacheIds]);
            }
            // Iterate all items
            foreach ($ids as $value) {
                $this->singleCheckInsert($value);
                // Check if other fails are counted
                if ($supress_validation === \false) {
                    $this->singleCheckInsertPermissions($value);
                }
            }
            /**
             * This action is fired before items gets moved to a specific folder.
             * It allows you for example to throw an exception with an error message
             * to cancel the movement.
             *
             * @param {int} $fid The destination folder id
             * @param {int[]} $attachments The attachment post ids
             * @param {IFolder} $folder The folder object
             * @param {boolean} $isShortcut If true the attachments are copied to a folder
             * @hook RML/Item/Move
             */
            do_action('RML/Item/Move', $this->id, $ids, $this, $isShortcut);
            // Get the folder IDs of the attachments
            $foldersToUpdate = wp_attachment_folder($ids);
            $sourceFolders = $foldersToUpdate;
            // Update the folder
            foreach ($ids as $value) {
                _wp_rml_synchronize_attachment($value, $this->id, $isShortcut);
            }
            // Update the count and shortcuts
            $foldersToUpdate[] = $this->id;
            wp_rml_update_count($foldersToUpdate);
            // Finish
            $this->debug("Successfully moved (isShortcut: {$isShortcut})", __METHOD__);
            /**
             * This action is fired after items gets moved to a specific folder.
             *
             * @param {int} $fid The destination folder id
             * @param {int[]} $attachments The attachment post ids
             * @param {IFolder} $folder The folder object
             * @param {boolean} $isShortcut If true the attachments are copied to a folder
             * @param {int[]} $sourceFolders The´source folder ids of the attachments (since 4.0.9)
             * @hook RML/Item/MoveFinished
             */
            do_action('RML/Item/MoveFinished', $this->id, $ids, $this, $isShortcut, $sourceFolders);
            \MatthiasWeb\RealMediaLibrary\Util::getInstance()->doActionAnyParentHas($this, 'Folder/Insert', [
                $this->id,
                $ids,
                $this,
                $isShortcut,
                $sourceFolders
            ]);
            return \true;
        } else {
            throw new \Exception(__('You need to provide a set of files.', RML_TD));
        }
    }
    /**
     * Simply check, if an id can be inserted in this folder. If something is
     * wrong with the id, please throw an exception!
     *
     * @param int $id The id
     * @throws \Exception
     */
    protected function singleCheckInsertPermissions($id) {
        /**
         * Checks if an attachment can be inserted into a folder.
         *
         * @param {string[]} $errors An array of errors
         * @param {int} $id The folder id
         * @param {IFolder} $folder The folder object
         * @hook RML/Validate/Insert
         * @return {string[]} When the array has one or more items the movement is cancelled with the string message
         */
        $validation = apply_filters('RML/Validate/Insert', [], $id, $this);
        if (\count($validation) > 0) {
            throw new \Exception(\implode(' ', $validation));
        }
    }
    /**
     * Simply check, if an id can be inserted in this folder. If something is
     * wrong with the id, please throw an exception!
     *
     * @param int $id The id
     * @throws \Exception
     */
    protected function singleCheckInsert($id) {
        // Silence is golden.
    }
    /**
     * Persist the given creatable with the database. Think about it, that this only
     * works, when the ID === -1 (that means, it will be a new folder).
     *
     * After the folder is created, this instance is useless, you must get the
     * folder with the API wp_rml_get_by_id
     *
     * @throws \Exception
     * @return integer ID of the newly created folder
     */
    public function persist() {
        $this->debug('Persist to database...', __METHOD__);
        if ($this->id === -1) {
            global $wpdb;
            $parentObj = $this->persistCheckParent();
            // Create it!
            $table_name = $this->getTableName();
            $insert = $wpdb->insert($table_name, [
                'parent' => $this->parent,
                'slug' => $this->getSlug(),
                'name' => $this->name,
                'type' => $this->getType(),
                'ord' => $this->order > -1 ? $this->order : $parentObj->getMaxOrder() + 1,
                'restrictions' => \implode(',', \array_unique($this->restrictions)),
                'owner' => $this->owner
            ]);
            if ($insert !== \false) {
                $this->id = $wpdb->insert_id;
                $this->updateThisAndChildrensAbsolutePath();
                wp_rml_structure_reset(null, \false);
                /**
                 * A new folder is created.
                 *
                 * @param {int} $parent The parent folder id
                 * @param {string} $name The folder name
                 * @param {int} $type The folder type
                 * @param {int} $id The folder id
                 * @hook RML/Folder/Created
                 */
                do_action('RML/Folder/Created', $this->parent, $this->name, $this->getType(), $this->id);
                $this->debug('Successfully persisted creatable with id ' . $this->id, __METHOD__);
                $this->applySubfolderOrderBy();
                return $this->id;
            } else {
                throw new \Exception(__('The folder could not be created in the database.', RML_TD));
            }
        } else {
            throw new \Exception(__('The folder could not be created because it already exists.', RML_TD));
        }
    }
    // Documented in IFolderActions
    public function updateThisAndChildrensAbsolutePath() {
        // Update this absolute path
        $this->getAbsolutePath(\true, \true);
        // Update children
        $childs = $this->getChildren();
        if (\is_array($childs) && \count($childs) > 0) {
            foreach ($childs as $key => $value) {
                $value->updateThisAndChildrensAbsolutePath();
            }
        }
    }
    /**
     * For internal usage only!
     *
     * @param object $children
     * @internal
     */
    public function addChildren($children) {
        $this->children[] = $children;
    }
    // Documented in IFolder
    public function getMaxOrder() {
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        $table_name = $this->getTableName();
        $order = $wpdb->get_var("SELECT MAX(ord) FROM {$table_name} WHERE parent={$this->id}");
        // phpcs:enable WordPress.DB.PreparedSQL
        return \is_numeric($order) ? $order : 0;
    }
    // Documented in IFolder
    public function getRowData($field = null) {
        if (\is_object($this->row)) {
            if ($field === null) {
                return $this->row;
            } else {
                return $this->row->{$field};
            }
        } else {
            return \false;
        }
    }
    // Documented in IFolder
    public function getTypeName($default = null) {
        /**
         * Filter the description name for a custom folder type.
         *
         * @param {string} $name The name
         * @param {int} $type The type
         * @param {int} $fid The folder id
         * @return {string}
         * @hook RML/Folder/Type/Name
         */
        return apply_filters(
            'RML/Folder/Type/Name',
            $default === null ? __('Folder', RML_TD) : $default,
            $this->getType(),
            $this->getId()
        );
    }
    // Documented in IFolder
    public function getTypeDescription($default = null) {
        /**
         * Filter the description for a custom folder type.
         *
         * @param {string} $description The description
         * @param {int} $type The type
         * @param {int} $fid The folder id
         * @return {string}
         * @hook RML/Folder/Type/Name
         */
        return apply_filters(
            'RML/Folder/Type/Description',
            $default === null
                ? __('A folder can contain every type of file or a collection, but not gallery.', RML_TD)
                : $default,
            $this->getType(),
            $this->getId()
        );
    }
    // Documented in IFolderActions
    public function setVisible($visible) {
        $this->visible = $visible;
    }
    // Documented in IFolder
    public function setName($name, $supress_validation = \false) {
        $this->debug("Try to set name of {$this->id} from '{$this->name}' to '{$name}'...", __METHOD__);
        // Check valid folder name
        if (!$this->isValidName($name)) {
            // translators:
            throw new \Exception(\sprintf(__("'%s' is not a valid folder name.", RML_TD), $name));
        }
        // Check, if the parent has already the given folder name
        $parent = wp_rml_get_object_by_id($this->parent);
        if ($parent !== null && $parent->hasChildren($name)) {
            throw new \MatthiasWeb\RealMediaLibrary\exception\FolderAlreadyExistsException($this->parent, $name);
        }
        if ($supress_validation === \false) {
            /**
             * Checks if a folder can be renamed.
             *
             * @param {string[]} $errors An array of errors
             * @param {string} $name The new folder name
             * @param {IFolder} $folder The folder object
             * @hook RML/Validate/Rename
             * @return {string[]} When the array has one or more items the rename process is cancelled with the string message
             */
            $validation = apply_filters('RML/Validate/Rename', [], $name, $this);
            if (\count($validation) > 0) {
                throw new \Exception(\implode(' ', $validation));
            }
        }
        /**
         * This action is called before a folder gets renamed.
         *
         * @param {string} $name The new folder name
         * @param {IFolder} $folder The folder object
         * @hook RML/Folder/Rename
         * @since 4.0.7
         */
        do_action('RML/Folder/Rename', $name, $this);
        $oldData = $this->getRowData();
        $this->name = $name;
        // Save in Database
        if ($this->id > -1) {
            global $wpdb;
            $this->updateThisAndChildrensAbsolutePath();
            // phpcs:disable WordPress.DB.PreparedSQL
            $table_name = $this->getTableName();
            $wpdb->query($wpdb->prepare("UPDATE {$table_name} SET name=%s WHERE id = %d", $name, $this->id));
            // phpcs:enable WordPress.DB.PreparedSQL
            /**
             * This action is called when a folder was renamed.
             *
             * @param {string} $name The new folder name
             * @param {IFolder} $folder The folder object
             * @param {object} $oldData The old SQL row data (raw) of the folder
             * @hook RML/Folder/Renamed
             */
            do_action('RML/Folder/Renamed', $name, $this, $oldData);
            $this->debug('Successfully renamed and saved in database', __METHOD__);
        } else {
            $this->debug('Successfully setted the new name', __METHOD__);
            $this->getAbsolutePath(\true, \true);
        }
        return \true;
    }
    /**
     * Checks, if a given folder name is valid. The name is also sanitized so there can
     * be no problem for physical moves for example.
     *
     * @param string $name The folder name
     * @return boolean
     */
    public function isValidName($name) {
        $name = \trim($name);
        return \strlen($name) > 0 && !\in_array($name, $this->systemReservedFolders, \true);
    }
    /**
     * Read ids for a given folder id.
     *
     * @param int $id The folder id (-1 for root)
     * @param string $order The order
     * @param string $orderby The order by
     * @return array with ids
     */
    public static function xread($id, $order = null, $orderby = null) {
        $args = [
            'post_status' => 'inherit',
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'rml_folder' => $id,
            'fields' => 'ids'
        ];
        // Set orders
        if ($order !== null) {
            $args['order'] = $order;
        }
        if ($orderby !== null) {
            $args['orderby'] = $orderby;
        }
        /**
         * Modify the query arguments to fetch attachments within a folder.
         *
         * @param {array} $query The query with post_status, post_type and rml_folder
         * @hook RML/Folder/QueryArgs
         * @return {array} The query
         */
        $args = apply_filters('RML/Folder/QueryArgs', $args);
        $query = new \WP_Query($args);
        $posts = $query->get_posts();
        /**
         * The folder content (attachments) is fetched.
         *
         * @param {int[]|WP_Post[]} $posts The posts
         * @return {int[]|WP_Post[]}
         * @hook RML/Folder/QueryResult
         */
        $posts = apply_filters('RML/Folder/QueryResult', $posts);
        return $posts;
    }
    /**
     * Get all available order methods for subfolders.
     *
     * @param boolean $asMap
     * @return array
     */
    public static function getAvailableSubfolderOrders($asMap = \false) {
        if (self::$cachedOrders === null) {
            $orders = [
                'name_asc' => ['label' => __('Order by name ascending', RML_TD), 'sqlOrder' => 'rmlo.name'],
                'name_desc' => ['label' => __('Order by name descending', RML_TD), 'sqlOrder' => 'rmlo.name'],
                'id_asc' => ['label' => __('Order by ID ascending', RML_TD), 'sqlOrder' => 'rmlo.id'],
                'id_desc' => ['label' => __('Order by ID descending', RML_TD), 'sqlOrder' => 'rmlo.id']
            ];
            /**
             * Add an available order criterium to the tree. If you pass
             * user input to the SQL Order please be sure the values are escaped!
             *
             * @example
             * $orders["id_asc"] = [
             *  "label" => __("Order by ID ascending", RML_TD),
             *  "sqlOrder" => "rml.ID"
             * )
             * @param {object[]} $orders The available orders
             * @return {object[]}
             * @hook RML/Tree/Orderby
             * @since 4.4.0
             */
            self::$cachedOrders = apply_filters('RML/Tree/Orderby', $orders);
        }
        if ($asMap) {
            $sortables = [];
            foreach (self::$cachedOrders as $key => $value) {
                $sortables[$key] = $value['label'];
            }
            return $sortables;
        }
        return self::$cachedOrders;
    }
}

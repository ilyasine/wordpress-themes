<?php

namespace MatthiasWeb\RealMediaLibrary\attachment;

use MatthiasWeb\RealMediaLibrary\api\IStructure;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\folder\CRUD;
use MatthiasWeb\RealMediaLibrary\folder\Root;
use MatthiasWeb\RealMediaLibrary\view\View;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This class handles all hooks and functions for the structure.
 * If something will print out, this is a facade-wrapper function
 * for the class View (stored in private $view).
 */
class Structure implements \MatthiasWeb\RealMediaLibrary\api\IStructure {
    use UtilsProvider;
    /**
     * The structure should be accessible within one
     * or more blogs. For those purposes use the wp standard
     * method switch_to_blog();
     *
     * This is an array of Structure objects.
     */
    private static $blogs = [];
    /**
     * The root folder ID. Can only be set by the constructor!
     */
    private $root;
    /**
     * Array of database read rows
     */
    private $rows;
    private $parsed;
    private $tree;
    /**
     * The view handler for this structure
     */
    private $view;
    /**
     * Checks, if the folder tree is already loaded and do the initial
     * load if needed by an function. So, RML can guarantee lazy loading.
     */
    private $hasInitialLoad = \false;
    /**
     * Additional data passed to the structure.
     */
    private $data;
    // Documented in IStructure
    public function __construct($root = null, $data = null) {
        $this->view = new \MatthiasWeb\RealMediaLibrary\view\View($this);
        $this->data = $data === null ? [] : $data;
        $this->resetData($root, \false);
    }
    // Documented in IStructure
    public function initialLoad() {
        if (!$this->hasInitialLoad) {
            $this->hasInitialLoad = \true;
            $this->resetData($this->root);
        }
    }
    // Documented in IStructure
    public function resetData($root = null, $fetchData = \true) {
        $this->root = $root === null ? _wp_rml_root() : $root;
        $this->rows = [];
        $this->parsed = [];
        $this->tree = [];
        if ($fetchData) {
            $this->fetch();
        } else {
            $this->hasInitialLoad = \false;
        }
    }
    /**
     * Fetching all available folders into an array.
     */
    private function fetch() {
        global $wpdb;
        $table_name = $this->getTableName();
        /**
         * Modify the tree SQL select fields statement. Just push your
         * fields to select custom fields.
         *
         * @param {array} $fields The standard RML fields
         * @param {IStructure} $structure The structure
         * @hook RML/Tree/SQLStatement/SELECT
         * @return {array}
         */
        $fields = \join(
            ', ',
            apply_filters(
                'RML/Tree/SQLStatement/SELECT',
                [
                    // The whole row of the folder
                    'tn.*',
                    // Count images for this folder
                    'IFNULL(tn.cnt, (' .
                    \MatthiasWeb\RealMediaLibrary\attachment\CountCache::getInstance()->getSingleCountSql() .
                    ')) AS cnt_result',
                    'rml_meta_orderby.meta_value AS lastOrderBy',
                    'rml_meta_orderAutomatically.meta_value AS orderAutomatically',
                    'rml_meta_lastSubOrderBy.meta_value AS lastSubOrderBy',
                    'rml_meta_subOrderAutomatically.meta_value AS subOrderAutomatically'
                ],
                $this
            )
        );
        /**
         * Modify the tree SQL select join statement. Just push your
         * joins to $fields array.
         *
         * @param {array} $fields The standard RML fields
         * @param {IStructure} $structure The structure
         * @hook RML/Tree/SQLStatement/JOIN
         * @return {array} $fields
         */
        $joins = \join(
            ' ',
            apply_filters(
                'RML/Tree/SQLStatement/JOIN',
                [
                    // The last order by, saved in folder meta as "orderby"
                    'LEFT JOIN ' .
                    $this->getTableName('meta') .
                    " rml_meta_orderby ON rml_meta_orderby.realmedialibrary_id = tn.ID AND rml_meta_orderby.meta_key = 'orderby'",
                    // Determines, if the orderby should be applied automatically, saved in folder meta as "orderAutomatically"
                    'LEFT JOIN ' .
                    $this->getTableName('meta') .
                    " rml_meta_orderAutomatically ON rml_meta_orderAutomatically.realmedialibrary_id = tn.ID AND rml_meta_orderAutomatically.meta_key = 'orderAutomatically'",
                    // The last subfolder order by, saved in folder meta as "lastSubOrderBy"
                    'LEFT JOIN ' .
                    $this->getTableName('meta') .
                    " rml_meta_lastSubOrderBy ON rml_meta_lastSubOrderBy.realmedialibrary_id = tn.ID AND rml_meta_lastSubOrderBy.meta_key = 'lastSubOrderBy'",
                    // Determines, if the sbufolder orderby should be applied automatically, saved in folder meta as "subOrderAutomatically"
                    'LEFT JOIN ' .
                    $this->getTableName('meta') .
                    " rml_meta_subOrderAutomatically ON rml_meta_subOrderAutomatically.realmedialibrary_id = tn.ID AND rml_meta_subOrderAutomatically.meta_key = 'subOrderAutomatically'"
                ],
                $this
            )
        );
        /**
         * Add WHERE statement to modify parts of the tree SQL statement.
         *
         * @param {string} $sql The sql query
         * @param {IStructure} $structure The structure
         * @hook RML/Tree/SQLStatement/WHERE
         * @return {string}
         * @since 4.0.8
         */
        $where = apply_filters('RML/Tree/SQLStatement/WHERE', '', $this);
        /**
         * Modify the full tree SQL statement.
         *
         * @param {string} $sql The sql query
         * @param {IStructure} $structure The structure
         * @hook RML/Tree/SQLStatement
         * @return {string}
         */
        $sqlStatement = apply_filters(
            'RML/Tree/SQLStatement',
            ["SELECT {$fields} FROM {$table_name} AS tn {$joins} {$where} ORDER BY parent, ord"],
            $this
        );
        // phpcs:disable WordPress.DB.PreparedSQL
        $this->rows = $wpdb->get_results($sqlStatement[0]);
        // phpcs:enable WordPress.DB.PreparedSQL
        /**
         * The tree content is loaded.
         *
         * @param {object[]} $rows The SQL results
         * @param {IStructure} $structure The structure
         * @hook RML/Tree/SQLRows
         * @return {object[]}
         */
        $this->rows = apply_filters('RML/Tree/SQLRows', $this->rows, $this);
        $this->parse();
    }
    /**
     * This functions parses the read rows into folder objects.
     * It also handles the `cnt` cache for the attachments in this folder.
     */
    private function parse() {
        /**
         * Use this hook to register your own creatables with the help of
         * wp_rml_register_creatable().
         *
         * @hook RML/Creatable/Register
         */
        do_action('RML/Creatable/Register');
        if (!empty($this->rows)) {
            $noCntCache = \false;
            foreach ($this->rows as $key => $value) {
                // Check for image cache
                if (\is_null($value->cnt)) {
                    $noCntCache = \true;
                }
                // Prepare the data types
                $this->rows[$key]->id = \intval($value->id);
                $this->rows[$key]->parent = \intval($value->parent);
                $this->rows[$key]->ord = \intval($value->ord);
                $this->rows[$key]->cnt_result = \intval($value->cnt_result);
                // Craetable instance
                $creatable = \MatthiasWeb\RealMediaLibrary\folder\CRUD::getInstance()->getCreatables($value->type);
                if (isset($creatable)) {
                    $result = \call_user_func_array([$creatable, 'instance'], [$value]);
                    if (is_rml_folder($result)) {
                        $this->parsed[] = $result;
                    }
                }
            }
            if ($noCntCache) {
                \MatthiasWeb\RealMediaLibrary\attachment\CountCache::getInstance()->updateCountCache();
            }
        }
        /**
         * Here you can modify the fresh-created instances of an IFolder object. This allows you,
         * e. g. hide folders on frontend with `setVisible`.
         *
         * @param {IFolder[]} $parsed
         * @hook RML/Tree/Parsed
         * @return IFolder[]
         * @since 4.8.1
         */
        $this->parsed = apply_filters('RML/Tree/Parsed', $this->parsed);
        // Create the tree
        $folder = null;
        foreach ($this->parsed as $key => $category) {
            $parent = $category->getParent();
            if ($parent > -1) {
                $folder = $this->byId($parent);
                if ($folder !== null) {
                    $folder->addChildren($category);
                }
            }
        }
        $cats_tree = [];
        foreach ($this->parsed as $category) {
            if ($category->getParent() <= -1) {
                $cats_tree[] = $category;
            }
        }
        $this->tree = $cats_tree;
    }
    // Documented in IStructure
    public function byId($id, $nullForRoot = \true) {
        $id = \intval($id);
        if (!$nullForRoot && $id === -1) {
            return \MatthiasWeb\RealMediaLibrary\folder\Root::getInstance();
        }
        foreach ($this->getParsed() as $folder) {
            if ($folder->getId() === $id) {
                return $folder;
            }
        }
        /**
         * When a folder is not found by an absolute path this filter is
         * called and looks up for folders which are perhaps handled by other
         * structures. If you are hooking into this function please consider that
         * you apply_filters for RML/Tree/Parsed manually.
         *
         * @param {IFolder} $folder The folder (null if not found)
         * @param {integer} $id The searched Id
         * @param {IStructure} $structure The structure
         * @hook RML/Tree/ResolveById
         * @return {IFolder} The found folder or null if not found
         * @since 3.3.1
         */
        return apply_filters('RML/Tree/ResolveById', null, $id, $this);
    }
    // Documented in IStructure
    public function byAbsolutePath($path) {
        $path = \trim($path, '/');
        foreach ($this->getParsed() as $folder) {
            if (\strtolower($folder->getAbsolutePath()) === \strtolower($path)) {
                return $folder;
            }
        }
        /**
         * When a folder is not found by an absolute path this filter is
         * called and looks up for folders which are perhaps handled by other
         * structures. If you are hooking into this function please consider that
         * you apply_filters for RML/Tree/Parsed manually.
         *
         * @param {IFolder} $folder The folder (null if not found)
         * @param {string} $path The searched path
         * @param {IStructure} $structure The structure
         * @hook RML/Tree/ResolveByAbsolutePath
         * @return {IFolder} The found folder or null if not found
         * @since 3.3.1
         */
        return apply_filters('RML/Tree/ResolveByAbsolutePath', null, $path, $this);
    }
    // Documented in IStructure
    public function getRows() {
        $this->initialLoad();
        return $this->rows;
    }
    // Documented in IStructure
    public function getParsed() {
        $this->initialLoad();
        return $this->parsed;
    }
    // Documented in IStructure
    public function getTree() {
        $this->initialLoad();
        return $this->tree;
    }
    // Documented in IStructure
    public function getPlainTree() {
        $result = [];
        $tree = $this->getTree();
        if (\is_array($tree)) {
            foreach ($tree as $obj) {
                $plain = $obj->getPlain(\true);
                if ($plain !== null) {
                    $result[] = $plain;
                }
            }
        }
        return $result;
    }
    // Documented in IStructure
    public function getCntAttachments() {
        if (has_filter('RML/Tree/CountAttachments')) {
            /**
             * Counts all attachments which are available in the structure.
             *
             * @param {integer} $count The count
             * @param {object} $structure The structure class
             * @return {integer} The count
             * @hook RML/Tree/CountAttachments
             */
            return apply_filters('RML/Tree/CountAttachments', 0, $this);
        }
        return (int) wp_count_posts('attachment')->inherit;
    }
    /**
     * Get all folder counts.
     *
     * @return Array<string|int,int>
     */
    public function getFolderCounts() {
        $result = [];
        // Default folder counts
        $root = _wp_rml_root();
        $result['all'] = $this->getCntAttachments();
        $result[$root] = $this->getCntRoot();
        // Iterate through our folders
        $folders = $this->getParsed();
        if (\is_array($folders)) {
            foreach ($folders as $value) {
                $id = $value->getId();
                $result[$id] = $value->getCnt();
            }
        }
        return $result;
    }
    // Documented in IStructure
    public function getCntRoot() {
        $cnt = 0;
        foreach ($this->getParsed() as $folder) {
            $cnt += $folder->getCnt();
        }
        $result = $this->getCntAttachments() - $cnt;
        return $result >= 0 ? $result : 0;
    }
    /**
     * Get the view class instance.
     */
    public function getView() {
        return $this->view;
    }
    // Documented in IStructure
    public function getData() {
        return $this->data;
    }
    // Documented in IStructure
    public function setData($data) {
        $this->data = \is_array($data) ? $data : [];
    }
    /**
     * Get instance.
     *
     * @return Structure
     */
    public static function getInstance() {
        $bid = get_current_blog_id();
        return !isset(self::$blogs[$bid])
            ? (self::$blogs[$bid] = new \MatthiasWeb\RealMediaLibrary\attachment\Structure())
            : self::$blogs[$bid];
    }
}

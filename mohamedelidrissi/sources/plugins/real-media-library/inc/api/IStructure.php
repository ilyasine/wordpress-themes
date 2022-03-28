<?php

namespace MatthiasWeb\RealMediaLibrary\api;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Structure implementation for Real Media Library. It handles all SQL query which
 * reads all folders from the database and "collects" it into one tree. You can modify the
 * structure queries by RML/Tree* filters and extending the MatthiasWeb\RealMediaLibrary\attachment\Structure
 * class (implements IStructure).
 *
 * @see wp_rml_structure_reset()
 * @see wp_rml_structure()
 * @since 3.3.1
 */
interface IStructure {
    /**
     * Start reading a structure. If you pass a $root parameter the parameter is not
     * automatically respected. You should then use your own implementation or filters
     * to respect the root. Use this constructor to add your filters and respect your
     * custom Structure class implementation.
     *
     * @param int $root The root folder defined for the structure
     * @param array $data Custom data for the structure
     */
    public function __construct($root = null, $data = null);
    /**
     * Checks, if the SQL result is available and load it if not.
     */
    public function initialLoad();
    /**
     * Resets the data of the structure.
     *
     * @param int $root The root folder
     * @param boolean $fetchData Determine, if the data should be re-fetched
     * @see wp_rml_structure_reset()
     */
    public function resetData($root = null, $fetchData = \true);
    /**
     * Get a folder by id.
     *
     * @param int $id The id of the folder
     * @param boolean $nullForRoot If set to false and $id == -1 then the Root instance is returned
     * @return IFolder|null
     * @see wp_rml_get_object_by_id()
     * @see wp_rml_get_by_id()
     */
    public function byId($id, $nullForRoot = \true);
    /**
     * Get a folder by absolute path.
     *
     * @param string $path The path
     * @return IFolder|null
     * @see wp_rml_get_by_absolute_path
     */
    public function byAbsolutePath($path);
    /**
     * Get the SQL query result instead of IFolder objects.
     *
     * @return object[] The SQL result
     */
    public function getRows();
    /**
     * Get all SQL query results as IFolder objects.
     *
     * @return IFolder[] The folders
     */
    public function getParsed();
    /**
     * Get all SQL query results placed to a tree. That means it is a "hierarchical"
     * result where you work with ->getChildren(). The first level contains the top folders.
     *
     * @return IFolder[] The folders
     */
    public function getTree();
    /**
     * Get all SQL query results placed to a tree. It is fully resolved with all hierarchical
     * plain objects of the folders expect the invisible nodes.
     *
     * @return object[]
     */
    public function getPlainTree();
    /**
     * Get the attachment count for this structure.
     *
     * @return int Count
     */
    public function getCntAttachments();
    /**
     * Get the attachment count for the "Unorganized" folder for this structure.
     *
     * @return int Count
     */
    public function getCntRoot();
    /**
     * Get the custom data.
     *
     * @return array Data
     */
    public function getData();
    /**
     * Set the custom data.
     *
     * @param array $data The custom data
     */
    public function setData($data);
}

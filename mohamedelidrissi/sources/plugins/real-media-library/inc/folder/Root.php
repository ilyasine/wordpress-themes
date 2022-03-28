<?php

namespace MatthiasWeb\RealMediaLibrary\folder;

use MatthiasWeb\RealMediaLibrary\attachment\Structure;
use MatthiasWeb\RealMediaLibrary\order\Sortable;
use Exception;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This class creates a root object. (Type 4)
 * See parent classes / interfaces for better documentation.
 */
class Root extends \MatthiasWeb\RealMediaLibrary\order\Sortable {
    private static $me = null;
    // Documented in IFolder
    public function __construct() {
        parent::__construct(-1, null, '/' . __('Unorganized', RML_TD), '/', '/');
    }
    // Documented in IFolder
    public function persist() {
        throw new \Exception('You cannot persist the root folder.');
    }
    // Documented in IFolder
    public function getSlug($force = \false, $fromSetName = \false) {
        return $this->slug;
    }
    // Documented in IFolder
    public function getAbsolutePath($force = \false, $fromSetName = \false) {
        return $this->absolutePath;
    }
    // Documented in IFolder
    public function getCnt($forceReload = \false) {
        return \MatthiasWeb\RealMediaLibrary\attachment\Structure::getInstance()->getCntRoot();
    }
    // Documented in IFolder
    public function setParent($id, $ord = -1, $force = \false) {
        throw new \Exception('You cannot set a parent for the root folder.');
    }
    // Documented in IFolder
    public function setName($name, $supress_validation = \false) {
        throw new \Exception('You cannot set a name for the root folder.');
    }
    // Documented in IFolder
    public function setRestrictions($restrictions = []) {
        throw new \Exception('You cannot set permissions for the root folder.');
    }
    // Documented in IFolder
    public function getChildren() {
        return \MatthiasWeb\RealMediaLibrary\attachment\Structure::getInstance()->getTree();
    }
    // Documented in IFolder
    public function getAllowedChildrenTypes() {
        // already documented
        return apply_filters('RML/Folder/Types/' . $this->getType(), [RML_TYPE_FOLDER, RML_TYPE_COLLECTION]);
    }
    // Documented in IFolder
    public function getType() {
        return RML_TYPE_ROOT;
    }
    // Documented in IFolder
    public function getContentCustomOrder() {
        return 2;
    }
    // Documented in IFolder
    public function getTypeName($default = null) {
        return parent::getTypeName($default === null ? __('Unorganized', RML_TD) : $default);
    }
    // Documented in IFolder
    public function getTypeDescription($default = null) {
        return parent::getTypeDescription(
            $default === null
                ? __(
                    'Unorganized is the same as a root folder. Here you can find all files which are not assigned to a folder.',
                    RML_TD
                )
                : $default
        );
    }
    /**
     * Get instance.
     *
     * @return Root
     */
    public static function getInstance() {
        return self::$me === null ? (self::$me = new \MatthiasWeb\RealMediaLibrary\folder\Root()) : self::$me;
    }
}

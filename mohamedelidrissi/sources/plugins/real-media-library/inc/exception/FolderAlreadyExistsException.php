<?php

namespace MatthiasWeb\RealMediaLibrary\exception;

use MatthiasWeb\RealMediaLibrary\api\IFolder;
use Exception;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * When we want to set parent of a folder and the given name
 * already exists in the parent folder.
 */
class FolderAlreadyExistsException extends \Exception {
    private $parent;
    // Parent ID
    private $name;
    // The name which exists in this folder
    /**
     * C'tor.
     *
     * @param int $parent
     * @param string $name
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($parent, $name, $code = 0, \Exception $previous = null) {
        parent::__construct(
            // translators:
            \sprintf(__("'%s' already exists in this folder.", RML_TD), \htmlentities($name)),
            $code,
            $previous
        );
        $this->parent = $parent;
        $this->name = $name;
    }
    /**
     * Get the folder of the children in this parent.
     *
     * @return IFolder
     */
    public function getFolder() {
        $parent = wp_rml_get_object_by_id($this->getParentId());
        return $parent->hasChildren($this->getName(), \true);
    }
    /**
     * Getter.
     *
     * @return int
     */
    public function getParentId() {
        return $this->parent;
    }
    /**
     * Getter.
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}

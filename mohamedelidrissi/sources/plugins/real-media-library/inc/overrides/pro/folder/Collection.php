<?php

namespace MatthiasWeb\RealMediaLibrary\lite\folder;

use MatthiasWeb\RealMediaLibrary\order\Sortable;
use Exception;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This class creates a collection object. (Type 1)
 * See parent classes / interfaces for better documentation.
 */
class Collection extends \MatthiasWeb\RealMediaLibrary\order\Sortable {
    // Documented in Creatable
    public function insert($ids, $supress_validation = \false, $isShortcut = \false) {
        throw new \Exception(__('A collection cannot contain files.', RML_TD));
    }
    // Documented in Creatable
    public static function create($rowData) {
        $result = new \MatthiasWeb\RealMediaLibrary\lite\folder\Collection($rowData->id);
        $result->setParent($rowData->parent);
        $result->setName($rowData->name, $rowData->supress_validation);
        $result->setRestrictions($rowData->restrictions);
        return $result;
    }
    // Documented in Creatable
    public static function instance($rowData) {
        return new \MatthiasWeb\RealMediaLibrary\lite\folder\Collection(
            $rowData->id,
            $rowData->parent,
            $rowData->name,
            $rowData->slug,
            $rowData->absolute,
            $rowData->ord,
            $rowData->cnt_result,
            $rowData
        );
    }
    // Documented in IFolder
    public function getAllowedChildrenTypes() {
        return [RML_TYPE_GALLERY, RML_TYPE_COLLECTION];
    }
    // Documented in IFolder
    public function getTypeName($default = null) {
        return parent::getTypeName($default === null ? __('Collection', RML_TD) : $default);
    }
    // Documented in IFolder
    public function getTypeDescription($default = null) {
        return parent::getTypeDescription(
            $default === null
                ? __(
                    'A collection cannot contain files. But you can create other collections and <strong> galleries</strong> there.',
                    RML_TD
                )
                : $default
        );
    }
    // Documented in IFolder
    public function getType() {
        return RML_TYPE_COLLECTION;
    }
}

<?php

namespace MatthiasWeb\RealMediaLibrary\folder;

use MatthiasWeb\RealMediaLibrary\order\Sortable;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This class creates a folder object. (Type 0)
 * See parent classes / interfaces for better documentation.
 */
class Folder extends \MatthiasWeb\RealMediaLibrary\order\Sortable {
    // Documented in Creatable
    public static function create($rowData) {
        $result = new \MatthiasWeb\RealMediaLibrary\folder\Folder($rowData->id);
        $result->setParent($rowData->parent);
        $result->setName($rowData->name, $rowData->supress_validation);
        $result->setRestrictions($rowData->restrictions);
        return $result;
    }
    // Documented in Creatable
    public static function instance($rowData) {
        return new \MatthiasWeb\RealMediaLibrary\folder\Folder(
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
    // Documented in Creatable
    public function getAllowedChildrenTypes() {
        /**
         * Get allowed children folder types for a given folder type. $type can be
         * replaced with RML_TYPE_FOLDER for example.
         *
         * @param {int[]} $allowed The allowed folder types
         * @hook RML/Folder/Types/$type
         * @return {int[]} The allowed folder types
         */
        return apply_filters('RML/Folder/Types/' . $this->getType(), [RML_TYPE_FOLDER, RML_TYPE_COLLECTION]);
    }
    // Documented in Creatable
    public function getType() {
        return RML_TYPE_FOLDER;
    }
}

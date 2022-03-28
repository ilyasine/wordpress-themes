<?php

namespace MatthiasWeb\RealMediaLibrary\lite\folder;

use MatthiasWeb\RealMediaLibrary\order\Sortable;
use Exception;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This class creates a gallery data folder object. (Type 2)
 * See parent classes / interfaces for better documentation.
 */
class Gallery extends \MatthiasWeb\RealMediaLibrary\order\Sortable {
    // Documented in IFolder
    protected function singleCheckInsert($id) {
        if (!wp_attachment_is_image($id)) {
            throw new \Exception(__('You can only move images to a gallery.', RML_TD));
        }
    }
    // Documented in IFolder
    public static function create($rowData) {
        $result = new \MatthiasWeb\RealMediaLibrary\lite\folder\Gallery($rowData->id);
        $result->setParent($rowData->parent);
        $result->setName($rowData->name, $rowData->supress_validation);
        $result->setRestrictions($rowData->restrictions);
        return $result;
    }
    // Documented in Creatable
    public static function instance($rowData) {
        return new \MatthiasWeb\RealMediaLibrary\lite\folder\Gallery(
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
        return [];
    }
    // Documented in IFolder
    public function getTypeName($default = null) {
        return parent::getTypeName($default === null ? __('Gallery', RML_TD) : $default);
    }
    // Documented in IFolder
    public function getTypeDescription($default = null) {
        return parent::getTypeDescription(
            $default === null
                ? __(
                    'A gallery can only contain images. To view a gallery, go to a post and look at the buttons of the visual editor..',
                    RML_TD
                )
                : $default
        );
    }
    // Documented in IFolder
    public function getType() {
        return RML_TYPE_GALLERY;
    }
}

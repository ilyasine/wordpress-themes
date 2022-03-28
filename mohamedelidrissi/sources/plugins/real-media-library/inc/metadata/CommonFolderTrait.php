<?php

namespace MatthiasWeb\RealMediaLibrary\metadata;

use MatthiasWeb\RealMediaLibrary\api\IFolder;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Trait common folder meta helper methods.
 *
 * @since 4.0.8
 */
trait CommonFolderTrait {
    use CommonTrait;
    /**
     * Gets (and persists) a checkbox to the folder metadata.
     *
     * @param string $meta The meta key
     * @param IFolder $folder The folder
     * @param boolean $persist If set it will be updated or deleted
     * @return boolean
     */
    protected static function is($meta, $folder, $persist = null) {
        if ($persist !== null) {
            if ($persist) {
                $update = update_media_folder_meta($folder->getId(), $meta, $persist);
                return $update > 0 || $update;
            } else {
                return delete_media_folder_meta($folder->getId(), $meta);
            }
        }
        return (bool) get_media_folder_meta($folder->getId(), $meta, \true);
    }
}

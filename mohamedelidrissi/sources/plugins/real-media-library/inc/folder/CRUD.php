<?php

namespace MatthiasWeb\RealMediaLibrary\folder;

use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\exception\FolderAlreadyExistsException;
use MatthiasWeb\RealMediaLibrary\exception\OnlyInProVersionException;
use MatthiasWeb\RealMediaLibrary\overrides\interfce\folder\IOverrideCRUD;
use MatthiasWeb\RealMediaLibrary\lite\folder\CRUD as LiteCRUD;
use Exception;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Static class to perform CRUD operations on folders. Please do not use this directly
 * instead use the API functions.
 * The R (Read) is not implemented here because updates should be executed through the API functions.
 * It also holds the registered creatables.
 */
class CRUD implements \MatthiasWeb\RealMediaLibrary\overrides\interfce\folder\IOverrideCRUD {
    use UtilsProvider;
    use LiteCRUD;
    private static $me = null;
    /**
     * Available creatables.
     */
    private $creatables = [];
    // Documented in wp_rml_create()
    public function create(
        $name,
        $parent,
        $type,
        $restrictions = [],
        $supress_validation = \false,
        $return_existing_id = \false
    ) {
        $this->debug("Try to create folder with name '{$name}'...", __METHOD__);
        $errors = [];
        try {
            // Create restrictions from parent
            if ($parent >= 0) {
                $parentFolder = wp_rml_get_by_id($parent, null, \true);
                if (is_rml_folder($parentFolder)) {
                    $parentRestrictions = $parentFolder->getRestrictions();
                    foreach ($parentRestrictions as $parentRestriction) {
                        if (\substr($parentRestriction, -1) === '>') {
                            $restrictions[] = $parentRestriction;
                        }
                    }
                }
            }
            // Create the new instance for the folder
            if ($type === null) {
                throw new \Exception('Type not given.');
            }
            $creatable = $this->getCreatables($type);
            if (!$creatable) {
                throw new \Exception('Creatable class type not found.');
            }
            $result = \call_user_func_array(
                [$creatable, 'create'],
                [
                    (object) [
                        'id' => -1,
                        'parent' => \intval($parent),
                        'name' => $name,
                        'restrictions' => $restrictions,
                        'supress_validation' => $supress_validation
                    ]
                ]
            );
            // Check if other fails are counted
            if (!$supress_validation) {
                /**
                 * Checks if a creation of a folder is allowed.
                 *
                 * @param {string[]} $errors An array of errors
                 * @hook RML/Validate/Create
                 * @return {string[]} When the array has one or more items the creation is cancelled with the string message
                 */
                $errors = apply_filters('RML/Validate/Create', $errors, $name, $parent, $type);
                if (\count($errors) > 0) {
                    throw new \Exception(\implode(' ', $errors));
                }
            }
            // Persist it!
            return $result->persist();
        } catch (\MatthiasWeb\RealMediaLibrary\exception\FolderAlreadyExistsException $e) {
            if ($return_existing_id) {
                $existing_id = $e->getFolder()->getId();
                $this->debug(
                    "Found folder with same name, now return the already existing folder id {$existing_id}...",
                    __METHOD__
                );
                return $existing_id;
            }
        } catch (\MatthiasWeb\RealMediaLibrary\exception\OnlyInProVersionException $e) {
            // Safely rethrow this exception
            throw $e;
        } catch (\Exception $e) {
            // Silence is golden. Populate $e
        }
        $this->debug('Error: ' . $e->getMessage(), __METHOD__);
        return [$e->getMessage()];
    }
    // Documented in wp_rml_rename()
    public function update($name, $id, $supress_validation = \false) {
        try {
            $folder = wp_rml_get_by_id($id, null, \true);
            if ($folder !== null) {
                $folder->setName($name, $supress_validation);
            } else {
                throw new \Exception(__('The given folder does not exist or you cannot rename this folder.', RML_TD));
            }
            return \true;
        } catch (\Exception $e) {
            $this->debug('Error:' . $e->getMessage(), __METHOD__);
            return [$e->getMessage()];
        }
    }
    // Documented in wp_rml_delete()
    public function remove($id, $supress_validation = \false) {
        $this->debug("Try to delete folder id {$id}...", __METHOD__);
        try {
            $folder = wp_rml_get_object_by_id($id);
            if ($folder !== null) {
                // Check if subfolders
                if (\count($folder->getChildren()) > 0) {
                    throw new \Exception(__('The folder you try to delete has subfolders.', RML_TD));
                }
                // Check if other fails are counted
                if ($supress_validation === \false) {
                    $errors = apply_filters('RML/Validate/Delete', [], $id, $folder);
                    if (\count($errors) > 0) {
                        throw new \Exception(\implode(' ', $errors));
                    }
                }
                /**
                 * This action is fired before a folder gets deleted. It allows you
                 * for example to throw an exception if the deletion should be stopped with an
                 * error message.
                 *
                 * @param {IFolder} $folder The folder object
                 * @hook RML/Folder/Predeletion
                 */
                do_action('RML/Folder/Predeletion', $folder);
                /**
                 * This action is fired before a folder gets deleted and surely gets deleted
                 * (that means after the RML/Folder/Predeletion action).
                 *
                 * @param {IFolder} $folder The folder object
                 * @hook RML/Folder/Delete
                 * @since 4.0.7
                 */
                do_action('RML/Folder/Delete', $folder);
                global $wpdb;
                // Delete folder
                // phpcs:disable WordPress.DB.PreparedSQL
                $table_name = $this->getTableName();
                $wpdb->query($wpdb->prepare("DELETE FROM {$table_name} WHERE id = %d", $id));
                // Delete post relations and move to unorganized
                $table_name = $this->getTableName('posts');
                $wpdb->query($wpdb->prepare("DELETE FROM {$table_name} WHERE fid = %d", $id));
                // phpcs:enable WordPress.DB.PreparedSQL
                // Do the action
                $rowData = $folder->getRowData();
                wp_rml_structure_reset();
                /**
                 * This action is fired after a folder is deleted.
                 *
                 * @param {int} $id The folder id
                 * @param {object} $rowData The SQL row data (raw) data for the deleted folder
                 * @hook RML/Folder/Deleted
                 */
                do_action('RML/Folder/Deleted', $id, $rowData);
                $this->debug("Successfully deleted folder id {$id}", __METHOD__);
                return \true;
            } else {
                throw new \Exception(__('The given folder does not exist.', RML_TD));
            }
        } catch (\Exception $e) {
            $this->debug('Error: ' . $e->getMessage(), __METHOD__);
            return [$e->getMessage()];
        }
    }
    // Documented in wp_rml_register_creatable()
    public function registerCreatable($qualified, $type, $onRegister = \false) {
        $this->creatables[$type] = $qualified;
        if ($onRegister) {
            \call_user_func([$qualified, 'onRegister']);
        }
    }
    /**
     * Get available creatables.
     *
     * @param int $type
     * @return int[]|Creatable
     */
    public function getCreatables($type = null) {
        return $type === null ? $this->creatables : (isset($this->creatables[$type]) ? $this->creatables[$type] : null);
    }
    /**
     * Get instance.
     *
     * @return CRUD
     */
    public static function getInstance() {
        return self::$me === null ? (self::$me = new \MatthiasWeb\RealMediaLibrary\folder\CRUD()) : self::$me;
    }
}

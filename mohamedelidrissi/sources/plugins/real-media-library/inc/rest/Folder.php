<?php

namespace MatthiasWeb\RealMediaLibrary\rest;

use MatthiasWeb\RealMediaLibrary\attachment\Structure;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\lite\rest\Folder as LiteFolder;
use MatthiasWeb\RealMediaLibrary\metadata\Meta;
use MatthiasWeb\RealMediaLibrary\overrides\interfce\rest\IOverrideFolder;
use MatthiasWeb\RealMediaLibrary\rest\Service;
use WP_Error;
use WP_REST_Response;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Enables the /folders REST for all creatable items.
 */
class Folder implements \MatthiasWeb\RealMediaLibrary\overrides\interfce\rest\IOverrideFolder {
    use UtilsProvider;
    use LiteFolder;
    /**
     * Register endpoints.
     */
    public function rest_api_init() {
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/folders/content/counts', [
            'methods' => 'GET',
            'callback' => [$this, 'getContentCounts'],
            'permission_callback' => [$this, 'permission_callback']
        ]);
        if ($this->isPro()) {
            register_rest_route(
                \MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE,
                '/folders/(?P<fid>\\d+)/content/sortables',
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'applyContentSortables'],
                    'permission_callback' => [$this, 'permission_callback']
                ]
            );
            register_rest_route(
                \MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE,
                '/folders/(?P<fid>\\d+)/sortables',
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'applyFolderSortables'],
                    'permission_callback' => [$this, 'permission_callback']
                ]
            );
        }
        register_rest_route(
            \MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE,
            '/folders/(?P<id>\\d+)/meta',
            [
                'methods' => 'GET',
                'callback' => [$this, 'getMetaHTML'],
                'permission_callback' => [$this, 'permission_callback']
            ]
        );
        register_rest_route(
            \MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE,
            '/folders/(?P<id>\\d+)/meta',
            [
                'methods' => 'PUT',
                'callback' => [$this, 'updateMeta'],
                'permission_callback' => [$this, 'permission_callback']
            ]
        );
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/folders/(?P<id>\\d+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'updateItem'],
            'permission_callback' => [$this, 'permission_callback']
        ]);
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/folders/(?P<id>\\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'deleteItem'],
            'permission_callback' => [$this, 'permission_callback']
        ]);
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/folders', [
            'methods' => 'POST',
            'callback' => [$this, 'createItem'],
            'permission_callback' => [$this, 'permission_callback']
        ]);
    }
    /**
     * Check if user is allowed to call this service requests.
     */
    public function permission_callback() {
        $permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit();
        return $permit === null ? \true : $permit;
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {get} /realmedialibrary/v1/folders/content/counts Get all folder counts
     * @apiName GetFolderContentCounts
     * @apiGroup Folder
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     */
    public function getContentCounts($request) {
        return new \WP_REST_Response(
            \MatthiasWeb\RealMediaLibrary\attachment\Structure::getInstance()->getFolderCounts()
        );
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {post} /realmedialibrary/v1/folders/:fid/content/sortables Set a folders content order
     * @apiParam {string} id The sortable id. Pass "original" to reset the folder,
     *  pass "deactivate" to deactive the automatic order,
     *  pass "reindex" to reindex the order indexes,
     *  pass "last" to try to to reset to the last available order
     * @apiParam {boolean} [automatically] Automatically use this order when new files are added to the folder
     * @apiName ApplyFolderContentSorting
     * @apiGroup Folder
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     * @apiPermission Pro only
     * @since 4.4 The old API /folders/content/sortables is deleted
     */
    public function applyContentSortables($request) {
        $sortable = $request->get_param('id');
        $applyTo = $request->get_param('fid');
        $automatically = $request->get_param('automatically');
        $automatically = \gettype($automatically) === 'string' ? $automatically === 'true' : $automatically;
        $folder = wp_rml_get_object_by_id($applyTo);
        $isFolder = is_rml_folder($folder);
        $result = \false;
        if ($sortable === 'original') {
            $result = $isFolder && $folder->contentDeleteOrder();
        } elseif ($sortable === 'deactivate') {
            $result = update_media_folder_meta($folder->getId(), 'orderAutomatically', \false);
        } elseif ($sortable === 'reindex') {
            $result = $isFolder && $folder->contentReindex();
        } elseif ($sortable === 'last') {
            $result = $isFolder && $folder->getContentOldCustomNrCount() > 0 && $folder->contentRestoreOldCustomNr();
        } else {
            $result = $isFolder && $folder->contentOrderBy($sortable);
            if ($result) {
                update_media_folder_meta($folder->getId(), 'orderAutomatically', $automatically);
            }
        }
        return new \WP_REST_Response(['success' => $result]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {post} /realmedialibrary/v1/folders/:fid/sortables Set a folders subfolder order
     * @apiParam {string} id The sortable id. Pass "original" to reset the folder,
     *  pass "deactivate" to deactive the automatic order,
     *  pass "reindex" to reindex the order indexes,
     *  pass "last" to try to to reset to the last available order
     * @apiParam {boolean} [automatically] Automatically use this order when new subfolders are added to the folder
     * @apiName ApplyFolderSorting
     * @apiGroup Folder
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     * @apiPermission Pro only
     * @since 4.4
     */
    public function applyFolderSortables($request) {
        $sortable = $request->get_param('id');
        $applyTo = $request->get_param('fid');
        $automatically = $request->get_param('automatically');
        $automatically = \gettype($automatically) === 'string' ? $automatically === 'true' : $automatically;
        $folder = wp_rml_get_object_by_id($applyTo);
        $isFolder = is_rml_folder($folder);
        $result = \false;
        if ($sortable === 'original') {
            $result = $isFolder && $folder->resetSubfolderOrder();
        } elseif ($sortable === 'deactivate') {
            $result = update_media_folder_meta($folder->getId(), 'subOrderAutomatically', \false);
        } elseif ($sortable === 'reindex') {
            $result = $isFolder && $folder->reindexChildrens();
        } elseif ($sortable === 'last') {
            //$result = $isFolder && $folder->getContentOldCustomNrCount() > 0 && $folder->contentRestoreOldCustomNr();
        } else {
            $result = $isFolder && $folder->orderSubfolders($sortable);
            if ($result) {
                update_media_folder_meta($folder->getId(), 'subOrderAutomatically', $automatically);
            }
        }
        return new \WP_REST_Response(['success' => $result]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {delete} /realmedialibrary/v1/folders/:id Delete a folder by id
     * @apiName DeleteFolder
     * @apiGroup Folder
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     */
    public function deleteItem($request) {
        $id = $request->get_param('id');
        $delete = wp_rml_delete($id);
        if ($delete === \true) {
            return new \WP_REST_Response(['success' => $delete]);
        } else {
            return new \WP_Error('rest_rml_folder_delete', \implode(' ', $delete), ['status' => 500]);
        }
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {get} /realmedialibrary/v1/folders/:id/meta Get the HTML meta content
     * @apiName GetFolderMeta
     * @apiGroup Folder
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     */
    public function getMetaHTML($request) {
        $id = $request->get_param('id');
        return new \WP_REST_Response([
            'html' => \MatthiasWeb\RealMediaLibrary\metadata\Meta::getInstance()->prepare_content($id)
        ]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {put} /realmedialibrary/v1/folders/:id/meta Update meta of a folder
     * @apiDescription Send a key value map of form data so Meta implementations (IMetadata) can handle it
     * @apiName UpdateFolderMeta
     * @apiGroup Folder
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     */
    public function updateMeta($request) {
        $folder = wp_rml_get_object_by_id($request->get_param('id'));
        if (!is_rml_folder($folder)) {
            return new \WP_Error('rest_rml_folder_meta_update_not_found', 'Not found', ['status' => 404]);
        }
        /**
         * This filter is called to save the metadata. You can use the $_POST
         * fields to validate the input. If an error occurs you can pass an
         * "error" array (string) to the response. Do not use this filter directly instead use the
         * add_rml_meta_box() function!
         *
         * @param {array} $response The response passed to the frontend
         * @param {WP_REST_Request} $request The server request
         * @hook RML/Folder/Meta/Save
         * @return {array}
         */
        $response = apply_filters('RML/Folder/Meta/Save', ['errors' => [], 'data' => []], $folder, $request);
        if (\is_array($response) && isset($response['errors']) && \count($response['errors']) > 0) {
            return new \WP_Error('rest_rml_folder_update', $response['errors'], ['status' => 500]);
        } else {
            if (isset($response['data']) && \is_array($response['data'])) {
                $response = $response['data'];
            }
            return new \WP_REST_Response($response);
        }
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {put} /realmedialibrary/v1/folders/:id Update a folder by id
     * @apiParam {string} name The new name for the folder
     * @apiName UpdateFolder
     * @apiGroup Folder
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     */
    public function updateItem($request) {
        $name = $request->get_param('name');
        $id = $request->get_param('id');
        $update = wp_rml_rename($name, $id);
        if ($update === \true) {
            $folder = wp_rml_get_by_id($id, null, \true);
            return new \WP_REST_Response($folder->getPlain());
        } else {
            return new \WP_Error('rest_rml_folder_update', \implode(' ', $update), ['status' => 500]);
        }
    }
}

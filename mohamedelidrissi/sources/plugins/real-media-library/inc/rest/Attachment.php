<?php

namespace MatthiasWeb\RealMediaLibrary\rest;

use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\rest\Service;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use MatthiasWeb\RealMediaLibrary\attachment\CustomField;
use MatthiasWeb\RealMediaLibrary\attachment\Structure;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Enables the /attachments REST.
 */
class Attachment {
    use UtilsProvider;
    const MODIFIER_TYPE_BULK_MOVE = 'bulkMove';
    /**
     * Register endpoints.
     */
    public function rest_api_init() {
        if ($this->isPro()) {
            register_rest_route(
                \MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE,
                '/attachments/(?P<id>\\d+)',
                [
                    'methods' => 'PUT',
                    'callback' => [$this, 'updateItem'],
                    'permission_callback' => [$this, 'permission_callback']
                ]
            );
        }
        register_rest_route(
            \MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE,
            '/attachments/(?P<id>\\d+)/shortcutInfo',
            [
                'methods' => 'GET',
                'callback' => [$this, 'routeShortcutInfo'],
                'permission_callback' => [$this, 'permission_callback']
            ]
        );
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/attachments/bulk/move', [
            'methods' => 'PUT',
            'callback' => [$this, 'routeBulkMove'],
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
     * Extend the WP core REST API to allow orderby and rml_folder in
     * wp-json/wp/v2/media.
     *
     * @param array $params
     * @return array
     * @since 4.5.3
     */
    public function rest_attachment_collection_params($params) {
        $params['orderby']['enum'][] = 'rml';
        $params['rml_folder'] = [
            'description' => __('Fetch only media in a folder by folder id.', RML_TD),
            'type' => 'integer'
        ];
        return $params;
    }
    /**
     * Extend the WP core REST API to parse orderby and rml_folder in
     * wp-json/wp/v2/media.
     *
     * @param array $args
     * @param WP_REST_Request $request
     * @return array
     * @since 4.5.3
     */
    public function rest_attachment_query($args, $request) {
        $fid = $request->get_param('rml_folder');
        if (isset($fid)) {
            $args['rml_folder'] = $fid;
        }
        return $args;
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {put} /realmedialibrary/v1/attachments/:id Update an attachment order by id
     * @apiParam {int} folderId The folder id
     * @apiParam {int} attachmentId The attachment id
     * @apiParam {int} [nextId] The next id relative to the attachment
     * @apiParam {int} lastId The last id in the current sortable view
     * @apiName UpdateAttachment
     * @apiGroup Attachment
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     * @apiPermission Pro only
     */
    public function updateItem($request) {
        $folderId = $request->get_param('folderId');
        $attachmentId = $request->get_param('id');
        $nextId = $request->get_param('nextId');
        $lastIdInView = $request->get_param('lastId');
        if (!empty($folderId) && !empty($nextId)) {
            $update = wp_attachment_order_update($folderId, $attachmentId, $nextId, $lastIdInView);
            if (\is_array($update)) {
                return new \WP_Error('rest_rml_folder_content_order_failed', \implode(' ', $update), ['status' => 500]);
            } else {
                return new \WP_REST_Response(['success' => \true]);
            }
        }
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {get} /realmedialibrary/v1/posts/:id/shortcutInfo Get the shortcut container
     * @apiName GetAttachmentShortcutInfo
     * @apiGroup Attachment
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     */
    public function routeShortcutInfo($request) {
        $id = $request->get_param('id');
        return new \WP_REST_Response([
            'html' => \MatthiasWeb\RealMediaLibrary\attachment\CustomField::getInstance()->getShortcutInfoContainer($id)
        ]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {put} /realmedialibrary/v1/attachments/bulk/move Move/Copy multipe attachments
     * @apiParam {int[]} ids The post ids to move / copy
     * @apiParam {int} to The term id
     * @apiParam {boolean} isCopy If true the post is appended to the category
     * @apiName UpdatePostBulkMove
     * @apiGroup Attachment
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     */
    public function routeBulkMove($request) {
        $ids = $request->get_param('ids');
        $to = \intval($request->get_param('to'));
        $isCopy = $request->get_param('isCopy');
        $isCopy = \gettype($isCopy) === 'string' ? $isCopy === 'true' : $isCopy;
        if (!\is_array($ids) || \count($ids) === 0 || $to === null) {
            return new \WP_Error('rest_rml_posts_bulk_move_failed', __('Something went wrong.', RML_TD), [
                'status' => 500
            ]);
        }
        $result = wp_rml_move($to, $ids, \false, $isCopy);
        if (\is_array($result)) {
            return new \WP_Error('rest_rml_attachment_bulk_move_failed', \implode(' ', $result), ['status' => 500]);
        } else {
            wp_rml_structure_reset();
            return new \WP_REST_Response(
                \MatthiasWeb\RealMediaLibrary\rest\Service::responseModify(self::MODIFIER_TYPE_BULK_MOVE, [
                    'counts' => \MatthiasWeb\RealMediaLibrary\attachment\Structure::getInstance()->getFolderCounts()
                ])
            );
        }
    }
}

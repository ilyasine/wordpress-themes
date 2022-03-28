<?php

namespace MatthiasWeb\RealMediaLibrary\overrides\interfce\rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
interface IOverrideService {
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {put} /realmedialibrary/v1/hierarchy/:id Change a folder position within the hierarchy
     * @apiParam {int} id The folder id
     * @apiParam {int} parent The parent
     * @apiParam {int} nextId The next id to the folder
     * @apiName PutHierarchy
     * @apiGroup Tree
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     * @apiSince 4.12.1 This function ignores the `parent` parameter in Lite version as creating subfolders is no longer supported
     */
    public function routeHierarchy($request);
}

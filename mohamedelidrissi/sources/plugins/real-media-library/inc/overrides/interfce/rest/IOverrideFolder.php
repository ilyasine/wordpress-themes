<?php

namespace MatthiasWeb\RealMediaLibrary\overrides\interfce\rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
interface IOverrideFolder {
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {post} /realmedialibrary/v1/folders Create a new folder
     * @apiParam {string} name The new name for the folder
     * @apiParam {int} parent The parent
     * @apiParam {string} type The folder type
     * @apiName DeleteFolder
     * @apiGroup Folder
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     * @apiSince 4.12.1 This function ignores the `parent` parameter in Lite version as creating subfolders is no longer supported
     */
    public function createItem($request);
}

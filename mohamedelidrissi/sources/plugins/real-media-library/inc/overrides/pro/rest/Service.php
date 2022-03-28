<?php

namespace MatthiasWeb\RealMediaLibrary\lite\rest;

use WP_Error;
use WP_REST_Response;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
trait Service {
    // Documented in IOverrideService
    public function routeHierarchy($request) {
        $id = $request->get_param('id');
        $parent = $request->get_param('parent');
        $nextId = $request->get_param('nextId');
        $folder = wp_rml_get_object_by_id($id);
        if (is_rml_folder($folder)) {
            $result = $folder->relocate($parent, $nextId);
            if ($result === \true) {
                return new \WP_REST_Response(['success' => \true]);
            } else {
                return new \WP_Error('rest_rml_hierarchy_failed', \implode(' ', $result), ['status' => 500]);
            }
        } else {
            return new \WP_Error('rest_rml_hierarchy_not_found', __('Folder not found.', RML_TD), ['status' => 500]);
        }
    }
}

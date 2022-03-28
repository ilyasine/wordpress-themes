<?php

namespace MatthiasWeb\RealMediaLibrary\lite\rest;

use MatthiasWeb\RealMediaLibrary\exception\OnlyInProVersionException;
use WP_Error;
use WP_REST_Response;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
trait Folder {
    // Documented in IOverrideFolder
    public function createItem($request) {
        $name = $request->get_param('name');
        $parent = $request->get_param('parent');
        $type = $request->get_param('type');
        try {
            $insert = wp_rml_create($name, $parent, $type);
        } catch (\MatthiasWeb\RealMediaLibrary\exception\OnlyInProVersionException $e) {
            return new \WP_Error('rest_rml_only_pro', $e->getMessage(), ['status' => 500]);
        }
        if (\is_array($insert)) {
            return new \WP_Error('rest_rml_folder_create_failed', \implode(' ', $insert), ['status' => 500]);
        } else {
            return new \WP_REST_Response(wp_rml_get_object_by_id($insert)->getPlain());
        }
    }
}

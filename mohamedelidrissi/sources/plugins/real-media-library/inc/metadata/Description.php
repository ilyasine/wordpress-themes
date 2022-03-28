<?php

namespace MatthiasWeb\RealMediaLibrary\metadata;

use MatthiasWeb\RealMediaLibrary\api\IMetadata;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Implements a description field.
 */
class Description implements \MatthiasWeb\RealMediaLibrary\api\IMetadata {
    /**
     * Get description if set.
     *
     * @param int $folder_id
     * @return string
     */
    public function getDescription($folder_id) {
        return get_media_folder_meta($folder_id, 'description', \true);
    }
    /**
     * Generate content for meta box.
     *
     * @param string $content
     * @param IFolder $folder
     */
    public function content($content, $folder) {
        $description = $this->getDescription($folder->getId());
        $content .=
            '<label>' .
            __('Description') .
            '</label><textarea name="description" class="regular-text">' .
            esc_textarea($description) .
            '</textarea>';
        return $content;
    }
    /**
     * Save content in meta box.
     *
     * @param string[] $response
     * @param IFolder $folder
     * @param WP_REST_Request $request
     * @return string[]
     */
    public function save($response, $folder, $request) {
        $fid = $folder->getId();
        $description = $this->getDescription($fid);
        $new = $request->get_param('description');
        if (isset($new) && $new !== $description) {
            if (\strlen($new) > 0) {
                update_media_folder_meta($fid, 'description', $new);
            } else {
                // Delete it
                delete_media_folder_meta($fid, 'description');
            }
        }
        return $response;
    }
    /**
     * Enqueue scripts.
     *
     * @param Assets $assets
     */
    public function scripts($assets) {
        // Silence is golden.
    }
}

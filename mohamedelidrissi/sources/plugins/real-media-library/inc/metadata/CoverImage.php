<?php

namespace MatthiasWeb\RealMediaLibrary\metadata;

use MatthiasWeb\RealMediaLibrary\api\IFolder;
use MatthiasWeb\RealMediaLibrary\api\IMetadata;
use MatthiasWeb\RealMediaLibrary\Assets;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use WP_REST_Request;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Implements a cover image for root folder, collections, galleries and normal folders.
 */
class CoverImage implements \MatthiasWeb\RealMediaLibrary\api\IMetadata {
    use UtilsProvider;
    /**
     * C'tor.
     */
    public function __construct() {
        add_action('delete_attachment', [$this, 'delete_attachment']);
        add_action('wp_ajax_get-attachment-by-url', [$this, 'ajax_get_attachment_by_url'], 15);
    }
    /**
     * Get attachment id by URL via admin-ajax.php.
     *
     * @see https://www.npmjs.com/package/wp-media-picker#implement-additional-ajax-function
     */
    public function ajax_get_attachment_by_url() {
        if (!isset($_REQUEST['url'])) {
            wp_send_json_error();
        }
        $id = attachment_url_to_postid($_REQUEST['url']);
        if (!$id) {
            wp_send_json_error();
        }
        $_REQUEST['id'] = $id;
        wp_ajax_get_attachment();
        die();
    }
    /**
     * An attachment gets deleted, so delete meta, too.
     *
     * @param int $postid
     */
    public function delete_attachment($postid) {
        delete_metadata('realmedialibrary', null, 'coverImage', $postid, \true);
    }
    /**
     * Enqueue scripts.
     *
     * @param Assets $assets
     */
    public function scripts($assets) {
        if ($this->isDisabled()) {
            return;
        }
        $assets->enqueueLibraryScript(
            'wp-media-picker',
            'wp-media-picker/wp-media-picker.min.js',
            ['underscore', 'media-views'],
            \true
        );
        $assets->enqueueLibraryStyle('wp-media-picker', 'wp-media-picker/wp-media-picker.min.css');
    }
    /**
     * Generate content for meta box.
     *
     * @param string $content
     * @param IFolder $folder
     */
    public function content($content, $folder) {
        if ($this->isDisabled()) {
            return __('This option is disabled on this page. Please navigate to the media library.', RML_TD);
        }
        $id = $this->getAttachmentID($folder->getId());
        return $content .
            '<label>' .
            __('Cover image', RML_TD) .
            '</label><input name="coverImage" data-wprfc-visible="1" data-wprfc="metaCoverImage" value="' .
            esc_attr($id) .
            '" type="text" />';
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
        $coverImage = $this->getAttachmentID($fid);
        $new = (int) $request->get_param('coverImage');
        if ($coverImage !== $new) {
            if (wp_attachment_is_image($new)) {
                update_media_folder_meta($fid, 'coverImage', $new);
            } else {
                // Delete it
                delete_media_folder_meta($fid, 'coverImage');
            }
        }
        return $response;
    }
    /**
     * WP Media Picker is not compatible with Thrive Quiz Builder so disable this meta in this view.
     */
    public function isDisabled() {
        return isset($_GET['post_type'], $_GET['tge']) && $_GET['post_type'] === 'tqb_quiz' && $_GET['tge'] === 'true';
    }
    /**
     * Get attachment ID if cover image is set.
     *
     * @param int $fid
     * @return int
     */
    public function getAttachmentID($fid) {
        return (int) get_media_folder_meta($fid, 'coverImage', \true);
    }
}

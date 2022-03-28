<?php

namespace MatthiasWeb\RealMediaLibrary\attachment;

use MatthiasWeb\RealMediaLibrary\view\Options;
use WP_Post;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This class handles all hooks for the custom field in a attachments dialog.
 */
class CustomField {
    private static $me = null;
    /**
     * C'tor.
     */
    private function __construct() {
        // Silence is golden.
    }
    /**
     * When editing a attachment show up a select option to change the parent folder.
     *
     * @param array $form_fields
     * @param WP_Post $post
     * @return array
     */
    public function attachment_fields_to_edit($form_fields, $post) {
        if (!wp_rml_active()) {
            return $form_fields;
        }
        // Check if RML is active on frontend
        if (!is_admin() && !\MatthiasWeb\RealMediaLibrary\view\Options::load_frontend()) {
            return $form_fields;
        }
        $folderID = wp_attachment_folder($post->ID);
        // Check move permission
        $editable = \true;
        if ($folderID > 0) {
            $folder = wp_rml_get_object_by_id($folderID);
            $editable = is_rml_folder($folder) && !$folder->isRestrictFor('mov');
        }
        $isShortcut = wp_attachment_is_shortcut($post->ID);
        $textToMove = $isShortcut
            ? __('If you move this shortcut, the location of the source/main file is not changed.', RML_TD)
            : __(
                'If you move this attachment, the folder location of the associated shortcuts are not changed.',
                RML_TD
            );
        /**
         * This content is showed in the attachment details below the custom field dropdown.
         *
         * @param {string} $output HTML output
         * @param {WP_Post} $post The attachment
         * @param {boolean} $isShortcut If true the file is a shortcut
         * @parma {array} $form_fields
         * @return {string} The HTML output
         * @since 4.0.7
         * @hook RML/CustomField
         */
        $appendHTML = apply_filters('RML/CustomField', '', $post, $isShortcut, $form_fields);
        $selector = wp_rml_selector([
            'selected' => $folderID,
            'name' => 'rmlFolder',
            'editable' => $editable,
            'disabled' => [RML_TYPE_COLLECTION],
            'name' => 'attachments[' . $post->ID . '][rml_folder]',
            'title' => __('Move to another folder', RML_TD)
        ]);
        // Create form field
        $form_fields['rml_dir'] = [
            'label' => __('Folder', RML_TD),
            'input' => 'html',
            'html' =>
                '<div class="rml-compat-preUploadUi">' .
                $selector .
                '</div><p class="description">' .
                $textToMove .
                '</p>' .
                $appendHTML
        ];
        // Create form field
        $form_fields['rml_shortcut'] = [
            'label' => '',
            'input' => 'html',
            'html' =>
                '<div class="rml-wprfc" data-wprfc="shortcutInfo" data-id="' .
                $post->ID .
                '"></div><script>jQuery(function() { window.rml.hooks.call("wprfc"); });</script>'
        ];
        return $form_fields;
    }
    /**
     * Get the HTML shortcut info container.
     *
     * @param int $postId The post id
     * @return string
     */
    public function getShortcutInfoContainer($postId) {
        $post = get_post($postId);
        $output = '';
        if ($post !== null) {
            // Return output
            $output =
                '<div class="rml-shortcut-info-container" data-id="' .
                $postId .
                '">
                <div style="clear:both;"></div>
                <h2>' .
                __('Shortcut infos', RML_TD) .
                '</h2>';
            $shortcut = wp_attachment_is_shortcut($post, \true);
            $output .= '<p class="description">';
            if ($shortcut > 0) {
                $output .=
                    __(
                        'This is a shortcut of a media library file. Shortcuts doesn\'t need any physical storage <strong>(0 kB)</strong>. If you want to change the file itself, you must do this in the original file (for example replace media file through a plugin).<br/>Note also that the fields in the shortcuts can be different to the original file, for example "Title", "Description" or "Caption".',
                        RML_TD
                    ) .
                    '
                    <a target="_blank" href="' .
                    admin_url('post.php?post=' . $shortcut . '&action=edit') .
                    '">Open original file.</a><br />';
            }
            $shortcuts = wp_attachment_get_shortcuts(wp_attachment_ensure_source_file($post->ID), \false, \true);
            // Filter out own id
            foreach ($shortcuts as $key => &$value) {
                if (\intval($value['attachment']) === $post->ID) {
                    unset($shortcuts[$key]);
                }
            }
            $shortcutsCnt = \count($shortcuts);
            if ($shortcutsCnt > 0) {
                $output .= \sprintf(
                    // translators:
                    _n(
                        'For this file is %d shortcut available in the following folder:',
                        'For this file are %d shortcuts available in the following folders:',
                        $shortcutsCnt,
                        RML_TD
                    ),
                    $shortcutsCnt
                );
                foreach ($shortcuts as $shortcut) {
                    $folderName =
                        $shortcut['folderId'] === '-1'
                            ? wp_rml_get_object_by_id(-1)->getName(\true)
                            : \htmlentities($shortcut['name']);
                    $output .= '<div>';
                    $output .=
                        $folderName .
                        ' (<a target="_blank" href="' .
                        admin_url('post.php?post=' . $shortcut['attachment'] . '&action=edit') .
                        '">Open shortcut file</a>)';
                    $output .= '</div>';
                }
            } elseif (!$shortcut) {
                $output .= __(
                    'This file has no associated shortcuts. You can create shortcuts by moving files per mouse and hold any key.',
                    RML_TD
                );
            }
            $output .= '</p>';
            /**
             * This content is showed in the attachment details. It shows informations
             * about the shortcut.
             *
             * @param {string} $output HTML output
             * @param {WP_Post} $post The attachment
             * @param {int} $shortcut If > 0 it is an attachment id (source)
             * @return {string} The HTML output
             * @hook RML/Shortcut/Info
             */
            apply_filters('RML/Shortcut/Info', $output, $post, $shortcut);
            $output .= '</div>';
        }
        return $output;
    }
    /**
     * When saving a attachment change the parent folder.
     *
     * @param WP_Post $post
     * @param array $attachment
     * @return WP_Post
     */
    public function attachment_fields_to_save($post, $attachment) {
        if (isset($attachment['rml_folder']) && wp_rml_active()) {
            $folder = wp_rml_get_object_by_id($attachment['rml_folder']);
            $folderId = $folder === null ? _wp_rml_root() : $folder->getId();
            // Get previous folder id
            $currentFolderId = wp_attachment_folder($post['ID']);
            if ($currentFolderId !== $folderId) {
                $updateCount = [$currentFolderId, $folderId];
                // Update to new folder id
                $result = wp_rml_move($folderId, [$post['ID']]);
                if (\is_array($result)) {
                    $post['errors']['rml_folder']['errors'][] = \implode(' ', $result);
                }
                // Reset the count of both folders manually because we do not use the wp_rml_move api method
                \MatthiasWeb\RealMediaLibrary\attachment\CountCache::getInstance()->resetCountCache($updateCount);
            }
        }
        return $post;
    }
    /**
     * Get instance.
     *
     * @return CustomField
     */
    public static function getInstance() {
        return self::$me === null
            ? (self::$me = new \MatthiasWeb\RealMediaLibrary\attachment\CustomField())
            : self::$me;
    }
}

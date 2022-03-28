<?php

namespace MatthiasWeb\RealMediaLibrary\view;

use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\folder\Creatable;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handles the shortcode for [folder-gallery].
 */
class FolderShortcode {
    use UtilsProvider;
    private static $me = null;
    public static $TAG = 'folder-gallery';
    /**
     * C'tor.
     */
    function __construct() {
        if (is_admin() && wp_rml_active()) {
            add_action('admin_head', [$this, 'admin_head']);
            add_filter('RML/Localize', [$this, 'localize']);
        }
    }
    /**
     * Modify admin_head section.
     */
    function admin_head() {
        // check user permissions
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }
        // check if WYSIWYG is enabled
        if (get_user_option('rich_editing')) {
            add_filter('mce_external_plugins', [$this, 'mce_external_plugins']);
            add_filter('mce_buttons', [$this, 'mce_buttons']);
        }
    }
    /**
     * Modify original shortcode attributes of [gallery].
     *
     * @param array $out
     * @param array $pairs
     * @param array $atts
     * @return array
     */
    public function shortcode_atts_gallery($out, $pairs, $atts) {
        $atts = shortcode_atts(['fid' => -2, 'order' => 'DESC', 'orderby' => 'date', 'posts_per_page' => -1], $atts);
        // The fid can also come from $out
        if (isset($out['fid']) && $out['fid'] > -2) {
            $atts['fid'] = $out['fid'];
        }
        // RML order is only available with ASC
        if ($atts['orderby'] === 'rml' || (isset($out['orderby']) && $out['orderby'] === 'rml')) {
            $out['orderby'] = 'menu_order ID';
        }
        if ($atts['fid'] > -2) {
            if (!isset($out['include'])) {
                $out['include'] = '';
            }
            if ($atts['fid'] > -1) {
                $folder = wp_rml_get_object_by_id($atts['fid']);
                if ($folder !== null) {
                    $out['include'] .= ',' . \implode(',', $folder->read($atts['order'], $atts['orderby']));
                }
            } else {
                $out['include'] .=
                    ',' .
                    \implode(
                        ',',
                        \MatthiasWeb\RealMediaLibrary\folder\Creatable::xread(-1, $atts['order'], $atts['orderby'])
                    );
            }
            $out['include'] = \ltrim($out['include'], ',');
            $out['include'] = \rtrim($out['include'], ',');
        }
        // Overwrite the default order by this shortcode
        if (isset($out['orderby']) && $out['orderby'] === 'menu_order ID') {
            $out['orderby'] = 'post__in';
        }
        return $out;
    }
    /**
     * Localized variables for TinyMCE shortcode generator.
     *
     * @param array $arr
     * @return array
     */
    public function localize($arr) {
        $arr['mce'] = [
            'mceButtonTooltip' => __('Gallery from Media Folder', RML_TD),
            'mceListBoxDirsTooltip' => __(
                'Note: You can only select galleries. Folders and collections are grayed.',
                RML_TD
            ),
            'mceBodyGallery' => __('Folder', RML_TD),
            'mceBodyLinkTo' => __('Link to'),
            'mceBodyColumns' => __('Columns'),
            'mceBodyRandomOrder' => __('Random Order'),
            'mceBodySize' => __('Size'),
            'mceBodyLinkToValues' => [
                ['value' => 'post', 'text' => __('Attachment File')],
                ['value' => 'file', 'text' => __('Media File')],
                ['value' => 'none', 'text' => __('None')]
            ],
            'mceBodySizeValues' => [
                ['value' => 'thumbnail', 'text' => __('Thumbnail')],
                ['value' => 'medium', 'text' => __('Medium')],
                ['value' => 'large', 'text' => __('Large')],
                ['value' => 'full', 'text' => __('Full Size')]
            ]
        ];
        return $arr;
    }
    /**
     * Add external plugin to MCE.
     *
     * @param array $plugin_array
     * @return array
     */
    function mce_external_plugins($plugin_array) {
        $assets = $this->getCore()->getAssets();
        $dir = $assets->getPublicFolder();
        $plugin_array[self::$TAG] = plugins_url(
            $dir . 'rml_shortcode.' . ($this->isPro() ? 'pro' : 'lite') . '.js',
            RML_FILE
        );
        return $plugin_array;
    }
    /**
     * Add button to MCE.
     *
     * @param string[] $buttons
     * @return string[]
     */
    function mce_buttons($buttons) {
        \array_push($buttons, self::$TAG);
        return $buttons;
    }
    /**
     * Get instance.
     *
     * @return FolderShortcode
     */
    public static function getInstance() {
        return self::$me === null ? (self::$me = new \MatthiasWeb\RealMediaLibrary\view\FolderShortcode()) : self::$me;
    }
}

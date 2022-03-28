<?php

namespace MatthiasWeb\RealMediaLibrary\view;

use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use WP_Query;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Create gutenberg blocks with Server side rendering.
 */
class Gutenberg {
    use UtilsProvider;
    private static $me = null;
    const ID_GALLERY = RML_TD . '/gallery';
    /**
     * Register Gutenberg block.
     */
    private function __construct() {
        register_block_type(self::ID_GALLERY, [
            'render_callback' => [$this, 'renderGallery'],
            'attributes' => [
                'fid' => ['type' => 'number'],
                'align' => ['type' => 'string', 'default' => 'undefined'],
                'columns' => ['type' => 'number', 'default' => 3],
                'imageCrop' => ['type' => 'boolean', 'default' => \true],
                'captions' => ['type' => 'boolean', 'default' => \true],
                'linkTo' => ['type' => 'string', 'default' => 'none'],
                'lastEditReload' => ['type' => 'number', 'default' => 0]
            ]
        ]);
    }
    /**
     * Render gallery in website.
     *
     * @param array $attributes
     * @param string $content
     * @return string
     * @see https://github.com/WordPress/gutenberg/blob/master/packages/block-library/src/gallery/index.js#L196-L222
     */
    public function renderGallery($attributes, $content) {
        if (!isset($attributes['fid']) || empty($attributes['fid'])) {
            return '<span></span>';
        }
        // Fetch images
        $query = new \WP_Query([
            'post_status' => 'inherit',
            'post_type' => 'attachment',
            'rml_folder' => $attributes['fid'],
            'posts_per_page' => -1
        ]);
        $posts = $query->get_posts();
        // Iterate all items
        $html =
            '<ul data-count="' .
            \count($posts) .
            '" class="wp-block-gallery align' .
            $attributes['align'] .
            ' columns-' .
            $attributes['columns'] .
            ' ' .
            ($attributes['imageCrop'] ? 'is-cropped' : '') .
            '">';
        foreach ($posts as $post) {
            if (!wp_attachment_is_image($post)) {
                continue;
            }
            // Collect data
            $href = \false;
            $link = get_attachment_link($post->ID);
            $src = wp_get_attachment_image_src($post->ID, 'full');
            if (!isset($src[0])) {
                continue;
            }
            $src = $src[0];
            $alt = get_post_meta($post->id, '_wp_attachment_image_alt', \true);
            $alt = empty($alt) ? $post->post_title : $alt;
            $caption = $attributes['captions'] ? $post->post_excerpt : '';
            switch ($attributes['linkTo']) {
                case 'media':
                    $href = $src;
                    break;
                case 'attachment':
                    $href = $link;
                    break;
            }
            // Create output
            $img =
                '<img src="' .
                $src .
                '" alt="' .
                $alt .
                '" data-id="' .
                $post->ID .
                '" data-link="' .
                $link .
                '" class="wp-image-' .
                $post->ID .
                '"/>';
            $img = empty($href) ? $img : '<a href="' . $href . '">' . $img . '</a>';
            $html .=
                '<li class="blocks-gallery-item"><figure>' .
                $img .
                (empty($caption) ? '' : '<figcaption>' . $caption . '</figcaption>') .
                '</figure></li>';
        }
        return $html . '</ul>';
    }
    /**
     * Get instance.
     *
     * @return Gutenberg
     */
    public static function getInstance() {
        return self::$me === null ? (self::$me = new \MatthiasWeb\RealMediaLibrary\view\Gutenberg()) : self::$me;
    }
}

<?php

namespace MatthiasWeb\RealMediaLibrary\comp;

use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\view\Options;
use WP_Post;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This class handles the compatibility for general page builders. If a page builder
 * has more compatibility options, please see / create another compatibility class.
 */
class PageBuilders {
    use UtilsProvider;
    private static $me = null;
    /**
     * C'tor.
     */
    private function __construct() {
        // Silence is golden.
    }
    /**
     * Initialize the page builder handlers.
     */
    public function init() {
        $load_frontend = \MatthiasWeb\RealMediaLibrary\view\Options::load_frontend();
        /* if (class_exists('Tatsu_Builder')) {
               // Temporary removed cause Tatsu needs to update to React v16
               $this->oshine_tatsu_builder();
           } */
        if (\defined('ELEMENTOR_VERSION')) {
            $this->elementor();
        }
        if (\class_exists('Cornerstone_Preview_Frame_Loader') && $load_frontend) {
            $this->cornerstone();
        }
        if (\class_exists('Tailor')) {
            $this->tailor();
        }
        if (\defined('TVE_IN_ARCHITECT') || \class_exists('Thrive_Quiz_Builder')) {
            $this->thrive_architect();
        }
        if (\class_exists('FLBuilder')) {
            $this->bbuilder();
        }
        if (
            \class_exists('Fusion_App') &&
            \function_exists('Fusion_App') &&
            \method_exists(Fusion_App(), 'get_builder_status')
        ) {
            $this->fusionBuilderLive();
        }
        if (\class_exists('ET_Builder_Element')) {
            $this->diviBuilder();
        }
    }
    /**
     * Divi Page Builder
     *
     * @see https://www.elegantthemes.com/gallery/divi/
     */
    private function diviBuilder() {
        add_filter('RML/Scripts/Skip', [$this, 'diviBuilder_skip'], 10, 2);
        add_filter('et_fb_bundle_dependencies', [$this, 'et_fb_bundle_dependencies']);
        add_action('et_fb_enqueue_assets', [$this, 'et_fb_enqueue_assets']);
    }
    /**
     * Divi Page Builder.
     *
     * @param array $deps
     * @return array
     */
    public function et_fb_bundle_dependencies($deps) {
        // Remove react and react-dom from boot.js so it is also loaded in window.top.
        if (($key = \array_search('react', $deps, \true)) !== \false) {
            unset($deps[$key]);
        }
        if (($key = \array_search('react-dom', $deps, \true)) !== \false) {
            unset($deps[$key]);
        }
        return $deps;
    }
    /**
     * Divi Page Builder.
     */
    public function et_fb_enqueue_assets() {
        $this->getCore()
            ->getAssets()
            ->enqueue_scripts_and_styles('et_fb_enqueue_assets');
    }
    /**
     * Divi Page Builder.
     *
     * @param boolean $skip
     * @param string $type
     * @return string
     */
    public function diviBuilder_skip($skip, $type) {
        if ($type === 'et_fb_enqueue_assets') {
            return $skip;
        }
        if (et_core_is_fb_enabled() || (isset($_GET['et_pb_preview']) && $_GET['et_pb_preview'] === 'true')) {
            return \true;
        }
        return $skip;
    }
    /**
     * Fusion Builder Live (Avada)
     *
     * @see https://themeforest.net/item/avada-responsive-multipurpose-theme/2833226
     */
    private function fusionBuilderLive() {
        $is_builder = Fusion_App()->get_builder_status();
        if ($is_builder) {
            add_filter('RML/Scripts/Skip', [$this, 'fusionBuilderLive_skip'], 10, 2);
            add_action('wp_enqueue_scripts', [$this, 'fusionBuilderLive_enqueue_scripts'], 100);
        }
    }
    /**
     * Fusion Builder Live (Avada)
     *
     * @param boolean $skip
     * @param string $type
     * @return boolean
     */
    public function fusionBuilderLive_skip($skip, $type) {
        return $type === 'fusion_builder_live' ? $skip : \true;
    }
    /**
     * Fusion Builder Live (Avada)
     *
     * @param string $type
     */
    public function fusionBuilderLive_enqueue_scripts($type) {
        $this->getCore()
            ->getAssets()
            ->enqueue_scripts_and_styles('fusion_builder_live');
    }
    /**
     * Beaver Builder.
     *
     * @see https://www.wpbeaverbuilder.com/
     */
    private function bbuilder() {
        add_action('fl_before_sortable_enqueue', [$this, 'fl_before_sortable_enqueue']);
        add_filter('fl_builder_responsive_ignore', [$this, 'fl_builder_responsive_ignore']);
    }
    /**
     * Beaver Builder.
     */
    public function fl_before_sortable_enqueue() {
        $this->getCore()
            ->getAssets()
            ->admin_enqueue_scripts('fl_before_sortable_enqueue');
        /* class-fl-builder.php#enqueue_ui_styles_scripts: We have a custom version of sortable that fixes a bug. */
        wp_deregister_script('jquery-ui-sortable');
    }
    /**
     * Show media library sidebar also in responsive settings for mobile.
     *
     * @param string[] $ignore
     */
    public function fl_builder_responsive_ignore($ignore) {
        $ignore[] = RML_SLUG_LITE;
        $ignore[] = RML_SLUG_PRO;
        return \array_unique($ignore);
    }
    /**
     * Tailor page builder.
     *
     * @see https://de.wordpress.org/plugins/tailor/
     */
    private function tailor() {
        add_action('tailor_enqueue_sidebar_scripts', [$this->getCore()->getAssets(), 'admin_enqueue_scripts']);
    }
    /**
     * Cornerstone.
     *
     * @see https://codecanyon.net/item/cornerstone-the-wordpress-page-builder/15518868
     */
    private function cornerstone() {
        add_filter('print_head_scripts', [$this, 'cornerstone_print_head_scripts'], 0);
    }
    /**
     * Cornerstone.
     *
     * @param mixed $res
     * @return mixed
     */
    public function cornerstone_print_head_scripts($res) {
        $this->getCore()
            ->getAssets()
            ->admin_enqueue_scripts('cornerstone_print_head_scripts');
        return $res;
    }
    /**
     * Elementor.
     *
     * @see https://elementor.com/
     */
    private function elementor() {
        add_action('elementor/editor/before_enqueue_scripts', [$this->getCore()->getAssets(), 'admin_enqueue_scripts']);
    }
    /**
     * OSHINE TATSU PAGE BUILDER.
     *
     * @see https://themeforest.net/item/oshine-creative-multipurpose-wordpress-theme/9545812
     */
    private function oshine_tatsu_builder() {
        add_action('tatsu_builder_head', [$this->getCore()->getAssets(), 'admin_enqueue_scripts']);
    }
    /**
     * Thrive Architect
     *
     * @see https://thrivethemes.com
     */
    private function thrive_architect() {
        add_action('tcb_main_frame_enqueue', [$this->getCore()->getAssets(), 'admin_enqueue_scripts']);
        add_filter('tge_filter_edit_post', [$this, 'tge_filter_edit_post']);
    }
    /**
     * The Thrive Quiz Builder does not allow to enqueue custom scripts so I use the
     * tge_filter_edit_post filter as workaround.
     *
     * @param WP_Post $post
     * @return WP_Post
     */
    public function tge_filter_edit_post($post) {
        $this->getCore()
            ->getAssets()
            ->admin_enqueue_scripts('tge_filter_edit_post');
        return $post;
    }
    /**
     * Get instance.
     *
     * @return PageBuilders
     */
    public static function getInstance() {
        return self::$me === null ? (self::$me = new \MatthiasWeb\RealMediaLibrary\comp\PageBuilders()) : self::$me;
    }
}

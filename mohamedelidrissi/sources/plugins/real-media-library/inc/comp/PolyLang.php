<?php

namespace MatthiasWeb\RealMediaLibrary\comp;

use MatthiasWeb\RealMediaLibrary\api\IFolder;
use MatthiasWeb\RealMediaLibrary\attachment\CountCache;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This class handles the compatibility for Poly Lang plugin.
 */
class PolyLang {
    use UtilsProvider;
    private static $me = null;
    private $active = \false;
    /**
     * Avoid duplicate call of move action.
     */
    private $previousIds = null;
    /**
     * Avoid duplicate call of move action.
     */
    private $previousFolderId = null;
    /**
     * C'tor.
     */
    private function __construct() {
        // Silence is golden.
    }
    /**
     * Initialize actions.
     */
    public function init() {
        $this->active = \function_exists('MatthiasWeb\\RealMediaLibrary\\Vendor\\pll_get_post_translations');
        if ($this->active) {
            add_action('pll_translate_media', [$this, 'pll_translate_media'], 10, 3);
            add_action('RML/Options/Register', [$this, 'options_register']);
            add_action('RML/Item/MoveFinished', [$this, 'item_move_finished'], 10, 4);
        }
    }
    /**
     * Register option for PolyLang.
     */
    public function options_register() {
        register_setting('media', 'rml_polylang_move', 'esc_attr');
        add_settings_field(
            'rml_polylang_move',
            '<label for="rml_polylang_move">' . __('PolyLang: Automatically move translations', RML_TD) . '</label>',
            [$this, 'html_options_move'],
            'media',
            'rml_options_general'
        );
    }
    /**
     * Output button to move translation.
     */
    public function html_options_move() {
        $value = get_option('rml_polylang_move', '1');
        echo '<input type="checkbox" id="rml_polylang_move"
                name="rml_polylang_move" value="1" ' .
            checked(1, $value, \false) .
            ' />
                <label>' .
            __('If you move a file, the corresponding translated file will also be moved.', RML_TD) .
            '</label>';
    }
    /**
     * A file is moved (not copied) and then move also all the translations.
     *
     * @param int $folderId
     * @param int[] $ids
     * @param IFolder $folder
     * @param boolean $isShortcut
     */
    public function item_move_finished($folderId, $ids, $folder, $isShortcut) {
        if (
            !$isShortcut &&
            get_option('rml_polylang_move', '1') === '1' &&
            \json_encode($ids) !== \json_encode($this->previousIds) &&
            $folderId !== $this->previousFolderId
        ) {
            $moveToFolder = [];
            $this->previousFolderId = $folderId;
            $this->previousIds = $ids;
            // Iterate all moved ids
            foreach ($ids as $post_id) {
                $translations = pll_get_post_translations($post_id);
                // Iterate all translation ids
                foreach ($translations as $tr_id) {
                    if (!\in_array($tr_id, $ids, \true)) {
                        $moveToFolder[] = $tr_id;
                    }
                }
            }
            if (\count($moveToFolder) > 0) {
                $this->debug(
                    "Polylang: While moving to folder {$folderId} there are some translations which also must be moved: " .
                        \json_encode($moveToFolder),
                    __METHOD__
                );
                wp_rml_move($folderId, $moveToFolder);
            }
        }
    }
    /**
     * New translation created => synchronize with original post.
     * Then reset the count cache for the unorganized folder.
     *
     * @param int $post_id
     * @param int $tr_id
     * @param string $lang_slug
     */
    public function pll_translate_media($post_id, $tr_id, $lang_slug) {
        $folderId = wp_attachment_folder($post_id);
        _wp_rml_synchronize_attachment($tr_id, $folderId);
        $this->debug(
            'Polylang: Move translation id ' .
                $tr_id .
                ' to the original file (' .
                $post_id .
                ') folder id ' .
                $folderId,
            __METHOD__
        );
        \MatthiasWeb\RealMediaLibrary\attachment\CountCache::getInstance()
            ->addNewAttachment($tr_id)
            ->resetCountCacheOnWpDie(_wp_rml_root());
    }
    /**
     * Get instance.
     *
     * @return PolyLang
     */
    public static function getInstance() {
        return self::$me === null ? (self::$me = new \MatthiasWeb\RealMediaLibrary\comp\PolyLang()) : self::$me;
    }
}

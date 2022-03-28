<?php

namespace MatthiasWeb\RealMediaLibrary\comp;

use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\lite\comp\ExImport as CompExImport;
use MatthiasWeb\RealMediaLibrary\overrides\interfce\comp\IOverrideExImport;
use MatthiasWeb\RealMediaLibrary\Util;
use MatthiasWeb\RealMediaLibrary\view\Options;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\ExpireOption;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Import and export functionality.
 */
class ExImport implements \MatthiasWeb\RealMediaLibrary\overrides\interfce\comp\IOverrideExImport {
    use UtilsProvider;
    use CompExImport;
    private static $me = null;
    private $idOffset = null;
    const IMPORT_TAX_MATRIX = [
        'nt_wmc_folder' => 'FileBird',
        'filebase_folder' => 'FileBase',
        'media_folder' => 'Folders',
        'attachment_category' => 'Media Library Assistant',
        'media_category' => 'Enhanced Media Library',
        'mlo-category' => 'Media Library Organizer',
        'mediamatic_wpfolder' => 'Mediamatic Lite',
        'wpmf-category' => 'Media Library Folders',
        'rl_media_folder' => 'Responsive Lightbox & Gallery'
    ];
    const IMPORT_TAX_IGNORE = ['language'];
    /**
     * "Media Library Folders" uses `wp_posts` to store available folders.
     *
     * @see https://de.wordpress.org/plugins/media-library-plus/
     */
    const MLF_POST_TYPE = 'mgmlp_media_folder';
    /**
     * "Filebird" uses also an own database table system for media relationship.
     */
    const FILE_BIRD_TABLE_NAME = 'fbv';
    const FILE_BIRD_TABLE_NAME_POSTS = 'fbv_attachment_folder';
    /**
     * Column names for import and export.
     */
    private $columns = [];
    /**
     * C'tor.
     */
    private function __construct() {
        $this->columns = \explode(',', 'name,ord,type,restrictions,contentCustomOrder,importId');
    }
    /**
     * Register options in media settings.
     */
    public function options_register() {
        add_settings_section(
            'rml_options_import',
            __('RealMediaLibrary:Import / Export'),
            [\MatthiasWeb\RealMediaLibrary\view\Options::getInstance(), 'empty_callback'],
            'media'
        );
        add_settings_field(
            'rml_button_import_cats',
            '<label for="rml_button_import_cats">' . __('Import from other plugins', RML_TD) . '</label>',
            [$this, 'html_rml_button_import_cats'],
            'media',
            'rml_options_import'
        );
        add_settings_field(
            'rml_button_export',
            '<label for="rml_button_export">' . __('Export / Import Real Media Library folders', RML_TD) . '</label>',
            [$this, 'html_rml_button_export'],
            'media',
            'rml_options_import'
        );
    }
    /**
     * Output export button in options.
     */
    public function html_rml_button_export() {
        $disabled = $this->isPro() ? '' : 'disabled="disabled"';
        echo '<a class="rml-rest-button button button-primary" data-url="export" data-method="GET">' .
            __('Export', RML_TD) .
            '</a>
        <a class="rml-rest-button button" data-url="import" data-method="POST" ' .
            $disabled .
            '>' .
            __('Import', RML_TD) .
            '</a>
        <p class="description" style="margin-bottom:10px">' .
            __(
                'All available folders will be exported. The current structure is not lost during import - but check that there are no duplicate names in the import data, as these are not checked.',
                RML_TD
            ) .
            '</p>
        <div id="rml_export_data" style="float:left;margin-right: 10px;"><div>' .
            __('Exported data:', RML_TD) .
            '</div><textarea></textarea></div>
        <div id="rml_import_data" style="float:left;"><div>' .
            __('Import data:', RML_TD) .
            '</div><textarea ' .
            $disabled .
            '></textarea></div><div class="clear"></div>';
        if (!$this->isPro()) {
            echo '<p class="description"><strong>' .
                __('Importing data is only available in PRO version.', RML_TD) .
                ' <a href="' .
                (RML_PRO_VERSION . '&feature=import-export') .
                '" target="_blank">' .
                __('Learn more about PRO', RML_TD) .
                '</a></strong></p>';
        }
    }
    /**
     * Output import button in options.
     */
    public function html_rml_button_import_cats() {
        $hasMediaLibraryFolders = $this->hasMediaLibraryFolders();
        $hasFileBird = $this->hasFileBird();
        if (\count($this->getHierarchicalTaxos()) || $hasMediaLibraryFolders || $hasFileBird) {
            $disabled = $this->isPro() ? '' : 'disabled="disabled"';
            foreach ($this->getHierarchicalTaxos() as $tax) {
                $name = isset(self::IMPORT_TAX_MATRIX[$tax])
                    ? self::IMPORT_TAX_MATRIX[$tax]
                    : '<code>' . $tax . '</code>';
                echo '<a class="rml-rest-button button" data-url="import/taxonomy" data-method="POST" data-taxonomy="' .
                    esc_attr($tax) .
                    '" ' .
                    $disabled .
                    '>' .
                    __('Import', RML_TD) .
                    ' (' .
                    $name .
                    ')</a>&nbsp;';
            }
            // Media Library Folders plugin
            if ($hasMediaLibraryFolders) {
                echo '<a class="rml-rest-button button" data-url="import/mlf" data-method="POST" ' .
                    $disabled .
                    '>' .
                    __('Import', RML_TD) .
                    ' (Media Library Folders)</a>&nbsp;';
            }
            // Filebird plugin
            if ($hasFileBird) {
                echo '<a class="rml-rest-button button" data-url="import/filebird" data-method="POST" ' .
                    $disabled .
                    '>' .
                    __('Import', RML_TD) .
                    ' (FileBird)</a>&nbsp;';
            }
            echo '<p class="description">' . __('Imports categories and post relations.', RML_TD) . '</p>';
        } else {
            echo '<p>' . __('Nothing to import.', RML_TD) . '</p>';
        }
        if (!$this->isPro()) {
            echo '<p class="description"><strong>' .
                __('Importing categories from another plugin is only available in PRO version.', RML_TD) .
                ' <a href="' .
                (RML_PRO_VERSION . '&feature=import-export') .
                '" target="_blank">' .
                __('Learn more about PRO', RML_TD) .
                '</a></strong></p>';
        }
    }
    /**
     * Get the folder tree for import process.
     *
     * @return array
     */
    public function getFolders() {
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        // Folders
        $table_name = $this->getTableName();
        $folders = $wpdb->get_results(
            'SELECT id,parent,' . \implode(',', $this->columns) . " FROM {$table_name}",
            ARRAY_A
        );
        // Metas
        $table_name = $this->getTableName('meta');
        $metas = \MatthiasWeb\RealMediaLibrary\Util::getInstance()->group_by(
            $wpdb->get_results("SELECT * FROM {$table_name}", ARRAY_A),
            'realmedialibrary_id'
        );
        // phpcs:enable WordPress.DB.PreparedSQL
        // Group metas
        $grouped = [];
        foreach ($folders as $folder) {
            // Assign metas
            if (isset($metas[$folder['id']])) {
                $folder['__metas'] = [];
                foreach ($metas[$folder['id']] as $meta) {
                    unset($meta['meta_id']);
                    unset($meta['realmedialibrary_id']);
                    $folder['__metas'][] = $meta;
                }
            }
            $grouped[] = $folder;
        }
        // Cast
        foreach ($grouped as &$row) {
            $row['parent'] = \intval($row['parent']);
            $row['id'] = \intval($row['id']);
        }
        // Create tree
        $tree = \MatthiasWeb\RealMediaLibrary\Util::getInstance()->buildTree(
            $grouped,
            -1,
            'parent',
            'id',
            '__children'
        );
        return \MatthiasWeb\RealMediaLibrary\Util::getInstance()->clearTree($tree, ['id', 'parent'], '__children');
    }
    /**
     * Get the hierarchical taxonomies for the media taxonomy. It also
     * returns taxonomies which are no longer registered
     *
     * @return string[]
     */
    public function getHierarchicalTaxos() {
        global $wpdb;
        // Fetch the taxonomies which are able to filter
        $taxonomy_objects = get_object_taxonomies('attachment', 'objects');
        $taxos = [];
        foreach ($taxonomy_objects as $key => $value) {
            if ($value->hierarchical === \true) {
                $taxos[] = $key;
            }
        }
        // Read non-active ones
        // phpcs:disable WordPress.DB.PreparedSQL
        $taxonomy_sql = $wpdb->get_col(
            "SELECT DISTINCT(wptt.taxonomy) FROM {$wpdb->term_taxonomy} AS wptt\n        INNER JOIN {$wpdb->term_relationships} AS wptr ON wptt.term_taxonomy_id = wptr.term_taxonomy_id\n        INNER JOIN {$wpdb->posts} AS wpp ON wptr.object_id = wpp.ID\n        WHERE wpp.post_type = 'attachment'"
        );
        // phpcs:enable WordPress.DB.PreparedSQL
        return \array_diff(\array_unique(\array_merge($taxonomy_sql, $taxos)), self::IMPORT_TAX_IGNORE);
    }
    /**
     * Checks if the import notice in folder tree sidebar is dismissed.
     *
     * @param boolean $set
     * @return boolean
     */
    public function isImportTaxNoticeDismissed($set = null) {
        $value = '1';
        $expireOption = new \MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\ExpireOption(
            RML_OPT_PREFIX . '_importTaxNotice',
            \false,
            365 * \constant('DAY_IN_SECONDS')
        );
        $expireOption->enableTransientMigration(
            \MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\ExpireOption::TRANSIENT_MIGRATION_SITE_WIDE
        );
        if ($set !== null) {
            $expireOption->set($set ? $value : 0);
        }
        return $expireOption->get() === $value;
    }
    /**
     * Checks if "Media Library Folders" was used previously.
     *
     * @see https://de.wordpress.org/plugins/media-library-plus/
     */
    public function hasMediaLibraryFolders() {
        global $wpdb;
        return \intval(
            $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type=%s", self::MLF_POST_TYPE)
            )
        ) > 0;
    }
    /**
     * Checks if "Filebird" was used previously.
     */
    public function hasFileBird() {
        global $wpdb;
        // Check if table exists and create it
        // phpcs:disable WordPress.DB.PreparedSQL
        $table_name = $wpdb->prefix . self::FILE_BIRD_TABLE_NAME_POSTS;
        $exists = \strcasecmp($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'"), $table_name);
        // phpcs:enable WordPress.DB.PreparedSQL
        return 0 === $exists;
    }
    /**
     * Get instance.
     *
     * @return ExImport
     */
    public static function getInstance() {
        return self::$me === null ? (self::$me = new \MatthiasWeb\RealMediaLibrary\comp\ExImport()) : self::$me;
    }
}

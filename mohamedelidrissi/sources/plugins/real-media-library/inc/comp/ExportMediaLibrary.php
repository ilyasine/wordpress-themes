<?php

namespace MatthiasWeb\RealMediaLibrary\comp;

use MassEdge\WordPress\Plugin\ExportMediaLibrary\API as ExportMediaLibraryAPI;
use MatthiasWeb\RealMediaLibrary\api\IFolder;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\rest\Service;
use WP_Error;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Resolve filenames by a set of attachment ids.
 */
class HierarchicalFilenameResolver {
    private $folders = null;
    private $rootFolder = null;
    /**
     * C'tor.
     *
     * @param IFolder $rootFolder
     */
    public function __construct($rootFolder) {
        $this->rootFolder = $rootFolder;
    }
    /**
     * Exclude the main folder from the attachment path because the exported
     * zip already starts with the exported name as first-level folder.
     *
     * @param IFolder $folder
     * @return boolean
     */
    public function excludeRoot($folder) {
        return $folder->getId() !== $this->rootFolder->getId();
    }
    /**
     * Resolve the filename to RML specific folder.
     *
     * @param string $file
     * @param array $attachment
     * @param int[] $attachmentIds
     * @return string
     */
    public function resolve($file, $attachment, $attachmentIds) {
        $attachmentPath = $file['path'];
        $attachmentId = $attachment['attachment_id'];
        // Read folders of all exported attachment files (performance should be good because it's only performed once)
        if ($this->folders === null) {
            // Get attachment -> folder mapping with a simple SQL
            global $wpdb;
            $attachments_in = \implode(',', $attachmentIds);
            // We do not need to escape because it is the result of WP_Query
            $table_name = $wpdb->prefix . 'realmedialibrary_posts';
            // phpcs:disable WordPress.DB.PreparedSQL
            $folders = $wpdb->get_results(
                "SELECT rmlposts.attachment, rmlposts.fid FROM {$table_name} AS rmlposts WHERE rmlposts.attachment IN ({$attachments_in})"
            );
            // phpcs:enable WordPress.DB.PreparedSQL
            // Only get the pathes of the folders
            $this->folders = [];
            foreach ($folders as $row) {
                $id = (int) $row->attachment;
                $this->folders[$id] = \trim(wp_rml_get_object_by_id($row->fid)->getPath('/'), '/\\');
            }
        }
        $path = $this->folders[$attachmentId];
        $basename = \basename($file['name']);
        $path = empty($path) ? $basename : path_join($path, $basename);
        $file['name'] = $path;
        return $file;
    }
}
/**
 * This class handles the compatibility for this plugin:
 * https://wordpress.org/plugins/export-media-library/
 *
 * @see https://github.com/massedge/wordpress-plugin-export-media-library/issues/9
 * @since 4.5.0
 */
class ExportMediaLibrary {
    use UtilsProvider;
    const AJAX_ACTION = 'massedge-wp-plugin-eml-ape-rml-download';
    /**
     * C'tor.
     */
    public function __construct() {
        add_action('rest_api_init', [$this, 'rest_api_init']);
        add_filter('RML/Localize', [$this, 'localize']);
    }
    /**
     * Register REST API endpoints.
     */
    public function rest_api_init() {
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/massedge/export', [
            'methods' => 'GET',
            'callback' => [$this, 'export'],
            'permission_callback' => [$this, 'permission_callback'],
            'args' => [
                'type' => ['required' => \true, 'type' => 'string'],
                'folder' => ['required' => \true, 'type' => 'integer']
            ]
        ]);
    }
    /**
     * Check if user is allowed to call this service requests.
     */
    public function permission_callback() {
        $permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit();
        return $permit === null ? \true : $permit;
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {get} /realmedialibrary/v1/massedge/export Export folder as zip
     * @apiHeader {string} X-WP-Nonce
     * @apiName GetExportFolder
     * @apiParam {string='wosFlat','wosHierarchical','wsFlat','wsHierarchicalRML','wsHierarchical'} type The export type
     * @apiParam {int} folder The folder id
     * @apiGroup ThirdParty
     * @apiVersion 1.0.0
     * @apiPermission upload_media
     */
    public function export($request) {
        global $wpdb;
        $type = $request->get_param('type');
        $folder = wp_rml_get_object_by_id($request->get_param('folder'));
        if (!is_rml_folder($folder)) {
            return new \WP_Error('rest_rml_massedge_export', __('No valid folder.'), ['status' => 500]);
        }
        $filename = sanitize_file_name($folder->getName());
        $id = $folder->getId();
        // Get options
        $options = \MassEdge\WordPress\Plugin\ExportMediaLibrary\API::defaultExportOptions();
        $options = \array_merge($options, ['filename' => $filename . '.zip']);
        $options['query_args'] = \array_merge($options['query_args'], ['rml_folder' => $id]);
        // Prepare options for each type
        switch ($type) {
            // Without subfolders
            case 'wosFlat':
                $options = \array_merge($options, [
                    'folder_structure' => \MassEdge\WordPress\Plugin\ExportMediaLibrary\API::FOLDER_STRUCTURE_FLAT
                ]);
                break;
            case 'wosHierarchical':
                $options = \array_merge($options, [
                    'folder_structure' => \MassEdge\WordPress\Plugin\ExportMediaLibrary\API::FOLDER_STRUCTURE_NESTED
                ]);
                break;
            // With subfolders
            case 'wsFlat':
                $options = \array_merge($options, [
                    'folder_structure' => \MassEdge\WordPress\Plugin\ExportMediaLibrary\API::FOLDER_STRUCTURE_FLAT
                ]);
                $options['query_args'] = \array_merge($options['query_args'], ['rml_include_children' => \true]);
                break;
            case 'wsHierarchicalRML':
                $resolver = new \MatthiasWeb\RealMediaLibrary\comp\HierarchicalFilenameResolver($folder);
                $options['query_args'] = \array_merge($options['query_args'], ['rml_include_children' => \true]);
                // Double read the attachment ids, see GitHub issue
                $query = new \WP_Query();
                $attachmentIds = $query->query($options['query_args']);
                $options = \array_merge($options, [
                    'folder_structure' => \MassEdge\WordPress\Plugin\ExportMediaLibrary\API::FOLDER_STRUCTURE_NESTED,
                    'add_attachment_callback' => function ($file, $attachment) use ($resolver, $attachmentIds) {
                        return $resolver->resolve($file, $attachment, $attachmentIds);
                    }
                ]);
                break;
            case 'wsHierarchical':
                $options = \array_merge($options, [
                    'folder_structure' => \MassEdge\WordPress\Plugin\ExportMediaLibrary\API::FOLDER_STRUCTURE_NESTED
                ]);
                $options['query_args'] = \array_merge($options['query_args'], ['rml_include_children' => \true]);
                break;
            default:
                return new \WP_Error('rest_rml_massedge_export', __('No valid type.'), ['status' => 500]);
        }
        // Export the file
        \MassEdge\WordPress\Plugin\ExportMediaLibrary\API::export($options);
        $wpdb->close();
        exit();
    }
    /**
     * Add a localized nonce to the rmlOpts variable for AJAX interaction (admin-ajax.php).
     *
     * @param array $arr
     * @return array
     */
    public function localize($arr) {
        return \array_merge($arr, ['massedge_wp_export' => \true]);
    }
}

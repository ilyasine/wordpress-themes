<?php

namespace MatthiasWeb\RealMediaLibrary\rest;

use MatthiasWeb\RealMediaLibrary\attachment\CountCache;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\comp\ExImport;
use MatthiasWeb\RealMediaLibrary\rest\Service;
use MatthiasWeb\RealMediaLibrary\Util;
use WP_REST_Response;
use MatthiasWeb\RealMediaLibrary\lite\order\Sortable;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Enables the /reset, /import and /export REST for admins (manage_options).
 */
class Reset {
    use UtilsProvider;
    /**
     * Register endpoints.
     */
    public function rest_api_init() {
        if ($this->isPro()) {
            register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/reset/order', [
                'methods' => 'DELETE',
                'callback' => [$this, 'resetOrder'],
                'permission_callback' => [$this, 'permission_callback']
            ]);
        }
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/reset/count', [
            'methods' => 'DELETE',
            'callback' => [$this, 'resetCount'],
            'permission_callback' => [$this, 'permission_callback']
        ]);
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/reset/slugs', [
            'methods' => 'DELETE',
            'callback' => [$this, 'resetSlugs'],
            'permission_callback' => [$this, 'permission_callback']
        ]);
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/reset/relations', [
            'methods' => 'DELETE',
            'callback' => [$this, 'resetRelations'],
            'permission_callback' => [$this, 'permission_callback']
        ]);
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/reset/folders', [
            'methods' => 'DELETE',
            'callback' => [$this, 'resetFolders'],
            'permission_callback' => [$this, 'permission_callback']
        ]);
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/export', [
            'methods' => 'GET',
            'callback' => [$this, 'export'],
            'permission_callback' => [$this, 'permission_callback']
        ]);
        if ($this->isPro()) {
            register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/import', [
                'methods' => 'POST',
                'callback' => [$this, 'import'],
                'permission_callback' => [$this, 'permission_callback']
            ]);
            register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/import/taxonomy', [
                'methods' => 'POST',
                'callback' => [$this, 'importTaxonomy'],
                'permission_callback' => [$this, 'permission_callback']
            ]);
            register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/import/mlf', [
                'methods' => 'POST',
                'callback' => [$this, 'importMlf'],
                'permission_callback' => [$this, 'permission_callback']
            ]);
            register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/import/filebird', [
                'methods' => 'POST',
                'callback' => [$this, 'importFileBird'],
                'permission_callback' => [$this, 'permission_callback']
            ]);
        }
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/reset/debug', [
            'methods' => 'DELETE',
            'callback' => [$this, 'resetDebug'],
            'permission_callback' => [$this, 'permission_callback']
        ]);
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/notice/license', [
            'methods' => 'DELETE',
            'callback' => [$this, 'routeNoticeDismissLicense'],
            'permission_callback' => [$this, 'permission_callback_activate_plugins']
        ]);
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/notice/lite', [
            'methods' => 'DELETE',
            'callback' => [$this, 'routeNoticeDismissLite'],
            'permission_callback' => [$this, 'permission_callback_activate_plugins']
        ]);
        register_rest_route(\MatthiasWeb\RealMediaLibrary\rest\Service::LEGACY_NAMESPACE, '/notice/import', [
            'methods' => 'DELETE',
            'callback' => [$this, 'routeNoticeDismissImportTax'],
            'permission_callback' => [$this, 'permission_callback_activate_plugins']
        ]);
    }
    /**
     * Check if user is allowed to call this service requests.
     */
    public function permission_callback() {
        $permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('manage_options');
        return $permit === null ? \true : $permit;
    }
    /**
     * Check if user is allowed to call this service requests.
     */
    public function permission_callback_activate_plugins() {
        $permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('activate_plugins');
        return $permit === null ? \true : $permit;
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {delete} /realmedialibrary/v1/reset/order Resets the order of all available folders
     * @apiName ResetOrder
     * @apiGroup Reset
     * @apiVersion 1.0.0
     * @apiPermission manage_options
     * @apiPermission Pro only
     */
    public function resetOrder($request) {
        if (($permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('manage_options')) !== null) {
            return $permit;
        }
        \MatthiasWeb\RealMediaLibrary\lite\order\Sortable::delete_all_order();
        return new \WP_REST_Response(['success' => \true]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {delete} /realmedialibrary/v1/reset/count Resets the count of all available folders
     * @apiName ResetCount
     * @apiGroup Reset
     * @apiVersion 1.0.0
     * @apiPermission manage_options
     */
    public function resetCount($request) {
        if (($permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('manage_options')) !== null) {
            return $permit;
        }
        \MatthiasWeb\RealMediaLibrary\attachment\CountCache::getInstance()->resetCountCache();
        return new \WP_REST_Response(['success' => \true]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {delete} /realmedialibrary/v1/reset/slugs Resets the slugs of all available folders
     * @apiName ResetSlugs
     * @apiGroup Reset
     * @apiVersion 1.0.0
     * @apiPermission manage_options
     */
    public function resetSlugs($request) {
        if (($permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('manage_options')) !== null) {
            return $permit;
        }
        \MatthiasWeb\RealMediaLibrary\Util::getInstance()->resetAllSlugsAndAbsolutePathes('html_entity_decode');
        return new \WP_REST_Response(['success' => \true]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {delete} /realmedialibrary/v1/reset/relations Resets the relations of all available folders to attachments
     * @apiName ResetRelations
     * @apiGroup Reset
     * @apiVersion 1.0.0
     * @apiPermission manage_options
     */
    public function resetRelations($request) {
        if (($permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('manage_options')) !== null) {
            return $permit;
        }
        global $wpdb;
        $table_posts = $this->getTableName('posts');
        // phpcs:disable WordPress.DB.PreparedSQL
        $wpdb->query("DELETE FROM {$table_posts}");
        // phpcs:enable WordPress.DB.PreparedSQL
        \MatthiasWeb\RealMediaLibrary\attachment\CountCache::getInstance()->resetCountCache();
        /**
         * This action is fired after folder / attachment relations reset.
         *
         * @hook RML/Reset/Relations
         * @since 4.0.7
         */
        do_action('RML/Reset/Relations');
        return new \WP_REST_Response(['success' => \true]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {delete} /realmedialibrary/v1/reset/folders Deletes all available folders with relations
     * @apiName ResetFolders
     * @apiGroup Reset
     * @apiVersion 1.0.0
     * @apiPermission manage_options
     */
    public function resetFolders($request) {
        if (($permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('manage_options')) !== null) {
            return $permit;
        }
        global $wpdb;
        // phpcs:disable WordPress.DB.PreparedSQL
        $table_posts = $this->getTableName('posts');
        $wpdb->query("DELETE FROM {$table_posts}");
        $table_name = $this->getTableName();
        $wpdb->query("DELETE FROM {$table_name}");
        $table_meta = $this->getTableName('meta');
        $wpdb->query("DELETE FROM {$table_meta}");
        // phpcs:enable WordPress.DB.PreparedSQL
        \MatthiasWeb\RealMediaLibrary\attachment\CountCache::getInstance()->resetCountCache();
        /**
         * This action is fired after whole reset.
         *
         * @hook RML/Reset
         * @since 4.0.7
         */
        do_action('RML/Reset');
        return new \WP_REST_Response(['success' => \true]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {get} /realmedialibrary/v1/export Get exported folders as JSON string
     * @apiName Export
     * @apiGroup Reset
     * @apiVersion 1.0.0
     * @apiPermission manage_options
     */
    public function export($request) {
        if (($permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('manage_options')) !== null) {
            return $permit;
        }
        return new \WP_REST_Response(
            \json_encode(\MatthiasWeb\RealMediaLibrary\comp\ExImport::getInstance()->getFolders())
        );
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {post} /realmedialibrary/v1/import Set exported folders as RML folders
     * @apiParam {string} import The JSON import string
     * @apiName Import
     * @apiGroup Reset
     * @apiVersion 1.0.0
     * @apiPermission manage_options
     * @apiPermission Pro only
     */
    public function import($request) {
        if (($permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('manage_options')) !== null) {
            return $permit;
        }
        // TODO: we could remove `urldecode` as this is no longer needed
        $import = \urldecode($request->get_param('import'));
        $import = \json_decode($import, \true);
        \MatthiasWeb\RealMediaLibrary\comp\ExImport::getInstance()->import($import);
        return new \WP_REST_Response(['success' => \true]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {post} /realmedialibrary/v1/import/taxonomy Import a taxonomy to RML folders with relations to files
     * @apiParam {string} taxonomy The taxonomy name
     * @apiName ImportTaxonomy
     * @apiGroup Reset
     * @apiVersion 1.0.0
     * @apiPermission manage_options
     * @apiPermission Pro only
     */
    public function importTaxonomy($request) {
        if (($permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('manage_options')) !== null) {
            return $permit;
        }
        $taxonomy = $request->get_param('taxonomy');
        \MatthiasWeb\RealMediaLibrary\comp\ExImport::getInstance()->importTaxonomy($taxonomy);
        return new \WP_REST_Response(['success' => \true]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     * @since 4.8.0
     * @api {post} /realmedialibrary/v1/import/mlf Import "Media Library Folders" to RML folders with relations to files
     * @apiName ImportMlf
     * @apiGroup Reset
     * @apiVersion 1.0.0
     * @apiPermission manage_options
     * @apiPermission Pro only
     */
    public function importMlf($request) {
        if (($permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('manage_options')) !== null) {
            return $permit;
        }
        \MatthiasWeb\RealMediaLibrary\comp\ExImport::getInstance()->importMlf();
        return new \WP_REST_Response(['success' => \true]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     * @since 4.8.0
     * @api {post} /realmedialibrary/v1/import/file Import "Filebird" to RML folders with relations to files
     * @apiName ImportMlf
     * @apiGroup Reset
     * @apiVersion 1.0.0
     * @apiPermission manage_options
     * @apiPermission Pro only
     */
    public function importFileBird($request) {
        if (($permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('manage_options')) !== null) {
            return $permit;
        }
        \MatthiasWeb\RealMediaLibrary\comp\ExImport::getInstance()->importFileBird();
        return new \WP_REST_Response(['success' => \true]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {delete} /realmedialibrary/v1/notice/license Dismiss the license notice for a given time (transient)
     * @apiName DismissLicenseNotice
     * @apiGroup Plugin
     * @apiVersion 4.1.0
     * @since 4.1.0
     * @apiPermission activate_plugins
     */
    public function routeNoticeDismissLicense() {
        if (($permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('activate_plugins')) !== null) {
            return $permit;
        }
        $this->getCore()->isLicenseNoticeDismissed(\true);
        return new \WP_REST_Response(['success' => \true]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {delete} /realmedialibrary/v1/notice/lite Dismiss the lite notice for a given time (transient)
     * @apiName DismissLiteNotice
     * @apiGroup Plugin
     * @apiVersion 4.6.0
     * @since 4.6.0
     * @apiPermission activate_plugins
     */
    public function routeNoticeDismissLite() {
        if (($permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('activate_plugins')) !== null) {
            return $permit;
        }
        $this->getCore()->isLiteNoticeDismissed(\true);
        return new \WP_REST_Response(['success' => \true]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {delete} /realmedialibrary/v1/notice/import Dismiss the import tax notice for a given time (transient)
     * @apiName DismissImportTaxNotice
     * @apiGroup Plugin
     * @apiVersion 4.6.2
     * @since 4.6.2
     * @apiPermission activate_plugins
     */
    public function routeNoticeDismissImportTax() {
        if (($permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('activate_plugins')) !== null) {
            return $permit;
        }
        \MatthiasWeb\RealMediaLibrary\comp\ExImport::getInstance()->isImportTaxNoticeDismissed(\true);
        return new \WP_REST_Response(['success' => \true]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {delete} /realmedialibrary/v1/reset/debug Reset the database log
     * @apiName ResetDebug
     * @apiGroup Reset
     * @apiVersion 1.0.0
     * @apiPermission manage_options
     */
    public function resetDebug($request) {
        if (($permit = \MatthiasWeb\RealMediaLibrary\rest\Service::permit('manage_options')) !== null) {
            return $permit;
        }
        global $wpdb;
        $tablename = $this->getTableName('debug');
        // phpcs:disable WordPress.DB.PreparedSQL
        $wpdb->query("DELETE FROM {$tablename}", ARRAY_A);
        // phpcs:enable WordPress.DB.PreparedSQL
        return new \WP_REST_Response(['success' => \true]);
    }
}

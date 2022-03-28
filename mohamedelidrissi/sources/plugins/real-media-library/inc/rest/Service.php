<?php

namespace MatthiasWeb\RealMediaLibrary\rest;

use MatthiasWeb\RealMediaLibrary\attachment\Structure;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\lite\rest\Service as LiteService;
use MatthiasWeb\RealMediaLibrary\metadata\Meta;
use MatthiasWeb\RealMediaLibrary\overrides\interfce\rest\IOverrideService;
use WP_Error;
use WP_REST_Response;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Create a REST Service.
 */
class Service implements \MatthiasWeb\RealMediaLibrary\overrides\interfce\rest\IOverrideService {
    use UtilsProvider;
    use LiteService;
    private static $responseModifier = [];
    /**
     * Legacy namespace while switched from 4.5.4 to 4.6.0 in a new boilerplate.
     */
    const LEGACY_NAMESPACE = 'realmedialibrary/v1';
    /**
     * Register endpoints.
     */
    public function rest_api_init() {
        register_rest_route(self::LEGACY_NAMESPACE, '/tree', [
            'methods' => 'GET',
            'callback' => [$this, 'routeTree'],
            'permission_callback' => [$this, 'permission_callback']
        ]);
        register_rest_route(self::LEGACY_NAMESPACE, '/tree/dropdown', [
            'methods' => 'GET',
            'callback' => [$this, 'routeTreeDropdown'],
            'permission_callback' => [$this, 'permission_callback']
        ]);
        register_rest_route(self::LEGACY_NAMESPACE, '/hierarchy/(?P<id>\\d+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'routeHierarchy'],
            'permission_callback' => [$this, 'permission_callback']
        ]);
        register_rest_route(self::LEGACY_NAMESPACE, '/usersettings', [
            'methods' => 'GET',
            'callback' => [$this, 'getUserSettingsHTML'],
            'permission_callback' => [$this, 'permission_callback']
        ]);
        register_rest_route(self::LEGACY_NAMESPACE, '/usersettings', [
            'methods' => 'PUT',
            'callback' => [$this, 'updateUserSettings'],
            'permission_callback' => [$this, 'permission_callback']
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
     * @api {get} /realmedialibrary/v1/tree Get the full categories tree
     * @apiParam {string} [currentUrl] The current url to detect the active item
     * @apiName GetTree
     * @apiGroup Tree
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     */
    public function routeTree($request) {
        $currentUrl = $request->get_param('currentUrl');
        // Receive structure
        $structure = \MatthiasWeb\RealMediaLibrary\attachment\Structure::getInstance();
        return new \WP_REST_Response([
            'tree' => $structure->getPlainTree(),
            'slugs' => $structure->getView()->namesSlugArray(),
            'cntAll' => $structure->getCntAttachments(),
            'cntRoot' => $structure->getCntRoot()
        ]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {get} /realmedialibrary/v1/tree/dropdown Get the full categories tree as dropdown options (HTML)
     * @apiParam {string} [selected] The selected folder id
     * @apiName GetTreeDropdown
     * @apiGroup Tree
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     */
    public function routeTreeDropdown($request) {
        return new \WP_REST_Response([
            'html' => \MatthiasWeb\RealMediaLibrary\attachment\Structure::getInstance()
                ->getView()
                ->dropdown($request->get_param('selected'), null, \false)
        ]);
    }
    /**
     * See API docs.
     *
     * @return WP_REST_Response|WP_Error
     *
     * @api {get} /realmedialibrary/v1/usersettings Get the HTML for user settings
     * @apiName GetUserSettingsHTML
     * @apiGroup Folder
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     */
    public function getUserSettingsHTML() {
        return new \WP_REST_Response([
            'html' => \MatthiasWeb\RealMediaLibrary\metadata\Meta::getInstance()->prepare_content('')
        ]);
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     *
     * @api {put} /realmedialibrary/v1/usersettings Update user settings
     * @apiDescription Send a key value map of form data so UserSettings implementations (IUserSettings) can handle it
     * @apiName UpdateUserSettings
     * @apiGroup UserSettings
     * @apiVersion 1.0.0
     * @apiPermission upload_files
     */
    public function updateUserSettings($request) {
        /**
         * This filter is called to save the general user settings. You can use the $_POST
         * fields to validate the input. If an error occurs you can pass an
         * "error" array (string) to the response. Do not use this filter directly instead use the
         * add_rml_user_settings_box() function!
         *
         * @param {array} $response The response passed to the frontend
         * @param {int} $userId The current user id
         * @param {WP_REST_Request} $request The server request
         * @hook RML/User/Settings/Save
         * @return {array}
         */
        $response = apply_filters(
            'RML/User/Settings/Save',
            ['errors' => [], 'data' => []],
            get_current_user_id(),
            $request
        );
        if (\is_array($response) && isset($response['errors']) && \count($response['errors']) > 0) {
            return new \WP_Error('rest_rml_folder_update', $response['errors'], ['status' => 500]);
        } else {
            if (isset($response['data']) && \is_array($response['data'])) {
                $response = $response['data'];
            }
            return new \WP_REST_Response($response);
        }
    }
    /**
     * Exclude REST API Url from SuperPWA cache
     *
     * @param string $superpwa_sw_never_cache_urls
     * @return string
     * @see https://superpwa.com/codex/superpwa_sw_never_cache_urls/
     */
    function superpwa_exclude_from_cache($superpwa_sw_never_cache_urls) {
        return $superpwa_sw_never_cache_urls . ',/\\/realmedialibrary\\/v1';
    }
    /**
     * Checks if the current user has a given capability and throws an error if not.
     *
     * @param string $cap The capability
     * @return WP_Error|null
     */
    public static function permit($cap = 'upload_files') {
        if (!current_user_can($cap)) {
            return new \WP_Error('rest_rml_forbidden', __('Forbidden'), ['status' => 403]);
        }
        if (!wp_rml_active()) {
            return new \WP_Error(
                'rest_rml_not_activated',
                __('Real Media Library is not active for the current user.', RML_TD),
                ['status' => 500]
            );
        }
        return null;
    }
    /**
     * Allows you to modify a given type of response body. If you want to find the
     * different types you must have a look at the Service class constants.
     *
     * @param string $type
     * @param array $data
     * @since 4.0.9
     */
    public static function addResponseModifier($type, $data) {
        if (!isset(self::$responseModifier[$type])) {
            self::$responseModifier[$type] = [];
        }
        self::$responseModifier[$type] = \array_merge_recursive(self::$responseModifier[$type], $data);
    }
    /**
     * Apply response modifications to a given array.
     *
     * @param string $type
     * @param array $data
     * @return array
     */
    public static function responseModify($type, $data) {
        if (isset(self::$responseModifier[$type])) {
            return \array_merge_recursive($data, self::$responseModifier[$type]);
        }
        return $data;
    }
}

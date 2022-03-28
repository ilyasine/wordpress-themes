<?php

namespace MatthiasWeb\RealMediaLibrary\attachment;

use MatthiasWeb\RealMediaLibrary\Activator;
use MatthiasWeb\RealMediaLibrary\Assets;
use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\usersettings\DefaultFolder;
use WP_Post;
use WP_Query;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This class handles all hooks for the general filters.
 */
class Filter {
    use UtilsProvider;
    private static $me = null;
    /**
     * Get folders of attachments.
     *
     * @param int|int[] $attachmentId
     * @param int $default
     * @see wp_attachment_folder()
     * @return int|int[]|null
     */
    public function getAttachmentFolder($attachmentId, $default = null) {
        $isArray = \is_array($attachmentId);
        $attachmentId = $isArray ? $attachmentId : [$attachmentId];
        if (\count($attachmentId) > 0) {
            global $wpdb;
            $attachments_in = \implode(',', $attachmentId);
            $table_name = $this->getTableName('posts');
            // phpcs:disable WordPress.DB.PreparedSQL
            $folders = $wpdb->get_col(
                "SELECT DISTINCT(rmlposts.fid) FROM {$table_name} AS rmlposts WHERE rmlposts.attachment IN ({$attachments_in})"
            );
            // phpcs:enable WordPress.DB.PreparedSQL
            if ($isArray) {
                return $folders;
            } else {
                return (int) (isset($folders[0]) ? $folders[0] : ($default === null ? _wp_rml_root() : $default));
            }
        }
        return $default;
    }
    /**
     * Changes the SQL query like this way to JOIN the realmedialibrary_posts
     * table and search for the given folder.
     *
     * @param array $clauses
     * @param WP_Query $query
     * @return array
     */
    public function posts_clauses($clauses, $query) {
        global $wpdb;
        if ($query->get('post_type') !== 'attachment') {
            $query->set('use_rml_folder', \false);
            return $clauses;
        }
        $table_name = $this->getTableName('posts');
        $saveInCookie = $this->isQuerying() && !\headers_sent();
        // Shortcut destinations
        $fields = \trim($clauses['fields'], ',');
        // Get folder
        $folderId = isset($query->query_vars['parsed_rml_folder']) ? $query->query_vars['parsed_rml_folder'] : 0;
        $includeChilds = isset($query->query_vars['rml_include_children'])
            ? $query->query_vars['rml_include_children']
            : \false;
        $root = _wp_rml_root();
        $cookieValue = $root;
        // Save the last queried cookie for "New media" dropdown
        /**
         * Do a filter to restrict the RML posts clauses and apply an own clauses modifier.
         * If you want to use your own implementation of posts_clauses you can add this code
         * to to restrict the RML standard posts_clauses: <code>$clauses["_restrictRML"] = true;</code>
         *
         * @param {array} $clauses The list of clauses for the query
         * @param {WP_Query} $query The WP_Query instance
         * @param {int} $folderId The folder ID to query (0 = 'All files')
         * @param {int} $root The root folder ID, see also {@link RML/RootParent}
         * @return {array} $clauses
         * @hook RML/Filter/PostsClauses
         * @see https://developer.wordpress.org/reference/hooks/posts_clauses/
         */
        $clauses = apply_filters('RML/Filter/PostsClauses', $clauses, $query, $folderId, $root);
        $builtIn = !isset($clauses['_restrictRML']);
        if (!$builtIn) {
            unset($clauses['_restrictRML']);
        }
        $query->set('use_rml_folder', $builtIn);
        // Change fields
        $fields = \trim($clauses['fields'], ',');
        $clauses['fields'] =
            $fields .
            $wpdb->prepare(', IFNULL(rmlposts.fid, %d) AS fid, IFNULL(rmlposts.isShortcut, 0) AS isShortcut ', $root);
        // Change join regarding the folder id
        $clauses['join'] .= " LEFT JOIN {$table_name} AS rmlposts ON rmlposts.attachment = " . $wpdb->posts . '.ID ';
        // Folder relevant data
        if ($builtIn === \true) {
            if ($folderId > 0 || $folderId === $root) {
                if ($folderId > 0) {
                    // Allow recursive read
                    if ($includeChilds && wp_rml_all_children_sql_supported(\false, 'function')) {
                        $function_name = $wpdb->prefix . \MatthiasWeb\RealMediaLibrary\Activator::CHILD_UDF_NAME;
                        // phpcs:disable WordPress.DB.PreparedSQL
                        $clauses['join'] .= $wpdb->prepare(
                            ' AND FIND_IN_SET(rmlposts.fid, ' . $function_name . '(%d, false)) ',
                            $folderId
                        );
                        // phpcs:enable WordPress.DB.PreparedSQL
                    } else {
                        $clauses['join'] .= $wpdb->prepare(' AND rmlposts.fid = %d ', $folderId);
                    }
                    $clauses['where'] .= ' AND rmlposts.fid IS NOT NULL ';
                } else {
                    $clauses['where'] .= $wpdb->prepare(' AND (rmlposts.fid IS NULL OR rmlposts.fid = %d) ', $root);
                }
                $cookieValue = $folderId;
            } else {
                $cookieValue = 0;
            }
        }
        // Save cookie value
        if ($saveInCookie && $builtIn) {
            $this->lastQueriedFolder($cookieValue);
        }
        return $clauses;
    }
    /**
     * Set or get the last queried folder.
     *
     * @param int $folder The folder id (0 is handled as "All files" folder)
     * @return int
     * @since 4.0.8 The data is now saved in user meta data instead of Cookie
     */
    public function lastQueriedFolder($folder = null) {
        $key = 'rml_' . get_current_blog_id() . '_lastquery';
        $userId = get_current_user_id();
        $prevCookie = \intval(get_user_meta($userId, $key, \true));
        if ($folder !== null) {
            $folder = \intval($folder);
            if ($folder !== $prevCookie) {
                $prevCookie = $folder;
                update_user_meta($userId, $key, $folder);
            }
        }
        return $prevCookie === null ? _wp_rml_root() : $prevCookie;
    }
    /**
     * Define a new query option.
     *
     * @param WP_Query $query
     */
    public function pre_get_posts($query) {
        global $wp_current_filter;
        if (\in_array('snax_map_meta_caps', $wp_current_filter, \true)) {
            return;
        }
        $assets = $this->getCore()->getAssets();
        $folder = $this->getFolder(
            $query,
            $assets->isScreenBase('upload') ||
                $assets->isScreenBase(\MatthiasWeb\RealMediaLibrary\Assets::MLA_SCREEN_BASE)
        );
        $folder = isset($folder) ? $folder : 0;
        $query->set('parsed_rml_folder', $folder);
    }
    /**
     * Get folder from different sources (WP_Query, GET Query).
     *
     * @param WP_Query $query
     * @param boolean $fromRequest
     * @return int
     */
    public function getFolder($query, $fromRequest = \false) {
        $id = null;
        if ($query !== null && ($queryFolder = $query->get('rml_folder')) && isset($queryFolder)) {
            // Query rml folder from query itself
            $id = $queryFolder;
        } elseif (wp_rml_active()) {
            if ($fromRequest) {
                if (isset($_REQUEST['rml_folder'])) {
                    // Query rml folder from list mode
                    $id = \intval($_REQUEST['rml_folder']);
                } elseif (isset($_POST['query']['rml_folder'])) {
                    // Query rml folder from grid mode
                    $id = \intval($_POST['query']['rml_folder']);
                } elseif (isset($_REQUEST['rmlFolder'])) {
                    // From upload
                    $id = \intval($_REQUEST['rmlFolder']);
                } else {
                    return null;
                }
            }
        } else {
            return null;
        }
        // Resolve last queried folder
        if ($id === \MatthiasWeb\RealMediaLibrary\usersettings\DefaultFolder::ID_LAST_QUERIED) {
            $id = wp_rml_last_queried_folder();
        }
        return $id;
    }
    /**
     * Modify AJAX request for query-attachments request.
     *
     * @param WP_Query $query
     * @return WP_Query
     */
    public function ajax_query_attachments_args($query) {
        $fid = $this->getFolder(null, \true);
        if ($fid !== null) {
            $query['rml_folder'] = $fid;
        }
        return $query;
    }
    /**
     * Add the attachment ID to the count update when deleting it.
     *
     * @param int $postID
     */
    public function delete_attachment($postID) {
        // Reset folder count
        \MatthiasWeb\RealMediaLibrary\attachment\CountCache::getInstance()->resetCountCacheOnWpDie(
            wp_attachment_folder($postID)
        );
        // Delete row in posts table
        global $wpdb;
        $table_name = $this->getTableName('posts');
        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->query($wpdb->prepare("DELETE FROM {$table_name} WHERE attachment = %d", $postID));
        // phpcs:enable WordPress.DB.PreparedSQL
        // Reindex folder
        $folder = wp_rml_get_object_by_id(wp_attachment_folder($postID));
        if (is_rml_folder($folder) && $this->isPro()) {
            $folder->contentReindex();
        }
    }
    /**
     * Add a attribute to the ajax output. The attribute represents
     * the folder order number if it is a gallery.
     *
     * @param array $response
     * @param WP_Post $attachment
     * @param mixed $meta
     * @return array
     */
    public function wp_prepare_attachment_for_js($response, $attachment, $meta) {
        // append attribute
        $rFolderId = $this->getFolder(null, \true);
        $attachmentFid = isset($attachment->fid) ? $attachment->fid : null;
        $folderId = $attachmentFid !== null ? \intval($attachmentFid) : null;
        $folderId = $folderId === null ? (!empty($rFolderId) ? $rFolderId : _wp_rml_root()) : $folderId;
        $response['rmlFolderId'] = $folderId;
        $response['rmlGalleryOrder'] = \intval($attachment->orderNr);
        $response['rmlIsShortcut'] = $attachment->isShortcut > 0;
        // Allow SVG images displayed in media library
        if ($response['subtype'] === 'svg+xml') {
            $response['sizes'] = ['full' => ['url' => $response['url']]];
        }
        // return
        return $response;
    }
    /**
     * Create a select option in list table of attachments.
     */
    public function restrict_manage_posts() {
        $screen = get_current_screen();
        if ($screen->id === 'upload') {
            echo '<select name="rml_folder" id="filter-by-rml-folder" class="attachment-filters attachment-filters-rml">
    			' .
                \MatthiasWeb\RealMediaLibrary\attachment\Structure::getInstance()
                    ->getView()
                    ->dropdown(isset($_REQUEST['rml_folder']) ? $_REQUEST['rml_folder'] : '', []) .
                '
    		</select>&nbsp;';
        }
    }
    /**
     * Check if the current request is a query request for attachments.
     *
     * @return boolean
     */
    public function isQuerying() {
        $isGridMode =
            is_admin() &&
            \defined('DOING_AJAX') &&
            \constant('DOING_AJAX') &&
            isset($_REQUEST['action']) &&
            $_REQUEST['action'] === 'query-attachments';
        $assets = $this->getCore()->getAssets();
        $isListMode = $assets->isScreenBase('upload');
        $isMLAPage = $assets->isScreenBase(\MatthiasWeb\RealMediaLibrary\Assets::MLA_SCREEN_BASE);
        return $isGridMode || $isListMode || $isMLAPage;
    }
    /**
     * Get instance.
     *
     * @return Filter
     */
    public static function getInstance() {
        return self::$me === null ? (self::$me = new \MatthiasWeb\RealMediaLibrary\attachment\Filter()) : self::$me;
    }
}

<?php

namespace MatthiasWeb\RealMediaLibrary;

use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
use MatthiasWeb\RealMediaLibrary\Vendor\MatthiasWeb\Utils\Activator as UtilsActivator;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * The activator class handles the plugin relevant activation hooks: Uninstall, activation,
 * deactivation and installation. The "installation" means installing needed database tables.
 */
class Activator {
    use UtilsProvider;
    use UtilsActivator;
    const DB_CHILD_QUERY_SUPPORTED = '_cqs';
    const CHILD_UDF_NAME = 'fn_realmedialibrary_childs';
    private $childSupportCurrentType = null;
    /**
     * Method gets fired when the user activates the plugin.
     */
    public function activate() {
        /**
         * This hook is fired when RML gets activated.
         *
         * @hook RML/Activate
         */
        do_action('RML/Activate');
    }
    /**
     * Method gets fired when the user deactivates the plugin.
     */
    public function deactivate() {
        // Your implementation...
    }
    /**
     * Install tables, stored procedures or whatever in the database.
     * This method is always called when the version bumps up or for
     * the first initial activation.
     *
     * @param boolean $errorlevel If true throw errors
     */
    public function dbDelta($errorlevel) {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $this->supportsChildQuery(\true);
        $max_index_length = $this->getMaxIndexLength();
        // wp_realmedialibrary
        $table_name = $this->getTableName();
        $sql = "CREATE TABLE {$table_name} (\n            id mediumint(9) NOT NULL AUTO_INCREMENT,\n            parent mediumint(9) DEFAULT '-1' NOT NULL,\n            name tinytext NOT NULL,\n            slug text DEFAULT '' NOT NULL,\n            absolute text DEFAULT '' NOT NULL,\n            owner bigint(20) NOT NULL,\n            ord mediumint(10) DEFAULT 0 NOT NULL,\n            oldCustomOrder mediumint(10) DEFAULT NULL,\n            contentCustomOrder tinyint(1) DEFAULT 0 NOT NULL,\n            type varchar(10) DEFAULT '0' NOT NULL,\n            restrictions varchar(255) DEFAULT '' NOT NULL,\n            cnt mediumint(10) DEFAULT NULL,\n            importId bigint(20) DEFAULT NULL,\n            PRIMARY KEY  (id)\n        ) {$charset_collate};";
        dbDelta($sql);
        if ($errorlevel) {
            $wpdb->print_error();
        }
        // Table wp_realmedialibrary_posts
        $table_name = $this->getTableName('posts');
        $sql = "CREATE TABLE {$table_name} (\n            attachment bigint(20) NOT NULL,\n            fid mediumint(9) NOT NULL DEFAULT '-1',\n            isShortcut bigint(20) NOT NULL DEFAULT 0,\n            nr bigint(20),\n            oldCustomNr bigint(20) DEFAULT NULL,\n            importData text,\n            KEY rmljoin (attachment,fid),\n            PRIMARY KEY  (attachment,isShortcut)\n        ) {$charset_collate};";
        dbDelta($sql);
        // Table wp_realmedialibrary_meta
        $table_name = $this->getTableName('meta');
        $sql = "CREATE TABLE {$table_name} (\n          `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n          `realmedialibrary_id` bigint(20) unsigned NOT NULL DEFAULT '0',\n          `meta_key` varchar(255) DEFAULT NULL,\n          `meta_value` longtext,\n          PRIMARY KEY  (meta_id),\n          KEY realmedialibrary_id (realmedialibrary_id),\n          KEY meta_key (meta_key({$max_index_length}))\n        ) {$charset_collate};";
        dbDelta($sql);
        if ($errorlevel) {
            $wpdb->print_error();
        }
    }
    /**
     * Create a MySQL function wp_realmedialibrary_childs to read recursively
     * children of a folder.
     */
    public function createChildQueryFunction() {
        global $wpdb;
        $function_name = $wpdb->prefix . self::CHILD_UDF_NAME;
        $table_name = $this->getTableName();
        // phpcs:disable WordPress.DB.PreparedSQL
        $wpdb->query("DROP FUNCTION IF EXISTS {$function_name}");
        // phpcs:enable WordPress.DB.PreparedSQL
        $sql =
            "CREATE FUNCTION {$function_name}(rootId INT, _useTempChildTableForCheck BOOLEAN)\n                RETURNS varchar(1000)\n                NOT DETERMINISTIC\n                READS SQL DATA\n                SQL SECURITY INVOKER\n            BEGIN\n        \tDECLARE sTemp VARCHAR(1000);\n        \tDECLARE sTempChd VARCHAR(1000);\n        \tSET sTemp = '\$';\n        \tSET sTempChd = CAST(rootId AS CHAR);\n\t\t    SET SESSION group_concat_max_len = 100000;\n        \tWHILE sTempChd IS NOT NULL DO\n        \t\tSET sTemp = CONCAT(sTemp,',',sTempChd);\n        \t\tIF _useTempChildTableForCheck IS NULL OR _useTempChildTableForCheck = false THEN\n        \t\t    SELECT GROUP_CONCAT(id) INTO sTempChd FROM {$table_name} WHERE FIND_IN_SET(parent,sTempChd) > 0;\n        \t\tELSE\n        \t\t    SELECT GROUP_CONCAT(id) INTO sTempChd FROM " .
            $this->getTableName('tmp') .
            ' WHERE FIND_IN_SET(parent,sTempChd) > 0;
        		END IF;
        	END WHILE;
        	RETURN sTemp;
        END';
        $suppress = $wpdb->suppress_errors();
        // phpcs:disable WordPress.DB.PreparedSQL
        $wpdb->query($sql);
        // phpcs:enable WordPress.DB.PreparedSQL
        $wpdb->suppress_errors($suppress);
    }
    /**
     * Checks if the current database supports functions or recursive drill-down query.
     *
     * @param boolean $force
     * @param string $type Can be 'legacy' or 'function'.
     * @see wp_rml_all_children_sql_supported
     * @return boolean
     */
    public function supportsChildQuery($force = \false, $type = 'legacy') {
        global $wpdb;
        $value = get_option(RML_OPT_PREFIX . self::DB_CHILD_QUERY_SUPPORTED, null);
        $function_exists = $this->checkDirtyFunction($wpdb->prefix . self::CHILD_UDF_NAME);
        // The function does not exist but the option says, that it is supported -> force
        if (!$function_exists && ($value === '2' || $force)) {
            $this->debug(
                'The database function does not exist but the option says, it is supported... force recreation',
                __METHOD__
            );
            $this->install(\false, [$this, 'createChildQueryFunction']);
            $force = \true;
        }
        if ($value === null || $force) {
            $this->childSupportCurrentType = 'function';
            $this->install(\false, [$this, 'checkChildQuery']);
            $value = get_option(RML_OPT_PREFIX . self::DB_CHILD_QUERY_SUPPORTED, null);
            if ($type === 'function') {
                return $value > 0;
            }
            // Fallback to the legacy
            if ($value === '0') {
                $this->childSupportCurrentType = 'legacy';
                $this->install(\false, [$this, 'checkChildQuery']);
                $value = get_option(RML_OPT_PREFIX . self::DB_CHILD_QUERY_SUPPORTED, null);
            }
        }
        if ($type === 'function') {
            return $value === '2';
        }
        return $value > 0;
    }
    /**
     * Check if the current instance type works as expected.
     */
    public function checkChildQuery() {
        // phpcs:disable WordPress.DB.PreparedSQL
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $suppress = $wpdb->suppress_errors();
        $table_name = $this->getTableName('tmp');
        // Main table
        $sql = "CREATE TABLE {$table_name} (\n\t\t\tid mediumint(9) NOT NULL AUTO_INCREMENT,\n\t\t\tparent mediumint(9) DEFAULT '-1' NOT NULL,\n\t\t\tname tinytext NOT NULL,\n\t\t\tord mediumint(10) DEFAULT 0 NOT NULL,\n\t\t\tPRIMARY KEY  (id)\n\t\t) {$charset_collate};";
        dbDelta($sql);
        $wpdb->query('DELETE FROM ' . $table_name);
        // Create hierarchy
        $wpdb->query('INSERT INTO ' . $table_name . ' VALUES(NULL, -1, "Root", 0)');
        $wpdb->query(
            'INSERT INTO ' . $table_name . ' VALUES(NULL, ' . $wpdb->insert_id . ', "Roots 1st child (Bob)", 0)'
        );
        $bob = $wpdb->insert_id;
        $wpdb->query('INSERT INTO ' . $table_name . ' VALUES(NULL, ' . $bob . ', "Bobs 1st child", 0)');
        $wpdb->query('INSERT INTO ' . $table_name . ' VALUES(NULL, ' . $bob . ', "Bobs 2nd child (Marie)", 1)');
        $wpdb->query('INSERT INTO ' . $table_name . ' VALUES(NULL, ' . $wpdb->insert_id . ', "Maries 1st child", 0)');
        // Check query result
        $sql = \MatthiasWeb\RealMediaLibrary\Util::getInstance()->createSQLForAllChildren(
            $bob,
            \true,
            null,
            $this->childSupportCurrentType
        );
        $sql = \str_replace($this->getTableName(), $table_name, $sql);
        $supported = \count($wpdb->get_results($sql)) === 4;
        $onSuccess = $this->childSupportCurrentType === 'function' ? '2' : '1';
        $option_value = $supported ? $onSuccess : '0';
        update_option(RML_OPT_PREFIX . self::DB_CHILD_QUERY_SUPPORTED, $option_value, \true);
        $this->debug(
            'Your system supports recursive child SQL queries: ' . $this->childSupportCurrentType . '=' . $option_value,
            __METHOD__
        );
        $wpdb->query('DELETE FROM ' . $table_name);
        // phpcs:enable WordPress.DB.PreparedSQL
        $wpdb->suppress_errors($suppress);
    }
    /**
     * Checks if the Database supports functions but the function is not yet created (due to exports for example).
     *
     * @param string $function_name
     * @return boolean
     */
    public function checkDirtyFunction($function_name) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare('SHOW FUNCTION STATUS LIKE %s', $function_name), 1) === $function_name;
    }
}

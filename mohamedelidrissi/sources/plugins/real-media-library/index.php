<?php
/**
 * Main file for WordPress.
 *
 * @wordpress-plugin
 * Plugin Name: 	Real Media Library
 * Plugin URI:		https://devowl.io/wordpress-real-media-library/
 * Description: 	Organize uploaded media in folders, collections and galleries: A file manager for WordPress. Media management made easy!
 * Author:          devowl.io
 * Author URI:		https://devowl.io
 * Version: 		4.16.2
 * Text Domain:		real-media-library
 * Domain Path:		/languages
 */

defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request
update_site_option( 'wpls_license_real-media-library', 'activated' );
update_site_option( 'wpls_activation_id_real-media-library', 'activated' );
/**
 * Plugin constants. This file is procedural coding style for initialization of
 * the plugin core and definition of plugin configuration.
 */
if (defined('RML_PATH')) {
    require_once path_join(dirname(__FILE__), 'inc/base/others/fallback-already.php');
    return;
}
define('RML_FILE', __FILE__);
define('RML_PATH', dirname(RML_FILE));
define('RML_ROOT_SLUG', 'devowl-wp');
define('RML_SLUG', basename(RML_PATH));
define('RML_INC', trailingslashit(path_join(RML_PATH, 'inc')));
define('RML_MIN_PHP', '7.0.0'); // Minimum of PHP 5.3 required for autoloading and namespacing
define('RML_MIN_WP', '5.0.0'); // Minimum of WordPress 5.0 required
define('RML_NS', 'MatthiasWeb\\RealMediaLibrary');
define('RML_DB_PREFIX', 'realmedialibrary'); // The table name prefix wp_{prefix}
define('RML_OPT_PREFIX', 'rml'); // The option name prefix in wp_options
define('RML_SLUG_CAMELCASE', lcfirst(str_replace('-', '', ucwords(RML_SLUG, '-'))));
//define('RML_TD', ''); This constant is defined in the core class. Use this constant in all your __() methods
//define('RML_VERSION', ''); This constant is defined in the core class
//define('RML_DEBUG', true); This constant should be defined in wp-config.php to enable the Base#debug() method

define('RML_SLUG_LITE', 'real-media-library-lite');
define('RML_SLUG_PRO', 'real-media-library');
define('RML_PRO_VERSION', 'https://devowl.io/go/real-media-library?source=rml-lite');

define('RML_TYPE_FOLDER', 0);
define('RML_TYPE_COLLECTION', 1);
define('RML_TYPE_GALLERY', 2);
define('RML_TYPE_ALL', 3);
define('RML_TYPE_ROOT', 4);

// Check PHP Version and print notice if minimum not reached, otherwise start the plugin core
require_once RML_INC .
    'base/others/' .
    (version_compare(phpversion(), RML_MIN_PHP, '>=') ? 'start.php' : 'fallback-php-version.php');

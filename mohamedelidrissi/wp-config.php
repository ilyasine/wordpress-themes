<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'c3midrissi' );
/** Disable revision */
define( 'WP_POST_REVISIONS', false );
/** MySQL database username */
define( 'DB_USER', 'c3midrissi' );
/** MySQL database password */
define( 'DB_PASSWORD', '_xuG2PusYJ' );
/** MySQL hostname */
define( 'DB_HOST', 'localhost' );
/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );
/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );
/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'gl0Q6XGdTcgurWm+uMpQao0bfaPnQrjgX0rm0KoGGhG2tLtmtLRYbl8TBylIzYG/jRtMRvPJk0fiVLh4LXp10g==');
define('SECURE_AUTH_KEY',  'NClmYjKbNiGmmhhpdmIRoAHmEUVrpIvzu5o2DQDrHS9MU0oR/pCkgX7dS+3IF21H0F2FGTpBnh31lJKHMcePEg==');
define('LOGGED_IN_KEY',    'csFWY+1HCzVWMvQk24IsHup/G67MBre6czwxnoVIHsV4dHmaUQszvTE2BhLLK4OEAFyTsxrrI/ba7i8LBToRWw==');
define('NONCE_KEY',        'xNy8FEm+1F33SAL6YgiS/EoGuzWFPs4CvUtZQ7p9KgZiMba8bBUXHQIiJ86j500eoFYZd09ZHstM+r6Ww9gJeA==');
define('AUTH_SALT',        'hjck8Qe3+IisD6CDBSuopn7M21SC2rq/fVJv2xx7m7mkbg4EEzYz14l/8lv1U6N0pDSjv9dqvEEa7JfJH5lhnw==');
define('SECURE_AUTH_SALT', 'b51EB8urplOQ0bSFJNcQ5rb+vQ0KGfUEVvsBZUyfyge2lJlSqpEau5eRJs8Mv89k/wD7dggS5lgEqqrj5kflpg==');
define('LOGGED_IN_SALT',   '1j+gYEUQmSPpHG2cN4CS0YJU22vAciFnLqbmq3Dji1y+iADIf0kre2qnMo0bYMIc/pgzu1qQsA+1As2V35Enbw==');
define('NONCE_SALT',       'wkJVtVlfI+acfd51/cKjs3fvVXZQWOfd8U/OCDIos0flozwVpn9csXZ4FxSh/BG77SHdENvUlIhEM/0ideqIUA==');
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';
define('WP_MEMORY_LIMIT', '512M');
/* That's all, stop editing! Happy publishing. */
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}
/* change content folder */
define ('WP_CONTENT_FOLDERNAME', 'sources');
define ('WP_CONTENT_DIR', ABSPATH . WP_CONTENT_FOLDERNAME) ;
define('WP_SITEURL', 'http://' . $_SERVER['HTTP_HOST'] . '/');
define('WP_CONTENT_URL', WP_SITEURL . WP_CONTENT_FOLDERNAME);
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
<?php
/**
 * Plugin Name:       Your ACT Content Library
 * Plugin URI:        https://youract.net
 * Description:       Structured Resources and Events content library for YourACT.net.
 * Version:           1.0.3
 * Requires at least: 6.7
 * Requires PHP:      8.1
 * Author:            YourACT.net
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       youract-content-library
 * Domain Path:       /languages
 *
 * @package YourACT\ContentLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'YOURACT_CONTENT_LIBRARY_VERSION' ) ) {
	define( 'YOURACT_CONTENT_LIBRARY_VERSION', '1.0.3' );
}

if ( ! defined( 'YOURACT_CONTENT_LIBRARY_FILE' ) ) {
	define( 'YOURACT_CONTENT_LIBRARY_FILE', __FILE__ );
}

if ( ! defined( 'YOURACT_CONTENT_LIBRARY_PATH' ) ) {
	define( 'YOURACT_CONTENT_LIBRARY_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YOURACT_CONTENT_LIBRARY_URL' ) ) {
	define( 'YOURACT_CONTENT_LIBRARY_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'YOURACT_CONTENT_LIBRARY_BASENAME' ) ) {
	define( 'YOURACT_CONTENT_LIBRARY_BASENAME', plugin_basename( __FILE__ ) );
}

require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( '\\YourACT\\ContentLibrary\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\\YourACT\\ContentLibrary\\Plugin', 'deactivate' ) );

/**
 * Boots the plugin.
 *
 * @return \YourACT\ContentLibrary\Plugin
 */
function youract_content_library() {
	return \YourACT\ContentLibrary\Plugin::instance();
}

add_action(
	'plugins_loaded',
	'youract_content_library_boot'
);

/**
 * Initializes plugin hooks on plugins_loaded.
 *
 * @return void
 */
function youract_content_library_boot() {
	load_plugin_textdomain( 'youract-content-library', false, dirname( YOURACT_CONTENT_LIBRARY_BASENAME ) . '/languages' );
	youract_content_library()->run();
}

<?php
/**
 * Plugin deactivation handler.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivation behavior.
 */
class Deactivator {

	/**
	 * Runs plugin deactivation tasks.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}

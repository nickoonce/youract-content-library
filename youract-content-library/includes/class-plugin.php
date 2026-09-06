<?php
/**
 * Main plugin bootstrap class.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coordinates plugin services.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Post type registrar.
	 *
	 * @var Post_Types
	 */
	private Post_Types $post_types;

	/**
	 * Taxonomy registrar.
	 *
	 * @var Taxonomies
	 */
	private Taxonomies $taxonomies;

	/**
	 * Meta registrar.
	 *
	 * @var Meta_Registration
	 */
	private Meta_Registration $meta_registration;

	/**
	 * Resource admin manager.
	 *
	 * @var Admin_Resource
	 */
	private Admin_Resource $admin_resource;

	/**
	 * Event admin manager.
	 *
	 * @var Admin_Event
	 */
	private Admin_Event $admin_event;

	/**
	 * Renderer service.
	 *
	 * @var Renderer
	 */
	private Renderer $renderer;

	/**
	 * Shortcode service.
	 *
	 * @var Shortcodes
	 */
	private Shortcodes $shortcodes;

	/**
	 * Structured-data service.
	 *
	 * @var Structured_Data
	 */
	private Structured_Data $structured_data;

	/**
	 * Gets singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-post-types.php';
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-taxonomies.php';
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-utils.php';
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-meta-registration.php';
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-admin-resource.php';
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-admin-event.php';
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-resource-query.php';
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-event-query.php';
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-renderer.php';
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-shortcodes.php';
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-structured-data.php';

		$resource_query = new Resource_Query();
		$event_query    = new Event_Query();

		$this->post_types = new Post_Types();
		$this->taxonomies = new Taxonomies();
		$this->meta_registration = new Meta_Registration();
		$this->admin_resource    = new Admin_Resource();
		$this->admin_event       = new Admin_Event();
		$this->renderer          = new Renderer();
		$this->shortcodes        = new Shortcodes( $resource_query, $event_query, $this->renderer );
		$this->structured_data   = new Structured_Data();
	}

	/**
	 * Registers runtime hooks.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->post_types->register();
		$this->taxonomies->register();
		$this->meta_registration->register();
		$this->renderer->register();
		$this->shortcodes->register();
		$this->structured_data->register();

		if ( is_admin() ) {
			$this->admin_resource->register();
			$this->admin_event->register();
		}
	}

	/**
	 * Handles activation logic.
	 *
	 * @return void
	 */
	public static function activate(): void {
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-post-types.php';
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-taxonomies.php';
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-activator.php';

		Activator::activate();
	}

	/**
	 * Handles deactivation logic.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		require_once YOURACT_CONTENT_LIBRARY_PATH . 'includes/class-deactivator.php';

		Deactivator::deactivate();
	}
}

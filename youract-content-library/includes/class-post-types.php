<?php
/**
 * Custom post type registration.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers plugin post types.
 */
class Post_Types {

	/**
	 * Hook registrations.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	/**
	 * Registers Resource and Event post types.
	 *
	 * @return void
	 */
	public function register_post_types(): void {
		register_post_type(
			'act_resource',
			array(
				'labels'          => array(
					'name'                  => __( 'Resources', 'youract-content-library' ),
					'singular_name'         => __( 'Resource', 'youract-content-library' ),
					'menu_name'             => __( 'Resources', 'youract-content-library' ),
					'name_admin_bar'        => __( 'Resource', 'youract-content-library' ),
					'add_new'               => __( 'Add New', 'youract-content-library' ),
					'add_new_item'          => __( 'Add New Resource', 'youract-content-library' ),
					'edit_item'             => __( 'Edit Resource', 'youract-content-library' ),
					'new_item'              => __( 'New Resource', 'youract-content-library' ),
					'view_item'             => __( 'View Resource', 'youract-content-library' ),
					'view_items'            => __( 'View Resources', 'youract-content-library' ),
					'search_items'          => __( 'Search Resources', 'youract-content-library' ),
					'not_found'             => __( 'No resources found.', 'youract-content-library' ),
					'not_found_in_trash'    => __( 'No resources found in Trash.', 'youract-content-library' ),
					'all_items'             => __( 'All Resources', 'youract-content-library' ),
					'archives'              => __( 'Resource Archives', 'youract-content-library' ),
					'attributes'            => __( 'Resource Attributes', 'youract-content-library' ),
					'insert_into_item'      => __( 'Insert into resource', 'youract-content-library' ),
					'uploaded_to_this_item' => __( 'Uploaded to this resource', 'youract-content-library' ),
				),
				'public'          => true,
				'has_archive'     => true,
				'rewrite'         => array( 'slug' => 'resources' ),
				'show_in_rest'    => true,
				'menu_icon'       => 'dashicons-media-document',
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author' ),
				'publicly_queryable' => true,
				'show_in_nav_menus'  => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
			),
		);

		register_post_type(
			'act_event',
			array(
				'labels'          => array(
					'name'                  => __( 'Events', 'youract-content-library' ),
					'singular_name'         => __( 'Event', 'youract-content-library' ),
					'menu_name'             => __( 'Events', 'youract-content-library' ),
					'name_admin_bar'        => __( 'Event', 'youract-content-library' ),
					'add_new'               => __( 'Add New', 'youract-content-library' ),
					'add_new_item'          => __( 'Add New Event', 'youract-content-library' ),
					'edit_item'             => __( 'Edit Event', 'youract-content-library' ),
					'new_item'              => __( 'New Event', 'youract-content-library' ),
					'view_item'             => __( 'View Event', 'youract-content-library' ),
					'view_items'            => __( 'View Events', 'youract-content-library' ),
					'search_items'          => __( 'Search Events', 'youract-content-library' ),
					'not_found'             => __( 'No events found.', 'youract-content-library' ),
					'not_found_in_trash'    => __( 'No events found in Trash.', 'youract-content-library' ),
					'all_items'             => __( 'All Events', 'youract-content-library' ),
					'archives'              => __( 'Event Archives', 'youract-content-library' ),
					'attributes'            => __( 'Event Attributes', 'youract-content-library' ),
					'insert_into_item'      => __( 'Insert into event', 'youract-content-library' ),
					'uploaded_to_this_item' => __( 'Uploaded to this event', 'youract-content-library' ),
				),
				'public'          => true,
				'has_archive'     => true,
				'rewrite'         => array( 'slug' => 'events' ),
				'show_in_rest'    => true,
				'menu_icon'       => 'dashicons-calendar-alt',
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author' ),
				'publicly_queryable' => true,
				'show_in_nav_menus'  => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
			),
		);
	}
}

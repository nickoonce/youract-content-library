<?php
/**
 * Taxonomy registration.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers plugin taxonomies.
 */
class Taxonomies {

	/**
	 * Hook registrations.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_taxonomies' ) );
	}

	/**
	 * Registers all taxonomies for resources and events.
	 *
	 * @return void
	 */
	public function register_taxonomies(): void {
		$shared_args = array(
			'hierarchical'      => true,
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'show_ui'           => true,
			'query_var'         => true,
		);

		register_taxonomy(
			'act_topic',
			array( 'act_resource', 'act_event' ),
			array_merge(
				$shared_args,
				array(
					'labels'  => $this->taxonomy_labels( 'Topics', 'Topic' ),
					'rewrite' => array( 'slug' => 'topics' ),
				)
			)
		);

		register_taxonomy(
			'act_geography',
			array( 'act_resource', 'act_event' ),
			array_merge(
				$shared_args,
				array(
					'labels'  => $this->taxonomy_labels( 'Geographic Scope', 'Geographic Scope' ),
					'rewrite' => array( 'slug' => 'geography' ),
				)
			)
		);

		register_taxonomy(
			'act_resource_type',
			array( 'act_resource' ),
			array_merge(
				$shared_args,
				array(
					'labels'  => $this->taxonomy_labels( 'Resource Types', 'Resource Type' ),
					'rewrite' => array( 'slug' => 'resource-types' ),
				)
			)
		);

		register_taxonomy(
			'act_event_type',
			array( 'act_event' ),
			array_merge(
				$shared_args,
				array(
					'labels'  => $this->taxonomy_labels( 'Event Types', 'Event Type' ),
					'rewrite' => array( 'slug' => 'event-types' ),
				)
			)
		);
	}

	/**
	 * Creates standard hierarchical taxonomy labels.
	 *
	 * @param string $plural_label   Plural label.
	 * @param string $singular_label Singular label.
	 * @return array<string, string>
	 */
	private function taxonomy_labels( string $plural_label, string $singular_label ): array {
		return array(
			'name'              => __( $plural_label, 'youract-content-library' ),
			'singular_name'     => __( $singular_label, 'youract-content-library' ),
			'search_items'      => sprintf( __( 'Search %s', 'youract-content-library' ), __( $plural_label, 'youract-content-library' ) ),
			'all_items'         => sprintf( __( 'All %s', 'youract-content-library' ), __( $plural_label, 'youract-content-library' ) ),
			'parent_item'       => sprintf( __( 'Parent %s', 'youract-content-library' ), __( $singular_label, 'youract-content-library' ) ),
			'parent_item_colon' => sprintf( __( 'Parent %s:', 'youract-content-library' ), __( $singular_label, 'youract-content-library' ) ),
			'edit_item'         => sprintf( __( 'Edit %s', 'youract-content-library' ), __( $singular_label, 'youract-content-library' ) ),
			'view_item'         => sprintf( __( 'View %s', 'youract-content-library' ), __( $singular_label, 'youract-content-library' ) ),
			'update_item'       => sprintf( __( 'Update %s', 'youract-content-library' ), __( $singular_label, 'youract-content-library' ) ),
			'add_new_item'      => sprintf( __( 'Add New %s', 'youract-content-library' ), __( $singular_label, 'youract-content-library' ) ),
			'new_item_name'     => sprintf( __( 'New %s Name', 'youract-content-library' ), __( $singular_label, 'youract-content-library' ) ),
			'menu_name'         => __( $plural_label, 'youract-content-library' ),
		);
	}
}

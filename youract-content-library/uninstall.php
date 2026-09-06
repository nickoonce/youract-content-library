<?php
/**
 * Uninstall handler for Your ACT Content Library.
 *
 * Data is preserved by default. To allow deletion on uninstall, set either:
 * - define( 'YOURACT_CONTENT_LIBRARY_DELETE_DATA', true );
 * - update_option( 'youract_delete_data_on_uninstall', 1 );
 *
 * @package YourACT\ContentLibrary
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$delete_enabled = defined( 'YOURACT_CONTENT_LIBRARY_DELETE_DATA' ) && YOURACT_CONTENT_LIBRARY_DELETE_DATA;
if ( ! $delete_enabled ) {
	$delete_enabled = (bool) get_option( 'youract_delete_data_on_uninstall', false );
}

if ( ! $delete_enabled ) {
	return;
}

$post_types = array( 'act_resource', 'act_event' );
$taxonomies = array( 'act_topic', 'act_geography', 'act_resource_type', 'act_event_type' );

foreach ( $post_types as $post_type ) {
	$ids = get_posts(
		array(
			'post_type'              => $post_type,
			'post_status'            => 'any',
			'fields'                 => 'ids',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	foreach ( $ids as $id ) {
		wp_delete_post( (int) $id, true );
	}
}

foreach ( $taxonomies as $taxonomy ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);

	if ( is_wp_error( $terms ) ) {
		continue;
	}

	foreach ( $terms as $term_id ) {
		wp_delete_term( (int) $term_id, $taxonomy );
	}
}

delete_option( 'youract_content_library_terms_seeded_v1' );
delete_option( 'youract_delete_data_on_uninstall' );

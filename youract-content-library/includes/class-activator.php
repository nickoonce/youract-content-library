<?php
/**
 * Plugin activation handler.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activation behavior.
 */
class Activator {

	/**
	 * Option key indicating default term seeding is complete.
	 *
	 * @var string
	 */
	private const TERMS_SEEDED_OPTION = 'youract_content_library_terms_seeded_v1';

	/**
	 * Runs plugin activation tasks.
	 *
	 * @return void
	 */
	public static function activate(): void {
		$post_types = new Post_Types();
		$taxonomies = new Taxonomies();

		$post_types->register_post_types();
		$taxonomies->register_taxonomies();

		self::maybe_seed_default_terms();
		flush_rewrite_rules();
	}

	/**
	 * Seeds default taxonomy terms once.
	 *
	 * @return void
	 */
	private static function maybe_seed_default_terms(): void {
		if ( get_option( self::TERMS_SEEDED_OPTION, false ) ) {
			return;
		}

		$resource_types = array(
			'Organization',
			'Guide or Toolkit',
			'Service',
			'Article or Report',
			'Law or Policy',
			'Code or Standard',
			'Advocacy Tool',
			'Video or Recording',
			'Directory',
		);

		$event_types = array(
			'Public Meeting',
			'Community Event',
			'Hearing',
			'Webinar',
			'Training',
			'Advocacy Opportunity',
			'Comment Deadline',
			'Conference',
		);

		self::insert_terms_if_missing( 'act_resource_type', $resource_types );
		self::insert_terms_if_missing( 'act_event_type', $event_types );

		update_option( self::TERMS_SEEDED_OPTION, 1, false );
	}

	/**
	 * Inserts terms if they do not already exist in the taxonomy.
	 *
	 * @param string   $taxonomy Taxonomy slug.
	 * @param string[] $terms    Terms to ensure.
	 * @return void
	 */
	private static function insert_terms_if_missing( string $taxonomy, array $terms ): void {
		foreach ( $terms as $term_name ) {
			if ( term_exists( $term_name, $taxonomy ) ) {
				continue;
			}

			wp_insert_term( $term_name, $taxonomy );
		}
	}
}

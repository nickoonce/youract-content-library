<?php
/**
 * Opportunity query builder.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds public Opportunity queries.
 */
class Opportunity_Query {

	/**
	 * Current Opportunity stages.
	 *
	 * @var string[]
	 */
	private const CURRENT_STAGES = array( 'exploring', 'seeking-partners', 'in-progress' );

	/**
	 * Hooks registration.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'pre_get_posts', array( $this, 'handle_opportunity_archive_query' ) );
		add_filter( 'posts_clauses', array( $this, 'apply_featured_first_ordering' ), 10, 2 );
	}

	/**
	 * Applies public visibility and ordering to the Opportunity archive.
	 *
	 * @param \WP_Query $query Main public query.
	 * @return void
	 */
	public function handle_opportunity_archive_query( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'act_opportunity' ) ) {
			return;
		}

		$query->set( 'post_status', 'publish' );
		$query->set( 'youract_opportunity_featured_order', true );
		$this->append_meta_query( $query, $this->non_inactive_stage_clause() );
	}

	/**
	 * Builds args for current Opportunities.
	 *
	 * @param int $limit Number of posts.
	 * @return array<string, mixed>
	 */
	public function build_current_opportunities_query_args( int $limit = 10 ): array {
		return $this->build_collection_query_args( $this->current_stage_clause(), $limit );
	}

	/**
	 * Builds args for Success Stories.
	 *
	 * @param int $limit Number of posts.
	 * @return array<string, mixed>
	 */
	public function build_success_stories_query_args( int $limit = 10 ): array {
		return $this->build_collection_query_args( $this->stage_clause( array( 'success-story' ) ), $limit );
	}

	/**
	 * Builds args for featured current Opportunities.
	 *
	 * @param int $limit Number of posts.
	 * @return array<string, mixed>
	 */
	public function build_featured_current_opportunities_query_args( int $limit = 10 ): array {
		return $this->build_collection_query_args( $this->current_stage_clause(), $limit, true );
	}

	/**
	 * Builds args for featured Success Stories.
	 *
	 * @param int $limit Number of posts.
	 * @return array<string, mixed>
	 */
	public function build_featured_success_stories_query_args( int $limit = 10 ): array {
		return $this->build_collection_query_args( $this->stage_clause( array( 'success-story' ) ), $limit, true );
	}

	/**
	 * Builds a public Opportunity collection query.
	 *
	 * @param array<int|string, mixed> $stage_clause Allowed stages clause.
	 * @param int                      $limit        Number of posts.
	 * @param bool                     $featured     Whether featured is required.
	 * @return array<string, mixed>
	 */
	private function build_collection_query_args( array $stage_clause, int $limit, bool $featured = false ): array {
		$meta_query = array(
			'relation' => 'AND',
			$stage_clause,
		);

		if ( $featured ) {
			$meta_query[] = array(
				'key'     => 'act_opportunity_featured',
				'value'   => '1',
				'compare' => '=',
			);
		}

		return array(
			'post_type'           => 'act_opportunity',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, $limit ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'youract_opportunity_featured_order' => true,
			'meta_query'          => $meta_query,
		);
	}

	/**
	 * Applies featured-first, newest-first ordering without excluding posts that
	 * rely on the registered default false value instead of stored metadata.
	 *
	 * @param array<string, string> $clauses Query SQL clauses.
	 * @param \WP_Query             $query   Query object.
	 * @return array<string, string>
	 */
	public function apply_featured_first_ordering( array $clauses, \WP_Query $query ): array {
		if ( ! $query->get( 'youract_opportunity_featured_order' ) ) {
			return $clauses;
		}

		global $wpdb;

		$alias = 'youract_opportunity_featured_meta';
		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS {$alias} ON ({$wpdb->posts}.ID = {$alias}.post_id AND {$alias}.meta_key = 'act_opportunity_featured')";
		$clauses['orderby'] = "CAST(COALESCE({$alias}.meta_value, '0') AS UNSIGNED) DESC, {$wpdb->posts}.post_date DESC";

		return $clauses;
	}

	/**
	 * Appends a meta query while retaining existing constraints.
	 *
	 * @param \WP_Query               $query  Query object.
	 * @param array<int|string, mixed> $clause Meta clause.
	 * @return void
	 */
	private function append_meta_query( \WP_Query $query, array $clause ): void {
		$existing = $query->get( 'meta_query' );
		if ( empty( $existing ) ) {
			$query->set( 'meta_query', $clause );
			return;
		}

		$query->set(
			'meta_query',
			array(
				'relation' => 'AND',
				$existing,
				$clause,
			)
		);
	}

	/**
	 * Returns the stage clause for current Opportunities.
	 *
	 * @return array<int|string, mixed>
	 */
	private function current_stage_clause(): array {
		return array(
			'relation' => 'OR',
			array(
				'key'     => 'act_opportunity_stage',
				'compare' => 'NOT EXISTS',
			),
			$this->stage_clause( self::CURRENT_STAGES ),
		);
	}

	/**
	 * Returns a clause for explicit Opportunity stages.
	 *
	 * @param string[] $stages Allowed stages.
	 * @return array<string, mixed>
	 */
	private function stage_clause( array $stages ): array {
		return array(
			'key'     => 'act_opportunity_stage',
			'value'   => $stages,
			'compare' => 'IN',
		);
	}

	/**
	 * Returns a clause that excludes inactive Opportunities.
	 *
	 * @return array<int|string, mixed>
	 */
	private function non_inactive_stage_clause(): array {
		return array(
			'relation' => 'OR',
			array(
				'key'     => 'act_opportunity_stage',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => 'act_opportunity_stage',
				'value'   => 'inactive',
				'compare' => '!=',
			),
		);
	}
}

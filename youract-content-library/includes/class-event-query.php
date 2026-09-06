<?php
/**
 * Event query builder.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds Event list queries.
 */
class Event_Query {

	/**
	 * Builds args for [youract_events].
	 *
	 * @param string $scope    upcoming|past|all.
	 * @param int    $paged    Current page.
	 * @param int    $per_page Posts per page.
	 * @return array<string, mixed>
	 */
	public function build_events_query_args( string $scope, int $paged, int $per_page ): array {
		$scope = in_array( $scope, array( 'upcoming', 'past', 'all' ), true ) ? $scope : 'upcoming';
		$now   = time();

		$args = array(
			'post_type'           => 'act_event',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, $per_page ),
			'paged'               => max( 1, $paged ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => false,
			'meta_key'            => '_youract_event_start_utc',
			'orderby'             => 'meta_value_num',
		);

		if ( 'past' === $scope ) {
			$args['order']      = 'DESC';
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => '_youract_event_end_utc',
					'value'   => $now,
					'compare' => '<',
					'type'    => 'NUMERIC',
				),
				array(
					'relation' => 'AND',
					array(
						'relation' => 'OR',
						array(
							'key'     => '_youract_event_end_utc',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_youract_event_end_utc',
							'value'   => 0,
							'compare' => '=',
							'type'    => 'NUMERIC',
						),
					),
					array(
						'key'     => '_youract_event_start_utc',
						'value'   => $now,
						'compare' => '<',
						'type'    => 'NUMERIC',
					),
				),
			);
		} elseif ( 'all' === $scope ) {
			$args['order'] = 'ASC';
		} else {
			$args['order']      = 'ASC';
			$args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'relation' => 'OR',
					array(
						'key'     => '_youract_event_status',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_youract_event_status',
						'value'   => array( 'cancelled', 'completed' ),
						'compare' => 'NOT IN',
					),
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => '_youract_event_end_utc',
						'value'   => $now,
						'compare' => '>=',
						'type'    => 'NUMERIC',
					),
					array(
						'relation' => 'AND',
						array(
							'key'     => '_youract_event_end_utc',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_youract_event_start_utc',
							'value'   => $now,
							'compare' => '>=',
							'type'    => 'NUMERIC',
						),
					),
					array(
						'relation' => 'AND',
						array(
							'key'     => '_youract_event_end_utc',
							'value'   => 0,
							'compare' => '=',
							'type'    => 'NUMERIC',
						),
						array(
							'key'     => '_youract_event_start_utc',
							'value'   => $now,
							'compare' => '>=',
							'type'    => 'NUMERIC',
						),
					),
				),
			);
		}

		/**
		 * Filters Event query arguments.
		 *
		 * @param array<string, mixed> $args  Query args.
		 * @param string               $scope Active scope.
		 */
		return apply_filters( 'youract_event_query_args', $args, $scope );
	}

	/**
	 * Builds args for [youract_upcoming_events].
	 *
	 * @param int $limit Number of posts.
	 * @return array<string, mixed>
	 */
	public function build_upcoming_query_args( int $limit ): array {
		$args                 = $this->build_events_query_args( 'upcoming', 1, max( 1, $limit ) );
		$args['no_found_rows'] = true;

		/**
		 * Filters upcoming Event query arguments.
		 *
		 * @param array<string, mixed> $args Query args.
		 */
		return apply_filters( 'youract_upcoming_event_query_args', $args );
	}
}

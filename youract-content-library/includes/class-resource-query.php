<?php
/**
 * Resource query builder.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds query arguments for Resource listings.
 */
class Resource_Query {

	/**
	 * Returns normalized filter values from request.
	 *
	 * @return array<string, string>
	 */
	public function get_filters_from_request(): array {
		return array(
			'keyword'          => $this->request_string( 'youract_resource_keyword' ),
			'topic'            => $this->request_slug( 'youract_resource_topic' ),
			'geography'        => $this->request_slug( 'youract_resource_geography' ),
			'resource_type'    => $this->request_slug( 'youract_resource_type' ),
			'include_archived' => $this->request_string( 'youract_include_archived' ),
		);
	}

	/**
	 * Builds args for [youract_resources].
	 *
	 * @param array<string, string> $filters Resource filters.
	 * @param int                   $paged   Current page.
	 * @param int                   $per_page Posts per page.
	 * @return array<string, mixed>
	 */
	public function build_library_query_args( array $filters, int $paged, int $per_page ): array {
		$args = array(
			'post_type'           => 'act_resource',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, $per_page ),
			'paged'               => max( 1, $paged ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => false,
			'orderby'             => 'date',
			'order'               => 'DESC',
		);

		if ( '' !== ( $filters['keyword'] ?? '' ) ) {
			$args['s'] = $filters['keyword'];
		}

		$tax_query = array();

		if ( '' !== ( $filters['topic'] ?? '' ) ) {
			$tax_query[] = array(
				'taxonomy' => 'act_topic',
				'field'    => 'slug',
				'terms'    => $filters['topic'],
			);
		}

		if ( '' !== ( $filters['geography'] ?? '' ) ) {
			$tax_query[] = array(
				'taxonomy' => 'act_geography',
				'field'    => 'slug',
				'terms'    => $filters['geography'],
			);
		}

		if ( '' !== ( $filters['resource_type'] ?? '' ) ) {
			$tax_query[] = array(
				'taxonomy' => 'act_resource_type',
				'field'    => 'slug',
				'terms'    => $filters['resource_type'],
			);
		}

		if ( ! empty( $tax_query ) ) {
			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}
			$args['tax_query'] = $tax_query;
		}

		$args['meta_query'] = $this->resource_status_meta_query( $filters );

		/**
		 * Filters Resource library query arguments.
		 *
		 * @param array<string, mixed>  $args    Query args.
		 * @param array<string, string> $filters Active filters.
		 */
		return apply_filters( 'youract_resource_query_args', $args, $filters );
	}

	/**
	 * Builds args for featured resources.
	 *
	 * @param int $limit Number of resources.
	 * @return array<string, mixed>
	 */
	public function build_featured_query_args( int $limit ): array {
		$args = array(
			'post_type'           => 'act_resource',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, $limit ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'meta_query'          => array(
				'relation' => 'AND',
				array(
					'key'     => '_youract_featured_resource',
					'value'   => '1',
					'compare' => '=',
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => '_youract_resource_status',
						'value'   => 'active',
						'compare' => '=',
					),
					array(
						'key'     => '_youract_resource_status',
						'compare' => 'NOT EXISTS',
					),
				),
			),
			'orderby'             => 'date',
			'order'               => 'DESC',
		);

		/**
		 * Filters featured Resource query arguments.
		 *
		 * @param array<string, mixed> $args Query args.
		 */
		return apply_filters( 'youract_featured_resource_query_args', $args );
	}

	/**
	 * Builds meta query for archived-resource behavior.
	 *
	 * @param array<string, string> $filters Resource filters.
	 * @return array<int|string, array<string, string>|string>
	 */
	private function resource_status_meta_query( array $filters ): array {
		$include_archived = '1' === ( $filters['include_archived'] ?? '' ) && current_user_can( 'edit_posts' );

		if ( $include_archived ) {
			return array();
		}

		return array(
			'relation' => 'OR',
			array(
				'key'     => '_youract_resource_status',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_youract_resource_status',
				'value'   => 'archived',
				'compare' => '!=',
			),
		);
	}

	/**
	 * Retrieves a sanitized text request value.
	 *
	 * @param string $key Request key.
	 * @return string
	 */
	private function request_string( string $key ): string {
		if ( ! isset( $_GET[ $key ] ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( (string) $_GET[ $key ] ) );
	}

	/**
	 * Retrieves a sanitized slug request value.
	 *
	 * @param string $key Request key.
	 * @return string
	 */
	private function request_slug( string $key ): string {
		if ( ! isset( $_GET[ $key ] ) ) {
			return '';
		}

		return sanitize_title( wp_unslash( (string) $_GET[ $key ] ) );
	}
}

<?php
/**
 * Resource admin editing interface.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles Resource admin UI and saving.
 */
class Admin_Resource {

	/**
	 * Hooks registration.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'manage_edit-act_resource_columns', array( $this, 'set_columns' ) );
		add_action( 'manage_act_resource_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_filter( 'manage_edit-act_resource_sortable_columns', array( $this, 'set_sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'handle_sorting' ) );
	}

	/**
	 * Sets custom Resource columns.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function set_columns( array $columns ): array {
		$columns['act_resource_type']   = __( 'Resource Type', 'youract-content-library' );
		$columns['source_organization'] = __( 'Source', 'youract-content-library' );
		$columns['resource_status']     = __( 'Status', 'youract-content-library' );
		$columns['original_publication_date'] = __( 'Original Publication Date', 'youract-content-library' );
		$columns['last_reviewed']       = __( 'Last Reviewed', 'youract-content-library' );
		$columns['featured_resource']   = __( 'Featured', 'youract-content-library' );

		return $columns;
	}

	/**
	 * Renders custom Resource column values.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_column( string $column, int $post_id ): void {
		if ( 'act_resource_type' === $column ) {
			$terms = get_the_terms( $post_id, 'act_resource_type' );
			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				echo '&#8212;';
				return;
			}

			echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) );
			return;
		}

		if ( 'source_organization' === $column ) {
			$value = (string) get_post_meta( $post_id, 'youract_source_organization', true );
			echo '' !== $value ? esc_html( $value ) : '&#8212;';
			return;
		}

		if ( 'resource_status' === $column ) {
			$labels = array(
				'active'       => __( 'Active', 'youract-content-library' ),
				'needs-review' => __( 'Needs Review', 'youract-content-library' ),
				'archived'     => __( 'Archived', 'youract-content-library' ),
			);
			$value = (string) get_post_meta( $post_id, 'youract_resource_status', true );
			echo '' !== $value ? esc_html( $labels[ $value ] ?? $value ) : esc_html( $labels['active'] );
			return;
		}

		if ( 'last_reviewed' === $column ) {
			$value = (string) get_post_meta( $post_id, 'youract_last_reviewed', true );
			$display = Utils::format_resource_date( $value );
			echo '' !== $display ? esc_html( $display ) : '&#8212;';
			return;
		}

		if ( 'original_publication_date' === $column ) {
			$value   = (string) get_post_meta( $post_id, 'youract_original_publication_date', true );
			$display = Utils::format_resource_date( $value );
			echo '' !== $display ? esc_html( $display ) : '&#8212;';
			return;
		}

		if ( 'featured_resource' === $column ) {
			$value = (bool) get_post_meta( $post_id, 'youract_featured_resource', true );
			echo $value ? esc_html__( 'Yes', 'youract-content-library' ) : esc_html__( 'No', 'youract-content-library' );
		}
	}

	/**
	 * Declares sortable Resource columns.
	 *
	 * @param array<string, string> $columns Sortable columns.
	 * @return array<string, string>
	 */
	public function set_sortable_columns( array $columns ): array {
		$columns['source_organization'] = 'source_organization';
		$columns['resource_status']     = 'resource_status';
		$columns['original_publication_date'] = 'original_publication_date';
		$columns['last_reviewed']       = 'last_reviewed';
		$columns['featured_resource']   = 'featured_resource';

		return $columns;
	}

	/**
	 * Applies meta-based sorting for custom columns.
	 *
	 * @param \WP_Query $query Admin list query.
	 * @return void
	 */
	public function handle_sorting( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'act_resource' !== $query->get( 'post_type' ) ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		$map = array(
			'source_organization' => 'youract_source_organization',
			'resource_status'     => 'youract_resource_status',
			'last_reviewed'       => 'youract_last_reviewed',
			'featured_resource'   => 'youract_featured_resource',
		);

		if ( ! isset( $map[ $orderby ] ) ) {
			return;
		}

		$query->set( 'meta_key', $map[ $orderby ] );
		$query->set( 'orderby', 'meta_value' );

		if ( 'featured_resource' === $orderby ) {
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/**
	 * Enqueues admin CSS.
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		$screen = get_current_screen();

		if ( ! $screen || 'act_resource' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			'youract-content-library-admin',
			YOURACT_CONTENT_LIBRARY_URL . 'assets/css/admin.css',
			array(),
			YOURACT_CONTENT_LIBRARY_VERSION
		);
	}

}

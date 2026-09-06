<?php
/**
 * Shortcode handlers.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders plugin shortcodes.
 */
class Shortcodes {

	/**
	 * Resource query service.
	 *
	 * @var Resource_Query
	 */
	private Resource_Query $resource_query;

	/**
	 * Event query service.
	 *
	 * @var Event_Query
	 */
	private Event_Query $event_query;

	/**
	 * Template renderer.
	 *
	 * @var object
	 */
	private $renderer;

	/**
	 * Instance counter for unique markup IDs.
	 *
	 * @var int
	 */
	private static int $instance_counter = 0;

	/**
	 * Constructor.
	 *
	 * @param Resource_Query $resource_query Resource query object.
	 * @param Event_Query    $event_query Event query object.
	 * @param object         $renderer Renderer.
	 */
	public function __construct( Resource_Query $resource_query, Event_Query $event_query, $renderer ) {
		$this->resource_query = $resource_query;
		$this->event_query    = $event_query;
		$this->renderer       = $renderer;
	}

	/**
	 * Registers all shortcodes.
	 *
	 * @return void
	 */
	public function register(): void {
		add_shortcode( 'youract_resources', array( $this, 'render_resources_shortcode' ) );
		add_shortcode( 'youract_events', array( $this, 'render_events_shortcode' ) );
		add_shortcode( 'youract_featured_resources', array( $this, 'render_featured_resources_shortcode' ) );
		add_shortcode( 'youract_upcoming_events', array( $this, 'render_upcoming_events_shortcode' ) );
	}

	/**
	 * Renders the Resource library shortcode.
	 *
	 * @param array<string, string> $atts Shortcode attributes.
	 * @return string
	 */
	public function render_resources_shortcode( array $atts ): string {
		$atts = shortcode_atts(
			array(
				'per_page' => '10',
			),
			$atts,
			'youract_resources'
		);

		$instance_id = $this->next_instance_id( 'resources' );
		$paged       = $this->request_page( 'youract_resources_page' );
		$filters     = $this->resource_query->get_filters_from_request();
		$query_args  = $this->resource_query->build_library_query_args( $filters, $paged, (int) $atts['per_page'] );
		$query       = new \WP_Query( $query_args );

		do_action( 'youract_before_resources_loop', $query_args, $filters, $query );

		ob_start();
		?>
		<section class="youract-resource-library" aria-label="<?php esc_attr_e( 'Resource library', 'youract-content-library' ); ?>">
			<?php $this->render_resource_filters_form( $filters, $instance_id ); ?>
			<p class="youract-result-count" role="status" aria-live="polite">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: number of resources. */
						_n( '%d resource found', '%d resources found', (int) $query->found_posts, 'youract-content-library' ),
						(int) $query->found_posts
					)
				);
				?>
			</p>
			<?php if ( $query->have_posts() ) : ?>
				<div class="youract-card-grid">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						echo $this->renderer->render_resource_card( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					endwhile;
					?>
				</div>
				<?php echo $this->render_pagination( $query, 'youract_resources_page' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php else : ?>
				<p><?php esc_html_e( 'No resources matched the current filters.', 'youract-content-library' ); ?></p>
			<?php endif; ?>
		</section>
		<?php
		wp_reset_postdata();
		do_action( 'youract_after_resources_loop', $query_args, $filters, $query );

		return (string) ob_get_clean();
	}

	/**
	 * Renders the Event list shortcode.
	 *
	 * @param array<string, string> $atts Shortcode attributes.
	 * @return string
	 */
	public function render_events_shortcode( array $atts ): string {
		$atts = shortcode_atts(
			array(
				'scope'    => 'upcoming',
				'per_page' => '10',
			),
			$atts,
			'youract_events'
		);

		$scope       = sanitize_key( $atts['scope'] );
		$instance_id = $this->next_instance_id( 'events' );
		$paged       = $this->request_page( 'youract_events_page' );
		$query_args  = $this->event_query->build_events_query_args( $scope, $paged, (int) $atts['per_page'] );
		$query       = new \WP_Query( $query_args );

		do_action( 'youract_before_events_loop', $query_args, $scope, $query );

		ob_start();
		?>
		<section class="youract-event-list" aria-label="<?php esc_attr_e( 'Event list', 'youract-content-library' ); ?>" id="<?php echo esc_attr( $instance_id ); ?>">
			<p class="youract-result-count" role="status" aria-live="polite">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: number of events. */
						_n( '%d event found', '%d events found', (int) $query->found_posts, 'youract-content-library' ),
						(int) $query->found_posts
					)
				);
				?>
			</p>
			<?php if ( $query->have_posts() ) : ?>
				<div class="youract-card-grid">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						echo $this->renderer->render_event_card( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					endwhile;
					?>
				</div>
				<?php echo $this->render_pagination( $query, 'youract_events_page' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php else : ?>
				<p><?php esc_html_e( 'No events matched this scope.', 'youract-content-library' ); ?></p>
			<?php endif; ?>
		</section>
		<?php
		wp_reset_postdata();
		do_action( 'youract_after_events_loop', $query_args, $scope, $query );

		return (string) ob_get_clean();
	}

	/**
	 * Renders featured resources.
	 *
	 * @param array<string, string> $atts Shortcode attributes.
	 * @return string
	 */
	public function render_featured_resources_shortcode( array $atts ): string {
		$atts = shortcode_atts(
			array(
				'limit' => '3',
			),
			$atts,
			'youract_featured_resources'
		);

		$query = new \WP_Query( $this->resource_query->build_featured_query_args( (int) $atts['limit'] ) );

		ob_start();
		?>
		<section class="youract-featured-resources" aria-label="<?php esc_attr_e( 'Featured resources', 'youract-content-library' ); ?>">
			<?php if ( $query->have_posts() ) : ?>
				<div class="youract-card-grid">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						echo $this->renderer->render_resource_card( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					endwhile;
					?>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'No featured resources are currently available.', 'youract-content-library' ); ?></p>
			<?php endif; ?>
		</section>
		<?php
		wp_reset_postdata();

		return (string) ob_get_clean();
	}

	/**
	 * Renders upcoming events summary.
	 *
	 * @param array<string, string> $atts Shortcode attributes.
	 * @return string
	 */
	public function render_upcoming_events_shortcode( array $atts ): string {
		$atts = shortcode_atts(
			array(
				'limit' => '3',
			),
			$atts,
			'youract_upcoming_events'
		);

		$query = new \WP_Query( $this->event_query->build_upcoming_query_args( (int) $atts['limit'] ) );

		ob_start();
		?>
		<section class="youract-upcoming-events" aria-label="<?php esc_attr_e( 'Upcoming events', 'youract-content-library' ); ?>">
			<?php if ( $query->have_posts() ) : ?>
				<div class="youract-card-grid">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						echo $this->renderer->render_event_card( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					endwhile;
					?>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'No upcoming events are currently available.', 'youract-content-library' ); ?></p>
			<?php endif; ?>
		</section>
		<?php
		wp_reset_postdata();

		return (string) ob_get_clean();
	}

	/**
	 * Renders Resource GET filter controls.
	 *
	 * @param array<string, string> $filters     Active filters.
	 * @param string                $instance_id Unique instance id.
	 * @return void
	 */
	private function render_resource_filters_form( array $filters, string $instance_id ): void {
		$topic_id         = $instance_id . '-topic';
		$geography_id     = $instance_id . '-geography';
		$resource_type_id = $instance_id . '-resource-type';
		$keyword_id       = $instance_id . '-keyword';

		$clear_url = remove_query_arg(
			array(
				'youract_resource_keyword',
				'youract_resource_topic',
				'youract_resource_geography',
				'youract_resource_type',
				'youract_resources_page',
				'youract_include_archived',
			)
		);
		?>
		<form class="youract-filter-form" method="get" action="<?php echo esc_url( get_permalink() ); ?>">
			<div class="youract-filter-grid">
				<p>
					<label for="<?php echo esc_attr( $keyword_id ); ?>"><strong><?php esc_html_e( 'Keyword', 'youract-content-library' ); ?></strong></label><br />
					<input id="<?php echo esc_attr( $keyword_id ); ?>" type="search" name="youract_resource_keyword" value="<?php echo esc_attr( $filters['keyword'] ?? '' ); ?>" />
				</p>
				<p>
					<label for="<?php echo esc_attr( $topic_id ); ?>"><strong><?php esc_html_e( 'Topic', 'youract-content-library' ); ?></strong></label><br />
					<?php
					echo wp_dropdown_categories(
						array(
							'taxonomy'         => 'act_topic',
							'show_option_all'  => __( 'All topics', 'youract-content-library' ),
							'name'             => 'youract_resource_topic',
							'id'               => $topic_id,
							'orderby'          => 'name',
							'hide_empty'       => false,
							'selected'         => $filters['topic'] ?? '',
							'value_field'      => 'slug',
							'echo'             => 0,
						)
					); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</p>
				<p>
					<label for="<?php echo esc_attr( $geography_id ); ?>"><strong><?php esc_html_e( 'Geographic scope', 'youract-content-library' ); ?></strong></label><br />
					<?php
					echo wp_dropdown_categories(
						array(
							'taxonomy'         => 'act_geography',
							'show_option_all'  => __( 'All locations', 'youract-content-library' ),
							'name'             => 'youract_resource_geography',
							'id'               => $geography_id,
							'orderby'          => 'name',
							'hide_empty'       => false,
							'selected'         => $filters['geography'] ?? '',
							'value_field'      => 'slug',
							'echo'             => 0,
						)
					); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</p>
				<p>
					<label for="<?php echo esc_attr( $resource_type_id ); ?>"><strong><?php esc_html_e( 'Resource type', 'youract-content-library' ); ?></strong></label><br />
					<?php
					echo wp_dropdown_categories(
						array(
							'taxonomy'         => 'act_resource_type',
							'show_option_all'  => __( 'All resource types', 'youract-content-library' ),
							'name'             => 'youract_resource_type',
							'id'               => $resource_type_id,
							'orderby'          => 'name',
							'hide_empty'       => false,
							'selected'         => $filters['resource_type'] ?? '',
							'value_field'      => 'slug',
							'echo'             => 0,
						)
					); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</p>
			</div>
			<?php if ( current_user_can( 'edit_posts' ) ) : ?>
				<p>
					<label for="<?php echo esc_attr( $instance_id . '-include-archived' ); ?>">
						<input id="<?php echo esc_attr( $instance_id . '-include-archived' ); ?>" type="checkbox" name="youract_include_archived" value="1" <?php checked( '1', $filters['include_archived'] ?? '' ); ?> />
						<?php esc_html_e( 'Include archived resources', 'youract-content-library' ); ?>
					</label>
				</p>
			<?php endif; ?>
			<p class="youract-filter-actions">
				<button type="submit"><?php esc_html_e( 'Apply filters', 'youract-content-library' ); ?></button>
				<a href="<?php echo esc_url( $clear_url ); ?>"><?php esc_html_e( 'Clear filters', 'youract-content-library' ); ?></a>
			</p>
		</form>
		<?php
	}

	/**
	 * Renders pagination links for a query.
	 *
	 * @param \WP_Query $query        Query object.
	 * @param string    $page_var_name Prefixed page query arg.
	 * @return string
	 */
	private function render_pagination( \WP_Query $query, string $page_var_name ): string {
		if ( $query->max_num_pages < 2 ) {
			return '';
		}

		$base_url = remove_query_arg( $page_var_name );
		$links    = paginate_links(
			array(
				'base'      => add_query_arg( $page_var_name, '%#%', $base_url ),
				'format'    => '',
				'current'   => max( 1, $this->request_page( $page_var_name ) ),
				'total'     => (int) $query->max_num_pages,
				'prev_text' => __( 'Previous', 'youract-content-library' ),
				'next_text' => __( 'Next', 'youract-content-library' ),
				'type'      => 'list',
			)
		);

		if ( ! is_string( $links ) ) {
			return '';
		}

		return '<nav class="youract-pagination" aria-label="' . esc_attr__( 'Pagination', 'youract-content-library' ) . '">' . $links . '</nav>';
	}

	/**
	 * Retrieves requested page number from query string.
	 *
	 * @param string $key Query arg.
	 * @return int
	 */
	private function request_page( string $key ): int {
		if ( ! isset( $_GET[ $key ] ) ) {
			return 1;
		}

		return max( 1, absint( wp_unslash( $_GET[ $key ] ) ) );
	}

	/**
	 * Generates a unique shortcode instance id.
	 *
	 * @param string $prefix Identifier prefix.
	 * @return string
	 */
	private function next_instance_id( string $prefix ): string {
		self::$instance_counter++;
		return 'youract-' . sanitize_key( $prefix ) . '-' . self::$instance_counter;
	}
}

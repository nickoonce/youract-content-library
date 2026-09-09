<?php
/**
 * Front-end rendering utilities.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

use DateTimeImmutable;
use DateTimeZone;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders cards, templates, and singular detail sections.
 */
class Renderer {

	/**
	 * Counter for unique card IDs.
	 *
	 * @var int
	 */
	private static int $card_counter = 0;

	/**
	 * Counter for unique details heading IDs.
	 *
	 * @var int
	 */
	private static int $details_counter = 0;

	/**
	 * Registers runtime hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'the_content', array( $this, 'append_singular_details' ) );
	}

	/**
	 * Enqueues frontend CSS.
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		wp_enqueue_style(
			'youract-content-library-frontend',
			YOURACT_CONTENT_LIBRARY_URL . 'assets/css/frontend.css',
			array(),
			YOURACT_CONTENT_LIBRARY_VERSION
		);
	}

	/**
	 * Renders one Resource card.
	 *
	 * @param int $post_id Resource post ID.
	 * @return string
	 */
	public function render_resource_card( int $post_id ): string {
		$heading_id = $this->next_heading_id( 'resource' );

		$data = array(
			'post_id'              => $post_id,
			'heading_id'           => $heading_id,
			'title'                => get_the_title( $post_id ),
			'permalink'            => get_permalink( $post_id ),
			'excerpt'              => has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : '',
			'resource_summary'     => (string) get_post_meta( $post_id, '_youract_resource_summary', true ),
			'source_organization'  => (string) get_post_meta( $post_id, '_youract_source_organization', true ),
			'external_url'         => (string) get_post_meta( $post_id, '_youract_external_url', true ),
			'last_reviewed'        => (string) get_post_meta( $post_id, '_youract_last_reviewed', true ),
			'topics'               => $this->term_names( $post_id, 'act_topic' ),
			'geographies'          => $this->term_names( $post_id, 'act_geography' ),
			'resource_types'       => $this->term_names( $post_id, 'act_resource_type' ),
		);

		/**
		 * Filters Resource card data before rendering.
		 *
		 * @param array<string, mixed> $data Resource card data.
		 * @param int                  $post_id Resource ID.
		 */
		$data = apply_filters( 'youract_resource_card_data', $data, $post_id );

		$html = $this->render_template( 'resource-card.php', array( 'resource' => $data ) );

		/**
		 * Filters full Resource card output.
		 *
		 * @param string              $html Rendered HTML.
		 * @param array<string, mixed> $data Resource data.
		 */
		return (string) apply_filters( 'youract_resource_card_html', $html, $data );
	}

	/**
	 * Renders one Event card.
	 *
	 * @param int $post_id Event post ID.
	 * @return string
	 */
	public function render_event_card( int $post_id ): string {
		$heading_id = $this->next_heading_id( 'event' );

		$timezone = (string) get_post_meta( $post_id, '_youract_event_timezone', true );
		if ( ! Utils::is_valid_timezone( $timezone ) ) {
			$timezone = Utils::get_default_timezone();
		}

		$timing = $this->event_timing_data(
			(int) get_post_meta( $post_id, '_youract_event_start_utc', true ),
			(int) get_post_meta( $post_id, '_youract_event_end_utc', true ),
			$timezone,
			(bool) get_post_meta( $post_id, '_youract_event_all_day', true )
		);

		$status = (string) get_post_meta( $post_id, '_youract_event_status', true );
		$format = (string) get_post_meta( $post_id, '_youract_event_format', true );

		$data = array(
			'post_id'         => $post_id,
			'heading_id'      => $heading_id,
			'title'           => get_the_title( $post_id ),
			'permalink'       => get_permalink( $post_id ),
			'excerpt'         => has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : '',
			'status'          => $status,
			'status_label'    => $this->event_status_label( $status ),
			'format'          => $format,
			'format_label'    => $this->event_format_label( $format ),
			'event_url'       => (string) get_post_meta( $post_id, '_youract_event_url', true ),
			'timezone'        => $timezone,
			'timing'          => $timing,
			'topics'          => $this->term_names( $post_id, 'act_topic' ),
			'geographies'     => $this->term_names( $post_id, 'act_geography' ),
			'event_types'     => $this->term_names( $post_id, 'act_event_type' ),
		);

		/**
		 * Filters Event card data before rendering.
		 *
		 * @param array<string, mixed> $data Event card data.
		 * @param int                  $post_id Event ID.
		 */
		$data = apply_filters( 'youract_event_card_data', $data, $post_id );

		$html = $this->render_template( 'event-card.php', array( 'event' => $data ) );

		/**
		 * Filters full Event card output.
		 *
		 * @param string               $html Rendered HTML.
		 * @param array<string, mixed> $data Event data.
		 */
		return (string) apply_filters( 'youract_event_card_html', $html, $data );
	}

	/**
	 * Appends singular detail templates to main content.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function append_singular_details( string $content ): string {
		if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		if ( is_singular( 'act_resource' ) ) {
			$enabled = (bool) apply_filters( 'youract_resource_details_enabled', true, get_the_ID() );
			if ( ! $enabled ) {
				return $content;
			}

			do_action( 'youract_before_resource_details', get_the_ID() );

			$details = $this->build_resource_details_data( get_the_ID() );
			$html    = $this->render_template( 'resource-details.php', array( 'resource' => $details ) );

			/**
			 * Filters singular Resource details HTML.
			 *
			 * @param string               $html    Details HTML.
			 * @param array<string, mixed> $details Details data.
			 */
			$html = (string) apply_filters( 'youract_resource_details_html', $html, $details );
			do_action( 'youract_after_resource_details', get_the_ID(), $details, $html );
			return $content . $html;
		}

		if ( is_singular( 'act_event' ) ) {
			if ( has_block( 'youract/event-details', $content ) || $this->template_has_event_details_block() ) {
				return $content;
			}

			$enabled = (bool) apply_filters( 'youract_event_details_enabled', true, get_the_ID() );
			if ( ! $enabled ) {
				return $content;
			}

			do_action( 'youract_before_event_details', get_the_ID() );
			$details = $this->get_event_details_data( get_the_ID() );
			$html    = $this->render_event_details( get_the_ID(), $details );
			do_action( 'youract_after_event_details', get_the_ID(), $details, $html );
			return $content . $html;
		}

		return $content;
	}

	/**
	 * Renders Event details for one Event post.
	 *
	 * @param int                        $post_id Event post ID.
	 * @param array<string, mixed>|null $details Optional prepared details.
	 * @return string
	 */
	public function render_event_details( int $post_id, ?array $details = null ): string {
		if ( null === $details ) {
			$details = $this->get_event_details_data( $post_id );
		}

		$html    = $this->render_template( 'event-details.php', array( 'event' => $details ) );

		/**
		 * Filters singular Event details HTML.
		 *
		 * @param string               $html    Details HTML.
		 * @param array<string, mixed> $details Details data.
		 */
		return (string) apply_filters( 'youract_event_details_html', $html, $details );
	}

	/**
	 * Returns Event details data for one Event post.
	 *
	 * @param int $post_id Event post ID.
	 * @return array<string, mixed>
	 */
	public function get_event_details_data( int $post_id ): array {
		return $this->build_event_details_data( $post_id );
	}

	/**
	 * Template helper for theme/plugin override loading.
	 *
	 * @param string               $template_name Template file name.
	 * @param array<string, mixed> $args          Template variables.
	 * @return string
	 */
	public function render_template( string $template_name, array $args = array() ): string {
		$template_path = $this->locate_template( $template_name );
		if ( '' === $template_path ) {
			return '';
		}

		ob_start();
		extract( $args, EXTR_SKIP );
		require $template_path;
		return (string) ob_get_clean();
	}

	/**
	 * Locates a template file in theme or plugin.
	 *
	 * @param string $template_name Template name.
	 * @return string
	 */
	public function locate_template( string $template_name ): string {
		$template_name = ltrim( $template_name, '/' );

		$theme_candidates = apply_filters(
			'youract_template_locations',
			array(
				'youract-content-library/' . $template_name,
			)
		);

		$theme_template = locate_template( $theme_candidates );
		if ( '' !== $theme_template ) {
			return $theme_template;
		}

		$plugin_template = YOURACT_CONTENT_LIBRARY_PATH . 'templates/' . $template_name;
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}

		return '';
	}

	/**
	 * Creates formatted timing values for templates.
	 *
	 * @param int    $start_utc Start timestamp in UTC.
	 * @param int    $end_utc   End timestamp in UTC.
	 * @param string $timezone  Timezone.
	 * @param bool   $all_day   All-day marker.
	 * @return array<string, string|bool>
	 */
	public function event_timing_data( int $start_utc, int $end_utc, string $timezone, bool $all_day ): array {
		$tz = Utils::is_valid_timezone( $timezone ) ? $timezone : Utils::get_default_timezone();
		$date_time_format = (string) apply_filters( 'youract_datetime_format', get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
		$date_only_format = (string) apply_filters( 'youract_date_format', get_option( 'date_format' ) );
		$time_only_format = (string) apply_filters( 'youract_time_format', get_option( 'time_format' ) );
		$pst_timezone = new DateTimeZone( 'America/Los_Angeles' );

		$result = array(
			'start_iso'    => '',
			'end_iso'      => '',
			'start_text'   => '',
			'end_text'     => '',
			'start_date_text' => '',
			'start_time_text' => '',
			'end_time_text'   => '',
			'is_same_day'     => false,
			'timezone'     => $tz,
			'timezone_abbr'=> '',
			'pst_note'     => '',
			'all_day'      => $all_day,
		);

		if ( $start_utc <= 0 ) {
			return $result;
		}

		$local_tz = Utils::get_timezone_object( $tz );
		$start_dt = ( new DateTimeImmutable( '@' . $start_utc ) )->setTimezone( $local_tz );

		$result['start_iso']     = $start_dt->format( DATE_ATOM );
		$result['start_text']    = wp_date( $date_time_format, $start_utc, $local_tz );
		$result['start_date_text'] = wp_date( $date_only_format, $start_utc, $local_tz );
		$result['start_time_text'] = wp_date( $time_only_format, $start_utc, $local_tz );
		$result['timezone_abbr'] = $start_dt->format( 'T' );
		$pst_start_text = wp_date( $date_time_format, $start_utc, $pst_timezone );

		if ( $all_day ) {
			$result['start_text'] = wp_date( $date_only_format, $start_utc, $local_tz );
			$pst_start_text       = wp_date( $date_only_format, $start_utc, $pst_timezone );
		}

		if ( $end_utc > 0 ) {
			$end_dt             = ( new DateTimeImmutable( '@' . $end_utc ) )->setTimezone( $local_tz );
			$result['end_iso']  = $end_dt->format( DATE_ATOM );
			$result['end_text'] = wp_date( $date_time_format, $end_utc, $local_tz );
			$result['end_time_text'] = wp_date( $time_only_format, $end_utc, $local_tz );
			$result['is_same_day'] = $start_dt->format( 'Y-m-d' ) === $end_dt->format( 'Y-m-d' );
			$pst_end_dt           = ( new DateTimeImmutable( '@' . $end_utc ) )->setTimezone( $pst_timezone );
			$pst_same_day         = $start_dt->setTimezone( $pst_timezone )->format( 'Y-m-d' ) === $pst_end_dt->format( 'Y-m-d' );
			$pst_end_text         = wp_date( $date_time_format, $end_utc, $pst_timezone );

			if ( $all_day ) {
				$result['end_text'] = wp_date( $date_only_format, $end_utc, $local_tz );
				$result['end_time_text'] = '';
				$pst_end_text = wp_date( $date_only_format, $end_utc, $pst_timezone );
				$result['pst_note'] = $pst_start_text === $pst_end_text ? $pst_start_text : $pst_start_text . ' to ' . $pst_end_text;
			} elseif ( $pst_same_day ) {
				$result['pst_note'] = wp_date( $date_only_format, $start_utc, $pst_timezone ) . ' ' . wp_date( $time_only_format, $start_utc, $pst_timezone ) . ' to ' . wp_date( $time_only_format, $end_utc, $pst_timezone );
			} else {
				$result['pst_note'] = $pst_start_text . ' to ' . $pst_end_text;
			}
		} else {
			$result['pst_note'] = $pst_start_text;
		}

		return $result;
	}

	/**
	 * Builds singular Resource details array.
	 *
	 * @param int $post_id Resource ID.
	 * @return array<string, mixed>
	 */
	private function build_resource_details_data( int $post_id ): array {
		$data = array(
			'post_id'              => $post_id,
			'source_organization'  => (string) get_post_meta( $post_id, '_youract_source_organization', true ),
			'external_url'         => (string) get_post_meta( $post_id, '_youract_external_url', true ),
			'access_notes'         => (string) get_post_meta( $post_id, '_youract_access_notes', true ),
			'last_reviewed'        => (string) get_post_meta( $post_id, '_youract_last_reviewed', true ),
			'resource_status'      => (string) get_post_meta( $post_id, '_youract_resource_status', true ),
			'topics'               => $this->term_names( $post_id, 'act_topic' ),
			'geographies'          => $this->term_names( $post_id, 'act_geography' ),
			'resource_types'       => $this->term_names( $post_id, 'act_resource_type' ),
		);

		return apply_filters( 'youract_resource_details_data', $data, $post_id );
	}

	/**
	 * Builds singular Event details array.
	 *
	 * @param int $post_id Event ID.
	 * @return array<string, mixed>
	 */
	private function build_event_details_data( int $post_id ): array {
		$timezone = (string) get_post_meta( $post_id, '_youract_event_timezone', true );
		if ( ! Utils::is_valid_timezone( $timezone ) ) {
			$timezone = Utils::get_default_timezone();
		}

		$status = (string) get_post_meta( $post_id, '_youract_event_status', true );
		$timing = $this->event_timing_data(
			(int) get_post_meta( $post_id, '_youract_event_start_utc', true ),
			(int) get_post_meta( $post_id, '_youract_event_end_utc', true ),
			$timezone,
			(bool) get_post_meta( $post_id, '_youract_event_all_day', true )
		);

		$disclaimer = __( 'This event is shared for community information. Please confirm current details with the event host before attending.', 'youract-content-library' );
		$disclaimer = apply_filters( 'youract_external_event_disclaimer', $disclaimer, $post_id );

		$data = array(
			'post_id'                    => $post_id,
			'heading_id'                 => $this->next_details_heading_id( $post_id ),
			'status'                     => $status,
			'status_label'               => $this->event_status_label( $status ),
			'timing'                     => $timing,
			'timezone'                   => $timezone,
			'format'                     => (string) get_post_meta( $post_id, '_youract_event_format', true ),
			'format_label'               => $this->event_format_label( (string) get_post_meta( $post_id, '_youract_event_format', true ) ),
			'registration_deadline_utc'  => (int) get_post_meta( $post_id, '_youract_registration_deadline_utc', true ),
			'event_url'                  => (string) get_post_meta( $post_id, '_youract_event_url', true ),
			'event_types'                => $this->term_names( $post_id, 'act_event_type' ),
			'topics'                     => $this->term_names( $post_id, 'act_topic' ),
			'geographies'                => $this->term_names( $post_id, 'act_geography' ),
			'disclaimer'                 => $disclaimer,
		);

		return apply_filters( 'youract_event_details_data', $data, $post_id );
	}

	/**
	 * Returns term names for a taxonomy.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $taxonomy Taxonomy.
	 * @return string[]
	 */
	private function term_names( int $post_id, string $taxonomy ): array {
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return array();
		}

		return wp_list_pluck( $terms, 'name' );
	}

	/**
	 * Human-readable Event status label.
	 *
	 * @param string $status Stored status key.
	 * @return string
	 */
	public function event_status_label( string $status ): string {
		$map = array(
			'scheduled' => __( 'Scheduled', 'youract-content-library' ),
			'postponed' => __( 'Postponed', 'youract-content-library' ),
			'cancelled' => __( 'Cancelled', 'youract-content-library' ),
			'completed' => __( 'Completed', 'youract-content-library' ),
		);

		return $map[ $status ] ?? __( 'Scheduled', 'youract-content-library' );
	}

	/**
	 * Human-readable Event format label.
	 *
	 * @param string $format Stored format key.
	 * @return string
	 */
	public function event_format_label( string $format ): string {
		$map = array(
			'in-person' => __( 'In-person', 'youract-content-library' ),
			'online'    => __( 'Online', 'youract-content-library' ),
			'hybrid'    => __( 'Hybrid', 'youract-content-library' ),
		);

		return $map[ $format ] ?? __( 'In-person', 'youract-content-library' );
	}

	/**
	 * Returns a unique heading ID for card markup.
	 *
	 * @param string $prefix ID prefix.
	 * @return string
	 */
	private function next_heading_id( string $prefix ): string {
		self::$card_counter++;
		return 'youract-' . sanitize_key( $prefix ) . '-card-title-' . self::$card_counter;
	}

	/**
	 * Returns a unique heading ID for details markup.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private function next_details_heading_id( int $post_id ): string {
		self::$details_counter++;
		return 'youract-event-details-heading-' . max( 0, $post_id ) . '-' . self::$details_counter;
	}

	/**
	 * Detects whether the active block template already includes the Event Details block.
	 *
	 * @return bool
	 */
	private function template_has_event_details_block(): bool {
		global $_wp_current_template_content;

		if ( ! is_string( $_wp_current_template_content ) || '' === $_wp_current_template_content ) {
			return false;
		}

		return false !== strpos( $_wp_current_template_content, 'wp:youract/event-details' );
	}
}

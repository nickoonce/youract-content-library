<?php
/**
 * Event structured data output.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

use DateTimeImmutable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs JSON-LD for singular Events.
 */
class Structured_Data {

	/**
	 * Registers runtime hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_head', array( $this, 'output_event_json_ld' ) );
		add_action( 'wp_head', array( $this, 'output_resource_json_ld' ) );
	}

	/**
	 * Outputs JSON-LD for a singular Resource.
	 *
	 * @return void
	 */
	public function output_resource_json_ld(): void {
		if ( ! is_singular( 'act_resource' ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return;
		}

		$data = $this->build_resource_json_ld( $post_id );
		if ( empty( $data ) ) {
			return;
		}

		$data = apply_filters( 'youract_resource_json_ld_data', $data, $post_id );
		if ( empty( $data ) || ! is_array( $data ) ) {
			return;
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
	}

	/**
	 * Outputs JSON-LD when an Event has sufficient data.
	 *
	 * @return void
	 */
	public function output_event_json_ld(): void {
		if ( ! is_singular( 'act_event' ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return;
		}

		$enabled = (bool) apply_filters( 'youract_event_json_ld_enabled', true, $post_id );
		if ( ! $enabled ) {
			return;
		}

		$data = $this->build_event_json_ld( $post_id );
		if ( empty( $data ) ) {
			return;
		}

		$data = apply_filters( 'youract_event_json_ld_data', $data, $post_id );
		if ( empty( $data ) || ! is_array( $data ) ) {
			return;
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
	}

	/**
	 * Builds Event JSON-LD array.
	 *
	 * @param int $post_id Event post ID.
	 * @return array<string, mixed>
	 */
	private function build_event_json_ld( int $post_id ): array {
		$title     = get_the_title( $post_id );
		$start_utc = (int) get_post_meta( $post_id, '_youract_event_start_utc', true );
		$end_utc   = (int) get_post_meta( $post_id, '_youract_event_end_utc', true );
		$timezone  = Utils::get_event_timezone();
		$status    = (string) get_post_meta( $post_id, '_youract_event_status', true );
		$format    = (string) get_post_meta( $post_id, '_youract_event_format', true );

		if ( '' === $title || $start_utc <= 0 ) {
			return array();
		}

		$tz_obj     = Utils::get_timezone_object( $timezone );
		$start_date = ( new DateTimeImmutable( '@' . $start_utc ) )->setTimezone( $tz_obj )->format( DATE_ATOM );
		$end_date   = '';

		if ( $end_utc > 0 ) {
			$end_date = ( new DateTimeImmutable( '@' . $end_utc ) )->setTimezone( $tz_obj )->format( DATE_ATOM );
		}

		$data = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'Event',
			'name'       => wp_strip_all_tags( $title ),
			'url'        => get_permalink( $post_id ),
			'startDate'  => $start_date,
		);

		$description = wp_strip_all_tags( has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : (string) get_post_field( 'post_content', $post_id ) );
		if ( '' !== trim( $description ) ) {
			$data['description'] = $description;
		}

		if ( '' !== $end_date ) {
			$data['endDate'] = $end_date;
		}

		$event_status = $this->schema_event_status( $status );
		if ( '' !== $event_status ) {
			$data['eventStatus'] = $event_status;
		}

		$attendance_mode = $this->schema_attendance_mode( $format );
		if ( '' !== $attendance_mode ) {
			$data['eventAttendanceMode'] = $attendance_mode;
		}

		$location = $this->schema_location( $post_id, $format );
		if ( ! empty( $location ) ) {
			$data['location'] = $location;
		}

		return $data;
	}

	/**
	 * Builds Resource JSON-LD array.
	 *
	 * @param int $post_id Resource post ID.
	 * @return array<string, mixed>
	 */
	private function build_resource_json_ld( int $post_id ): array {
		$title       = get_the_title( $post_id );
		$permalink   = get_permalink( $post_id );
		$external_url = (string) get_post_meta( $post_id, 'youract_external_url', true );
		$source       = (string) get_post_meta( $post_id, 'youract_source_organization', true );
		$author       = (string) get_post_meta( $post_id, 'youract_original_author', true );
		$published    = Utils::normalize_resource_date( (string) get_post_meta( $post_id, 'youract_original_publication_date', true ) );
		$reviewed     = Utils::normalize_resource_date( (string) get_post_meta( $post_id, 'youract_last_reviewed', true ) );

		if ( '' === $title ) {
			return array();
		}

		$data = array(
			'@context' => 'https://schema.org',
			'@type'    => 'CreativeWork',
			'name'     => wp_strip_all_tags( $title ),
			'url'      => '' !== $external_url ? $external_url : $permalink,
		);

		$description = wp_strip_all_tags( has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : '' );
		if ( '' !== trim( $description ) ) {
			$data['description'] = $description;
		}

		if ( '' !== $permalink && '' !== $external_url ) {
			$data['mainEntityOfPage'] = $permalink;
		}

		if ( '' !== $source ) {
			$data['publisher'] = array(
				'@type' => 'Organization',
				'name'  => $source,
			);
		}

		if ( '' !== $author ) {
			$data['author'] = array(
				'@type' => 'Person',
				'name'  => $author,
			);
		}

		if ( '' !== $published ) {
			$data['datePublished'] = $published;
		}

		if ( '' !== $reviewed ) {
			$data['dateModified'] = $reviewed;
		}

		$keywords = array_merge(
			$this->resource_term_names( $post_id, 'act_topic' ),
			$this->resource_term_names( $post_id, 'act_geography' ),
			$this->resource_term_names( $post_id, 'act_resource_type' )
		);
		if ( ! empty( $keywords ) ) {
			$data['keywords'] = implode( ', ', $keywords );
		}

		return $data;
	}

	/**
	 * Returns term names for Resource structured data.
	 *
	 * @param int    $post_id  Resource post ID.
	 * @param string $taxonomy Taxonomy name.
	 * @return string[]
	 */
	private function resource_term_names( int $post_id, string $taxonomy ): array {
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return array();
		}

		return wp_list_pluck( $terms, 'name' );
	}

	/**
	 * Maps internal status to Schema.org status URL.
	 *
	 * @param string $status Event status key.
	 * @return string
	 */
	private function schema_event_status( string $status ): string {
		$map = array(
			'scheduled' => 'https://schema.org/EventScheduled',
			'postponed' => 'https://schema.org/EventPostponed',
			'cancelled' => 'https://schema.org/EventCancelled',
			'completed' => 'https://schema.org/EventCompleted',
		);

		return $map[ $status ] ?? '';
	}

	/**
	 * Maps internal format to attendance mode.
	 *
	 * @param string $format Event format key.
	 * @return string
	 */
	private function schema_attendance_mode( string $format ): string {
		$map = array(
			'online'    => 'https://schema.org/OnlineEventAttendanceMode',
			'in-person' => 'https://schema.org/OfflineEventAttendanceMode',
			'hybrid'    => 'https://schema.org/MixedEventAttendanceMode',
		);

		return $map[ $format ] ?? '';
	}

	/**
	 * Builds schema location for event.
	 *
	 * @param int    $post_id Event ID.
	 * @param string $format  Event format.
	 * @return array<string, mixed>
	 */
	private function schema_location( int $post_id, string $format ): array {
		$url     = (string) get_post_meta( $post_id, '_youract_event_url', true );

		if ( 'online' === $format ) {
			if ( '' === $url ) {
				return array();
			}

			return array(
				'@type' => 'VirtualLocation',
				'url'   => $url,
			);
		}

		if ( 'hybrid' === $format ) {
			if ( '' !== $url ) {
				return array(
					'@type' => 'VirtualLocation',
					'url'   => $url,
				);
			}
		}

		return array();
	}
}

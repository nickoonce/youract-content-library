<?php
/**
 * Post meta registration.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers resource and event metadata.
 */
class Meta_Registration {

	/**
	 * Resource status values.
	 *
	 * @var string[]
	 */
	private const RESOURCE_STATUSES = array( 'active', 'needs-review', 'archived' );

	/**
	 * Event format values.
	 *
	 * @var string[]
	 */
	private const EVENT_FORMATS = array( 'in-person', 'online', 'hybrid' );

	/**
	 * Event status values.
	 *
	 * @var string[]
	 */
	private const EVENT_STATUSES = array( 'scheduled', 'postponed', 'cancelled', 'completed' );

	/**
	 * Hooks registration.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	/**
	 * Registers all post meta fields.
	 *
	 * @return void
	 */
	public function register_meta(): void {
		$this->register_resource_meta();
		$this->register_event_meta();
	}

	/**
	 * Registers Resource metadata.
	 *
	 * @return void
	 */
	private function register_resource_meta(): void {
		$this->register_string_meta( 'act_resource', '_youract_external_url', 'uri', array( $this, 'sanitize_url' ) );
		$this->register_string_meta( 'act_resource', '_youract_source_organization', 'string', 'sanitize_text_field' );
		$this->register_string_meta( 'act_resource', '_youract_resource_summary', 'string', 'sanitize_textarea_field' );
		$this->register_string_meta( 'act_resource', '_youract_access_notes', 'string', 'sanitize_textarea_field' );
		$this->register_string_meta( 'act_resource', '_youract_last_reviewed', 'string', array( $this, 'sanitize_date' ) );
		$this->register_string_meta( 'act_resource', '_youract_resource_status', 'string', array( $this, 'sanitize_resource_status' ) );

		register_post_meta(
			'act_resource',
			'_youract_featured_resource',
			array(
				'single'            => true,
				'type'              => 'boolean',
				'default'           => false,
				'show_in_rest'      => true,
				'sanitize_callback' => array( $this, 'sanitize_boolean' ),
				'auth_callback'     => array( $this, 'can_edit_post_meta' ),
			)
		);
	}

	/**
	 * Registers Event metadata.
	 *
	 * @return void
	 */
	private function register_event_meta(): void {
		$timestamp_fields = array(
			'_youract_event_start_utc',
			'_youract_event_end_utc',
			'_youract_registration_deadline_utc',
			'_youract_accommodation_deadline_utc',
		);

		foreach ( $timestamp_fields as $key ) {
			register_post_meta(
				'act_event',
				$key,
				array(
					'single'            => true,
					'type'              => 'integer',
					'show_in_rest'      => true,
					'sanitize_callback' => array( $this, 'sanitize_timestamp' ),
					'auth_callback'     => array( $this, 'can_edit_post_meta' ),
				)
			);
		}

		$this->register_string_meta( 'act_event', '_youract_event_timezone', 'string', array( $this, 'sanitize_timezone' ) );

		register_post_meta(
			'act_event',
			'_youract_event_all_day',
			array(
				'single'            => true,
				'type'              => 'boolean',
				'default'           => false,
				'show_in_rest'      => true,
				'sanitize_callback' => array( $this, 'sanitize_boolean' ),
				'auth_callback'     => array( $this, 'can_edit_post_meta' ),
			)
		);

		$this->register_string_meta( 'act_event', '_youract_event_format', 'string', array( $this, 'sanitize_event_format' ) );
		$this->register_string_meta( 'act_event', '_youract_event_venue', 'string', 'sanitize_text_field' );
		$this->register_string_meta( 'act_event', '_youract_event_address', 'string', 'sanitize_textarea_field' );
		$this->register_string_meta( 'act_event', '_youract_event_organizer', 'string', 'sanitize_text_field' );
		$this->register_string_meta( 'act_event', '_youract_event_url', 'uri', array( $this, 'sanitize_url' ) );
		$this->register_string_meta( 'act_event', '_youract_registration_url', 'uri', array( $this, 'sanitize_url' ) );
		$this->register_string_meta( 'act_event', '_youract_event_cost', 'string', 'sanitize_text_field' );
		$this->register_string_meta( 'act_event', '_youract_accessibility_information', 'string', 'sanitize_textarea_field' );
		$this->register_string_meta( 'act_event', '_youract_accommodation_contact', 'string', 'sanitize_text_field' );
		$this->register_string_meta( 'act_event', '_youract_event_status', 'string', array( $this, 'sanitize_event_status' ) );
		$this->register_string_meta( 'act_event', '_youract_last_verified', 'string', array( $this, 'sanitize_date' ) );
	}

	/**
	 * Registers a string-like post meta key.
	 *
	 * @param string   $post_type         Post type.
	 * @param string   $meta_key          Meta key.
	 * @param string   $schema_type       REST schema type.
	 * @param callable $sanitize_callback Sanitizer.
	 * @return void
	 */
	private function register_string_meta( string $post_type, string $meta_key, string $schema_type, callable $sanitize_callback ): void {
		register_post_meta(
			$post_type,
			$meta_key,
			array(
				'single'            => true,
				'type'              => 'string',
				'show_in_rest'      => array(
					'schema' => array(
						'type' => $schema_type,
					),
				),
				'sanitize_callback' => $sanitize_callback,
				'auth_callback'     => array( $this, 'can_edit_post_meta' ),
			)
		);
	}

	/**
	 * Authorization callback for post meta updates.
	 *
	 * @param mixed  $allowed  Whether access is granted.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id  Post ID.
	 * @param int    $user_id  User ID.
	 * @return bool
	 */
	public function can_edit_post_meta( $allowed, string $meta_key, int $post_id, int $user_id ): bool {
		if ( ! $allowed ) {
			return false;
		}

		if ( $post_id <= 0 ) {
			return false;
		}

		return user_can( $user_id, 'edit_post', $post_id );
	}

	/**
	 * Sanitizes boolean fields.
	 *
	 * @param mixed $value Raw value.
	 * @return bool
	 */
	public function sanitize_boolean( $value ): bool {
		return ! empty( $value );
	}

	/**
	 * Sanitizes URL fields.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public function sanitize_url( $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}

		return esc_url_raw( trim( $value ) );
	}

	/**
	 * Sanitizes date fields.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public function sanitize_date( $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = trim( $value );

		if ( '' === $value ) {
			return '';
		}

		if ( $this->is_valid_date( $value ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * Sanitizes timestamps.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public function sanitize_timestamp( $value ): int {
		if ( '' === $value || null === $value ) {
			return 0;
		}

		return max( 0, absint( $value ) );
	}

	/**
	 * Sanitizes timezone values.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public function sanitize_timezone( $value ): string {
		if ( ! is_string( $value ) ) {
			return $this->default_timezone();
		}

		$value = trim( $value );

		if ( $this->is_valid_timezone( $value ) ) {
			return $value;
		}

		return $this->default_timezone();
	}

	/**
	 * Validates Y-m-d dates.
	 *
	 * @param string $date Date value.
	 * @return bool
	 */
	private function is_valid_date( string $date ): bool {
		$date = trim( $date );

		if ( '' === $date ) {
			return false;
		}

		$parsed = \DateTimeImmutable::createFromFormat( 'Y-m-d', $date );
		$errors = \DateTimeImmutable::getLastErrors();

		if ( false === $parsed ) {
			return false;
		}

		if ( is_array( $errors ) && ( ! empty( $errors['warning_count'] ) || ! empty( $errors['error_count'] ) ) ) {
			return false;
		}

		return $parsed->format( 'Y-m-d' ) === $date;
	}

	/**
	 * Returns a valid fallback timezone.
	 *
	 * @return string
	 */
	private function default_timezone(): string {
		$timezone = wp_timezone_string();
		if ( $this->is_valid_timezone( $timezone ) ) {
			return $timezone;
		}

		return 'UTC';
	}

	/**
	 * Validates IANA timezone names.
	 *
	 * @param string $timezone Timezone.
	 * @return bool
	 */
	private function is_valid_timezone( string $timezone ): bool {
		if ( '' === $timezone ) {
			return false;
		}

		return in_array( $timezone, timezone_identifiers_list(), true );
	}

	/**
	 * Sanitizes Resource status values.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public function sanitize_resource_status( $value ): string {
		if ( ! is_string( $value ) ) {
			return 'active';
		}

		$value = sanitize_key( $value );

		if ( in_array( $value, self::RESOURCE_STATUSES, true ) ) {
			return $value;
		}

		return 'active';
	}

	/**
	 * Sanitizes Event format values.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public function sanitize_event_format( $value ): string {
		if ( ! is_string( $value ) ) {
			return 'in-person';
		}

		$value = sanitize_key( $value );

		if ( in_array( $value, self::EVENT_FORMATS, true ) ) {
			return $value;
		}

		return 'in-person';
	}

	/**
	 * Sanitizes Event status values.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public function sanitize_event_status( $value ): string {
		if ( ! is_string( $value ) ) {
			return 'scheduled';
		}

		$value = sanitize_key( $value );

		if ( in_array( $value, self::EVENT_STATUSES, true ) ) {
			return $value;
		}

		return 'scheduled';
	}
}

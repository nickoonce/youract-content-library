<?php
/**
 * Utility helpers.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared utility methods.
 */
class Utils {

	/**
	 * Returns a valid default IANA timezone.
	 *
	 * @return string
	 */
	public static function get_default_timezone(): string {
		$timezone = wp_timezone_string();

		if ( self::is_valid_timezone( $timezone ) ) {
			return $timezone;
		}

		return 'UTC';
	}

	/**
	 * Checks whether a timezone identifier is valid.
	 *
	 * @param string $timezone Timezone identifier.
	 * @return bool
	 */
	public static function is_valid_timezone( string $timezone ): bool {
		if ( '' === $timezone ) {
			return false;
		}

		return in_array( $timezone, timezone_identifiers_list(), true );
	}

	/**
	 * Converts a local date/time pair to UTC timestamp.
	 *
	 * @param string $date     Date in Y-m-d format.
	 * @param string $time     Time in H:i format.
	 * @param string $timezone IANA timezone identifier.
	 * @return int|null
	 */
	public static function local_datetime_to_utc( string $date, string $time, string $timezone ): ?int {
		if ( ! self::is_valid_timezone( $timezone ) ) {
			return null;
		}

		$date = trim( $date );
		$time = trim( $time );

		if ( '' === $date || '' === $time ) {
			return null;
		}

		$format   = 'Y-m-d H:i';
		$combined = $date . ' ' . $time;

		try {
			$tz      = new DateTimeZone( $timezone );
			$parsed  = DateTimeImmutable::createFromFormat( $format, $combined, $tz );
			$errors  = DateTimeImmutable::getLastErrors();
			$invalid = false;

			if ( false === $parsed ) {
				$invalid = true;
			}

			if ( is_array( $errors ) && ( ! empty( $errors['warning_count'] ) || ! empty( $errors['error_count'] ) ) ) {
				$invalid = true;
			}

			if ( $invalid ) {
				return null;
			}

			return $parsed->setTimezone( new DateTimeZone( 'UTC' ) )->getTimestamp();
		} catch ( Exception $exception ) {
			return null;
		}
	}

	/**
	 * Converts a local date to UTC range for all-day events.
	 *
	 * @param string $date     Date in Y-m-d format.
	 * @param string $timezone IANA timezone identifier.
	 * @return array<string, int>|null
	 */
	public static function local_date_to_utc_day_range( string $date, string $timezone ): ?array {
		$start = self::local_datetime_to_utc( $date, '00:00', $timezone );
		$end   = self::local_datetime_to_utc( $date, '23:59', $timezone );

		if ( null === $start || null === $end ) {
			return null;
		}

		return array(
			'start' => $start,
			'end'   => $end,
		);
	}

	/**
	 * Converts UTC timestamp to local date and time strings.
	 *
	 * @param int    $timestamp UTC timestamp.
	 * @param string $timezone  IANA timezone identifier.
	 * @return array<string, string>
	 */
	public static function utc_to_local_inputs( int $timestamp, string $timezone ): array {
		if ( $timestamp <= 0 || ! self::is_valid_timezone( $timezone ) ) {
			return array(
				'date' => '',
				'time' => '',
			);
		}

		try {
			$utc_dt = new DateTimeImmutable( '@' . $timestamp );
			$local  = $utc_dt->setTimezone( new DateTimeZone( $timezone ) );

			return array(
				'date' => $local->format( 'Y-m-d' ),
				'time' => $local->format( 'H:i' ),
			);
		} catch ( Exception $exception ) {
			return array(
				'date' => '',
				'time' => '',
			);
		}
	}

	/**
	 * Validates Y-m-d date.
	 *
	 * @param string $date Date value.
	 * @return bool
	 */
	public static function is_valid_date( string $date ): bool {
		$date = trim( $date );

		if ( '' === $date ) {
			return false;
		}

		$parsed = DateTimeImmutable::createFromFormat( 'Y-m-d', $date );
		$errors = DateTimeImmutable::getLastErrors();

		if ( false === $parsed ) {
			return false;
		}

		if ( is_array( $errors ) && ( ! empty( $errors['warning_count'] ) || ! empty( $errors['error_count'] ) ) ) {
			return false;
		}

		return $parsed->format( 'Y-m-d' ) === $date;
	}
}

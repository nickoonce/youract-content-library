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
	 * Returns the fixed event timezone used for admin entry and frontend display.
	 *
	 * @return string
	 */
	public static function get_event_timezone(): string {
		return (string) apply_filters( 'youract_event_timezone', 'America/Los_Angeles' );
	}

	/**
	 * Returns a valid default IANA timezone.
	 *
	 * @return string
	 */
	public static function get_default_timezone(): string {
		$timezone = trim( (string) wp_timezone_string() );

		if ( self::is_valid_timezone( $timezone ) ) {
			return $timezone;
		}

		$gmt_offset = get_option( 'gmt_offset' );
		if ( is_numeric( $gmt_offset ) ) {
			$offset_value = (string) $gmt_offset;
			if ( self::is_valid_timezone( $offset_value ) ) {
				return $offset_value;
			}
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
		return null !== self::create_timezone_object( $timezone );
	}

	/**
	 * Returns a usable DateTimeZone object with safe fallback behavior.
	 *
	 * @param string $timezone Timezone identifier or offset.
	 * @return DateTimeZone
	 */
	public static function get_timezone_object( string $timezone ): DateTimeZone {
		$timezone_object = self::create_timezone_object( $timezone );
		if ( $timezone_object instanceof DateTimeZone ) {
			return $timezone_object;
		}

		$default_timezone = self::create_timezone_object( self::get_default_timezone() );
		if ( $default_timezone instanceof DateTimeZone ) {
			return $default_timezone;
		}

		return new DateTimeZone( 'UTC' );
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
		$tz = self::create_timezone_object( $timezone );
		if ( ! ( $tz instanceof DateTimeZone ) ) {
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
		if ( $timestamp <= 0 ) {
			return array(
				'date' => '',
				'time' => '',
			);
		}

		$tz = self::create_timezone_object( $timezone );
		if ( ! ( $tz instanceof DateTimeZone ) ) {
			return array(
				'date' => '',
				'time' => '',
			);
		}

		try {
			$utc_dt = new DateTimeImmutable( '@' . $timestamp );
			$local  = $utc_dt->setTimezone( $tz );

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
	 * Attempts to create a DateTimeZone from IANA or WP-style UTC offset strings.
	 *
	 * @param string $timezone Raw timezone string.
	 * @return DateTimeZone|null
	 */
	private static function create_timezone_object( string $timezone ): ?DateTimeZone {
		$timezone = trim( $timezone );
		if ( '' === $timezone ) {
			return null;
		}

		try {
			return new DateTimeZone( $timezone );
		} catch ( Exception $exception ) {
			// Try normalized offset variants below.
		}

		if ( preg_match( '/^UTC([+-])(\d{1,2})(?::?(\d{2}))?$/i', $timezone, $matches ) ) {
			$sign    = (string) $matches[1];
			$hours   = (int) $matches[2];
			$minutes = isset( $matches[3] ) && '' !== $matches[3] ? (int) $matches[3] : 0;
			return self::create_from_offset_parts( $sign, $hours, $minutes );
		}

		if ( preg_match( '/^[+-]?(?:\d{1,2})(?:\.\d+)?$/', $timezone ) ) {
			$offset = (float) $timezone;
			$sign   = $offset < 0 ? '-' : '+';
			$abs    = abs( $offset );
			$hours  = (int) floor( $abs );
			$minutes = (int) round( ( $abs - $hours ) * 60 );

			if ( 60 === $minutes ) {
				$hours  += 1;
				$minutes = 0;
			}

			return self::create_from_offset_parts( $sign, $hours, $minutes );
		}

		return null;
	}

	/**
	 * Builds a DateTimeZone object from validated offset parts.
	 *
	 * @param string $sign    Offset sign (+ or -).
	 * @param int    $hours   Offset hours.
	 * @param int    $minutes Offset minutes.
	 * @return DateTimeZone|null
	 */
	private static function create_from_offset_parts( string $sign, int $hours, int $minutes ): ?DateTimeZone {
		if ( ! in_array( $sign, array( '+', '-' ), true ) ) {
			return null;
		}

		if ( $hours < 0 || $hours > 14 || $minutes < 0 || $minutes > 59 ) {
			return null;
		}

		$offset = sprintf( '%s%02d:%02d', $sign, $hours, $minutes );

		try {
			return new DateTimeZone( $offset );
		} catch ( Exception $exception ) {
			return null;
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

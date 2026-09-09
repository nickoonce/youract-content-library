<?php
/**
 * Event details template.
 *
 * @var array<string, mixed> $event Event details.
 *
 * @package YourACT\ContentLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$timing = isset( $event['timing'] ) && is_array( $event['timing'] ) ? $event['timing'] : array();
$heading_id = isset( $event['heading_id'] ) ? sanitize_html_class( (string) $event['heading_id'] ) : '';
if ( '' === $heading_id ) {
	$heading_id = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'youract-event-details-heading-' ) : 'youract-event-details-heading';
}
$is_compact = ! empty( $event['is_compact'] );
?>
<section class="youract-details youract-event-details<?php echo $is_compact ? ' is-compact' : ''; ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php esc_html_e( 'Event Details', 'youract-content-library' ); ?></h2>
	<dl class="youract-meta-list">
		<dt><?php esc_html_e( 'Status', 'youract-content-library' ); ?></dt>
		<dd><?php echo esc_html( (string) $event['status_label'] ); ?></dd>

		<?php if ( ! empty( $timing['start_text'] ) ) : ?>
			<dt><?php esc_html_e( 'Date and time', 'youract-content-library' ); ?></dt>
			<dd>
				<?php if ( ! empty( $timing['end_text'] ) && empty( $timing['all_day'] ) && ! empty( $timing['is_same_day'] ) && ! empty( $timing['start_date_text'] ) && ! empty( $timing['start_time_text'] ) && ! empty( $timing['end_time_text'] ) ) : ?>
					<time datetime="<?php echo esc_attr( (string) $timing['start_iso'] ); ?>"><?php echo esc_html( (string) $timing['start_date_text'] . ' ' . (string) $timing['start_time_text'] ); ?></time>
					<?php esc_html_e( ' to ', 'youract-content-library' ); ?>
					<time datetime="<?php echo esc_attr( (string) $timing['end_iso'] ); ?>"><?php echo esc_html( (string) $timing['end_time_text'] ); ?></time>
				<?php else : ?>
					<time datetime="<?php echo esc_attr( (string) $timing['start_iso'] ); ?>"><?php echo esc_html( (string) $timing['start_text'] ); ?></time>
					<?php if ( ! empty( $timing['end_text'] ) ) : ?>
						<?php esc_html_e( ' to ', 'youract-content-library' ); ?>
						<time datetime="<?php echo esc_attr( (string) $timing['end_iso'] ); ?>"><?php echo esc_html( (string) $timing['end_text'] ); ?></time>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ( ! empty( $timing['all_day'] ) ) : ?>
					<?php esc_html_e( ' (All day)', 'youract-content-library' ); ?>
				<?php endif; ?>
			</dd>
		<?php endif; ?>

		<dt><?php esc_html_e( 'Time zone', 'youract-content-library' ); ?></dt>
		<dd><?php echo esc_html( (string) ( $timing['timezone_abbr'] ?: $event['timezone'] ) ); ?></dd>

		<?php if ( ! empty( $event['format_label'] ) ) : ?>
			<dt><?php esc_html_e( 'Format', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( (string) $event['format_label'] ); ?></dd>
		<?php endif; ?>

		<?php if ( ! empty( $event['event_url'] ) ) : ?>
			<dt><?php esc_html_e( 'Official event link', 'youract-content-library' ); ?></dt>
			<dd><a href="<?php echo esc_url( (string) $event['event_url'] ); ?>"><?php esc_html_e( 'Visit official event page', 'youract-content-library' ); ?></a></dd>
		<?php endif; ?>

		<?php if ( $is_compact ) : ?>
			</dl>
		</section>
		<?php return; ?>
		<?php endif; ?>

		<?php if ( ! empty( $event['registration_deadline_utc'] ) ) : ?>
			<dt><?php esc_html_e( 'Registration', 'youract-content-library' ); ?></dt>
			<dd>
				<?php $registration_tz = \YourACT\ContentLibrary\Utils::is_valid_timezone( (string) $event['timezone'] ) ? new DateTimeZone( (string) $event['timezone'] ) : new DateTimeZone( \YourACT\ContentLibrary\Utils::get_default_timezone() ); ?>
				<?php $registration_iso = wp_date( DATE_ATOM, (int) $event['registration_deadline_utc'], $registration_tz ); ?>
				<div>
					<?php esc_html_e( 'Deadline:', 'youract-content-library' ); ?>
					<time datetime="<?php echo esc_attr( $registration_iso ); ?>"><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $event['registration_deadline_utc'], $registration_tz ) ); ?></time>
				</div>
			</dd>
		<?php endif; ?>

		<?php if ( ! empty( $event['disclaimer'] ) ) : ?>
			<dt><?php esc_html_e( 'Disclaimer', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( (string) $event['disclaimer'] ); ?></dd>
		<?php endif; ?>
	</dl>
</section>

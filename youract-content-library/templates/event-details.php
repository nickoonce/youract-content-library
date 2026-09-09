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
$hide_heading = ! empty( $event['hide_heading'] );
$hide_status = ! empty( $event['hide_status'] );
$hide_disclaimer = ! empty( $event['hide_disclaimer'] );
$event_url_label = isset( $event['event_url_label'] ) && is_string( $event['event_url_label'] ) ? $event['event_url_label'] : __( 'Official event link', 'youract-content-library' );
$event_url_use_raw_text = ! empty( $event['event_url_use_raw_text'] );
$timezone_display = isset( $timing['timezone'] ) && '' !== (string) $timing['timezone']
	? (string) $timing['timezone']
	: ( isset( $event['timezone'] ) ? (string) $event['timezone'] : '' );
$pst_note = isset( $timing['pst_note'] ) ? (string) $timing['pst_note'] : '';
?>
<section class="youract-details youract-event-details<?php echo $is_compact ? ' is-compact' : ''; ?>" <?php echo $hide_heading ? 'aria-label="' . esc_attr__( 'Event details', 'youract-content-library' ) . '"' : 'aria-labelledby="' . esc_attr( $heading_id ) . '"'; ?>>
	<?php if ( ! $hide_heading ) : ?>
		<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php esc_html_e( 'Event Details', 'youract-content-library' ); ?></h2>
	<?php endif; ?>
	<dl class="youract-meta-list">
		<?php if ( ! $hide_status ) : ?>
			<dt><?php esc_html_e( 'Status', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( (string) $event['status_label'] ); ?></dd>
		<?php endif; ?>

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
				<?php if ( '' !== $timezone_display ) : ?>
					<?php echo ' ' . esc_html( $timezone_display ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
				<?php if ( '' !== $pst_note ) : ?>
					<?php echo ' (' . esc_html__( 'PST:', 'youract-content-library' ) . ' ' . esc_html( $pst_note ) . ')'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			</dd>
		<?php endif; ?>

		<?php if ( ! empty( $event['format_label'] ) ) : ?>
			<dt><?php esc_html_e( 'Format', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( (string) $event['format_label'] ); ?></dd>
		<?php endif; ?>

		<?php if ( ! empty( $event['event_url'] ) ) : ?>
			<dt><?php echo esc_html( $event_url_label ); ?></dt>
			<dd>
				<a href="<?php echo esc_url( (string) $event['event_url'] ); ?>">
					<?php echo esc_html( $event_url_use_raw_text ? (string) $event['event_url'] : __( 'Visit official event page', 'youract-content-library' ) ); ?>
				</a>
			</dd>
		<?php endif; ?>

		<?php if ( $is_compact ) : ?>
			</dl>
		</section>
		<?php return; ?>
		<?php endif; ?>

		<?php if ( ! empty( $event['registration_deadline_utc'] ) ) : ?>
			<dt><?php esc_html_e( 'Registration', 'youract-content-library' ); ?></dt>
			<dd>
				<?php $registration_tz = \YourACT\ContentLibrary\Utils::get_timezone_object( (string) $event['timezone'] ); ?>
				<?php $registration_iso = wp_date( DATE_ATOM, (int) $event['registration_deadline_utc'], $registration_tz ); ?>
				<div>
					<?php esc_html_e( 'Deadline:', 'youract-content-library' ); ?>
					<time datetime="<?php echo esc_attr( $registration_iso ); ?>"><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $event['registration_deadline_utc'], $registration_tz ) ); ?></time>
				</div>
			</dd>
		<?php endif; ?>

		<?php if ( ! $hide_disclaimer && ! empty( $event['disclaimer'] ) ) : ?>
			<dt><?php esc_html_e( 'Disclaimer', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( (string) $event['disclaimer'] ); ?></dd>
		<?php endif; ?>
	</dl>
</section>

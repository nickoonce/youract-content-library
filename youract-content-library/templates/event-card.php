<?php
/**
 * Event card template.
 *
 * @var array<string, mixed> $event Event data.
 *
 * @package YourACT\ContentLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$timing = isset( $event['timing'] ) && is_array( $event['timing'] ) ? $event['timing'] : array();
?>
<article class="youract-card youract-event-card" aria-labelledby="<?php echo esc_attr( (string) $event['heading_id'] ); ?>">
	<h3 class="youract-card-title" id="<?php echo esc_attr( (string) $event['heading_id'] ); ?>">
		<a href="<?php echo esc_url( (string) $event['permalink'] ); ?>"><?php echo esc_html( (string) $event['title'] ); ?></a>
	</h3>

	<p class="youract-status youract-status-<?php echo esc_attr( (string) $event['status'] ); ?>">
		<strong><?php esc_html_e( 'Status:', 'youract-content-library' ); ?></strong>
		<?php echo esc_html( (string) $event['status_label'] ); ?>
	</p>

	<?php if ( ! empty( $timing['start_text'] ) ) : ?>
		<p>
			<strong><?php esc_html_e( 'Date and time:', 'youract-content-library' ); ?></strong>
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
				<?php esc_html_e( ' - All day', 'youract-content-library' ); ?>
			<?php endif; ?>
			<?php esc_html_e( ' PST', 'youract-content-library' ); ?>
		</p>
	<?php endif; ?>

	<dl class="youract-meta-list">
		<?php if ( ! empty( $event['format_label'] ) ) : ?>
			<dt><?php esc_html_e( 'Format', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( (string) $event['format_label'] ); ?></dd>
		<?php endif; ?>
		<?php if ( ! empty( $event['event_types'] ) && is_array( $event['event_types'] ) ) : ?>
			<dt><?php esc_html_e( 'Event type', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( implode( ', ', $event['event_types'] ) ); ?></dd>
		<?php endif; ?>
		<?php if ( ! empty( $event['topics'] ) && is_array( $event['topics'] ) ) : ?>
			<dt><?php esc_html_e( 'Topic', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( implode( ', ', $event['topics'] ) ); ?></dd>
		<?php endif; ?>
		<?php if ( ! empty( $event['geographies'] ) && is_array( $event['geographies'] ) ) : ?>
			<dt><?php esc_html_e( 'Geographic scope', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( implode( ', ', $event['geographies'] ) ); ?></dd>
		<?php endif; ?>
	</dl>

	<?php if ( ! empty( $event['excerpt'] ) ) : ?>
		<p><?php echo esc_html( (string) $event['excerpt'] ); ?></p>
	<?php endif; ?>

	<p class="youract-card-actions">
		<a href="<?php echo esc_url( (string) $event['permalink'] ); ?>"><?php esc_html_e( 'View event details', 'youract-content-library' ); ?></a>
	</p>
</article>

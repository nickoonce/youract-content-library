<?php
/**
 * Resource card template.
 *
 * @var array<string, mixed> $resource Resource data.
 *
 * @package YourACT\ContentLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$summary = (string) ( $resource['excerpt'] ?? '' );
?>
<article class="youract-card youract-resource-card" aria-labelledby="<?php echo esc_attr( (string) $resource['heading_id'] ); ?>">
	<h3 class="youract-card-title" id="<?php echo esc_attr( (string) $resource['heading_id'] ); ?>">
		<a href="<?php echo esc_url( (string) $resource['permalink'] ); ?>"><?php echo esc_html( (string) $resource['title'] ); ?></a>
	</h3>
	<?php if ( '' !== $summary ) : ?>
		<p><?php echo esc_html( $summary ); ?></p>
	<?php endif; ?>

	<dl class="youract-meta-list">
		<?php if ( ! empty( $resource['source_organization'] ) ) : ?>
			<dt><?php esc_html_e( 'Source', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( (string) $resource['source_organization'] ); ?></dd>
		<?php endif; ?>

		<?php if ( ! empty( $resource['original_publication_date'] ) && ! empty( $resource['original_publication_date_text'] ) ) : ?>
			<dt><?php esc_html_e( 'Original publication date', 'youract-content-library' ); ?></dt>
			<dd><time datetime="<?php echo esc_attr( (string) $resource['original_publication_date'] ); ?>"><?php echo esc_html( (string) $resource['original_publication_date_text'] ); ?></time></dd>
		<?php endif; ?>

		<?php if ( ! empty( $resource['resource_types'] ) && is_array( $resource['resource_types'] ) ) : ?>
			<dt><?php esc_html_e( 'Resource type', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( implode( ', ', $resource['resource_types'] ) ); ?></dd>
		<?php endif; ?>

		<?php if ( ! empty( $resource['topics'] ) && is_array( $resource['topics'] ) ) : ?>
			<dt><?php esc_html_e( 'Topic', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( implode( ', ', $resource['topics'] ) ); ?></dd>
		<?php endif; ?>

		<?php if ( ! empty( $resource['geographies'] ) && is_array( $resource['geographies'] ) ) : ?>
			<dt><?php esc_html_e( 'Geographic scope', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( implode( ', ', $resource['geographies'] ) ); ?></dd>
		<?php endif; ?>

		<?php if ( ! empty( $resource['last_reviewed'] ) && ! empty( $resource['last_reviewed_text'] ) ) : ?>
			<dt><?php esc_html_e( 'Last reviewed', 'youract-content-library' ); ?></dt>
			<dd><time datetime="<?php echo esc_attr( (string) $resource['last_reviewed'] ); ?>"><?php echo esc_html( (string) $resource['last_reviewed_text'] ); ?></time></dd>
		<?php endif; ?>
	</dl>

	<p class="youract-card-actions">
		<?php if ( ! empty( $resource['external_url'] ) ) : ?>
			<a href="<?php echo esc_url( (string) $resource['external_url'] ); ?>"><?php echo esc_html( sprintf( __( 'Visit resource: %s', 'youract-content-library' ), (string) $resource['title'] ) ); ?></a>
		<?php else : ?>
			<a href="<?php echo esc_url( (string) $resource['permalink'] ); ?>"><?php esc_html_e( 'View resource details', 'youract-content-library' ); ?></a>
		<?php endif; ?>
	</p>
</article>

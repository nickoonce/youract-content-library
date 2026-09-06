<?php
/**
 * Resource details template.
 *
 * @var array<string, mixed> $resource Resource details.
 *
 * @package YourACT\ContentLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_labels = array(
	'active'       => __( 'Active', 'youract-content-library' ),
	'needs-review' => __( 'Needs review', 'youract-content-library' ),
	'archived'     => __( 'Archived', 'youract-content-library' ),
);
$status = (string) ( $resource['resource_status'] ?? '' );
?>
<section class="youract-details youract-resource-details" aria-labelledby="youract-resource-details-heading">
	<h2 id="youract-resource-details-heading"><?php esc_html_e( 'Resource Details', 'youract-content-library' ); ?></h2>
	<dl class="youract-meta-list">
		<?php if ( ! empty( $resource['source_organization'] ) ) : ?>
			<dt><?php esc_html_e( 'Source organization', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( (string) $resource['source_organization'] ); ?></dd>
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

		<?php if ( ! empty( $resource['access_notes'] ) ) : ?>
			<dt><?php esc_html_e( 'Access notes', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( (string) $resource['access_notes'] ); ?></dd>
		<?php endif; ?>

		<?php if ( ! empty( $resource['external_url'] ) ) : ?>
			<dt><?php esc_html_e( 'External URL', 'youract-content-library' ); ?></dt>
			<dd><a href="<?php echo esc_url( (string) $resource['external_url'] ); ?>"><?php esc_html_e( 'Visit this resource on its source site', 'youract-content-library' ); ?></a></dd>
		<?php endif; ?>

		<?php if ( ! empty( $resource['last_reviewed'] ) ) : ?>
			<dt><?php esc_html_e( 'Last reviewed', 'youract-content-library' ); ?></dt>
			<dd><time datetime="<?php echo esc_attr( (string) $resource['last_reviewed'] ); ?>"><?php echo esc_html( (string) $resource['last_reviewed'] ); ?></time></dd>
		<?php endif; ?>

		<?php if ( ! empty( $status ) && 'active' !== $status ) : ?>
			<dt><?php esc_html_e( 'Resource status', 'youract-content-library' ); ?></dt>
			<dd><?php echo esc_html( $status_labels[ $status ] ?? $status ); ?></dd>
		<?php endif; ?>
	</dl>
</section>

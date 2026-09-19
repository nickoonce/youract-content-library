<?php
/**
 * Opportunity details template.
 *
 * @var array<string, mixed> $opportunity Opportunity details.
 *
 * @package YourACT\ContentLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stage_labels = array(
	'exploring'        => __( 'Exploring', 'youract-content-library' ),
	'seeking-partners' => __( 'Seeking partners', 'youract-content-library' ),
	'in-progress'      => __( 'In progress', 'youract-content-library' ),
	'success-story'    => __( 'Success story', 'youract-content-library' ),
	'inactive'         => __( 'Inactive', 'youract-content-library' ),
);
$stage = (string) ( $opportunity['stage'] ?? 'exploring' );
?>
<section class="youract-details youract-opportunity-details" aria-labelledby="<?php echo esc_attr( (string) $opportunity['heading_id'] ); ?>">
	<h2 id="<?php echo esc_attr( (string) $opportunity['heading_id'] ); ?>"><?php esc_html_e( 'Opportunity Details', 'youract-content-library' ); ?></h2>
	<?php if ( ! empty( $opportunity['subtitle'] ) ) : ?>
		<p><?php echo esc_html( (string) $opportunity['subtitle'] ); ?></p>
	<?php endif; ?>
	<dl class="youract-meta-list">
		<dt><?php esc_html_e( 'Stage', 'youract-content-library' ); ?></dt>
		<dd><?php echo esc_html( $stage_labels[ $stage ] ?? $stage_labels['exploring'] ); ?></dd>
		<?php if ( ! empty( $opportunity['featured'] ) ) : ?>
			<dt><?php esc_html_e( 'Featured', 'youract-content-library' ); ?></dt>
			<dd><?php esc_html_e( 'Yes', 'youract-content-library' ); ?></dd>
		<?php endif; ?>
	</dl>
	<?php if ( ! empty( $opportunity['cta_label'] ) && ! empty( $opportunity['cta_url'] ) ) : ?>
		<p><a href="<?php echo esc_url( (string) $opportunity['cta_url'] ); ?>"><?php echo esc_html( (string) $opportunity['cta_label'] ); ?></a></p>
	<?php endif; ?>
</section>

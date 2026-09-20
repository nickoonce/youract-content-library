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
$stage       = (string) ( $opportunity['stage'] ?? 'exploring' );
$stage_label = $stage_labels[ $stage ] ?? $stage_labels['exploring'];
?>
<section class="youract-details youract-opportunity-details" aria-label="<?php esc_attr_e( 'Opportunity action', 'youract-content-library' ); ?>">
	<p><?php esc_html_e( 'Stage:', 'youract-content-library' ); ?> <?php echo esc_html( $stage_label ); ?></p>
	<?php if ( ! empty( $opportunity['cta_label'] ) && ! empty( $opportunity['cta_url'] ) ) : ?>
		<p><a href="<?php echo esc_url( (string) $opportunity['cta_url'] ); ?>"><?php echo esc_html( (string) $opportunity['cta_label'] ); ?></a></p>
	<?php endif; ?>
</section>

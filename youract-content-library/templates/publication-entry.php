<?php
/**
 * Recommended Publication entry template.
 *
 * @var array<string, mixed> $publication Publication data.
 *
 * @package YourACT\ContentLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading_id  = (string) ( $publication['heading_id'] ?? '' );
$title       = (string) ( $publication['title'] ?? '' );
$source_url  = (string) ( $publication['source_url'] ?? '' );
$source      = (string) ( $publication['source_organization'] ?? '' );
$date_iso    = (string) ( $publication['original_publication_date_iso'] ?? '' );
$date_display = (string) ( $publication['original_publication_date_display'] ?? '' );
$summary     = (string) ( $publication['summary'] ?? '' );
$why_matters = (string) ( $publication['why_it_matters'] ?? '' );
?>
<article class="youract-publication" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<h2 class="youract-publication-title" id="<?php echo esc_attr( $heading_id ); ?>">
		<a href="<?php echo esc_url( $source_url ); ?>"><?php echo esc_html( $title ); ?></a>
	</h2>

	<?php if ( '' !== $source || ( '' !== $date_iso && '' !== $date_display ) ) : ?>
		<p class="youract-publication-meta">
			<?php if ( '' !== $source ) : ?>
				<span class="youract-publication-source"><?php echo esc_html( $source ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $source && '' !== $date_iso && '' !== $date_display ) : ?>
				<span class="youract-publication-separator" aria-hidden="true"> · </span>
			<?php endif; ?>
			<?php if ( '' !== $date_iso && '' !== $date_display ) : ?>
				<time class="youract-publication-date" datetime="<?php echo esc_attr( $date_iso ); ?>"><?php echo esc_html( $date_display ); ?></time>
			<?php endif; ?>
		</p>
	<?php endif; ?>

	<?php if ( '' !== trim( $summary ) ) : ?>
		<section class="youract-publication-summary">
			<h3><?php esc_html_e( 'ACT Summary', 'youract-content-library' ); ?></h3>
			<div><?php echo wp_kses_post( wpautop( $summary ) ); ?></div>
		</section>
	<?php endif; ?>

	<?php if ( '' !== trim( $why_matters ) ) : ?>
		<section class="youract-publication-why">
			<h3><?php esc_html_e( 'Why it matters', 'youract-content-library' ); ?></h3>
			<div><?php echo wp_kses_post( wpautop( $why_matters ) ); ?></div>
		</section>
	<?php endif; ?>
</article>

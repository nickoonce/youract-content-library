<?php
/**
 * Opportunity Block Bindings registration.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers Block Bindings sources for Opportunities.
 */
class Opportunity_Bindings {

	/**
	 * Opportunity stage labels.
	 *
	 * @var array<string, string>
	 */
	private const STAGE_LABELS = array(
		'exploring'        => 'Exploring',
		'seeking-partners' => 'Seeking partners',
		'in-progress'      => 'In progress',
		'success-story'    => 'Success story',
		'inactive'         => 'Inactive',
	);

	/**
	 * Registers Block Bindings hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_sources' ) );
	}

	/**
	 * Registers the display-ready Opportunity stage source.
	 *
	 * @return void
	 */
	public function register_sources(): void {
		if ( ! function_exists( 'register_block_bindings_source' ) ) {
			return;
		}

		register_block_bindings_source(
			'youract/opportunity-stage',
			array(
				'label'              => __( 'Opportunity Stage', 'youract-content-library' ),
				'get_value_callback' => array( $this, 'get_stage_label' ),
				'uses_context'       => array( 'postId', 'postType' ),
			)
		);
	}

	/**
	 * Returns the human-readable stage label for the bound Opportunity.
	 *
	 * @param array<string, mixed> $source_args    Binding source arguments.
	 * @param \WP_Block            $block_instance Block instance.
	 * @param string                $attribute_name Bound attribute name.
	 * @return string
	 */
	public function get_stage_label( array $source_args, \WP_Block $block_instance, string $attribute_name ): string {
		unset( $source_args, $attribute_name );

		$context  = is_array( $block_instance->context ) ? $block_instance->context : array();
		$post_id  = isset( $context['postId'] ) ? (int) $context['postId'] : 0;
		$post_type = isset( $context['postType'] ) ? (string) $context['postType'] : '';

		if ( $post_id <= 0 ) {
			$post_id = (int) get_the_ID();
		}

		if ( '' === $post_type && $post_id > 0 ) {
			$post_type = (string) get_post_type( $post_id );
		}

		if ( $post_id <= 0 || 'act_opportunity' !== $post_type ) {
			return '';
		}

		$stage = str_replace( '_', '-', (string) get_post_meta( $post_id, 'act_opportunity_stage', true ) );

		$stage_label = self::STAGE_LABELS[ $stage ] ?? self::STAGE_LABELS['exploring'];

		return __( 'Stage: ', 'youract-content-library' ) . $stage_label;
	}
}

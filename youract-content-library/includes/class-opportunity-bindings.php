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
		add_filter( 'render_block', array( $this, 'format_stage_post_meta_block' ), 10, 3 );
	}

	/**
	 * Formats legacy core/post-meta stage bindings in Opportunity loops.
	 *
	 * @param string               $block_content Rendered block content.
	 * @param array<string, mixed> $parsed_block  Parsed block data.
	 * @param 
ull|\WP_Block      $block         Block instance.
	 * @return string
	 */
	public function format_stage_post_meta_block( string $block_content, array $parsed_block, $block = null ): string {
		if ( 'core/post-meta' !== ( $parsed_block['blockName'] ?? '' ) ) {
			return $block_content;
		}

		$attributes = isset( $parsed_block['attrs'] ) && is_array( $parsed_block['attrs'] ) ? $parsed_block['attrs'] : array();
		$binding    = $attributes['metadata']['bindings']['content'] ?? array();
		$is_stage_meta = 'act_opportunity_stage' === ( $attributes['metaKey'] ?? '' );
		$is_stage_binding = is_array( $binding ) && 'core/post-meta' === ( $binding['source'] ?? '' ) && 'act_opportunity_stage' === ( $binding['args']['key'] ?? '' );
		if ( ! $is_stage_meta && ! $is_stage_binding ) {
			return $block_content;
		}

		$post_id = is_object( $block ) && isset( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();
		if ( $post_id <= 0 || 'act_opportunity' !== get_post_type( $post_id ) ) {
			return $block_content;
		}

		$stage       = str_replace( '_', '-', (string) get_post_meta( $post_id, 'act_opportunity_stage', true ) );
		$stage_label = self::STAGE_LABELS[ $stage ] ?? self::STAGE_LABELS['exploring'];

		return str_replace( esc_html( $stage ), esc_html__( 'Stage: ', 'youract-content-library' ) . $stage_label, $block_content );
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

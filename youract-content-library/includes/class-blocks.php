<?php
/**
 * Dynamic block registration.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers server-rendered blocks.
 */
class Blocks {

	/**
	 * Template renderer service.
	 *
	 * @var Renderer
	 */
	private Renderer $renderer;

	/**
	 * Constructor.
	 *
	 * @param Renderer $renderer Renderer service.
	 */
	public function __construct( Renderer $renderer ) {
		$this->renderer = $renderer;
	}

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Registers dynamic block types.
	 *
	 * @return void
	 */
	public function register_blocks(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			'youract-event-details-block-editor',
			YOURACT_CONTENT_LIBRARY_URL . 'assets/js/event-details-block-editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-i18n' ),
			YOURACT_CONTENT_LIBRARY_VERSION,
			true
		);

		register_block_type(
			YOURACT_CONTENT_LIBRARY_PATH . 'blocks/event-details',
			array(
				'editor_script'   => 'youract-event-details-block-editor',
				'render_callback' => array( $this, 'render_event_details_block' ),
			)
		);
	}

	/**
	 * Renders the Event Details dynamic block.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content    Inner block content.
	 * @param object|null          $block      Parsed block instance.
	 * @return string
	 */
	public function render_event_details_block( array $attributes, string $content, $block = null ): string {
		unset( $content );

		$post_id = 0;
		if ( is_object( $block ) && isset( $block->context ) && is_array( $block->context ) ) {
			$post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : 0;
		}

		if ( $post_id <= 0 ) {
			$post_id = get_the_ID() ? (int) get_the_ID() : 0;
		}

		if ( $post_id <= 0 ) {
			$post_id = (int) get_queried_object_id();
		}

		if ( $post_id <= 0 || 'act_event' !== get_post_type( $post_id ) ) {
			return '';
		}

		$enabled = (bool) apply_filters( 'youract_event_details_enabled', true, $post_id );
		if ( ! $enabled ) {
			return '';
		}

		do_action( 'youract_before_event_details', $post_id );
		$details = $this->renderer->get_event_details_data( $post_id );

		$view_mode = isset( $attributes['viewMode'] ) ? sanitize_key( (string) $attributes['viewMode'] ) : 'auto';
		$in_query_loop = is_object( $block ) && isset( $block->context ) && is_array( $block->context ) && isset( $block->context['queryId'] );
		$details['is_compact'] = ( 'compact' === $view_mode ) || ( 'auto' === $view_mode && $in_query_loop );

		// Block-specific presentation overrides.
		$details['hide_heading']         = true;
		$details['hide_status']          = true;
		$details['hide_disclaimer']      = true;
		$details['event_url_label']      = __( 'More Info', 'youract-content-library' );
		$details['event_url_use_raw_text'] = true;

		$html    = $this->renderer->render_event_details( $post_id, $details );
		do_action( 'youract_after_event_details', $post_id, $details, $html );

		return $html;
	}
}

<?php
/**
 * Opportunity admin editing interface.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles Opportunity admin UI and saving.
 */
class Admin_Opportunity {

	/**
	 * Nonce action.
	 *
	 * @var string
	 */
	private const NONCE_ACTION = 'youract_opportunity_details_save';

	/**
	 * Nonce name.
	 *
	 * @var string
	 */
	private const NONCE_NAME = 'youract_opportunity_details_nonce';

	/**
	 * Opportunity stage labels.
	 *
	 * @var array<string, string>
	 */
	private const STAGES = array(
		'exploring'        => 'Exploring',
		'seeking-partners' => 'Seeking partners',
		'in-progress'      => 'In progress',
		'success-story'    => 'Success story',
		'inactive'         => 'Inactive',
	);

	/**
	 * Hooks registration.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'add_meta_boxes_act_opportunity', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_act_opportunity', array( $this, 'save_meta_box' ), 10, 2 );
		add_filter( 'manage_edit-act_opportunity_columns', array( $this, 'set_columns' ) );
		add_action( 'manage_act_opportunity_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( $this, 'render_stage_filter' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_admin_list' ) );
	}

	/**
	 * Registers the Opportunity details panel.
	 *
	 * @return void
	 */
	public function add_meta_box(): void {
		add_meta_box(
			'youract-opportunity-details',
			__( 'Opportunity Details', 'youract-content-library' ),
			array( $this, 'render_meta_box' ),
			'act_opportunity',
			'normal',
			'default'
		);
	}

	/**
	 * Renders Opportunity fields.
	 *
	 * @param \WP_Post $post Current post.
	 * @return void
	 */
	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$stage = (string) get_post_meta( $post->ID, 'act_opportunity_stage', true );
		$stage = isset( self::STAGES[ $stage ] ) ? $stage : 'exploring';
		?>
		<div class="youract-meta-wrap">
			<p>
				<label for="act_opportunity_stage"><strong><?php esc_html_e( 'Stage', 'youract-content-library' ); ?></strong></label><br />
				<select id="act_opportunity_stage" name="youract_opportunity[stage]">
					<?php foreach ( self::STAGES as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $stage, $value ); ?>><?php echo esc_html__( $label, 'youract-content-library' ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="act_opportunity_subtitle"><strong><?php esc_html_e( 'Subtitle', 'youract-content-library' ); ?></strong></label><br />
				<input id="act_opportunity_subtitle" name="youract_opportunity[subtitle]" type="text" class="widefat" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, 'act_opportunity_subtitle', true ) ); ?>" />
			</p>
			<p>
				<label for="act_opportunity_help_needed"><strong><?php esc_html_e( 'Help needed', 'youract-content-library' ); ?></strong></label><br />
				<textarea id="act_opportunity_help_needed" name="youract_opportunity[help_needed]" class="widefat" rows="5"><?php echo esc_textarea( (string) get_post_meta( $post->ID, 'act_opportunity_help_needed', true ) ); ?></textarea>
				<span class="description"><?php esc_html_e( 'Briefly describe the contributions that would move this opportunity forward now. Use the post content for the complete We’re looking for list.', 'youract-content-library' ); ?></span>
			</p>
			<p>
				<label for="act_opportunity_cta_label"><strong><?php esc_html_e( 'CTA label', 'youract-content-library' ); ?></strong></label><br />
				<input id="act_opportunity_cta_label" name="youract_opportunity[cta_label]" type="text" class="widefat" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, 'act_opportunity_cta_label', true ) ); ?>" />
			</p>
			<p>
				<label for="act_opportunity_cta_url"><strong><?php esc_html_e( 'CTA URL', 'youract-content-library' ); ?></strong></label><br />
				<input id="act_opportunity_cta_url" name="youract_opportunity[cta_url]" type="url" class="widefat" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, 'act_opportunity_cta_url', true ) ); ?>" />
			</p>
			<p>
				<label for="act_opportunity_featured">
					<input id="act_opportunity_featured" name="youract_opportunity[featured]" type="checkbox" value="1" <?php checked( (bool) get_post_meta( $post->ID, 'act_opportunity_featured', true ) ); ?> />
					<?php esc_html_e( 'Featured opportunity', 'youract-content-library' ); ?>
				</label>
			</p>
		</div>
		<?php
	}

	/**
	 * Saves Opportunity details without changing post identity or lifecycle data.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public function save_meta_box( int $post_id, \WP_Post $post ): void {
		if ( 'act_opportunity' !== $post->post_type || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		$input = isset( $_POST['youract_opportunity'] ) && is_array( $_POST['youract_opportunity'] ) ? wp_unslash( $_POST['youract_opportunity'] ) : array();
		$stage = isset( $input['stage'] ) ? sanitize_key( (string) $input['stage'] ) : 'exploring';
		$stage = isset( self::STAGES[ $stage ] ) ? $stage : 'exploring';

		update_post_meta( $post_id, 'act_opportunity_stage', $stage );
		update_post_meta( $post_id, 'act_opportunity_subtitle', sanitize_text_field( (string) ( $input['subtitle'] ?? '' ) ) );
		update_post_meta( $post_id, 'act_opportunity_help_needed', sanitize_textarea_field( (string) ( $input['help_needed'] ?? '' ) ) );
		update_post_meta( $post_id, 'act_opportunity_cta_label', sanitize_text_field( (string) ( $input['cta_label'] ?? '' ) ) );
		update_post_meta( $post_id, 'act_opportunity_cta_url', esc_url_raw( trim( (string) ( $input['cta_url'] ?? '' ) ) ) );
		update_post_meta( $post_id, 'act_opportunity_featured', empty( $input['featured'] ) ? 0 : 1 );
	}

	/**
	 * Adds Stage and Featured admin columns.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function set_columns( array $columns ): array {
		$columns['opportunity_stage']    = __( 'Stage', 'youract-content-library' );
		$columns['opportunity_featured'] = __( 'Featured', 'youract-content-library' );

		return $columns;
	}

	/**
	 * Renders Opportunity admin columns.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_column( string $column, int $post_id ): void {
		if ( 'opportunity_stage' === $column ) {
			$stage = (string) get_post_meta( $post_id, 'act_opportunity_stage', true );
			echo esc_html( self::STAGES[ $stage ] ?? self::STAGES['exploring'] );
			return;
		}

		if ( 'opportunity_featured' === $column ) {
			echo (bool) get_post_meta( $post_id, 'act_opportunity_featured', true ) ? esc_html__( 'Yes', 'youract-content-library' ) : esc_html__( 'No', 'youract-content-library' );
		}
	}

	/**
	 * Renders a Stage filter on the Opportunities list.
	 *
	 * @param string $post_type Current post type.
	 * @return void
	 */
	public function render_stage_filter( string $post_type ): void {
		if ( 'act_opportunity' !== $post_type ) {
			return;
		}

		$current = isset( $_GET['act_opportunity_stage'] ) ? sanitize_key( wp_unslash( $_GET['act_opportunity_stage'] ) ) : '';
		?>
		<select name="act_opportunity_stage">
			<option value=""><?php esc_html_e( 'All stages', 'youract-content-library' ); ?></option>
			<?php foreach ( self::STAGES as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>><?php echo esc_html__( $label, 'youract-content-library' ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Applies the selected Opportunity Stage filter.
	 *
	 * @param \WP_Query $query Admin list query.
	 * @return void
	 */
	public function filter_admin_list( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() || 'act_opportunity' !== $query->get( 'post_type' ) ) {
			return;
		}

		$stage = isset( $_GET['act_opportunity_stage'] ) ? sanitize_key( wp_unslash( $_GET['act_opportunity_stage'] ) ) : '';
		if ( ! isset( self::STAGES[ $stage ] ) ) {
			return;
		}

		$query->set( 'meta_key', 'act_opportunity_stage' );
		$query->set( 'meta_value', $stage );
	}
}

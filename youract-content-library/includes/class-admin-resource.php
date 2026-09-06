<?php
/**
 * Resource admin editing interface.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles Resource admin UI and saving.
 */
class Admin_Resource {

	/**
	 * Nonce action.
	 *
	 * @var string
	 */
	private const NONCE_ACTION = 'youract_resource_details_save';

	/**
	 * Nonce name.
	 *
	 * @var string
	 */
	private const NONCE_NAME = 'youract_resource_details_nonce';

	/**
	 * User meta key for last validation payload.
	 *
	 * @var string
	 */
	private const USER_NOTICE_KEY = 'youract_resource_notice';

	/**
	 * Hooks registration.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'add_meta_boxes_act_resource', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_act_resource', array( $this, 'save_meta_box' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'render_admin_notice' ) );
		add_filter( 'redirect_post_location', array( $this, 'add_notice_query_arg' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'manage_edit-act_resource_columns', array( $this, 'set_columns' ) );
		add_action( 'manage_act_resource_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_filter( 'manage_edit-act_resource_sortable_columns', array( $this, 'set_sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'handle_sorting' ) );
	}

	/**
	 * Sets custom Resource columns.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function set_columns( array $columns ): array {
		$columns['act_resource_type']   = __( 'Resource Type', 'youract-content-library' );
		$columns['source_organization'] = __( 'Source Organization', 'youract-content-library' );
		$columns['resource_status']     = __( 'Status', 'youract-content-library' );
		$columns['last_reviewed']       = __( 'Last Reviewed', 'youract-content-library' );
		$columns['featured_resource']   = __( 'Featured', 'youract-content-library' );

		return $columns;
	}

	/**
	 * Renders custom Resource column values.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_column( string $column, int $post_id ): void {
		if ( 'act_resource_type' === $column ) {
			$terms = get_the_terms( $post_id, 'act_resource_type' );
			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				echo '&#8212;';
				return;
			}

			echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) );
			return;
		}

		if ( 'source_organization' === $column ) {
			$value = (string) get_post_meta( $post_id, '_youract_source_organization', true );
			echo '' !== $value ? esc_html( $value ) : '&#8212;';
			return;
		}

		if ( 'resource_status' === $column ) {
			$labels = array(
				'active'       => __( 'Active', 'youract-content-library' ),
				'needs-review' => __( 'Needs review', 'youract-content-library' ),
				'archived'     => __( 'Archived', 'youract-content-library' ),
			);
			$value = (string) get_post_meta( $post_id, '_youract_resource_status', true );
			echo '' !== $value ? esc_html( $labels[ $value ] ?? $value ) : esc_html( $labels['active'] );
			return;
		}

		if ( 'last_reviewed' === $column ) {
			$value = (string) get_post_meta( $post_id, '_youract_last_reviewed', true );
			echo '' !== $value ? esc_html( $value ) : '&#8212;';
			return;
		}

		if ( 'featured_resource' === $column ) {
			$value = (bool) get_post_meta( $post_id, '_youract_featured_resource', true );
			echo $value ? esc_html__( 'Yes', 'youract-content-library' ) : esc_html__( 'No', 'youract-content-library' );
		}
	}

	/**
	 * Declares sortable Resource columns.
	 *
	 * @param array<string, string> $columns Sortable columns.
	 * @return array<string, string>
	 */
	public function set_sortable_columns( array $columns ): array {
		$columns['source_organization'] = 'source_organization';
		$columns['resource_status']     = 'resource_status';
		$columns['last_reviewed']       = 'last_reviewed';
		$columns['featured_resource']   = 'featured_resource';

		return $columns;
	}

	/**
	 * Applies meta-based sorting for custom columns.
	 *
	 * @param \WP_Query $query Admin list query.
	 * @return void
	 */
	public function handle_sorting( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'act_resource' !== $query->get( 'post_type' ) ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		$map = array(
			'source_organization' => '_youract_source_organization',
			'resource_status'     => '_youract_resource_status',
			'last_reviewed'       => '_youract_last_reviewed',
			'featured_resource'   => '_youract_featured_resource',
		);

		if ( ! isset( $map[ $orderby ] ) ) {
			return;
		}

		$query->set( 'meta_key', $map[ $orderby ] );
		$query->set( 'orderby', 'meta_value' );

		if ( 'featured_resource' === $orderby ) {
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/**
	 * Enqueues admin CSS.
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		$screen = get_current_screen();

		if ( ! $screen || 'act_resource' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			'youract-content-library-admin',
			YOURACT_CONTENT_LIBRARY_URL . 'assets/css/admin.css',
			array(),
			YOURACT_CONTENT_LIBRARY_VERSION
		);
	}

	/**
	 * Registers the Resource details meta box.
	 *
	 * @return void
	 */
	public function add_meta_box(): void {
		add_meta_box(
			'youract-resource-details',
			__( 'Resource Details', 'youract-content-library' ),
			array( $this, 'render_meta_box' ),
			'act_resource',
			'normal',
			'default'
		);
	}

	/**
	 * Renders Resource details fields.
	 *
	 * @param \WP_Post $post Current post.
	 * @return void
	 */
	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$state = $this->get_form_state( $post->ID );

		$values = array(
			'external_url'        => $this->get_form_value( $state, 'external_url', (string) get_post_meta( $post->ID, '_youract_external_url', true ) ),
			'source_organization' => $this->get_form_value( $state, 'source_organization', (string) get_post_meta( $post->ID, '_youract_source_organization', true ) ),
			'resource_summary'    => $this->get_form_value( $state, 'resource_summary', (string) get_post_meta( $post->ID, '_youract_resource_summary', true ) ),
			'access_notes'        => $this->get_form_value( $state, 'access_notes', (string) get_post_meta( $post->ID, '_youract_access_notes', true ) ),
			'last_reviewed'       => $this->get_form_value( $state, 'last_reviewed', (string) get_post_meta( $post->ID, '_youract_last_reviewed', true ) ),
			'resource_status'     => $this->get_form_value( $state, 'resource_status', (string) get_post_meta( $post->ID, '_youract_resource_status', true ) ),
			'featured_resource'   => (bool) $this->get_form_value( $state, 'featured_resource', (bool) get_post_meta( $post->ID, '_youract_featured_resource', true ) ),
		);

		if ( '' === $values['resource_status'] ) {
			$values['resource_status'] = 'active';
		}

		if ( ! empty( $state['errors'] ) ) {
			echo '<div class="notice notice-error" role="alert"><p>' . esc_html__( 'Please correct the highlighted Resource Details fields and save again.', 'youract-content-library' ) . '</p></div>';
		}
		?>
		<div class="youract-meta-wrap">
			<section class="youract-meta-group" aria-labelledby="youract-resource-source-group-label">
				<h3 id="youract-resource-source-group-label"><?php esc_html_e( 'Source and destination', 'youract-content-library' ); ?></h3>
				<p>
					<label for="youract_resource_external_url"><strong><?php esc_html_e( 'External URL', 'youract-content-library' ); ?></strong></label><br />
					<input id="youract_resource_external_url" name="youract_resource[external_url]" type="url" class="widefat" value="<?php echo esc_attr( $values['external_url'] ); ?>" />
					<span class="description"><?php esc_html_e( 'Public link to the resource. Include https:// when available.', 'youract-content-library' ); ?></span>
				</p>
				<p>
					<label for="youract_resource_source_organization"><strong><?php esc_html_e( 'Source organization', 'youract-content-library' ); ?></strong></label><br />
					<input id="youract_resource_source_organization" name="youract_resource[source_organization]" type="text" class="widefat" value="<?php echo esc_attr( $values['source_organization'] ); ?>" />
				</p>
			</section>

			<section class="youract-meta-group" aria-labelledby="youract-resource-description-group-label">
				<h3 id="youract-resource-description-group-label"><?php esc_html_e( 'Description and access notes', 'youract-content-library' ); ?></h3>
				<p>
					<label for="youract_resource_summary"><strong><?php esc_html_e( 'Resource summary', 'youract-content-library' ); ?></strong></label><br />
					<textarea id="youract_resource_summary" name="youract_resource[resource_summary]" class="widefat" rows="4"><?php echo esc_textarea( $values['resource_summary'] ); ?></textarea>
					<span class="description"><?php esc_html_e( 'Brief summary used when excerpts are unavailable.', 'youract-content-library' ); ?></span>
				</p>
				<p>
					<label for="youract_resource_access_notes"><strong><?php esc_html_e( 'Access notes', 'youract-content-library' ); ?></strong></label><br />
					<textarea id="youract_resource_access_notes" name="youract_resource[access_notes]" class="widefat" rows="4"><?php echo esc_textarea( $values['access_notes'] ); ?></textarea>
					<span class="description"><?php esc_html_e( 'Include accessibility limitations, language options, and known accommodations.', 'youract-content-library' ); ?></span>
				</p>
			</section>

			<section class="youract-meta-group" aria-labelledby="youract-resource-status-group-label">
				<h3 id="youract-resource-status-group-label"><?php esc_html_e( 'Review and publishing status', 'youract-content-library' ); ?></h3>
				<p>
					<label for="youract_resource_last_reviewed"><strong><?php esc_html_e( 'Last reviewed', 'youract-content-library' ); ?></strong></label><br />
					<input id="youract_resource_last_reviewed" name="youract_resource[last_reviewed]" type="date" value="<?php echo esc_attr( $values['last_reviewed'] ); ?>" />
					<span class="description"><?php esc_html_e( 'Use YYYY-MM-DD format.', 'youract-content-library' ); ?></span>
				</p>
				<p>
					<label for="youract_resource_status"><strong><?php esc_html_e( 'Resource status', 'youract-content-library' ); ?></strong></label><br />
					<select id="youract_resource_status" name="youract_resource[resource_status]">
						<option value="active" <?php selected( $values['resource_status'], 'active' ); ?>><?php esc_html_e( 'Active', 'youract-content-library' ); ?></option>
						<option value="needs-review" <?php selected( $values['resource_status'], 'needs-review' ); ?>><?php esc_html_e( 'Needs review', 'youract-content-library' ); ?></option>
						<option value="archived" <?php selected( $values['resource_status'], 'archived' ); ?>><?php esc_html_e( 'Archived', 'youract-content-library' ); ?></option>
					</select>
				</p>
				<p>
					<label for="youract_featured_resource">
						<input id="youract_featured_resource" name="youract_resource[featured_resource]" type="checkbox" value="1" <?php checked( $values['featured_resource'] ); ?> />
						<?php esc_html_e( 'Feature this resource', 'youract-content-library' ); ?>
					</label>
				</p>
			</section>
		</div>
		<?php
	}

	/**
	 * Saves Resource details.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post   Post object.
	 * @return void
	 */
	public function save_meta_box( int $post_id, \WP_Post $post ): void {
		if ( 'act_resource' !== $post->post_type ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$raw_input = isset( $_POST['youract_resource'] ) && is_array( $_POST['youract_resource'] ) ? wp_unslash( $_POST['youract_resource'] ) : array();
		$input     = array_map( 'strval', $raw_input );
		$errors    = array();

		$external_url = isset( $input['external_url'] ) ? trim( $input['external_url'] ) : '';
		if ( '' !== $external_url ) {
			$clean_url = esc_url_raw( $external_url );
			if ( '' === $clean_url ) {
				$errors[] = __( 'External URL must be a valid URL.', 'youract-content-library' );
			} else {
				update_post_meta( $post_id, '_youract_external_url', $clean_url );
			}
		} else {
			delete_post_meta( $post_id, '_youract_external_url' );
		}

		update_post_meta( $post_id, '_youract_source_organization', sanitize_text_field( $input['source_organization'] ?? '' ) );
		update_post_meta( $post_id, '_youract_resource_summary', sanitize_textarea_field( $input['resource_summary'] ?? '' ) );
		update_post_meta( $post_id, '_youract_access_notes', sanitize_textarea_field( $input['access_notes'] ?? '' ) );

		$last_reviewed = isset( $input['last_reviewed'] ) ? trim( $input['last_reviewed'] ) : '';
		if ( '' === $last_reviewed ) {
			delete_post_meta( $post_id, '_youract_last_reviewed' );
		} elseif ( Utils::is_valid_date( $last_reviewed ) ) {
			update_post_meta( $post_id, '_youract_last_reviewed', $last_reviewed );
		} else {
			$errors[] = __( 'Last reviewed must be a valid date in YYYY-MM-DD format.', 'youract-content-library' );
		}

		$status = isset( $input['resource_status'] ) ? sanitize_key( $input['resource_status'] ) : 'active';
		if ( in_array( $status, array( 'active', 'needs-review', 'archived' ), true ) ) {
			update_post_meta( $post_id, '_youract_resource_status', $status );
		} else {
			$errors[] = __( 'Resource status must be one of Active, Needs review, or Archived.', 'youract-content-library' );
		}

		$featured = ! empty( $input['featured_resource'] );
		update_post_meta( $post_id, '_youract_featured_resource', $featured ? 1 : 0 );

		if ( ! empty( $errors ) ) {
			$this->store_form_state(
				$post_id,
				array(
					'fields' => $input,
					'errors' => $errors,
				)
			);
			$this->store_notice( $post_id, $errors );
		} else {
			$this->clear_form_state( $post_id );
		}
	}

	/**
	 * Adds a redirect flag when validation errors exist.
	 *
	 * @param string $location Redirect URL.
	 * @param int    $post_id  Post ID.
	 * @return string
	 */
	public function add_notice_query_arg( string $location, int $post_id ): string {
		if ( ! $this->has_notice_for_post( $post_id ) ) {
			return $location;
		}

		return add_query_arg( 'youract_resource_notice', '1', $location );
	}

	/**
	 * Displays stored validation notices.
	 *
	 * @return void
	 */
	public function render_admin_notice(): void {
		if ( ! isset( $_GET['youract_resource_notice'], $_GET['post'] ) ) {
			return;
		}

		$post_id = absint( wp_unslash( $_GET['post'] ) );
		if ( $post_id <= 0 ) {
			return;
		}

		$notice = get_user_meta( get_current_user_id(), self::USER_NOTICE_KEY . '_' . $post_id, true );
		if ( empty( $notice ) || empty( $notice['messages'] ) || ! is_array( $notice['messages'] ) ) {
			return;
		}

		echo '<div class="notice notice-error" role="alert" aria-live="assertive"><p><strong>' . esc_html__( 'Resource details were not fully saved.', 'youract-content-library' ) . '</strong></p><ul>';
		foreach ( $notice['messages'] as $message ) {
			echo '<li>' . esc_html( $message ) . '</li>';
		}
		echo '</ul></div>';

		delete_user_meta( get_current_user_id(), self::USER_NOTICE_KEY . '_' . $post_id );
	}

	/**
	 * Gets one value from preserved form state.
	 *
	 * @param array<string, mixed> $state   State payload.
	 * @param string               $field   Field name.
	 * @param mixed                $default Fallback value.
	 * @return mixed
	 */
	private function get_form_value( array $state, string $field, $default ) {
		if ( isset( $state['fields'] ) && is_array( $state['fields'] ) && array_key_exists( $field, $state['fields'] ) ) {
			return $state['fields'][ $field ];
		}

		return $default;
	}

	/**
	 * Fetches preserved form state for this post.
	 *
	 * @param int $post_id Post ID.
	 * @return array<string, mixed>
	 */
	private function get_form_state( int $post_id ): array {
		$state = get_user_meta( get_current_user_id(), 'youract_resource_state_' . $post_id, true );
		if ( ! is_array( $state ) ) {
			return array();
		}

		delete_user_meta( get_current_user_id(), 'youract_resource_state_' . $post_id );

		return $state;
	}

	/**
	 * Stores form state for a failed validation.
	 *
	 * @param int                 $post_id Post ID.
	 * @param array<string, mixed> $state  State data.
	 * @return void
	 */
	private function store_form_state( int $post_id, array $state ): void {
		update_user_meta( get_current_user_id(), 'youract_resource_state_' . $post_id, $state );
	}

	/**
	 * Clears stored form state.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function clear_form_state( int $post_id ): void {
		delete_user_meta( get_current_user_id(), 'youract_resource_state_' . $post_id );
		delete_user_meta( get_current_user_id(), self::USER_NOTICE_KEY . '_' . $post_id );
	}

	/**
	 * Stores an admin notice for the next request.
	 *
	 * @param int      $post_id  Post ID.
	 * @param string[] $messages Validation messages.
	 * @return void
	 */
	private function store_notice( int $post_id, array $messages ): void {
		update_user_meta(
			get_current_user_id(),
			self::USER_NOTICE_KEY . '_' . $post_id,
			array(
				'messages' => $messages,
			)
		);
	}

	/**
	 * Checks whether a notice exists for this post.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	private function has_notice_for_post( int $post_id ): bool {
		$notice = get_user_meta( get_current_user_id(), self::USER_NOTICE_KEY . '_' . $post_id, true );

		return is_array( $notice ) && ! empty( $notice['messages'] );
	}
}

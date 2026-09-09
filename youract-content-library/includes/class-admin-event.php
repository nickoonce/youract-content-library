<?php
/**
 * Event admin editing interface.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles Event admin UI and saving.
 */
class Admin_Event {

	/**
	 * Nonce action.
	 *
	 * @var string
	 */
	private const NONCE_ACTION = 'youract_event_details_save';

	/**
	 * Nonce name.
	 *
	 * @var string
	 */
	private const NONCE_NAME = 'youract_event_details_nonce';

	/**
	 * User notice key.
	 *
	 * @var string
	 */
	private const USER_NOTICE_KEY = 'youract_event_notice';

	/**
	 * Hooks registration.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'add_meta_boxes_act_event', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_act_event', array( $this, 'save_meta_box' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'render_admin_notice' ) );
		add_filter( 'redirect_post_location', array( $this, 'add_notice_query_arg' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'manage_edit-act_event_columns', array( $this, 'set_columns' ) );
		add_filter( 'manage_act_event_posts_columns', array( $this, 'set_columns' ), 100 );
		add_action( 'manage_act_event_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_filter( 'manage_edit-act_event_sortable_columns', array( $this, 'set_sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'handle_sorting' ) );
	}

	/**
	 * Sets custom Event columns.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function set_columns( array $columns ): array {
		$cb = $columns['cb'] ?? '<input type="checkbox" />';

		return array(
			'cb'           => $cb,
			'title'        => __( 'Title', 'youract-content-library' ),
			'event_format' => __( 'Format', 'youract-content-library' ),
			'event_start'  => __( 'Start', 'youract-content-library' ),
			'event_end'    => __( 'End', 'youract-content-library' ),
		);
	}

	/**
	 * Renders custom Event column values.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_column( string $column, int $post_id ): void {
		if ( 'event_start' === $column ) {
			$start_utc = (int) get_post_meta( $post_id, '_youract_event_start_utc', true );
			$timezone  = (string) get_post_meta( $post_id, '_youract_event_timezone', true );
			if ( ! Utils::is_valid_timezone( $timezone ) ) {
				$timezone = Utils::get_default_timezone();
			}

			if ( $start_utc <= 0 ) {
				echo '&#8212;';
				return;
			}

			echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $start_utc, Utils::get_timezone_object( $timezone ) ) );
			return;
		}

		if ( 'event_end' === $column ) {
			$end_utc  = (int) get_post_meta( $post_id, '_youract_event_end_utc', true );
			$timezone = (string) get_post_meta( $post_id, '_youract_event_timezone', true );
			if ( ! Utils::is_valid_timezone( $timezone ) ) {
				$timezone = Utils::get_default_timezone();
			}

			if ( $end_utc <= 0 ) {
				echo '&#8212;';
				return;
			}

			echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $end_utc, Utils::get_timezone_object( $timezone ) ) );
			return;
		}

		if ( 'event_format' === $column ) {
			$format = (string) get_post_meta( $post_id, '_youract_event_format', true );
			$map    = array(
				'in-person' => __( 'In-person', 'youract-content-library' ),
				'online'    => __( 'Online', 'youract-content-library' ),
				'hybrid'    => __( 'Hybrid', 'youract-content-library' ),
			);
			echo '' !== $format ? esc_html( $map[ $format ] ?? $format ) : '&#8212;';
			return;
		}

	}

	/**
	 * Declares sortable Event columns.
	 *
	 * @param array<string, string> $columns Sortable columns.
	 * @return array<string, string>
	 */
	public function set_sortable_columns( array $columns ): array {
		$columns['event_start']    = 'event_start';
		$columns['event_end']      = 'event_end';
		$columns['event_format']   = 'event_format';

		return $columns;
	}

	/**
	 * Applies meta sort behavior for custom Event columns.
	 *
	 * @param \WP_Query $query Admin list query.
	 * @return void
	 */
	public function handle_sorting( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'act_event' !== $query->get( 'post_type' ) ) {
			return;
		}

		$orderby = $query->get( 'orderby' );
		$map     = array(
			'event_start'     => '_youract_event_start_utc',
			'event_end'       => '_youract_event_end_utc',
			'event_format'    => '_youract_event_format',
		);

		if ( ! isset( $map[ $orderby ] ) ) {
			return;
		}

		$query->set( 'meta_key', $map[ $orderby ] );
		$query->set( 'orderby', 'meta_value' );

		if ( 'event_start' === $orderby || 'event_end' === $orderby ) {
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

		if ( ! $screen || 'act_event' !== $screen->post_type ) {
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
	 * Registers Event details meta box.
	 *
	 * @return void
	 */
	public function add_meta_box(): void {
		add_meta_box(
			'youract-event-details',
			__( 'Event Details', 'youract-content-library' ),
			array( $this, 'render_meta_box' ),
			'act_event',
			'normal',
			'default'
		);
	}

	/**
	 * Renders event details fields.
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$state = $this->get_form_state( $post->ID );

		$timezone = $this->get_form_value( $state, 'event_timezone', (string) get_post_meta( $post->ID, '_youract_event_timezone', true ) );
		if ( '' === $timezone || ! Utils::is_valid_timezone( $timezone ) ) {
			$timezone = Utils::get_default_timezone();
		}

		$start_utc = (int) get_post_meta( $post->ID, '_youract_event_start_utc', true );
		$end_utc   = (int) get_post_meta( $post->ID, '_youract_event_end_utc', true );

		$start_local = Utils::utc_to_local_inputs( $start_utc, $timezone );
		$end_local   = Utils::utc_to_local_inputs( $end_utc, $timezone );

		$values = array(
			'event_timezone'             => $timezone,
			'event_all_day'              => (bool) $this->get_form_value( $state, 'event_all_day', (bool) get_post_meta( $post->ID, '_youract_event_all_day', true ) ),
			'event_start_date'           => $this->get_form_value( $state, 'event_start_date', $start_local['date'] ),
			'event_start_time'           => $this->get_form_value( $state, 'event_start_time', $start_local['time'] ),
			'event_end_date'             => $this->get_form_value( $state, 'event_end_date', $end_local['date'] ),
			'event_end_time'             => $this->get_form_value( $state, 'event_end_time', $end_local['time'] ),
			'event_format'               => $this->get_form_value( $state, 'event_format', (string) get_post_meta( $post->ID, '_youract_event_format', true ) ),
			'event_url'                  => $this->get_form_value( $state, 'event_url', (string) get_post_meta( $post->ID, '_youract_event_url', true ) ),
		);

		if ( '' === $values['event_format'] ) {
			$values['event_format'] = 'in-person';
		}

		if ( ! empty( $state['errors'] ) ) {
			echo '<div class="notice notice-error" role="alert"><p>' . esc_html__( 'Please correct the highlighted Event Details fields and save again.', 'youract-content-library' ) . '</p></div>';
		}
		?>
		<div class="youract-meta-wrap">
			<section class="youract-meta-group" aria-labelledby="youract-event-datetime-group-label">
				<h3 id="youract-event-datetime-group-label"><?php esc_html_e( 'Date and time', 'youract-content-library' ); ?></h3>
				<p>
					<label for="youract_event_timezone"><strong><?php esc_html_e( 'Event time zone', 'youract-content-library' ); ?></strong></label><br />
					<?php echo wp_timezone_choice( $values['event_timezone'], 'youract_event[event_timezone]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="description"><?php esc_html_e( 'Times below are entered and displayed in this time zone.', 'youract-content-library' ); ?></span>
				</p>
				<p>
					<label for="youract_event_all_day">
						<input id="youract_event_all_day" name="youract_event[event_all_day]" type="checkbox" value="1" <?php checked( $values['event_all_day'] ); ?> />
						<?php esc_html_e( 'All-day event', 'youract-content-library' ); ?>
					</label>
				</p>
				<div class="youract-two-col">
					<p>
						<label for="youract_event_start_date"><strong><?php esc_html_e( 'Start date', 'youract-content-library' ); ?></strong></label><br />
						<input id="youract_event_start_date" name="youract_event[event_start_date]" type="date" value="<?php echo esc_attr( $values['event_start_date'] ); ?>" required />
					</p>
					<p>
						<label for="youract_event_start_time"><strong><?php esc_html_e( 'Start time', 'youract-content-library' ); ?></strong></label><br />
						<input id="youract_event_start_time" name="youract_event[event_start_time]" type="time" value="<?php echo esc_attr( $values['event_start_time'] ); ?>" />
					</p>
				</div>
				<div class="youract-two-col">
					<p>
						<label for="youract_event_end_date"><strong><?php esc_html_e( 'End date', 'youract-content-library' ); ?></strong></label><br />
						<input id="youract_event_end_date" name="youract_event[event_end_date]" type="date" value="<?php echo esc_attr( $values['event_end_date'] ); ?>" />
					</p>
					<p>
						<label for="youract_event_end_time"><strong><?php esc_html_e( 'End time', 'youract-content-library' ); ?></strong></label><br />
						<input id="youract_event_end_time" name="youract_event[event_end_time]" type="time" value="<?php echo esc_attr( $values['event_end_time'] ); ?>" />
					</p>
				</div>
			</section>

			<section class="youract-meta-group" aria-labelledby="youract-event-format-group-label">
				<h3 id="youract-event-format-group-label"><?php esc_html_e( 'Format', 'youract-content-library' ); ?></h3>
				<p>
					<label for="youract_event_format"><strong><?php esc_html_e( 'Event format', 'youract-content-library' ); ?></strong></label><br />
					<select id="youract_event_format" name="youract_event[event_format]">
						<option value="in-person" <?php selected( $values['event_format'], 'in-person' ); ?>><?php esc_html_e( 'In-person', 'youract-content-library' ); ?></option>
						<option value="online" <?php selected( $values['event_format'], 'online' ); ?>><?php esc_html_e( 'Online', 'youract-content-library' ); ?></option>
						<option value="hybrid" <?php selected( $values['event_format'], 'hybrid' ); ?>><?php esc_html_e( 'Hybrid', 'youract-content-library' ); ?></option>
					</select>
				</p>
			</section>

			<section class="youract-meta-group" aria-labelledby="youract-event-links-group-label">
				<h3 id="youract-event-links-group-label"><?php esc_html_e( 'External links', 'youract-content-library' ); ?></h3>
				<p>
					<label for="youract_event_url"><strong><?php esc_html_e( 'Official event URL', 'youract-content-library' ); ?></strong></label><br />
					<input id="youract_event_url" name="youract_event[event_url]" type="url" class="widefat" value="<?php echo esc_attr( $values['event_url'] ); ?>" />
				</p>
			</section>
		</div>
		<?php
	}

	/**
	 * Saves event details.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post   Post object.
	 * @return void
	 */
	public function save_meta_box( int $post_id, \WP_Post $post ): void {
		if ( 'act_event' !== $post->post_type ) {
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

		$raw_input = isset( $_POST['youract_event'] ) && is_array( $_POST['youract_event'] ) ? wp_unslash( $_POST['youract_event'] ) : array();
		$input     = array_map( 'strval', $raw_input );
		$errors    = array();

		$timezone = trim( $input['event_timezone'] ?? '' );
		if ( '' === $timezone ) {
			$timezone = Utils::get_default_timezone();
		}
		if ( ! Utils::is_valid_timezone( $timezone ) ) {
			$errors[] = __( 'Event time zone must be valid.', 'youract-content-library' );
		} else {
			update_post_meta( $post_id, '_youract_event_timezone', $timezone );
		}

		$all_day = ! empty( $input['event_all_day'] );
		update_post_meta( $post_id, '_youract_event_all_day', $all_day ? 1 : 0 );

		$format = sanitize_key( $input['event_format'] ?? 'in-person' );
		if ( in_array( $format, array( 'in-person', 'online', 'hybrid' ), true ) ) {
			update_post_meta( $post_id, '_youract_event_format', $format );
		} else {
			$errors[] = __( 'Event format must be In-person, Online, or Hybrid.', 'youract-content-library' );
		}

		$this->save_url_field( $post_id, '_youract_event_url', $input['event_url'] ?? '', __( 'Official event URL must be a valid URL.', 'youract-content-library' ), $errors );

		$this->save_event_datetimes( $post_id, $input, $timezone, $all_day, $errors );

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
	 * Saves the start and end datetime values.
	 *
	 * @param int      $post_id  Post ID.
	 * @param string[] $input    Field values.
	 * @param string   $timezone Timezone.
	 * @param bool     $all_day  All-day flag.
	 * @param string[] $errors   Validation messages.
	 * @return void
	 */
	private function save_event_datetimes( int $post_id, array $input, string $timezone, bool $all_day, array &$errors ): void {
		if ( ! Utils::is_valid_timezone( $timezone ) ) {
			return;
		}

		$start_date = trim( $input['event_start_date'] ?? '' );
		$start_time = trim( $input['event_start_time'] ?? '' );
		$end_date   = trim( $input['event_end_date'] ?? '' );
		$end_time   = trim( $input['event_end_time'] ?? '' );

		if ( '' === $start_date || ! Utils::is_valid_date( $start_date ) ) {
			$errors[] = __( 'Start date is required and must be valid.', 'youract-content-library' );
			return;
		}

		if ( $all_day ) {
			if ( '' === $end_date ) {
				$end_date = $start_date;
			}

			if ( ! Utils::is_valid_date( $end_date ) ) {
				$errors[] = __( 'End date for all-day events must be a valid date.', 'youract-content-library' );
				return;
			}

			$start_range = Utils::local_date_to_utc_day_range( $start_date, $timezone );
			$end_range   = Utils::local_date_to_utc_day_range( $end_date, $timezone );

			if ( null === $start_range || null === $end_range ) {
				$errors[] = __( 'Could not convert all-day dates to UTC. Please verify date and time zone values.', 'youract-content-library' );
				return;
			}

			if ( $end_range['end'] < $start_range['start'] ) {
				$errors[] = __( 'End date cannot be earlier than the start date.', 'youract-content-library' );
				return;
			}

			update_post_meta( $post_id, '_youract_event_start_utc', $start_range['start'] );
			update_post_meta( $post_id, '_youract_event_end_utc', $end_range['end'] );
			return;
		}

		if ( '' === $start_time ) {
			$errors[] = __( 'Start time is required unless the event is marked all-day.', 'youract-content-library' );
			return;
		}

		$start_utc = Utils::local_datetime_to_utc( $start_date, $start_time, $timezone );
		if ( null === $start_utc ) {
			$errors[] = __( 'Start date/time is invalid for the selected time zone.', 'youract-content-library' );
			return;
		}

		$has_end = ( '' !== $end_date || '' !== $end_time );
		$end_utc = 0;

		if ( $has_end ) {
			if ( '' === $end_date || '' === $end_time ) {
				$errors[] = __( 'End date and end time must both be provided when setting an end.', 'youract-content-library' );
				return;
			}

			if ( ! Utils::is_valid_date( $end_date ) ) {
				$errors[] = __( 'End date must be valid.', 'youract-content-library' );
				return;
			}

			$end_utc = Utils::local_datetime_to_utc( $end_date, $end_time, $timezone );
			if ( null === $end_utc ) {
				$errors[] = __( 'End date/time is invalid for the selected time zone.', 'youract-content-library' );
				return;
			}

			if ( $end_utc < $start_utc ) {
				$errors[] = __( 'End date/time cannot be earlier than start date/time.', 'youract-content-library' );
				return;
			}
		}

		update_post_meta( $post_id, '_youract_event_start_utc', $start_utc );
		if ( $has_end ) {
			update_post_meta( $post_id, '_youract_event_end_utc', $end_utc );
		} else {
			delete_post_meta( $post_id, '_youract_event_end_utc' );
		}
	}

	/**
	 * Saves date/time deadline values.
	 *
	 * @param int      $post_id       Post ID.
	 * @param string[] $input         Input payload.
	 * @param string   $timezone      Timezone.
	 * @param string   $field_base    Field base key.
	 * @param string   $meta_key      Meta key.
	 * @param string   $error_message Validation message.
	 * @param string[] $errors        Validation errors.
	 * @return void
	 */
	private function save_deadline_datetime( int $post_id, array $input, string $timezone, string $field_base, string $meta_key, string $error_message, array &$errors ): void {
		if ( ! Utils::is_valid_timezone( $timezone ) ) {
			return;
		}

		$date = trim( $input[ $field_base . '_date' ] ?? '' );
		$time = trim( $input[ $field_base . '_time' ] ?? '' );

		if ( '' === $date && '' === $time ) {
			delete_post_meta( $post_id, $meta_key );
			return;
		}

		if ( '' === $date || '' === $time || ! Utils::is_valid_date( $date ) ) {
			$errors[] = $error_message;
			return;
		}

		$deadline_utc = Utils::local_datetime_to_utc( $date, $time, $timezone );
		if ( null === $deadline_utc ) {
			$errors[] = $error_message;
			return;
		}

		update_post_meta( $post_id, $meta_key, $deadline_utc );
	}

	/**
	 * Saves URL field and records errors.
	 *
	 * @param int      $post_id       Post ID.
	 * @param string   $meta_key      Meta key.
	 * @param string   $raw_value     Raw URL value.
	 * @param string   $error_message Error message.
	 * @param string[] $errors        Error list.
	 * @return void
	 */
	private function save_url_field( int $post_id, string $meta_key, string $raw_value, string $error_message, array &$errors ): void {
		$raw_value = trim( $raw_value );

		if ( '' === $raw_value ) {
			delete_post_meta( $post_id, $meta_key );
			return;
		}

		$clean_url = esc_url_raw( $raw_value );
		if ( '' === $clean_url ) {
			$errors[] = $error_message;
			return;
		}

		update_post_meta( $post_id, $meta_key, $clean_url );
	}

	/**
	 * Adds redirect query argument when notice exists.
	 *
	 * @param string $location Redirect location.
	 * @param int    $post_id  Post ID.
	 * @return string
	 */
	public function add_notice_query_arg( string $location, int $post_id ): string {
		if ( ! $this->has_notice_for_post( $post_id ) ) {
			return $location;
		}

		return add_query_arg( 'youract_event_notice', '1', $location );
	}

	/**
	 * Displays the saved admin notice.
	 *
	 * @return void
	 */
	public function render_admin_notice(): void {
		if ( ! isset( $_GET['youract_event_notice'], $_GET['post'] ) ) {
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

		echo '<div class="notice notice-error" role="alert" aria-live="assertive"><p><strong>' . esc_html__( 'Event details were not fully saved.', 'youract-content-library' ) . '</strong></p><ul>';
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
		$state = get_user_meta( get_current_user_id(), 'youract_event_state_' . $post_id, true );
		if ( ! is_array( $state ) ) {
			return array();
		}

		delete_user_meta( get_current_user_id(), 'youract_event_state_' . $post_id );

		return $state;
	}

	/**
	 * Stores form state for a failed validation.
	 *
	 * @param int                  $post_id Post ID.
	 * @param array<string, mixed> $state   State values.
	 * @return void
	 */
	private function store_form_state( int $post_id, array $state ): void {
		update_user_meta( get_current_user_id(), 'youract_event_state_' . $post_id, $state );
	}

	/**
	 * Clears persisted form state.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function clear_form_state( int $post_id ): void {
		delete_user_meta( get_current_user_id(), 'youract_event_state_' . $post_id );
		delete_user_meta( get_current_user_id(), self::USER_NOTICE_KEY . '_' . $post_id );
	}

	/**
	 * Stores an admin notice for next request.
	 *
	 * @param int      $post_id  Post ID.
	 * @param string[] $messages Messages.
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
	 * Checks notice availability.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	private function has_notice_for_post( int $post_id ): bool {
		$notice = get_user_meta( get_current_user_id(), self::USER_NOTICE_KEY . '_' . $post_id, true );

		return is_array( $notice ) && ! empty( $notice['messages'] );
	}
}

<?php
/**
 * ACF field group registration.
 *
 * @package YourACT\ContentLibrary
 */

namespace YourACT\ContentLibrary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the Resource ACF field group.
 */
class ACF_Fields {

	/**
	 * Hooks registration.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'acf/init', array( $this, 'register_resource_fields' ) );
		add_action( 'admin_notices', array( $this, 'render_missing_acf_notice' ) );
	}

	/**
	 * Registers the Resource field group when ACF is available.
	 *
	 * @return void
	 */
	public function register_resource_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		\acf_add_local_field_group(
			array(
				'key'                   => 'group_youract_resource_details',
				'title'                 => 'Resource Details',
				'fields'                => array(
					array(
						'key'               => 'field_youract_external_url',
						'label'             => 'External URL',
						'name'              => 'youract_external_url',
						'type'              => 'url',
						'instructions'      => 'Canonical public URL for the original resource. Recommended Reading items must be freely accessible without a paid subscription.',
						'required'          => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
					),
					array(
						'key'               => 'field_youract_source_organization',
						'label'             => 'Source',
						'name'              => 'youract_source_organization',
						'type'              => 'text',
						'instructions'      => 'Original publication, organization, or resource provider.',
					),
					array(
						'key'               => 'field_youract_original_author',
						'label'             => 'Original Author',
						'name'              => 'youract_original_author',
						'type'              => 'text',
						'instructions'      => 'Author credited by the original publisher, when known.',
					),
					array(
						'key'               => 'field_youract_original_publication_date',
						'label'             => 'Original Publication Date',
						'name'              => 'youract_original_publication_date',
						'type'              => 'date_picker',
						'display_format'    => 'F j, Y',
						'return_format'     => 'Y-m-d',
						'first_day'         => 0,
						'instructions'      => 'Date the source was originally published, when known.',
					),
					array(
						'key'               => 'field_youract_why_it_matters',
						'label'             => 'Why It Matters',
						'name'              => 'youract_why_it_matters',
						'type'              => 'textarea',
						'new_lines'         => 'wpautop',
						'instructions'      => 'ACT commentary explaining why this resource is relevant to our community.',
					),
					array(
						'key'               => 'field_youract_accessibility_notes',
						'label'             => 'Accessibility Notes',
						'name'              => 'youract_accessibility_notes',
						'type'              => 'textarea',
						'instructions'      => 'Known accessibility features, limitations, language options, transcripts, captions, or accommodations. ACT only shares freely accessible Resources.',
					),
					array(
						'key'               => 'field_youract_last_reviewed',
						'label'             => 'Last Reviewed',
						'name'              => 'youract_last_reviewed',
						'type'              => 'date_picker',
						'display_format'    => 'F j, Y',
						'return_format'     => 'Y-m-d',
						'first_day'         => 0,
						'instructions'      => 'Date the source, link, and free public availability were last verified.',
					),
					array(
						'key'               => 'field_youract_resource_status',
						'label'             => 'Resource Status',
						'name'              => 'youract_resource_status',
						'type'              => 'select',
						'choices'           => array(
							'active'       => 'Active',
							'needs-review' => 'Needs Review',
							'archived'     => 'Archived',
						),
						'default_value'    => 'active',
						'return_format'     => 'value',
						'required'          => 1,
					),
					array(
						'key'               => 'field_youract_featured_resource',
						'label'             => 'Featured Resource',
						'name'              => 'youract_featured_resource',
						'type'              => 'true_false',
						'ui'                => 1,
						'default_value'     => 0,
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'act_resource',
						),
					),
				),
				'position'              => 'normal',
				'label_placement'       => 'top',
				'active'                => 1,
				'show_in_rest'          => 1,
			)
		);
	}

	/**
	 * Displays an admin notice when ACF is unavailable.
	 *
	 * @return void
	 */
	public function render_missing_acf_notice(): void {
		if ( function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p>' . esc_html__( 'Advanced Custom Fields is required for the Resource editing interface in Your ACT Content Library.', 'youract-content-library' ) . '</p></div>';
	}
}

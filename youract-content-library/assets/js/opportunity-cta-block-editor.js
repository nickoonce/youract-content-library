( function ( blocks, element, i18n ) {
	if ( ! blocks || ! blocks.registerBlockType ) {
		return;
	}

	if ( blocks.getBlockType && blocks.getBlockType( 'youract/opportunity-cta' ) ) {
		return;
	}

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'youract/opportunity-cta', {
		title: __( 'YourACT Opportunity CTA', 'youract-content-library' ),
		description: __( 'Displays the CTA link for the current Opportunity post.', 'youract-content-library' ),
		icon: 'admin-links',
		category: 'widgets',
		supports: {
			html: false
		},
		edit: function () {
			return el(
				'p',
				{ className: 'youract-block-placeholder' },
				__( 'YourACT Opportunity CTA will render when the current Opportunity has both a CTA label and URL.', 'youract-content-library' )
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp && window.wp.blocks, window.wp && window.wp.element, window.wp && window.wp.i18n );

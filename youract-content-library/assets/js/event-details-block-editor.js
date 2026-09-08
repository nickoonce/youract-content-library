( function ( blocks, element, i18n ) {
	if ( ! blocks || ! blocks.registerBlockType ) {
		return;
	}

	if ( blocks.getBlockType && blocks.getBlockType( 'youract/event-details' ) ) {
		return;
	}

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'youract/event-details', {
		title: __( 'YourACT Event Details', 'youract-content-library' ),
		description: __( 'Displays structured details for the current Event post.', 'youract-content-library' ),
		icon: 'calendar-alt',
		category: 'widgets',
		supports: {
			html: false
		},
		edit: function () {
			return el(
				'p',
				{ className: 'youract-block-placeholder' },
				__( 'YourACT Event Details will render on the frontend using the current Event context.', 'youract-content-library' )
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp && window.wp.blocks, window.wp && window.wp.element, window.wp && window.wp.i18n );

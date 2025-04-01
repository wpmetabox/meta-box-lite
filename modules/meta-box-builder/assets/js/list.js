jQuery( $ => {
	// Add "Export" option to the Bulk Actions dropdowns.
	$( '<option value="mbb-export">' )
		.text( MBBDialog.export )
		.appendTo( 'select[name="action"], select[name="action2"]' );

	// Toggle upload form.
	var $form = $( $( '#mbb-import-form' ).html() ).insertAfter( '.wp-header-end' );
	var $toggle = $( '<button class="page-title-action">' )
		.text( MBBDialog.import )
		.insertAfter( '.page-title-action' );

	$toggle.on( 'click', e => {
		e.preventDefault();
		$form.toggle();
	} );

	// Enable submit button when selecting a file.
	const $input = $form.find( 'input[type="file"]' ),
		$submit = $form.find( 'input[type="submit"]' );

	$input.on( 'change', () => {
		$submit.prop( 'disabled', !$input.val() );
	} );
} );
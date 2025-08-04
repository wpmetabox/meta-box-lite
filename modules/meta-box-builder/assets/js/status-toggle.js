jQuery( function ( $ ) {
	$( document ).on( 'change', '.rwmb-switch', function () {
		const $this = $( this );

		$.post( ajaxurl, {
			action: 'mbb_toggle_status',
			post_id: $this.data( 'id' ),
			checked: $this.is( ':checked' ),
			_ajax_nonce: mbbStatusToggle.nonce
		}, function ( response ) {
			if ( !response.success ) {
				$this.prop( 'checked', !$this.is( ':checked' ) );
				alert( response.data.message );
			}
		} );
	} );
} );
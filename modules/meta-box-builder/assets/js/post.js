Object.keys( MBB.meta_box_post_ids ).forEach( id => {
	let metabox = document.getElementById( id );
	if ( !metabox ) {
		return;
	}
	let a = document.createElement( 'a' );
	const params = new URLSearchParams({
		slug: MBB.meta_box_post_ids[ id ]
	})
	const url = new URL( `${MBB.base_url}` );
	url.search = params;
	a.setAttribute( 'href', url.toString() );
	a.setAttribute( 'class', 'dashicons dashicons-admin-generic mbb-settings' );
	a.setAttribute( 'title', MBB.title );
	a.setAttribute( 'target', '_blank' );
	let actions = metabox.querySelector( '.handle-actions' );
	if ( actions ) {
		actions.prepend( a );
	}
} );

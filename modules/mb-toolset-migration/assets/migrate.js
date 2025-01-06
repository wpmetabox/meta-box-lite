( function ( document, i18n ) {
	'use strict';

	const status = document.querySelector( '#status' ),
		button = document.querySelector( '#process' );

	button.addEventListener( 'click', async () => {
		printMessage( i18n.start );

		await resetCounter();
		printMessage( i18n.migratingPostTypes );
		await migrate( 'post_types' );

		await resetCounter();
		printMessage( i18n.migratingTaxonomies );
		await migrate( 'taxonomies' );

		await resetCounter();
		printMessage( i18n.migratingFieldGroups );
		await migrate( 'field_groups' );

		await resetCounter();
		printMessage( i18n.migratingPosts );
		await migrate( 'posts' );

		await resetCounter();
		printMessage( i18n.migratingTerms );
		await migrate( 'terms' );

		await resetCounter();
		printMessage( i18n.migratingUsers );
		await migrate( 'users' );

		await resetCounter();
		printMessage( i18n.migratingRelationship );
		await migrate( 'relationship' );

		printMessage( i18n.done );
	} );

	const resetCounter = () => get( `${ajaxurl}?action=mbts_reset_counter` );

	async function migrate( type ) {
		const response = await get( `${ajaxurl}?action=mbts_migrate&type=${type}` );
		if ( response.data.type == 'continue' ) {
			printMessage( response.data.message );
			await migrate( type );
		}
	}

	async function get( url ) {
		const response = await fetch( url );
	    const json = await response.json();
		if ( ! response.ok ) {
	       	throw Error( json.data );
	    }
		return json;
	}

	const printMessage = text => status.innerHTML += `<p>${text}</p>`;
} )( document, MbTs );

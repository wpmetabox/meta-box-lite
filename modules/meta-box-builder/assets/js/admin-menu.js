document.addEventListener( 'DOMContentLoaded', () => {
	const key = 'mbb-admin-menu-folded';
	const body = document.body;

	const setupAdminMenuWidth = () => {
		if ( body.classList.contains( 'folded' ) ) {
			return;
		}

		const adminMenu = document.querySelector( '#adminmenu' );
		const app = document.querySelector( '.mb' );

		if ( adminMenu && app ) {
			const width = adminMenu.offsetWidth;

			app.style.setProperty( '--left', `${ width }px` );
		}
	};

	// Only handle the menu state on small screens.
	if ( window.innerWidth >= 1600 ) {
		return;
	}

	// Save menu state to local storage.
	// Note that WordPress auto handles the body class, so we have to use setTimeout to ensure the class is added/removed.
	document.getElementById( 'collapse-button' ).addEventListener( 'click', () => {
		setTimeout( () => {
			const isFolded = body.classList.contains( 'folded' );
			localStorage.setItem( key, isFolded ? 'true' : 'false' );
			setupAdminMenuWidth();
		}, 100 );
	} );

	const initMenuState = () => {
		const storedState = localStorage.getItem( key );

		// No preference saved, default to folded.
		if ( storedState === null ) {
			body.classList.add( 'folded' );
			localStorage.setItem( key, 'true' );
			return;
		}

		body.classList.toggle( 'folded', storedState === 'true' );
		setupAdminMenuWidth();
	};

	initMenuState();
} );
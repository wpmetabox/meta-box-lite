document.addEventListener( 'DOMContentLoaded', () => {
	const key = 'mbb-admin-menu-folded';
	const body = document.body;

	const setCssVariable = () => {
		const adminMenu = document.querySelector( '#adminmenu' );
		const app = document.querySelector( '.mb' );

		if ( adminMenu && app ) {
			const width = adminMenu.offsetWidth;
			app.style.setProperty( '--left', `${ width }px` );
		}
	};

	const initMenuState = () => {
		let storedState = localStorage.getItem( key );

		// Collapse admin menu by default on small screens.
		if ( storedState === null && window.innerWidth < 1600) {
			storedState = 'true';
			localStorage.setItem( key, storedState );
		}

		body.classList.toggle( 'folded', storedState === 'true' );
		setCssVariable();
	};

	initMenuState();

	// Persist menu state to localStorage on expand/collapse.
	// setTimeout needed - WordPress adds/removes the body class asynchronously.
	document.getElementById( 'collapse-button' ).addEventListener( 'click', () => {
		setTimeout( () => {
			const isFolded = body.classList.contains( 'folded' );
			localStorage.setItem( key, isFolded ? 'true' : 'false' );
			setCssVariable();
		}, 100 );
	} );
} );

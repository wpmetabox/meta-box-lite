import { create } from 'zustand';

const ignoreTypes = [ 'button', 'custom_html', 'divider', 'heading', 'tab', 'group' ];

const flatten = obj => {
	let fields = obj.fields || {};

	let value = {};
	Object.entries( fields ).map( ( [ id, field ] ) => {
		const type = field.type || 'text';
		value = { ...value, ...flatten( field ) };
		if ( !ignoreTypes.includes( type ) ) {
			value = { ...value, [ id ]: field.id };
		}
	} );

	return value;
};

const useFieldIds = create( set => ( {
	ids: flatten( MbbApp ),

	update: ( id, field ) => set( state => {
		const type = field.type || 'text';
		const ids  = ignoreTypes.includes( type ) ? { ...state.ids } : { ...state.ids, [ id ]: field.id };

		return { ids };
	} ),

	remove: id => set( state => {
		let ids = { ...state.ids };
		delete ids[ id ];
		return { ids };
	} )
} ) );

export default useFieldIds;
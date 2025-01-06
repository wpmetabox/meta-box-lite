import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { getFieldValue, ucwords, uniqid } from '../functions';
import useFieldIds from "./useFieldIds";

const useFields = ( initialFields, baseId ) => {
	const updateId = useFieldIds( state => state.update );
	const removeId = useFieldIds( state => state.remove );
	const [ fields, setFields ] = useState( initialFields );
	const [ expandAll, setExpandAll ] = useState( false );

	const add = type => {
		const id = `${ type }_${ uniqid() }`;
		const newField = {
			_id: id,
			_new: true,
			_expand: false,
			id,
			type,
			name: ucwords( type, '_' ),
		};

		updateId( id, newField );
		setFields( prev => [ ...prev, newField ] );
	};

	const remove = id => {
		removeId( id );
		setFields( prev => prev.filter( field => field._id !== id ) );
	};

	const duplicate = id => {
		let newField = getFieldValue( `${ baseId }[${ id }]` );
		const newId = `${ newField.type }_${ uniqid() }`;

		newField.id = newId;
		newField._id = newId;
		newField._new = true;
		newField._expand = false;
		newField.name += __( ' (Copy)', 'meta-box-builder' );

		updateId( newId, newField );
		setFields( prev => {
			const index = prev.findIndex( field => field._id === id );
			let newFields = [ ...prev ];
			newFields.splice( index + 1, 0, newField );

			return newFields;
		} );
	};

	const updateType = ( id, type ) => setFields( prev => {
		const index = prev.findIndex( field => field._id === id );
		let newFields = [ ...prev ];

		// Maintain existing input values.
		newFields[ index ] = { ...getFieldValue( `${ baseId }[${ id }]` ), type };

		return newFields;
	} );

	const toggle = id => setFields( prev => {
		const index = prev.findIndex( field => field._id === id );
		let newFields = [ ...prev ];

		newFields[ index ]._expand = !newFields[ index ]._expand;

		return newFields;
	} );

	const toggleAll = () => {
		const current = !expandAll;

		setExpandAll( prev => !prev );
		setFields( prev => [ ...prev ].map( field => ( { ...field, _expand: current } ) ) );
	};

	return {
		fields,
		add,
		remove,
		duplicate,
		updateType,
		setFields,
		toggle,
		toggleAll,
		expandAll,
	};
};

export default useFields;
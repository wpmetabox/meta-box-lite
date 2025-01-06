import { useState } from "@wordpress/element";

// Get correct key in the last [].
const getKey = name => name.replace( /\]/g, '' ).split( '[' ).pop();

const useFieldData = field => {
	const defaults = field.type === 'tab' ? { icon_type: 'dashicons' } : {};
	const [ data, updateData ] = useState( { ...defaults, ...field } );

	const updateFieldData = ( key, value ) => {
		updateData( prev => ( { ...prev, [ getKey( key ) ]: value } ) );
	};

	return {
		data,
		updateFieldData,
	};
};

export default useFieldData;
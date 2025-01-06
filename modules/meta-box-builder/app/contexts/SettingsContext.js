import { createContext, useState } from '@wordpress/element';
import { getSettings } from "../functions";

const SettingsContext = createContext( {} );

// Get correct key in the last [].
const getKey = name => name.replace( /\]/g, '' ).split( '[' ).pop();

const SettingsProvider = ( { children } ) => {
	const [ data, updateData ] = useState( getSettings() );

	const updateSettings = ( key, value ) => {
		updateData( prev => ( { ...prev, [ getKey( key ) ]: value } ) );
	};

	return (
		<SettingsContext.Provider value={ { settings: data, updateSettings } }>
			{ children }
		</SettingsContext.Provider>
	);
};

export { SettingsContext, SettingsProvider };

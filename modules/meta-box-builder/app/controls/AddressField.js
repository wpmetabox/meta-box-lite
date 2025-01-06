import useFieldIds from '../hooks/useFieldIds';
import DivRow from './DivRow';
import { useContext } from '@wordpress/element';
import { SettingsContext } from "../contexts/SettingsContext";
import FieldInserter from './FieldInserter';

const AddressField = ( { name, componentId, placeholder, defaultValue, ...rest } ) => {
	const ids = useFieldIds( state => state.ids );
	const { settings } = useContext( SettingsContext );
	const fields = Array.from( new Set( Object.values( ids ) ) );

	const handleSelectItem = ( inputRef, value ) => {
		const address = !inputRef.current.value ? '' : inputRef.current.value + ',';
		inputRef.current.value = address + `${ settings.prefix || '' }${ value }`;
	};

	return (
		<DivRow htmlFor={ componentId } { ...rest }>
			<FieldInserter id={ componentId } name={ name } defaultValue={ defaultValue } placeholder={ placeholder } required={ true } items={ fields } onSelect={ handleSelectItem } />
		</DivRow>
	);
};

export default AddressField;
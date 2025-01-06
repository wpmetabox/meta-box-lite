import DivRow from './DivRow';
import { useToggle } from '/hooks/useToggle';

const Select = ( { componentId, name, options, defaultValue, onChange, placeholder, updateFieldData, ...rest } ) => {
	const toggle = useToggle( componentId );

	const handleChange = e => {
		toggle();
		onChange && onChange( e );
		updateFieldData && updateFieldData( name, e.target.value );
	};

	return <DivRow htmlFor={ componentId } { ...rest }>
		<select placeholder={ placeholder } id={ componentId } name={ name } defaultValue={ defaultValue } onChange={ handleChange }>
			<option value=""></option>
			{
				Object.entries( options ).map( ( [ value, label ] ) => <option key={ value } value={ value }>{ label }</option> )
			}
		</select>
	</DivRow>;
};

export default Select;
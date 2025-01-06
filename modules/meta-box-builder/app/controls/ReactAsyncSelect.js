import { useState } from "@wordpress/element";
import AsyncSelect from 'react-select/async';
import { ensureArray } from '/functions';

const ReactAsyncSelect = ( { baseName, isMulti = true, className, defaultValue, ...rest } ) => {
	const [ labels, setLabels ] = useState( ensureArray( defaultValue.label || [] ) );

	let values = defaultValue.value || [];
	values = ensureArray( values );

	let transformedDefaultValue;
	if ( values ) {
		transformedDefaultValue = values.map( ( value, index ) => ( { value, label: labels[ index ] } ) );
	}

	const onChange = items => {
		const newLabels = Array.isArray( items ) ? items.map( item => item.label ) : [items.label];
		setLabels( newLabels );
	};

	return <>
		<AsyncSelect
			name={ isMulti ? `${ baseName }[value][]` : `${ baseName }[value]` }
			className={ `react-select ${ className }` }
			classNamePrefix="react-select"
			isMulti={ isMulti }
			defaultOptions
			defaultValue={ isMulti ? transformedDefaultValue : defaultValue }
			onChange={ onChange }
			{ ...rest }
		/>
		{
			labels.map( label => <input key={ label } type="hidden" name={ isMulti ? `${ baseName }[label][]` : `${ baseName }[label]` } defaultValue={ label } /> )
		}
	</>;
};

export default ReactAsyncSelect;
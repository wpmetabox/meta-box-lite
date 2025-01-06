import { Suspense } from "@wordpress/element";
import { getControlParams } from '/functions';

const Content = ( { id, controls, field, parent = '', updateFieldType, nameIdData, updateFieldData } ) => {
	const getControlComponent = control => {
		let [ Control, input, defaultValue ] = getControlParams( control, field, () => {}, true );

		// Safe fallback to 'text' for not-recommended HTML5 field types.
		if ( control.setting === 'type' ) {
			defaultValue = [ 'datetime-local', 'month', 'tel', 'week' ].includes( defaultValue ) ? 'text' : defaultValue;
		}

		return <Control
			fieldId={ id }
			componentName={ control.setting }
			componentId={ `fields-${ id }-${ control.setting }` }
			{ ...control.props }
			name={ `fields${ parent }[${ id }]${ input }` }
			defaultValue={ defaultValue }

			// For Type: allow to change field type.
			updateFieldType={ updateFieldType }

			nameIdData={ nameIdData }
			updateFieldData={ updateFieldData }

			fieldType={ field.type }
		/>;
	};

	return (
		<div className="og-item__content">
			{ controls.map( control => <Suspense fallback={ null } key={ control.setting }>{ getControlComponent( control ) }</Suspense> ) }
		</div>
	);
};

export default Content;
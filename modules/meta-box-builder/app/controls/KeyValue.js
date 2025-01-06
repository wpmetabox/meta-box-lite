import { Dashicon } from "@wordpress/components";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import DivRow from './DivRow';
import FieldInserter from './FieldInserter';
import { uniqid } from "/functions";

const KeyValue = ( {
	defaultValue,
	name,
	keyPlaceholder = __( 'Enter key', 'meta-box-builder' ),
	valuePlaceholder = __( 'Enter value', 'meta-box-builder' ),
	keys = [],
	values = [],
	...rest
} ) => {
	const [ items, setItems ] = useState( Object.values( defaultValue || {} ) );

	const add = () => setItems( prev => [ ...prev, { key: '', value: '', id: uniqid() } ] );
	const remove = id => setItems( prev => prev.filter( item => item.id !== id ) );

	return (
		<DivRow { ...rest }>
			{
				items.map( item => (
					<Item
						key={ item.id }
						item={ item }
						remove={ remove }
						name={ `${ name }[${ item.id }]` }
						keysList={ keys }
						values={ `${ name }-values` }
						valuesList={ values }
						keyPlaceholder={ keyPlaceholder }
						valuePlaceholder={ valuePlaceholder }
					/>
				) )
			}
			<button type="button" className="button" onClick={ add }>{ __( '+ Add New', 'meta-box-builder' ) }</button>
		</DivRow>
	);
};

const Item = ( { name, keysList, valuesList, item, remove, keyPlaceholder, valuePlaceholder } ) => {
	const [ values, setValues ] = useState( valuesList );

	const handleSelect = ( inputRef, value ) => {
		inputRef.current.value = value;

		const newValuesList = objectDepth( valuesList ) == 1 ? valuesList : valuesList[ value ] ? valuesList[ value ] : valuesList['default'];
		setValues( newValuesList || [] );
	};

	return (
		<div className="og-attribute">
			<input type="hidden" name={ `${ name }[id]` } defaultValue={ item.id } />
			<FieldInserter placeholder={ keyPlaceholder } name={ `${ name }[key]` } defaultValue={ item.key } items={ keysList }  onSelect={ handleSelect } onChange={ handleSelect } />
			<FieldInserter placeholder={ valuePlaceholder } name={ `${ name }[value]` } defaultValue={ item.value } items={ values } />
			<button type="button" className="og-remove" title={ __( 'Remove', 'meta-box-builder' ) } onClick={ () => remove( item.id ) }><Dashicon icon="dismiss" /></button>
		</div>
	);
};

const objectDepth = object => Object( object ) === object ? 1 + Math.max( -1, ...Object.values( object ).map( objectDepth ) ) : 0;

export default KeyValue;
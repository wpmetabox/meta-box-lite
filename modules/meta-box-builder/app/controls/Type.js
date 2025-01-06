import useApi from "../hooks/useApi";
import DivRow from './DivRow';

const Type = ( { fieldId, name, componentId, defaultValue, updateFieldType, ...rest } ) => {
	const categories = useApi( 'field-categories', [] );
	const onChange = e => updateFieldType( fieldId, e.target.value );

	return (
		<DivRow htmlFor={ componentId } { ...rest }>
			<select id={ componentId } name={ name } defaultValue={ defaultValue } onChange={ onChange }>
				{ categories.map( category => <Category key={ category.slug } category={ category } /> ) }
			</select>
		</DivRow>
	);
};

const Category = ( { category } ) => {
	const types = useApi( 'field-types', {} );
	const fields = Object.entries( types ).filter( ( [ type, field ] ) => field.category === category.slug );

	return (
		<optgroup label={ category.title }>
			{ fields.map( entry => <option key={ entry[ 0 ] } value={ entry[ 0 ] }>{ entry[ 1 ].title }</option> ) }
		</optgroup>
	);
};

export default Type;
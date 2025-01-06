import { Dashicon } from "@wordpress/components";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import useFieldIds from '../hooks/useFieldIds';
import DivRow from './DivRow';
import FieldInserter from './FieldInserter';
import { uniqid } from '/functions';

const ConditionalLogic = ( { defaultValue, name, ...rest } ) => {
	const [ rules, setRules ] = useState( Object.values( defaultValue.when || {} ) );

	const addRule = () => setRules( prev => [ ...prev, { name: '', operator: '=', value: '', id: uniqid() } ] );
	const removeRule = id => setRules( prev => prev.filter( rule => rule.id !== id ) );

	const ids = useFieldIds( state => state.ids );
	const fields = Array.from( new Set( Object.values( ids ) ) );

	return (
		<DivRow className="og-include-exclude" { ...rest }>
			{ rules.length > 0 && <Intro name={ name } defaultValue={ defaultValue } /> }
			{
				rules.map( rule => <Rule
					key={ rule.id }
					rule={ rule }
					fields={ fields }
					name={ `${ name }[when][${ rule.id }]` }
					removeRule={ removeRule }
				/> )
			}
			<button type="button" className="button" onClick={ addRule }>{ __( '+ Add Rule', 'meta-box-builder' ) }</button>
		</DivRow>
	);
};

const Intro = ( { name, defaultValue } ) => (
	<div className="og-include-exclude__intro">
		<select name={ `${ name }[type]` } defaultValue={ defaultValue.type || 'visible' }>
			<option value="visible">{ __( 'Visible', 'meta-box-builder' ) }</option>
			<option value="hidden">{ __( 'Hidden', 'meta-box-builder' ) }</option>
		</select>
		{ __( 'when', 'meta-box-builder' ) }
		<select name={ `${ name }[relation]` } defaultValue={ defaultValue.relation || 'or' }>
			<option value="or">{ __( 'any', 'meta-box-builder' ) }</option>
			<option value="and">{ __( 'all', 'meta-box-builder' ) }</option>
		</select>
		{ __( 'conditions match', 'meta-box-builder' ) }
	</div>
);

const Rule = ( { rule, fields, name, removeRule } ) => (
	<div className="og-include-exclude__rule og-attribute">
		<input type="hidden" name={ `${ name }[id]` } defaultValue={ rule.id } />
		<FieldInserter name={ `${ name }[name]` } defaultValue={ rule.name } placeholder={ __( 'Enter or select a field ID', 'meta-box-builder' ) } items={ fields } isID = { true } />
		<select name={ `${ name }[operator]` } className="og-include-exclude__operator" defaultValue={ rule.operator }>
			<option value="=">{ __( '=', 'meta-box-builder' ) }</option>
			<option value=">">{ __( '>', 'meta-box-builder' ) }</option>
			<option value="<">{ __( '<', 'meta-box-builder' ) }</option>
			<option value=">=">{ __( '>=', 'meta-box-builder' ) }</option>
			<option value="<=">{ __( '<=', 'meta-box-builder' ) }</option>
			<option value="!=">{ __( '!=', 'meta-box-builder' ) }</option>
			<option value="contains">{ __( 'contains', 'meta-box-builder' ) }</option>
			<option value="not contains">{ __( 'not contains', 'meta-box-builder' ) }</option>
			<option value="starts with">{ __( 'starts with', 'meta-box-builder' ) }</option>
			<option value="not starts with">{ __( 'not starts with', 'meta-box-builder' ) }</option>
			<option value="ends with">{ __( 'ends with', 'meta-box-builder' ) }</option>
			<option value="not ends with">{ __( 'not ends with', 'meta-box-builder' ) }</option>
			<option value="between">{ __( 'between', 'meta-box-builder' ) }</option>
			<option value="not between">{ __( 'not between', 'meta-box-builder' ) }</option>
			<option value="in">{ __( 'in', 'meta-box-builder' ) }</option>
			<option value="not in">{ __( 'not in', 'meta-box-builder' ) }</option>
			<option value="match">{ __( 'match', 'meta-box-builder' ) }</option>
			<option value="not match">{ __( 'not match', 'meta-box-builder' ) }</option>
		</select>
		<input defaultValue={ rule.value } type="text" placeholder={ __( 'Enter a value', 'meta-box-builder' ) } name={ `${ name }[value]` } />
		<button type="button" className="og-remove" title={ __( 'Remove', 'meta-box-builder' ) } onClick={ () => removeRule( rule.id ) }><Dashicon icon="dismiss" /></button>
	</div>
);

export default ConditionalLogic;
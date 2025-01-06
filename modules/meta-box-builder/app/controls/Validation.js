import { Dashicon } from "@wordpress/components";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import DivRow from './DivRow';
import { ensureArray, uniqid } from '/functions';

const Validation = ( { defaultValue, name, ...rest } ) => {
	const [ rules, setRules ] = useState( ensureArray( defaultValue ) );
	const addRule = () => setRules( prev => [ ...prev, { name: 'required', value: '', message: '', id: uniqid() } ] );
	const removeRule = id => setRules( prev => prev.filter( rule => rule.id !== id ) );

	return (
		<DivRow className="og-include-exclude" { ...rest }>
			{
				rules.map( rule => <Rule
					key={ rule.id }
					rule={ rule }
					baseName={ `${ name }[${ rule.id }]` }
					removeRule={ removeRule }
				/> )
			}
			<button type="button" className="button" onClick={ addRule }>{ __( '+ Add Rule', 'meta-box-builder' ) }</button>
		</DivRow>
	);
};

const Rule = ( { rule, baseName, removeRule } ) => {
	const [ name, setName ] = useState( rule.name );
	const onChangeName = e => setName( e.target.value );

	return (
		<div className="og-include-exclude__rule og-attribute">
			<input type="hidden" name={ `${ baseName }[id]` } defaultValue={ rule.id } />
			<select name={ `${ baseName }[name]` } className="og-include-exclude__name" defaultValue={ rule.name } onChange={ onChangeName }>
				<option value="required">{ __( 'Required', 'meta-box-builder' ) }</option>
				<option value="minlength">{ __( 'Min length', 'meta-box-builder' ) }</option>
				<option value="maxlength">{ __( 'Max length', 'meta-box-builder' ) }</option>
				<option value="rangelength">{ __( 'Range length', 'meta-box-builder' ) }</option>
				<option value="min">{ __( 'Min value', 'meta-box-builder' ) }</option>
				<option value="max">{ __( 'Max value', 'meta-box-builder' ) }</option>
				<option value="range">{ __( 'Range', 'meta-box-builder' ) }</option>
				<option value="step">{ __( 'Step', 'meta-box-builder' ) }</option>
				<option value="email">{ __( 'Email', 'meta-box-builder' ) }</option>
				<option value="url">{ __( 'URL', 'meta-box-builder' ) }</option>
				<option value="date">{ __( 'Date', 'meta-box-builder' ) }</option>
				<option value="dateISO">{ __( 'ISO date', 'meta-box-builder' ) }</option>
				<option value="number">{ __( 'Decimal number', 'meta-box-builder' ) }</option>
				<option value="digits">{ __( 'Digits only', 'meta-box-builder' ) }</option>
				<option value="creditcard">{ __( 'Credit card number', 'meta-box-builder' ) }</option>
				<option value="phoneUS">{ __( 'US phone number', 'meta-box-builder' ) }</option>
				<option value="accept">{ __( 'MIME types', 'meta-box-builder' ) }</option>
				<option value="extension">{ __( 'File extensions', 'meta-box-builder' ) }</option>
				<option value="equalTo">{ __( 'Equals to another field', 'meta-box-builder' ) }</option>
				<option value="remote">{ __( 'Remote', 'meta-box-builder' ) }</option>
			</select>
			{
				[ 'required', 'email', 'url', 'date', 'dateISO', 'number', 'digits', 'creditcard', 'phoneUS' ].includes( name ) &&
				<input type="checkbox" style={ { display: 'none' } } defaultChecked defaultValue={ true } name={ `${ baseName }[value]` } />
			}
			{
				[ 'minlength', 'maxlength', 'min', 'max', 'step', 'accept', 'extension', 'equalTo', 'remote' ].includes( name ) &&
				<input defaultValue={ rule.value } type="text" placeholder={ __( 'Enter a value', 'meta-box-builder' ) } name={ `${ baseName }[value]` } />
			}
			{
				[ 'rangelength', 'range' ].includes( name ) &&
				<input defaultValue={ rule.value } type="text" placeholder={ __( 'Ex. 2,6', 'meta-box-builder' ) } title={ __( 'Separate values by a comma', 'meta-box-builder' ) } name={ `${ baseName }[value]` } />
			}
			<input defaultValue={ rule.message } type="text" placeholder={ __( 'Custom error message', 'meta-box-builder' ) } name={ `${ baseName }[message]` } />
			<button type="button" className="og-remove" title={ __( 'Remove', 'meta-box-builder' ) } onClick={ () => removeRule( rule.id ) }><Dashicon icon="dismiss" /></button>
		</div>
	);
};

export default Validation;
import { Dashicon } from "@wordpress/components";
import { useEffect, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { fetcher, uniqid } from "../functions";
import useObjectType from "../hooks/useObjectType";
import DivRow from './DivRow';
import KeyValue from './KeyValue';
import ReactAsyncSelect from './ReactAsyncSelect';

const ShowHide = ( { defaultValue } ) => {
	const objectType = useObjectType( state => state.type );
	const [ rules, setRules ] = useState( Object.values( defaultValue.rules || {} ) );

	const addRule = () => setRules( prev => [ ...prev, { name: 'template', value: '', id: uniqid() } ] );
	const removeRule = id => setRules( prev => prev.filter( rule => rule.id !== id ) );

	return ( objectType !== 'block' &&
		<DivRow
			className="og-include-exclude"
			label={ `<a href="https://metabox.io/plugins/meta-box-show-hide/" target="_blank" rel="noopener norefferer">${ __( 'Toggle rules', 'meta-box-builder' ) }</a>` }
			tooltip={ __( 'Conditions to toggle visibility of the field group. For each rule, maximum 10 items are displayed. To select other items, please use the search.', 'meta-box-builder' ) }
		>
			{ rules.length > 0 && <Intro defaultValue={ defaultValue } /> }
			{
				rules.map( rule => <Rule
					key={ rule.id }
					rule={ rule }
					baseName={ `settings[show_hide][rules][${ rule.id }]` }
					removeRule={ removeRule }
					objectType={ objectType }
				/> )
			}
			<button type="button" className="button" onClick={ addRule }>{ __( '+ Add Rule', 'meta-box-builder' ) }</button>
		</DivRow>
	);
};

const Intro = ( { defaultValue } ) => (
	<div className="og-include-exclude__intro">
		<select name="settings[show_hide][type]" defaultValue={ defaultValue.type || 'show' }>
			<option value="show">{ __( 'Show', 'meta-box-builder' ) }</option>
			<option value="hide">{ __( 'Hide', 'meta-box-builder' ) }</option>
		</select>
		{ __( 'when', 'meta-box-builder' ) }
		<select name="settings[show_hide][relation]" defaultValue={ defaultValue.relation || 'OR' }>
			<option value="OR">{ __( 'any', 'meta-box-builder' ) }</option>
			<option value="AND">{ __( 'all', 'meta-box-builder' ) }</option>
		</select>
		{ __( 'conditions match', 'meta-box-builder' ) }
	</div>
);

const Rule = ( { rule, baseName, removeRule, objectType } ) => {
	const [ name, setName ] = useState( rule.name );
	const onChangeName = e => setName( e.target.value );
	const loadOptions = s => fetcher( 'show-hide', { name, s } );

	// Validate rule name.
	useEffect( () => {
		if ( objectType !== 'post' && name !== 'input_value' ) {
			setName( 'input_value' );
		}
	}, [ objectType ] );

	return (
		<div className={ `og-include-exclude__rule og-attribute${ name === 'input_value' ? ' og-show-hide__inputs' : '' }` }>
			<input type="hidden" name={ `${ baseName }[id]` } defaultValue={ rule.id } />
			<select name={ `${ baseName }[name]` } className="og-include-exclude__name" defaultValue={ name } onChange={ onChangeName }>
				{ objectType === 'post' && <option value="template">{ __( 'Page template', 'meta-box-builder' ) }</option> }
				{ objectType === 'post' && <option value="format">{ __( 'Post format', 'meta-box-builder' ) }</option> }
				{
					objectType === 'post' && MbbApp.taxonomies.map( taxonomy => <option key={ taxonomy.slug } value={ taxonomy.slug }>{ taxonomy.name } ({ taxonomy.slug })</option> )
				}
				{ objectType === 'post' && <option value="is_child">{ __( 'Is child post', 'meta-box-builder' ) }</option> }
				<option value="input_value">{ __( 'Input value', 'meta-box-builder' ) }</option>
			</select>
			{
				// Using an unused "key" prop to force re-rendering, which makes the loadOptions callback work.
				![ 'is_child', 'input_value' ].includes( name ) &&
				<ReactAsyncSelect
					key={ name + objectType }
					name={ `${ baseName }[value][]` }
					baseName={ baseName }
					className="og-include-exclude__value"
					defaultValue={ rule }
					loadOptions={ loadOptions }
				/>
			}
			{
				name === 'is_child' &&
				<select className="og-include-exclude__value" name={ `${ baseName }[value]` } defaultValue={ rule.value }>
					<option value="true">{ __( 'Yes', 'meta-box-builder' ) }</option>
					<option value="false">{ __( 'No', 'meta-box-builder' ) }</option>
				</select>
			}
			{
				name === 'input_value' &&
				<KeyValue
					name={ `${ baseName }[value]` }
					keyPlaceholder={ __( 'CSS selector', 'meta-box-builder' ) }
					defaultValue={ name === rule.name ? rule.value : {} }
				/>
			}
			<button type="button" className="og-remove" title={ __( 'Remove', 'meta-box-builder' ) } onClick={ () => removeRule( rule.id ) }><Dashicon icon="dismiss" /></button>
		</div>
	);
};

export default ShowHide;
import { useReducer } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { Icon, chevronDown, chevronUp, copy, dragHandle, trash } from "@wordpress/icons";
import clsx from "clsx";
import { inside } from "../../../functions";
import useFieldData from "../../../hooks/useFieldData";
import useFieldNameId from "../../../hooks/useFieldNameId";
import useFields from "../../../hooks/useFields";
import Field from './Field';
import Group from './Group';
import HeaderIcon from "./HeaderIcon";
import HeaderId from "./HeaderId";
import HeaderLabel from "./HeaderLabel";
import { Inserter } from "./Inserter";

const Node = ( { id, field, parent = '', removeField, duplicateField, updateFieldType, toggle } ) => {
	const [ showSubfields, toggleSubfields ] = useReducer( show => !show, true );
	const nameIdData = useFieldNameId( field );
	const { data, updateFieldData } = useFieldData( field );

	const toggleSettings = e => {
		if ( inside( e.target, '.og-item__action--toggle' ) || !inside( e.target, '.og-item__editable,.og-item__toggle,.og-item__actions' ) ) {
			toggle( id );
		}
	};

	const remove = () => {
		if ( confirm( __( 'Do you really want to remove this field?', 'meta-box-builder' ) ) ) {
			removeField( id );
		}
	};

	const duplicate = () => duplicateField( id );

	const groupData = useFields(
		Object.values( field.fields || {} ).filter( field => field.type ),
		`fields${ parent }[${ id }][fields]`
	);
	const groupHasFields = field.type === 'group' && groupData.fields.length > 0;

	const isExpanded = field._expand;

	return field.type && (
		<div className={ clsx(
			'og-item',
			`og-item--${ field.type }`,
			groupHasFields && 'og-item--group--has-fields',
			'og-collapsible',
			isExpanded && 'og-collapsible--expanded',
			!isExpanded && 'og-collapsible--collapsed',
			!showSubfields && 'og-item--hide-fields',
		) }>
			<input type="hidden" name={ `fields${ parent }[${ id }][_id]` } defaultValue={ id } />
			<div className="og-item__header og-collapsible__header" onClick={ toggleSettings } title={ __( 'Click to reveal field settings. Drag and drop to reorder fields.', 'meta-box-builder' ) }>
				<span className="og-column--drag"><Icon icon={ dragHandle } /></span>
				<span className="og-column--label">
					<HeaderIcon data={ data } />
					<HeaderLabel nameIdData={ nameIdData } />
					{ groupHasFields && <span className="og-item__toggle" onClick={ toggleSubfields } title={ __( 'Toggle subfields', 'meta-box-builder' ) }>[{ showSubfields ? '-' : '+' }]</span> }
				</span>
				<span className="og-column--space"></span>
				<HeaderId nameIdData={ nameIdData } />
				<span className="og-column--type">{ field.type }</span>
				<span className="og-column--actions og-item__actions">
					{
						field.type === 'group' && <Inserter
							addField={ groupData.add }
							type="group"
						/>
					}
					<span className="og-item__action og-item__action--duplicate" title={ __( 'Duplicate', 'meta-box-builder' ) } onClick={ duplicate }><Icon icon={ copy } /></span>
					<span className="og-item__action og-item__action--remove" title={ __( 'Remove', 'meta-box-builder' ) } onClick={ remove }><Icon icon={ trash } /></span>
					<span className="og-item__action og-item__action--toggle" title={ __( 'Toggle field settings', 'meta-box-builder' ) }><Icon icon={ isExpanded ? chevronUp : chevronDown } /></span>
				</span>
			</div>
			{
				field.type === 'group'
					? <Group id={ id } field={ field } parent={ parent } updateFieldType={ updateFieldType } nameIdData={ nameIdData } groupData={ groupData } />
					: <Field id={ id } field={ field } parent={ parent } updateFieldType={ updateFieldType } nameIdData={ nameIdData } updateFieldData={ updateFieldData } />
			}
		</div>
	);
};

export default Node;

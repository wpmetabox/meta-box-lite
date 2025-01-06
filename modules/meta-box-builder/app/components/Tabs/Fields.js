import { RawHTML } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { ReactSortable } from 'react-sortablejs';
import useApi from "../../hooks/useApi";
import useFields from "../../hooks/useFields";
import Header from "./FieldsTab/Header";
import { Inserter } from './FieldsTab/Inserter';
import Node from './FieldsTab/Node';

const Fields = prop => {
	const {
		fields,
		add,
		remove,
		duplicate,
		updateType,
		setFields,
		toggle,
		toggleAll,
		expandAll,
	} = useFields( prop.fields.filter( field => field.type ), 'fields' );

	// Don't render any field if fields data is not available.
	const types = useApi( 'field-types', {} );
	const categories = useApi( 'field-categories', [] );

	if ( Object.keys( types ).length === 0 || Object.keys( categories ).length === 0 ) {
		return <p className="og-none">{ __( 'Loading fields, please wait...', 'meta-box-builder' ) }</p>;
	}

	return fields.length === 0 ?
		<>
			<RawHTML className="og-none">{ __( 'There are no fields here. Click the <strong>+ Add Field</strong> to add a new field.', 'meta-box-builder' ) }</RawHTML>
			<Inserter addField={ add } />
		</>
		: <>
			<Header expandAll={ expandAll } toggleAll={ toggleAll } />
			<ReactSortable group={ {
				name: 'root',
				pull: true,
				put: true,
			} }
				animation={ 200 }
				delayOnTouchStart={ true }
				delay={ 2 }
				className="og-fields"
				list={ fields }
				setList={ setFields }
				handle=".og-column--drag"
			>
				{
					fields.map( field => <Node
						key={ field._id }
						id={ field._id }
						field={ field }
						removeField={ remove }
						duplicateField={ duplicate }
						updateFieldType={ updateType }
						toggle={ toggle }
					/> )
				}
			</ReactSortable>
			<Inserter addField={ add } />
		</>;
};

export default Fields;
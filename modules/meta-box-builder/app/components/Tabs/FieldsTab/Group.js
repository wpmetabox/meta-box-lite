import { __ } from "@wordpress/i18n";
import clsx from "clsx";
import { ReactSortable } from 'react-sortablejs';
import { Tab, TabList, TabPanel, Tabs } from 'react-tabs';
import useApi from "../../../hooks/useApi";
import Content from './Content';
import Header from "./Header";
import { Inserter } from './Inserter';
import Node from './Node';

const Group = ( { id, field, parent = '', updateFieldType, nameIdData, groupData } ) => {
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
	} = groupData;

	const fieldTypes = useApi( 'field-types', {} );

	if ( !fieldTypes.hasOwnProperty( field.type ) ) {
		return;
	}

	const controls = [ ...fieldTypes[ field.type ].controls ];

	return (
		<>
			<Tabs forceRenderTabPanel={ true } className="og-item__body og-collapsible__body">
				<TabList>
					<Tab>{ __( 'General', 'meta-box-builder' ) }</Tab>
					<Tab>{ __( 'Advanced', 'meta-box-builder' ) }</Tab>
				</TabList>
				<TabPanel>
					<Content id={ id } controls={ controls.filter( control => control.tab === 'general' ) } field={ field } parent={ parent } updateFieldType={ updateFieldType } nameIdData={ nameIdData } />
				</TabPanel>
				<TabPanel>
					<Content id={ id } controls={ controls.filter( control => control.tab === 'advanced' ) } field={ field } parent={ parent } nameIdData={ nameIdData } />
				</TabPanel>
			</Tabs>

			<div className={ clsx( 'og-group-fields', fields.length === 0 && 'og-group-fields--empty' ) }>
				{
					fields.length > 0 &&
					<>
						<div className="og-group-fields__title">{ __( 'Subfields', 'meta-box-builder' ) }</div>
						<div className="og-group-fields__inner">
							<Header expandAll={ expandAll } toggleAll={ toggleAll } />
							<ReactSortable
								group={ {
									name: 'nested',
									pull: true,
									put: [ 'root', 'nested' ],
								} }
								animation={ 200 }
								delayOnTouchStart={ true }
								delay={ 2 }
								list={ fields }
								setList={ setFields }
								handle=".og-item__header"
							>
								{
									fields.map( ( field, index ) => <Node
										key={ field._id }
										id={ field._id }
										field={ field }
										parent={ `${ parent }[${ id }][fields]` }
										removeField={ remove }
										duplicateField={ duplicate }
										updateFieldType={ updateType }
										toggle={ toggle }
									/> )
								}
							</ReactSortable>
						</div>
					</>
				}

				<Inserter addField={ add } buttonType="secondary" buttonText={ __( '+ Add Subfield', 'meta-box-builder' ) } />
			</div>
		</>
	);
};

export default Group;

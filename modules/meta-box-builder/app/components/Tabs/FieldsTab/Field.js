import { __ } from "@wordpress/i18n";
import { Tab, TabList, TabPanel, Tabs } from 'react-tabs';
import useApi from "../../../hooks/useApi";
import Content from './Content';

const Field = props => {
	const fieldTypes = useApi( 'field-types', {} );

	// Safe fallback to 'text' for not-recommended HTML5 field types.
	const ignore = [ 'datetime-local', 'month', 'tel', 'week' ];
	const type = ignore.includes( props.field.type ) ? 'text' : props.field.type;

	if ( !type || !fieldTypes.hasOwnProperty( type ) ) {
		return;
	}

	const controls = [ ...fieldTypes[ type ].controls ];
	const general = controls.filter( control => control.tab === 'general' );
	const advanced = controls.filter( control => control.tab === 'advanced' );

	if ( advanced.length === 0 ) {
		return <div className="og-item__body og-collapsible__body">
			<Content { ...props } controls={ general } />
		</div>;
	}

	return <Tabs forceRenderTabPanel={ true } className="og-item__body og-collapsible__body">
		<TabList>
			<Tab>{ __( 'General', 'meta-box-builder' ) }</Tab>
			<Tab>{ __( 'Advanced', 'meta-box-builder' ) }</Tab>
		</TabList>
		<TabPanel>
			<Content { ...props } controls={ general } />
		</TabPanel>
		<TabPanel>
			<Content { ...props } controls={ advanced } />
		</TabPanel>
	</Tabs>;
};

export default Field;
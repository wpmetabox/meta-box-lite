import { Dashicon } from "@wordpress/components";
import { useReducer } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { Tab, TabList, TabPanel, Tabs } from 'react-tabs';
import Content from './Content';

const Side = ( { id, title, controls } ) => {
	const [ expanded, toggle ] = useReducer( expanded => !expanded, true );

	return (
		<div className={ `og-item og-collapsible${ expanded ? ' og-collapsible--expanded' : '' }` }>
			<div className="og-item__header og-collapsible__header" onClick={ toggle } title={ __( 'Click to toggle side settings', 'meta-box-builder' ) }>
				<span className="og-item__title">{ title }</span>
				<span className="og-item__actions">
					<span className="og-item__action og-item__action--toggle" title={ __( 'Toggle settings', 'meta-box-builder' ) }><Dashicon icon="arrow-down" /></span>
				</span>
			</div>
			<Tabs forceRenderTabPanel={ true } className="og-item__body og-collapsible__body">
				<TabList>
					<Tab>{ __( 'General', 'meta-box-builder' ) }</Tab>
					<Tab>{ __( 'Meta Box', 'meta-box-builder' ) }</Tab>
					<Tab>{ __( 'Field', 'meta-box-builder' ) }</Tab>
				</TabList>
				<TabPanel>
					<Content id={ id } controls={ controls.filter( control => control.tab === 'general' ) } />
				</TabPanel>
				<TabPanel>
					<Content id={ id } controls={ controls.filter( control => control.tab === 'meta_box' ) } />
				</TabPanel>
				<TabPanel>
					<Content id={ id } controls={ controls.filter( control => control.tab === 'field' ) } />
				</TabPanel>
			</Tabs>
		</div>
	);
};

export default Side;
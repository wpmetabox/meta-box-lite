import { Tab, TabList, TabPanel, Tabs } from 'react-tabs';
import Content from './Content';
import Result from '/components/Tabs/Result';
const { render, useEffect } = wp.element;
const { __ } = wp.i18n;

const App = () => {
	useEffect( () => {
		// Hide option name by default.
		jQuery( '.toggle_option_name' ).closest( '.rwmb-field' ).next().hide();
		jQuery( '#post' )
			// Don't submit form when press Enter.
			.on( 'keypress keydown keyup', 'input', function ( e ) {
				if ( e.keyCode == 13 ) {
					e.preventDefault();
				}
			} )
			// Toggle option name.
			.on( 'click', '.toggle_option_name', function ( e ) {
				jQuery( this ).closest( '.rwmb-field' ).next().toggle();
			} );
	} );

	return (
		<Tabs forceRenderTabPanel={ true }>
			<TabList>
				<Tab>{ __( 'Settings', 'meta-box-builder' ) }</Tab>
				<Tab className='button button-small'>
					{ __( 'Get PHP Code', 'meta-box-builder' ) }
				</Tab>
			</TabList>
			<TabPanel className='react-tabs__tab-panel og-tab-panel--settings'>
				<Content />
			</TabPanel>
			<TabPanel className='react-tabs__tab-panel og-tab-panel--settings'>
				<Result endPoint='settings-page-generate' />
			</TabPanel>
		</Tabs>
	);
};

render( <App />, document.getElementById( 'root' ) );

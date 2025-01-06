import { render, useContext, useEffect } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { Icon, category, cog } from "@wordpress/icons";
import { Tab, TabList, TabPanel, Tabs } from 'react-tabs';
import Fields from './components/Tabs/Fields';
import Result from './components/Tabs/Result';
import Settings from './components/Tabs/Settings';
import ThemeCode from "./components/Tabs/ThemeCode";
import { ErrorBoundary } from "react-error-boundary";
import { SettingsContext, SettingsProvider } from "./contexts/SettingsContext";

const Root = () => {
	const forceValidate = () => document.querySelector( '#post' ).removeAttribute( 'novalidate' );

	useEffect( () => {
		const publishButton = document.querySelector( '#publish' );
		const saveButton = document.querySelector( '#save-post' );
		if ( publishButton ) {
			publishButton.addEventListener( 'click', forceValidate );
		}
		if ( saveButton ) {
			saveButton.addEventListener( 'click', forceValidate );
		}

		// Don't submit form when press Enter.
		jQuery( '#post' ).on( 'keypress keydown keyup', 'input', function ( e ) {
			if ( e.keyCode == 13 ) {
				e.preventDefault();
			}
		} );
	}, [] );

	return (
		<SettingsProvider>
			<App />
		</SettingsProvider>
	);
};

const App = () => {
	const { settings } = useContext( SettingsContext );

	return (
		<>
			<ErrorBoundary fallback={ <h2>{ __( 'Something went wrong. Please try again!', 'meta-box-builder' ) }</h2> }>
				<Tabs forceRenderTabPanel={ true }>
					<TabList className="react-tabs__tab-list og-tabs--main">
						<Tab>
							<Icon icon={ category } />
							{ __( 'Fields', 'meta-box-builder' ) }
						</Tab>
						<Tab>
							<Icon icon={ cog } />
							{ __( 'Settings', 'meta-box-builder' ) }
						</Tab>
						<Tab className="button button-small">{ __( 'Get PHP Code', 'meta-box-builder' ) }</Tab>
					</TabList>
					<TabPanel>
						<Fields fields={ MbbApp.fields } />
					</TabPanel>
					<TabPanel className="react-tabs__tab-panel og-tab-panel--settings">
						<Settings />
					</TabPanel>
					<TabPanel className="react-tabs__tab-panel og-tab-panel--settings">
						<Result endPoint="generate" />
					</TabPanel>
				</Tabs>
				<br />
				{
					MbbApp.fields.length > 0 && settings.object_type !== 'block' &&
					<div className="postbox og-theme-code">
						<div className="postbox-header">
							<h2 className="hndle ui-sortable-handle">{ __( "Theme code", "meta-box-builder" ) }</h2>
						</div>
						<div className="inside">
							<ThemeCode settings={ settings } fields={ MbbApp.fields } />
						</div>
					</div>
				}
			</ErrorBoundary>
		</>
	);
};

render( <Root />, document.getElementById( 'root' ) );
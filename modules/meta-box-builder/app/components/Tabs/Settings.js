import { Suspense, useContext } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { SettingsContext } from "../../contexts/SettingsContext";
import { getControlParams } from "../../functions";
import useApi from "../../hooks/useApi";

const getControlComponent = ( control, settings, updateSettings ) => {
	const [ Control, input, defaultValue ] = getControlParams( control, settings );

	return <Control
		componentId={ `settings-${ control.setting }` }
		name={ `settings${ input }` }
		{ ...control.props }
		defaultValue={ defaultValue }
		updateFieldData={ updateSettings }
	/>;
};

const Settings = () => {
	const settingsControls = useApi( 'settings-controls', [] );
	const { settings, updateSettings } = useContext( SettingsContext );

	return settingsControls.length === 0
		? <p>{ __( 'Loading settings, please wait...', 'meta-box-builder' ) }</p>
		: <>
			{
				settingsControls.map( control => (
					<Suspense fallback={ null } key={ control.setting }>{ getControlComponent( control, settings, updateSettings ) }</Suspense>
				) )
			}
		</>;
};

export default Settings;

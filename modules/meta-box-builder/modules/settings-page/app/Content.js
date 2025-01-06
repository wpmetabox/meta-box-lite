import useApi from '../../../app/hooks/useApi';
import { getControlParams } from '/functions';
const { Suspense, useEffect, useState } = wp.element;
const { __ } = wp.i18n;

const Content = () => {
	const controls = useApi( 'settings-page-controls', [] );

	const getControlComponent = control => {
		const [ Control, input, defaultValue ] = getControlParams( control, MbbApp.settings, () => import( `./controls/${ control.name }` ) );

		return <Control
			componentId={ `settings-${ control.setting }` }
			{ ...control.props }
			name={ `settings${ input }` }
			defaultValue={ defaultValue }
		/>;
	};

	const loading = <p>{ __( 'Loading settings, please wait...', 'meta-box-builder' ) }</p>;

	return controls.length === 0
		? loading
		: <>
			{ controls.map( control => <Suspense fallback={ null } key={ control.setting }>{ getControlComponent( control ) }</Suspense> ) }
		</>;
};

export default Content;
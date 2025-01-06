import { getControlParams } from '/functions';
const { memo, Suspense } = wp.element;

const Content = ( { id, controls } ) => {
	const getControlComponent = control => {
		const [ Control, input, defaultValue ] = getControlParams( control, MbbApp.settings[ id ] );

		return <Control
			componentId={ `relationship-${ id }-${ control.setting }` }
			{ ...control.props }
			name={ `settings[${ id }]${ input }` }
			defaultValue={ defaultValue }
		/>;
	};

	return (
		<>
			{ controls.map( control => <Suspense fallback={ null } key={ control.setting }>{ getControlComponent( control ) }</Suspense> ) }
		</>
	);
};

export default memo( Content, ( prevProps, nextProps ) => prevProps.id === nextProps.id );
import { useLayoutEffect, useRef, useState } from "@wordpress/element";
import DivRow from './DivRow';

/**
 * Fix cursor jumping to the end of the `<input>` after typing.
 * @link https://github.com/facebook/react/issues/18404#issuecomment-605294038
 */
const FontAwesome = ( { name, componentId, defaultValue, updateFieldData, ...rest } ) => {
	const [ value, setValue ] = useState( defaultValue );
	const ref = useRef();
	const [ selection, setSelection ] = useState();

	const handleChange = e => {
		setValue( e.target.value );
		updateFieldData && updateFieldData( name, e.target.value );
		setSelection( [ e.target.selectionStart, e.target.selectionEnd ] );
	}

	useLayoutEffect( () => {
		if ( selection && ref.current ) {
			[ ref.current.selectionStart, ref.current.selectionEnd ] = selection;
		}
	}, [ selection ] );

	return (
		<DivRow htmlFor={ componentId } className="og-icon" { ...rest }>
			<div className='og-icon-selected'>
				<span className={ `icon-fontawesome ${ value }` }></span>
				<input
					ref={ ref }
					type="text"
					className="og-icon-search"
					name={ name }
					value={ value }
					onChange={ handleChange }
				/>
			</div>
		</DivRow >
	);
};

export default FontAwesome;
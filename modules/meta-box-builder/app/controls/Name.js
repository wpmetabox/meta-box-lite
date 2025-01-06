import { useEffect, useLayoutEffect, useRef, useState } from '@wordpress/element';
import DivRow from './DivRow';

/**
 * Fix cursor jumping to the end of the `<input>` after typing.
 * @link https://github.com/facebook/react/issues/18404#issuecomment-605294038
 */
const Name = ( { name, componentId, nameIdData, ...rest } ) => {
	const ref = useRef();
	const [ selection, setSelection ] = useState();

	const handleChange = e => {		
		nameIdData.updateName( e.target.value );
		setSelection( [ e.target.selectionStart, e.target.selectionEnd ] );
	};

	useEffect( () => {
		if ( nameIdData.name ) {
			ref.current.value = nameIdData.name;
		}
	}, [ nameIdData.name ] );

	useLayoutEffect( () => {
		if ( selection && ref.current ) {
			[ ref.current.selectionStart, ref.current.selectionEnd ] = selection;
		}
	}, [ selection ] );

	return (
		<DivRow htmlFor={ componentId } { ...rest }>
			<input
				ref={ ref }
				type="text"
				id={ componentId }
				name={ name }
				defaultValue={ nameIdData.name }
				onBlur={ nameIdData.noAutoGenerateId }
				onInput={ handleChange }
			/>
		</DivRow>
	);
};

export default Name;
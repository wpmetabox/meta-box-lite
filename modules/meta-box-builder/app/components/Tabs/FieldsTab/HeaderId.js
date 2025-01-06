import { useEffect, useRef } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

// Output field id on the header bar with live input.
const HeaderId = ( { nameIdData } ) => {
	const hiddenRef = useRef();
	const inputRef = useRef();

	// Release when pressing "Enter" or "Escape".
	const maybeFinishEditing = e => {
		if ( ![ 'Enter', 'Escape' ].includes( e.key ) ) {
			return;
		}
		e.preventDefault();
		e.target.blur();
	};

	// Update the width of the input to match the width of the text.
	useEffect( () => {
		inputRef.current.style.width = `${ hiddenRef.current.offsetWidth || nameIdData.id.length * 7 }px`;
	}, [ nameIdData.id ] );

	return (
		<span className="og-column--id">
			<span className="og-item__hidden-text" ref={ hiddenRef }>{ nameIdData.id }</span>
			<input
				type="text"
				className="og-item__editable"
				title={ __( 'Click to edit', 'meta-box-builder' ) }
				value={ nameIdData.id }
				onKeyDown={ maybeFinishEditing }
				onChange={ e => nameIdData.updateId( e.target.value ) }
				ref={ inputRef }
			/>
			<span className="dashicons dashicons-edit"></span>
		</span>
	);
};

export default HeaderId;
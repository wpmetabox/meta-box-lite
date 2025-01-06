import { useEffect, useRef } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

// Output field label on the header bar.
const HeaderLabel = ( { nameIdData } ) => {
	const hiddenRef = useRef();
	const inputRef = useRef();

	// Release when pressing "Enter" or "Escape".
	const maybeFinishEditing = e => {
		if ( ![ 'Enter', 'Escape' ].includes( e.key ) ) {
			return;
		}
		e.preventDefault();
		e.target.blur();
		nameIdData.noAutoGenerateId();
	};

	const displayedLabel = nameIdData.label || __( '(No label)', 'meta-box-builder' );

	// Update the width of the input to match the width of the text.
	useEffect( () => {
		inputRef.current.style.width = `${ hiddenRef.current.offsetWidth || nameIdData.label.length * 7 }px`;
	}, [ displayedLabel ] );

	return (
		<>
			<span className="og-item__hidden-text og-item__hidden-text--label" ref={ hiddenRef }>{ displayedLabel }</span>
			<input
				type="text"
				className="og-item__editable og-item__editable--label"
				title={ __( 'Click to edit', 'meta-box-builder' ) }
				placeholder={ displayedLabel }
				value={ nameIdData.label }
				onKeyDown={ maybeFinishEditing }
				onChange={ e => nameIdData.updateName( e.target.value ) }
				onBlur={ nameIdData.noAutoGenerateId }
				ref={ inputRef }
			/>
			<span className="dashicons dashicons-edit"></span>
		</>
	);
};

export default HeaderLabel;
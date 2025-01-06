import { useCopyToClipboard } from "@wordpress/compose";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { UnControlled as CodeMirror } from 'react-codemirror2';
import { getSettings } from "../../functions";
import Input from '/controls/Input';

const ResultCode = ( { endPoint } ) => {
	const settings = getSettings();
	const [ data, setData ] = useState( '' );
	const [ isGenerating, setIsGenerating ] = useState( false );
	const [ copied, setCopied ] = useState( false );

	const copyRef = useCopyToClipboard( data, () => {
		setCopied( true );
		setTimeout( () => setCopied( false ), 2000 );
	} );

	const onClick = () => {
		setData( '' );
		setIsGenerating( true );

		/**
		 * Get all form fields, including WordPress fields.
		 * Remove WordPress nonce to have correct permission.
		 */
		const formData = new FormData( document.querySelector( '#post' ) );
		formData.delete( '_wpnonce' );

		fetch( `${ MbbApp.rest }/mbb/${ endPoint }`, {
			method: 'POST',
			body: formData,
			headers: { 'X-WP-Nonce': MbbApp.nonce }
		} ).then( response => response.json() ).then( response => {
			setData( response );
			setIsGenerating( false );
		} );
	};

	return <>
		<Input
			name="settings[text_domain]"
			label={ __( 'Text domain', 'meta-box-builder' ) }
			tooltip={ __( 'Required for multilingual website. Used in the exported code only.', 'meta-box-builder' ) }
			defaultValue={ settings.text_domain || 'your-text-domain' }
			componentId="text-domain"
		/>
		<Input
			name="settings[function_name]"
			label={ __( 'Function name', 'meta-box-builder' ) }
			defaultValue={ settings.function_name || 'your_prefix_function_name' }
			componentId="function-name"
		/>
		<button type="button" className="button" onClick={ onClick } disabled={ isGenerating }>{ __( 'Generate', 'meta-box-builder' ) }</button>
		{ isGenerating && <p>{ __( 'Generating code, please wait...', 'meta-box-builder' ) }</p> }
		{
			data.length > 0 &&
			<div className="og-result">
				<p>{ __( 'Copy the code and paste into your theme\'s functions.php file.', 'meta-box-builder' ) }</p>
				<div className="og-result__body">
					<CodeMirror
						value={ data }
						options={ {
							mode: 'php',
							lineNumbers: true,
							readOnly: true
						} }
					/>
					<button type="button" className="button" text={ data } ref={ copyRef }>
						{ copied ? __( 'Copied!', 'meta-box-builder' ) : __( 'Copy', 'meta-box-builder' ) }
					</button>
				</div>
			</div>
		}
	</>;
};

export default ResultCode;
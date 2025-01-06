import Tooltip from './Tooltip';

const Textarea = ( { label, name, placeholder, value, update, description = '', required = false, tooltip = '' } ) => (
	<div className="mb-cpt-field">
		<label className="mb-cpt-label" htmlFor={ name }>
			{ label }
			{ required && <span className="mb-cpt-required">*</span> }
			{ tooltip && <Tooltip id={ name } content={ tooltip } /> }
		</label>
		<div className="mb-cpt-input">
			<textarea id={ name } name={ name } placeholder={ placeholder } value={ value } onChange={ update } />
			{ description && <div className="mb-cpt-description">{ description }</div> }
		</div>
	</div>
);

export default Textarea;
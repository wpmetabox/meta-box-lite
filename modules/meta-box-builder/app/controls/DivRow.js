import { RawHTML } from "@wordpress/element";
import Tooltip from './Tooltip';

const DivRow = ( {
	children,
	label,
	description,
	tooltip,
	className = '',
	htmlFor = '',
	keyValue = '',
	required = false,
	dependency,
	error,
} ) => {
	return (
		<div className={ `og-field ${ className } ${ dependency ? `dep:${ dependency }` : '' }` } key={ keyValue }>
			{
				label &&
				<label className="og-label" htmlFor={ htmlFor }>
					<RawHTML>{ label }</RawHTML>
					{ required && <span className="og-required">*</span> }
					{ tooltip && <Tooltip id={ htmlFor } content={ tooltip } /> }
				</label>
			}
			<div className="og-input">
				{ children }
				{ description && <RawHTML className="og-description">{ description }</RawHTML> }
				{ error && <p className="og-error">{ error }</p> }
			</div>
		</div>
	);
};

export default DivRow;
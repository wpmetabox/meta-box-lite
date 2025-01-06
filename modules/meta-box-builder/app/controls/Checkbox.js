import DivRow from './DivRow';
import { useToggle } from '/hooks/useToggle';

const Checkbox = ( { name, componentId, label, className, defaultValue, ...rest } ) => {
	const toggle = useToggle( componentId );

	return <DivRow label={ label } className={ `og-field--checkbox ${ className }` } htmlFor={ componentId } { ...rest }>
		<label className="og-toggle">
			<input type="hidden" name={ name } value={ false } />
			<input type="checkbox" id={ componentId } name={ name } onChange={ toggle } defaultChecked={ defaultValue } value={ true } />
			<div className="og-toggle__switch"></div>
		</label>
	</DivRow>;
};
export default Checkbox;
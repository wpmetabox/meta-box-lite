import DivRow from './DivRow';

const Textarea = ( {
	componentId,
	name,
	defaultValue,
	placeholder,
	rows = 4,
	textareaClassName = '',
	...rest
} ) => (
	<DivRow { ...rest } htmlFor={ componentId }>
		<textarea defaultValue={ defaultValue } id={ componentId } name={ name } rows={ rows } placeholder={ placeholder } className={ textareaClassName }></textarea>
	</DivRow>
);

export default Textarea;
const DataList = ( { id, options } ) => options.length > 0 && (
	<datalist id={ id }>
		{ options.map( option => <option key={ option }>{ option }</option> ) }
	</datalist>
);

export default DataList;
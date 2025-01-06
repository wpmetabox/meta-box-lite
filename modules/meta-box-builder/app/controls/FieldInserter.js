import { Button, Dropdown } from "@wordpress/components";
import { useContext } from '@wordpress/element';
import { SettingsContext } from "../contexts/SettingsContext";
import { RawHTML, useLayoutEffect, useRef, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

const Search = ( { handleSearch } ) => (
	<div className="og-dropdown__search">
		<input onInput={ handleSearch } type="text" placeholder={ __( 'Search...', 'meta-box-builder' ) } />
	</div>
);

const Items = ( { items, searchTerm } ) => {
	const s = searchTerm.toLowerCase();
	items = items.filter( item => !s || item.toLowerCase().includes( s ) );

	return items.map( ( item ) => {
		const label = Array.isArray( item ) ? item[1] : item;
		const value = Array.isArray( item ) ? item[0] : item;
		return (
			<RawHTML key={ value } className="og-dropdown__item" data-value={ value }>
				{ label }
			</RawHTML>
		);
	} );
};

const DropdownInserter = ( { items = [], onSelect } ) => {
	const [ searchTerm, setSearchTerm ] = useState( '' );

	const handleClick = e => e.target.matches( '.og-dropdown__item' ) && onSelect( e );
	const handleSearch = e => setSearchTerm( e.target.value );

	return (
		<div onClick={ handleClick }>
			<Search handleSearch={ handleSearch } />
			<Items items={ items } searchTerm={ searchTerm } />
		</div>
	);
};

const FieldInserter = ( { items = [], required = false, className = '', isID = false, exclude = [], onChange, onSelect, ...rest } ) => {
	const [ selection, setSelection ] = useState();
	const { settings } = useContext( SettingsContext );
	const ref = useRef();

	const handleChange = e => {
		setSelection( [ e.target.selectionStart, e.target.selectionEnd ] );
		onChange && onChange( ref, e.target.value );
	};

	const handleSelect = ( e, onToggle ) => {
		onToggle();
		if ( onSelect ) {
			onSelect( ref, e.target.dataset.value );
		} else {
			ref.current.value = ! isID || exclude.includes( e.target.dataset.value ) ? e.target.dataset.value : `${ settings.prefix || '' }${ e.target.dataset.value }`;
		}
	};

	useLayoutEffect( () => {
		if ( selection && ref.current ) {
			[ ref.current.selectionStart, ref.current.selectionEnd ] = selection;
		}
	}, [ selection ] );

	return (
		<div className={ `og-field-insert ${ className }` } >
			<input ref={ ref } type="text" required={ required } onChange={ handleChange } { ...rest } />
			{
				items.length > 0 && <Dropdown
					className="og-dropdown"
					position="bottom left"
					renderToggle={ ( { onToggle } ) => <Button icon="ellipsis" onClick={ onToggle } /> }
					renderContent={ ( { onToggle } ) => <DropdownInserter items={ items } onSelect={ e => handleSelect( e, onToggle ) } /> }
				/>
			}
		</div>
	);
};

export default FieldInserter;
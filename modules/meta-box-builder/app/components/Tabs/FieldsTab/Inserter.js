import { Dropdown } from "@wordpress/components";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import clsx from "clsx";
import useApi from "../../../hooks/useApi";

export const Inserter = ( {
	addField,
	type = '',
	buttonType = 'primary',
	buttonText = __( '+ Add Field', 'meta-box-builder' ),
	title = __( 'Add a new field', 'meta-box-builder' ),
} ) => {
	const fieldCategories = useApi( 'field-categories', [] );
	const [ searchParam, setSearchParam ] = useState( '' );

	const search = e => setSearchParam( e.target.value );
	const insert = ( e, toggle ) => {
		addField( e.target.dataset.type );
		toggle();
	};

	return (
		<Dropdown
			className="og-inserter"
			onClose={ () => setSearchParam( '' ) }
			renderToggle={ ( { onToggle } ) => type === 'group'
				? <span className="og-item__action og-item__action--add" onClick={ onToggle } title={ title }>
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path fill="none" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M3 5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zm6 7h6m-3-3v6" />
					</svg>
				</span>
				: <button type="button" className={ clsx( 'button', buttonType === 'primary' && 'button-primary' ) } onClick={ onToggle } title={ title }>
					{ buttonText }
				</button>
			}
			renderContent={ ( { onToggle } ) => (
				<>
					<div className="og-inserter__search">
						<input type="search" placeholder={ __( 'Search for a field type', 'meta-box-builder' ) } onChange={ search } />
					</div>
					{
						fieldCategories.length > 0
							? fieldCategories.map( category =>
								<Category key={ category.slug } category={ category } insert={ e => insert( e, onToggle ) } searchParam={ searchParam } />
							)
							: <p>{ __( 'Fetching field types, please wait...', 'meta-box-builder' ) }</p>
					}
				</>
			) }
		/>
	);
};

const Category = ( { category, insert, searchParam } ) => {
	const fieldTypes = useApi( 'field-types', {} );
	const s = searchParam.toLowerCase();
	const fields = Object.entries( fieldTypes ).filter( ( [ type, field ] ) => field.category === category.slug && field.title.toLowerCase().includes( s ) );

	return fields.length > 0 && (
		<>
			<div className="og-inserter__title">{ category.title }</div>
			<div className="og-inserter__content">
				{
					fields.map( ( [ type, { title, description } ] ) =>
						<div className="og-inserter__item" key={ type } data-type={ type } onClick={ insert } title={ description }>{ title }</div>
					)
				}
			</div>
		</>
	);
};
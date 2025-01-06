import { __ } from "@wordpress/i18n";

const Header = ( { expandAll, toggleAll } ) => (
	<div className="og-header">
		<span className="og-column--drag">&nbsp;</span>
		<span className="og-column--label">
			{ __( 'Label', 'meta-box-builder' ) }
			<span
				className="og-item__toggle"
				onClick={ toggleAll }
				title={ __( 'Expand/Collapse all fields', 'meta-box-builder' ) }
			>
				[{ expandAll ? '-' : '+' }]
			</span>
		</span>
		<span className="og-column--space"></span>
		<span className="og-column--id">{ __( 'ID', 'meta-box-builder' ) }</span>
		<span className="og-column--type">{ __( 'Type', 'meta-box-builder' ) }</span>
		<span className="og-column--actions">{ __( 'Actions', 'meta-box-builder' ) }</span>
	</div>
);

export default Header;
<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

if ( $in_group ) {
	$this->out( "\$background = {$group_var}[ '" . $field['id'] . "' ] ?? [];" );
	$this->out( "echo \$background['color'];" );
	$this->out( "echo \$background['image'];" );
	return;
}

if ( empty( $field['clone'] ) ) {
	// Getting the background properties:
	$this->out( '<?php' );
	$this->out( '// Getting the background properties:' );
	$this->out( "\$background = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
	$this->out( "echo \$background['color'];" );
	$this->out( "echo \$background['image'];" );
	$this->out( '?>', 0, 3 );

	// Outputting the CSS for the background:
	$this->out( '<?php // Outputting the CSS for the background: ?>' );
	$this->out( "<div style=\"<?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>">' );
	$this->out( '<h2>My section title</h2>', 1 );
	$this->out( '<p>My section content</p>', 1 );
	$this->out( '</div>', 0, 0 );

	return;
}
// Displaying cloneable values:

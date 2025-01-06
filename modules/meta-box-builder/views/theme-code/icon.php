<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

// With group.
if ( $in_group ) {
	// Group with clone.
	if ( ! empty( $field['clone'] ) ) {
		$this->out( "\$values = {$group_var}[ '" . $field['id'] . "' ] ?? [];" );
		$this->out( '// Getting the icons\' classes:' );
		$this->out( 'foreach ( $values as $value ) {' );
			$this->out( 'echo $value;', 1 );
		$this->out( '}' );
		return;
	} else {
		// Group without clone
		$this->out( '// Getting the icon class:' );
		$this->out( "echo {$group_var}[ '" . $field['id'] . "' ] ?? '';" );
		return;
	}
}
// With clone.
if ( ! empty( $field['clone'] ) ) {
	// Getting the icon class:
	$this->out( '<?php' );
	$this->out( '// Getting the icons\' classes:' );
	$this->out( "\$values = rwmb_get_value( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
	$this->out( 'foreach ( $values as $value ) {' );
		$this->out( 'echo $value;', 1 );
	$this->out( '}' );
	$this->out( '?>' );
	return;
}

// Single.
// Displaying the icon.
$this->out( '<?php' );
$this->out( '// Displaying the icon:' );
$this->out( "rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>', 0, 2 );

// Getting the icon class.
$this->out( '<?php' );
$this->out( '// Getting the icon class:' );
$this->out( "\$value = rwmb_get_value( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( 'echo $value;' );
$this->out( '?>', 0, 0 );

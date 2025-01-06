<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

if ( $in_group ) {
	// Displaying in group
	if ( ! empty( $field['clone'] ) ) {
		$this->out( "\$checkboxes = {$group_var}[ '" . $field['id'] . "' ] ?? [];" );
		$this->out( 'foreach ( $checkboxes as $checkbox ) {' );
			$this->out( 'if ( $checkbox ) {', 1 );
				$this->out( 'echo \'Checked\';', 2 );
			$this->out( '} else {', 1 );
				$this->out( 'echo \'Unchecked\';', 2 );
			$this->out( '}', 1 );
		$this->out( '}' );

		return;
	}

	$this->out( "\$checkbox = {$group_var}[ '" . $field['id'] . "' ] ?? 0;" );
	$this->out( 'if ( $checkbox ) {' );
		$this->out( 'echo \'Checked\';', 1 );
	$this->out( '} else {' );
		$this->out( 'echo \'Unchecked\';', 1 );
	$this->out( '}' );

	return;
}

if ( ! empty( $field['clone'] ) ) {
	// Displaying cloneable values:
	$this->out( '<?php' );
	$this->out( "\$values = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
	$this->out( 'foreach ( $values as $value ) {' );
		$this->out( 'if ( $value ) {', 1 );
			$this->out( 'echo \'Checked\';', 2 );
		$this->out( '} else {', 1 );
			$this->out( 'echo \'Unchecked\';', 2 );
		$this->out( '}', 1 );
	$this->out( '}' );
	$this->out( '?>', 0, 0 );
	return;
}

// Conditional check:
$this->out( '<?php' );
$this->out( '// Conditional check:' );
$this->out( "\$value = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( 'if ( $value ) {' );
	$this->out( 'echo \'Checked\';', 1 );
$this->out( '} else {' );
	$this->out( 'echo \'Unchecked\';', 1 );
$this->out( '}' );
$this->out( '?>', 0, 3 );

// Displaying "Yes/No":
$this->out( '<?php' );
$this->out( '// Displaying "Yes/No":' );
$this->out( "rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>', 0, 0 );

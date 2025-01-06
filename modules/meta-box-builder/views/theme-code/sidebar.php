<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

if ( $in_group ) {
	if ( isset( $field['clone'] ) ) {
		$this->out( "\$sidebar_ids = {$group_var}[ '" . $field['id'] . "' ] ?? [];" );
		$this->out( 'foreach ( $sidebar_ids as $sidebar_id ) {' );
			$this->out( 'if ( is_active_sidebar( $sidebar_id ) ) {', 1 );
				$this->out( 'dynamic_sidebar( $sidebar_id );', 2 );
			$this->out( '}', 1 );
		$this->out( '}' );

		return;
	}

	$this->out( "\$sidebar_id = {$group_var}[ '" . $field['id'] . "' ] ?? '';" );
	$this->out( 'if ( is_active_sidebar( $sidebar_id ) ) {' );
		$this->out( 'dynamic_sidebar( $sidebar_id );', 1 );
	$this->out( '}' );
	return;
}

if ( isset( $field['clone'] ) ) {
	// Displaying cloneable values:
	$this->out( '<?php' );
	$this->out( "\$sidebar_ids = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
	$this->out( 'foreach ( $sidebar_ids as $sidebar_id ) {' );
		$this->out( 'if ( is_active_sidebar( $sidebar_id ) ) {', 1 );
			$this->out( 'dynamic_sidebar( $sidebar_id );', 2 );
		$this->out( '}', 1 );
	$this->out( '}' );
	$this->out( '?>', 0, 0 );

	return;
}

// Display widgets for the selected sidebar:
$this->out( '<?php // Display widgets for the selected sidebar: ?>' );
$this->out( '<div class="sidebar">' );
	$this->out( "<?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>', 1 );
$this->out( '</div>', 0, 3 );

// Checking if the selected sidebar has widgets and displaying it:
$this->out( '<?php' );
$this->out( '// Checking if the selected sidebar has widgets and displaying it:' );
$this->out( "\$sidebar_id = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( 'if ( is_active_sidebar( $sidebar_id ) ) {' );
	$this->out( 'dynamic_sidebar( $sidebar_id );', 1 );
$this->out( '}' );
$this->out( '?>', 0, 0 );

<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$subfields = $field['fields'] ?? [];

$clone                                  = ! empty( $field['clone'] );
[ $var_names, $var_name, $parent_name ] = GroupVars::get_current_group_vars( $clone );

// Cloneable group
if ( $clone ) {
	if ( ! $in_group ) {
		$this->out( '<?php' );
	}
	if ( $in_group ) {
		$this->out( "$var_names = {$parent_name}[ '" . $field['id'] . "' ] ?? '';" );
	} else {
		$this->out( "$var_names = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
	}
	$this->out( "foreach ( $var_names as $var_name ) {" );
	++$this->size_indent;
	foreach ( $subfields as $sub_field ) {
		if ( empty( $sub_field['id'] ) ) {
			continue;
		}
		$this->out( '' );
		$this->out( "// Field {$sub_field['id']}:" );
		echo $this->get_theme_code( $sub_field, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	--$this->size_indent;
	$this->out( '' );
	$this->out( '}' );
	if ( ! $in_group ) {
		$this->out( '?>', 0, 0 );
	}

	// Done outputing this group? Remove it from the stack.
	GroupVars::pop();

	return;
}

// Non-cloneable group
if ( ! $in_group ) {
	$this->out( '<?php' );
}
if ( $in_group ) {
	$this->out( "$var_names = {$parent_name}[ '" . $field['id'] . "' ] ?? '';" );
} else {
	$this->out( "$var_names = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
}
foreach ( $subfields as $sub_field ) {
	if ( empty( $sub_field['id'] ) ) {
		continue;
	}
	$this->out( '' );
	$this->out( "// Field {$sub_field['id']}:" );
	echo $this->get_theme_code( $sub_field, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
if ( ! $in_group ) {
	$this->out( '?>', 0, 0 );
}

// Done outputing this group? Remove it from the stack.
GroupVars::pop();

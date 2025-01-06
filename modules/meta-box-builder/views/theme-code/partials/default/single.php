<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Displaying the value:
$this->out( '<?php' );
$this->out( '// Displaying the value:' );
$this->out( "rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>', 0, 3 );

// Getting the value:
$this->out( '<?php' );
$this->out( '// Getting the value:' );
$this->out( "\$value = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( 'echo $value;' );
$this->out( '?>', 0, 0 );

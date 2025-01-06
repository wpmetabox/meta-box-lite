<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$this->out( '<?php' );
$this->out( '// Displaying selected value:' );
$this->out( "\$value = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( 'echo $value;' );
$this->out( '?>', 0, 0 );

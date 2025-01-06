<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Displaying selected value.
$this->out( '<?php' );
$this->out( '// Displaying selected value:' );
$this->out( "\$value = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( 'echo $value;' );
$this->out( '?>', 0, 3 );

// Displaying selected label.
$this->out( '<?php' );
$this->out( '// Displaying selected label:' );
$this->out( "rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>', 0, 3 );

// Displaying both value and label.
$this->out( '<?php' );
$this->out( '// Displaying both value and label:' );
$this->out( "\$field   = rwmb_get_field_settings( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( "\$options = \$field['options'];" );
$this->out( "\$value   = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>' );
$this->out( 'Value: <?php echo $value ?><br>' );
$this->out( 'Label: <?php echo $options[ $value ] ?>', 0, 0 );

<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$this->out( '<?php' );
$this->out( "\$field   = rwmb_get_field_settings( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( "\$options = \$field['options'];" );
$this->out( "\$values  = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>' );

$this->out( '<ul>' );
	$this->out( '<?php foreach ( $values as $value ) : ?>', 1 );
		$this->out( '<li>', 2 );
			$this->out( 'Value: <?php echo $value ?><br>', 3 );
			$this->out( 'Label: <?php echo $options[ $value ] ?>', 3 );
		$this->out( '</li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 0 );

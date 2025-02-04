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
	$this->out( '<?php foreach ( $values as $clone ) : ?>', 1 );
		$this->out( '<li>', 2 );

			$this->out( '<ul>', 3 );
				$this->out( '<?php foreach ( $clone as $value ) : ?>', 4 );
					$this->out( '<li>', 5 );
						$this->out( 'Value: <?php echo $value ?><br>', 6 );
						$this->out( 'Label: <?php echo $options[ $value ] ?>', 6 );
					$this->out( '</li>', 5 );
				$this->out( '<?php endforeach; ?>', 4 );
			$this->out( '</ul>', 3 );

		$this->out( '</li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 0 );

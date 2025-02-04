<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$this->out( "<?php \$values = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $values as $value ) : ?>', 1 );
		$this->out( '<li><?php echo $value ?></li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 0 );

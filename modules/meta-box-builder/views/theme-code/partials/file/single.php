<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Displaying file:
$this->out( '<?php // Displaying file: ?>' );
$this->out( "<?php \$value = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>' );
$this->out( '<p><a href="<?php echo $value ?>">Download file</a></p>', 0, 3 );

// Displaying uploaded image:
$this->out( '<?php // Displaying uploaded image: ?>' );
$this->out( "<?php \$value = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>' );
$this->out( '<p><img src="<?php echo $value ?>"></p>', 0, 0 );

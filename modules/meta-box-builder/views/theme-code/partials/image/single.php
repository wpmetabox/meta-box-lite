<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$this->out( '<?php // Displaying uploaded image: ?>' );
$this->out( "<?php \$image = rwmb_meta( '" . $this->get_encoded_value( $field['id'], [ 'size' => 'thumbnail' ] ) . ' ); ?>' );
$this->out( '<h3>Logo</h3>' );
$this->out( '<img src="<?php echo $image[\'url\']; ?>">', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( '<h3>Logo</h3>' );
$this->out( "<?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'], [ 'size' => 'thumbnail' ] ) . ' ) ?>', 0, 0 );

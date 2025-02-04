<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$this->out( "<?php \$images = rwmb_meta( '" . $this->get_encoded_value( $field['id'], [ 'size' => 'thumbnail' ] ) . ' ); ?>' );
$this->out( '<h3>Uploaded images</h3>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $images as $image ) : ?>', 1 );
		$this->out( '<li><img src="<?php echo $image[\'url\']; ?>"></li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 0 );

<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$this->out( "<?php \$images = rwmb_meta( '" . $this->get_encoded_value( $field['id'], [ 'size' => 'thumbnail' ] ) . ' ); ?>' );
$this->out( '<h3>Uploaded images</h3>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $images as $clone ) : ?>', 1 );
	$this->out( '<li>', 2 );
			$this->out( '<ul>', 3 );
				$this->out( '<?php foreach ( $clone as $image ) : ?>', 4 );
					$this->out( '<li><img src="<?php echo $image[\'url\']; ?>"></li>', 5 );
				$this->out( '<?php endforeach; ?>', 4 );
			$this->out( '</ul>', 3 );
	$this->out( '</li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 0 );

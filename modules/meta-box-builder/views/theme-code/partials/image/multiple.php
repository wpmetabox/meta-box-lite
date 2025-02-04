<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Displaying uploaded images:
$this->out( '<?php' );
$this->out( '// Displaying uploaded images:' );
$this->out( "\$images = rwmb_meta( '" . $this->get_encoded_value( $field['id'], [ 'size' => 'thumbnail' ] ) . ' );' );
$this->out( '?>' );
$this->out( '<h3>Uploaded images</h3>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $images as $image ) : ?>', 1 );
		$this->out( '<li><img src="<?php echo $image[\'url\']; ?>"></li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( '<h3>Uploaded files</h3>' );
$this->out( "<?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'], [ 'size' => 'thumbnail' ] ) . ' ) ?>', 0, 3 );

// Display images with links to the full-size versions (for lightbox effects):
$this->out( '<?php' );
$this->out( '// Display images with links to the full-size versions (for lightbox effects):' );
$this->out( "\$images = rwmb_meta( '" . $this->get_encoded_value( $field['id'], [ 'size' => 'thumbnail' ] ) . ' );' );
$this->out( '?>' );
$this->out( '<h3>Uploaded images</h3>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $images as $image ) : ?>', 1 );
	$this->out( '<li><a href="<?php echo $image[\'full_url\'] ?>"><img src="<?php echo $image[\'url\']; ?>"></a></li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( '<h3>Uploaded files</h3>' );
$this->out( "<?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'], [
	'size' => 'thumbnail',
	'link' => true,
] ) . ' ) ?>', 0, 3 );

// Displaying only one image:
$this->out( '<?php' );
$this->out( '// Displaying only one image:' );
$this->out( "\$images = rwmb_meta( '" . $this->get_encoded_value( $field['id'], [ 'limit' => 1 ] ) . ' );' );
$this->out( '$image = reset( $images );' );
$this->out( '?>' );
$this->out( '<img src="<?php echo $image[\'url\']; ?>">', 0, 0 );

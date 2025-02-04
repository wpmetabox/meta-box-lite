<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Displaying uploaded files with links:
$this->out( '<?php' );
$this->out( '// Displaying uploaded files with links:' );
$this->out( "\$files = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>' );
$this->out( '<h3>Uploaded files</h3>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $files as $file ) : ?>', 1 );
		$this->out( '<li><a href="<?php echo $file[\'url\']; ?>"><?php echo $file[\'name\']; ?></a></li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( '<h3>Uploaded files</h3>' );
$this->out( "<?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>', 0, 3 );

// Displaying only one file:
$this->out( '<?php' );
$this->out( '// Displaying only one file:' );
$this->out( "\$files = rwmb_meta( '" . $this->get_encoded_value( $field['id'], [ 'limit' => 1 ] ) . ' );' );
$this->out( '$file = reset( $files );' );
$this->out( '?>' );
$this->out( '<a class="button" href="<?php echo $file[\'url\'] ?>">Download file</a>', 0, 0 );

<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$this->out( "<?php \$files = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>' );
$this->out( '<h3>Uploaded files</h3>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $files as $clone ) : ?>', 1 );
		$this->out( '<li>', 2 );
			$this->out( '<ul>', 3 );
				$this->out( '<?php foreach ( $clone as $file ) : ?>', 4 );
					$this->out( '<li><a href="<?php echo $file[\'url\']; ?>"><?php echo $file[\'name\']; ?></a></li>', 5 );
				$this->out( '<?php endforeach; ?>', 4 );
			$this->out( '</ul>', 3 );
		$this->out( '</li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( '<h3>Uploaded files</h3>' );
$this->out( "<?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>', 0, 0 );

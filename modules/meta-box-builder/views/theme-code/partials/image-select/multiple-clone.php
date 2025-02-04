<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$this->out( "<?php \$clones = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $clones as $clone ) : ?>', 1 );
		$this->out( '<li>', 2 );

			$this->out( '<ul>', 3 );
				$this->out( '<?php foreach ( $clone as $value ) : ?>', 4 );
					$this->out( '<li><?php echo $value ?></li>', 5 );
				$this->out( '<?php endforeach; ?>', 4 );
			$this->out( '</ul>', 3 );

		$this->out( '</li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 0 );

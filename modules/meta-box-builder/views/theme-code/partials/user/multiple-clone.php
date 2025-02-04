<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Displaying cloneable values:
$this->out( "<?php \$clones = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>' );

$this->out( '<ul>' );
	$this->out( '<?php foreach ( $clones as $clone ) : ?>', 1 );
		$this->out( '<li>', 2 );
			$this->out( '<ul>', 3 );
				$this->out( '<?php foreach ( $clone as $user_id ) : ?>', 4 );
					$this->out( '<?php $user = get_userdata( $user_id ); ?>', 5 );
					$this->out( '<li><?php echo $user->display_name ?></li>', 5 );
				$this->out( '<?php endforeach; ?>', 4 );
			$this->out( '</ul>', 3 );
		$this->out( '</li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( "<h3><?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?></h3>', 0, 0 );

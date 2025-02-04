<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Displaying cloneable values:
$this->out( "<?php \$user_ids = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>' );
$this->out( '<h3>Speakers</h3>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $user_ids as $user_id ) : ?>', 1 );
		$this->out( '<?php $user = get_userdata( $user_id ); ?>', 2 );
		$this->out( '<li><?php echo $user->display_name ?></li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( '<h3>Speakers</h3>' );
$this->out( "<?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'], [ 'link' => false ] ) . ' ); ?>', 0, 0 );

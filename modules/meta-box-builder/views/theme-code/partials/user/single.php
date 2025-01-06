<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Getting selected user ID:
$this->out( '<?php' );
$this->out( '// Getting selected user ID:' );
$this->out( "\$user_id = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>' );
$this->out( '<p>Selected user ID: <?php echo $user_id ?></p>', 0, 3 );

// Getting selected user object:
$this->out( '<?php' );
$this->out( '// Getting selected user object:' );
$this->out( "\$user_id = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '$user = get_userdata( $user_id );' );
$this->out( '?>' );
$this->out( '<pre>' );
	$this->out( '<!-- Show all data from the selected user -->', 1 );
	$this->out( '<?php print_r( $user ); ?>', 1 );
$this->out( '</pre>', 0, 3 );

// Displaying selected user info:
$this->out( '<?php' );
$this->out( '// Displaying selected user info:' );
$this->out( "\$user_id = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '$user = get_userdata( $user_id );' );
$this->out( '?>' );
$this->out( '<p>Display name: <?php echo $user->display_name ?></p>' );
$this->out( '<p>Email: <?php echo $user->user_email ?></p>', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( "<p>Display name: <?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'], [ 'link' => false ] ) . ' ); ?></p>' );
$this->out( "<p>Email: <?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'], [
	'display_field' => 'user_email',
	'link'          => false,
] ) . ' ); ?></p>', 0, 0 );

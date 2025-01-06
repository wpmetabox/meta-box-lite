<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Getting selected term object:
$this->out( '<?php' );
$this->out( '// Getting selected term object:' );
$this->out( "\$term = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>' );
$this->out( '<pre>' );
	$this->out( '<!-- Show all data from the selected term -->', 1 );
	$this->out( '<?php print_r( $term ); ?>', 1 );
$this->out( '</pre>', 0, 3 );

// Displaying selected term name:
$this->out( '<?php' );
$this->out( '// Displaying selected term name:' );
$this->out( "\$term = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>' );
$this->out( '<p><?php echo $term->name; ?></p>', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( "<p><?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'], [ 'link' => false ] ) . ' ); ?></p>', 0, 3 );


// Displaying the selected term with link:
$this->out( '<?php' );
$this->out( '// Displaying the selected term with link:' );
$this->out( "\$term = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>' );
$this->out( '<p><a href="<?php echo get_term_link( $term ) ?>"><?php echo $term->name ?></a></p>', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( "<p><?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?></p>', 0, 0 );

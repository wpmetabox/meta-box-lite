<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Getting selected post ID:
$this->out( '<?php' );
$this->out( '// Getting selected post ID:' );
$this->out( "\$post_id = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>' );
$this->out( '<p>Selected post ID: <?php echo $post_id ?></p>', 0, 3 );

// Getting selected post object:
$this->out( '<?php' );
$this->out( '// Getting selected post object:' );
$this->out( "\$post_id = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '$post     = get_post( $post_id );' );
$this->out( '?>' );
$this->out( '<!-- Show all data from the selected post -->' );
$this->out( '<pre>' );
	$this->out( '<?php print_r( $post ); ?>', 1 );
$this->out( '</pre>', 0, 3 );

// Displaying selected post title:
$this->out( '<?php' );
$this->out( '// Displaying selected post title:' );
$this->out( "\$post_id = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>' );
$this->out( '<h3><?php echo get_the_title( $post_id ); ?></h3>', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( "<h3><?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'], [ 'link' => false ] ) . ' ); ?></h3>', 0, 3 );

// Displaying the selected post with link:
$this->out( '<?php' );
$this->out( '// Displaying the selected post with link:' );
$this->out( "\$post_id = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>' );
$this->out( '<h3><a href="<?php echo get_permalink( $post_id ) ?>"><?php echo get_the_title( $post_id ); ?></a></h3>', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( "<h3><?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?></h3>', 0, 0 );

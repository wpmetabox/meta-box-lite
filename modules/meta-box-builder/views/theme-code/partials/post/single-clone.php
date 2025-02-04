<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Displaying cloneable values:
$this->out( "<?php \$post_ids = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>' );
$this->out( '<h3>Related posts</h3>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $post_ids as $post_id ) : ?>', 1 );
		$this->out( '<li><a href="<?php echo get_permalink( $post_id ) ?>"><?php echo get_the_title( $post_id ); ?></a></li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( "<h3><?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?></h3>', 0, 0 );

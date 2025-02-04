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
				$this->out( '<?php foreach ( $clone as $term ) : ?>', 4 );
					$this->out( '<li><a href="<?php echo get_term_link( $term ) ?>"><?php echo $term->name ?></a></li>', 5 );
				$this->out( '<?php endforeach; ?>', 4 );
			$this->out( '</ul>', 3 );
		$this->out( '</li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( "<?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>', 0, 0 );

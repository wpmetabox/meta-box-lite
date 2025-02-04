<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

if ( $in_group ) {
	$this->out( "\$pairs = {$group_var}[ '" . $field['id'] . "' ] ?? [];" );
	$this->out( '?>' );
	$this->out( '<h3>Specification</h3>' );
	$this->out( '<ul>' );
		$this->out( '<?php foreach ( $pairs as $pair ) : ?>', 1 );
			$this->out( '<li><label><?php echo $pair[0] ?>:</label> <?php echo $pair[1] ?></li>', 2 );
		$this->out( '<?php endforeach; ?>', 1 );
	$this->out( '</ul>' );
	$this->out( '<?php' );

	return;
}

// Displaying list of key-value pairs:
$this->out( '<?php' );
$this->out( '// Displaying list of key-value pairs:' );
$this->out( "\$pairs = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>' );
$this->out( '<h3>Specification</h3>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $pairs as $pair ) : ?>', 1 );
		$this->out( '<li><label><?php echo $pair[0] ?>:</label> <?php echo $pair[1] ?></li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 3 );

// or simpler:
$this->out( '<?php // or simpler: ?>' );
$this->out( '<h3>Specification</h3>' );
$this->out( "<?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>', 0, 0 );

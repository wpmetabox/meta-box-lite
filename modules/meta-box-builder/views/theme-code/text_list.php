<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

if ( $in_group ) {
	if ( isset( $field['clone'] ) ) {
		$this->out( "\$values = {$group_var}[ '" . $field['id'] . "' ] ?? [];" );
		$this->out( '?>' );
		$this->out( '<ul>' );
			$this->out( '<?php foreach ( $values as $value ) : ?>', 1 );
				$this->out( '<li>', 2 );
					$this->out( '<span>Name: <?php echo $value[0] ?></span>', 3 );
					$this->out( '<span>Email: <?php echo $value[1] ?></span>', 3 );
				$this->out( '</li>', 2 );
			$this->out( '<?php endforeach; ?>', 1 );
		$this->out( '</ul>' );
		$this->out( '<?php' );

		return;
	}

	$this->out( "\$value = {$group_var}[ '" . $field['id'] . "' ] ?? [];" );
	$this->out( '?>' );
	$this->out( '<p>Name: <?php echo $value[0] ?></p>' );
	$this->out( '<p>Email: <?php echo $value[1] ?></p>' );
	$this->out( '<?php' );
	return;
}

if ( isset( $field['clone'] ) ) {
	// Displaying cloneable values:
	$this->out( "<?php \$values = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' ) ?>' );
	$this->out( '<ul>' );
		$this->out( '<?php foreach ( $values as $value ) : ?>', 1 );
			$this->out( '<li>', 2 );
				$this->out( '<span>Name: <?php echo $value[0] ?></span>', 3 );
				$this->out( '<span>Email: <?php echo $value[1] ?></span>', 3 );
			$this->out( '</li>', 2 );
		$this->out( '<?php endforeach; ?>', 1 );
	$this->out( '</ul>', 0, 0 );

	return;
}

// Displaying field inputs' values:
$this->out( '<?php // Displaying field inputs\' values: ?>' );
$this->out( "<?php \$value = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' ) ?>' );
$this->out( '<p>Name: <?php echo $value[0] ?></p>' );
$this->out( '<p>Email: <?php echo $value[1] ?></p>', 0, 3 );

// Displaying field values in a table:
$this->out( '<?php // Displaying field values in a table: ?>' );
$this->out( '<h3>Values</h3>' );
$this->out( "<?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' ) ?>', 0, 0 );

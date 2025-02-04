<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

if ( $in_group ) {
	if ( ! empty( $field['clone'] ) ) {
		$this->out( "\$dates = {$group_var}[ '" . $field['id'] . "' ] ?? [];" );
		$this->out( '?>' );
		$this->out( '<ul>' );
			$this->out( '<?php foreach ( $dates as $date ) : ?>', 1 );

				$value = empty( $field ['timestamp'] ) ? '$date' : 'date( \'F j, Y\', $value )';
				$this->out( '<li><?php echo ' . $value . ' ?></li>', 2 );

			$this->out( '<?php endforeach; ?>', 1 );
		$this->out( '</ul>' );
		$this->out( '<?php' );
		return;
	}

	if ( ! empty( $field ['timestamp'] ) ) {
		$this->out( "\$value = {$group_var}[ '" . $field['id'] . "' ] ?? '';" );
		$this->out( '?>' );
		$this->out( '<p>Date: <?php echo date( \'F j, Y\', $value ) ?></p>' );
		$this->out( '<?php' );

		return;
	}

	require __DIR__ . '/partials/default/single-group.php';
	return;
}

if ( ! empty( $field['clone'] ) ) {
	// Displaying cloneable values:
	$this->out( "<?php \$values = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>' );
	$this->out( '<ul>' );
		$this->out( '<?php foreach ( $values as $value ) : ?>', 1 );

			$value = empty( $field ['timestamp'] ) ? '$value' : 'date( \'F j, Y\', $value )';
			$this->out( '<li><?php echo ' . $value . ' ?></li>', 2 );

		$this->out( '<?php endforeach; ?>', 1 );
	$this->out( '</ul>' );
	return;
}

// Converting timestamp to another format:
if ( ! empty( $field ['timestamp'] ) ) {
	// Displaying the value:
	$this->out( '<?php // Displaying the value: ?>' );
	$this->out( "<p>Date: <?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'], [ 'format' => 'F j, Y' ] ) . ' ); ?></p>', 0, 3 );

	// Getting the value:
	$this->out( '<?php' );
	$this->out( '// Getting the value:' );
	$this->out( "\$value = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
	$this->out( '?>' );
	$this->out( '<p>Date: <?php echo date( \'F j, Y\', $value ) ?></p>', 0, 0 );

	return;
}

require __DIR__ . '/partials/default/single.php';

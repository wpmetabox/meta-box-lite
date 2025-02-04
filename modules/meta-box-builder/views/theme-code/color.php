<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

if ( $in_group ) {
	if ( ! empty( $field['clone'] ) ) {
		$this->out( "\$clones = {$group_var}[ '" . $field['id'] . "' ] ?? [];" );
		$this->out( '?>' );
		$this->out( '<ul>' );
			$this->out( '<?php foreach ( $clones as $clone ) : ?>', 1 );
				$this->out( '<li><?php echo $clone ?></li>', 2 );
			$this->out( '<?php endforeach; ?>', 1 );
		$this->out( '</ul>' );
		$this->out( '<?php' );

		return;
	}

	// Displaying in group
	$this->out( "\$value = {$group_var}[ '" . $field['id'] . "' ] ?? '';" );
	$this->out( '?>' );
	$this->out( '<div style="background-color: <?php echo $value ?>">' );
		$this->out( '<h2>My section title</h2>', 1 );
		$this->out( '<p>My section content</p>', 1 );
	$this->out( '</div>' );
	$this->out( '<?php' );

	return;
}

if ( ! empty( $field['clone'] ) ) {
	// Displaying cloneable values:
	$this->out( "<?php \$values = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>' );
	$this->out( '<ul>' );
		$this->out( '<?php foreach ( $values as $value ) : ?>', 1 );
			$this->out( '<li><?php echo $value ?></li>', 2 );
		$this->out( '<?php endforeach; ?>', 1 );
	$this->out( '</ul>', 0, 0 );

	return;
}

// Getting the value:
$this->out( '<?php' );
$this->out( '// Getting the value:' );
$this->out( "\$value = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?>' );
$this->out( '?>' );
$this->out( '<div style="background-color: <?php echo $value ?>">' );
	$this->out( '<h2>My section title</h2>', 1 );
	$this->out( '<p>My section content</p>', 1 );
$this->out( '</div>', 0, 3 );

// Displaying the selected color:
$this->out( '<?php // Displaying the selected color: ?>' );
$this->out( "<p>This is the color: <?php rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' ); ?></p>', 0, 0 );

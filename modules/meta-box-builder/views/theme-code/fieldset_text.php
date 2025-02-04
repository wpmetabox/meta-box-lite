<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

if ( $in_group ) {
	// Displaying in group
	if ( ! empty( $field['clone'] ) ) {
		$this->out( '// Displaying field inputs\' values:' );
		$this->out( "\$clones = {$group_var}[ '" . $field['id'] . "' ] ?? '';" );
		$this->out( '?>' );

		$this->out( '<?php foreach ( $clones as $clone ) : ?>' );
			$this->out( "<p>Name: <?php echo \$clone['name'] ?></p>", 1 );
			$this->out( "<p>Address: <?php echo \$clone['address'] ?></p>", 1 );
			$this->out( "<p>Email: <?php echo \$clone['email'] ?></p>", 1 );
		$this->out( 'endforeach', 1, 1 );
		$this->out( '<?php' );
		return;
	}

	$this->out( '// Displaying field inputs\' values:' );
	$this->out( "\$clone = {$group_var}[ '" . $field['id'] . "' ] ?? '';" );
	$this->out( '?>' );
	$this->out( "<p>Name: <?php echo \$clone['name'] ?></p>" );
	$this->out( "<p>Address: <?php echo \$clone['address'] ?></p>" );
	$this->out( "<p>Email: <?php echo \$clone['email'] ?></p>", 1, 1 );
	$this->out( '<?php' );
	return;
}

if ( ! empty( $field['clone'] ) ) {
	// Displaying cloneable values:
	$this->out( '<?php' );
	$this->out( '// Displaying field inputs\' values:' );
	$this->out( "\$values = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
	$this->out( '?>' );

	$this->out( '<?php foreach ( $values as $value ) : ?>' );
		$this->out( "<p>Name: <?php echo \$value['name'] ?></p>", 1 );
		$this->out( "<p>Address: <?php echo \$value['address'] ?></p>", 1 );
		$this->out( "<p>Email: <?php echo \$value['email'] ?></p>", 1 );
	$this->out( '<?php endforeach; ?>', 0, 0 );
	return;
}

// Displaying field inputs' values:
$this->out( '<?php' );
$this->out( '// Displaying field inputs\' values:' );
$this->out( "\$value = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>' );
$this->out( "<p>Name: <?php echo \$value['name'] ?></p>" );
$this->out( "<p>Address: <?php echo \$value['address'] ?></p>" );
$this->out( "<p>Email: <?php echo \$value['email'] ?></p>", 0, 0 );

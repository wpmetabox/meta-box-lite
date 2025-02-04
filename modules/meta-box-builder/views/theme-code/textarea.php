<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

if ( $in_group ) {
	if ( isset( $field['clone'] ) ) {
		$this->out( "<?php \$values = {$group_var}[ '" . $field['id'] . "' ] ?? [];" );
		$this->out( '<?php foreach ( $values as $value ) : ?>' );
			$this->out( '<div class="my-content"><?php echo $value ?></div>', 1 );
		$this->out( '<?php endforeach; ?>' );

		return;
	}

	$this->out( "echo {$group_var}[ '" . $field['id'] . "' ] ?? '';" );
	return;
}

if ( isset( $field['clone'] ) ) {
	// Displaying cloneable values:
	$this->out( "<?php \$values = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' ) ?>' );
	$this->out( '<?php foreach ( $values as $value ) : ?>' );
		$this->out( '<p><?php echo $value ?></p>', 1 );
	$this->out( '<?php endforeach; ?>', 0, 0 );

	return;
}

// Getting the value:
$this->out( '<?php' );
$this->out( '// Getting the value:' );
$this->out( "\$value = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( 'echo $value;' );
$this->out( '?>', 0, 3 );

// Displaying the value:
$this->out( '<?php' );
$this->out( '// Displaying the value:' );
$this->out( "rwmb_the_value( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( '?>', 0, 3 );

// Auto adding paragraphs to the text:
$this->out( '<?php' );
$this->out( '// Auto adding paragraphs to the text:' );
$this->out( "\$value = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( 'echo wpautop( $value );' );
$this->out( '?>', 0, 3 );

// Parse shortcodes:
$this->out( '<?php' );
$this->out( '// Parse shortcodes:' );
$this->out( "\$value = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( 'echo do_shortcode( $value );' );
$this->out( '?>', 0, 3 );

// Parse blocks
$this->out( '<?php' );
$this->out( '// Parse blocks:' );
$this->out( "\$value = rwmb_meta( '" . $this->get_encoded_value( $field['id'] ) . ' );' );
$this->out( 'echo do_blocks( $value );' );
$this->out( '?>', 0, 0 );

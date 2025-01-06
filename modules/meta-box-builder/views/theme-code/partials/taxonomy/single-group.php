<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

$this->out( "\$term_id = {$group_var}[ '" . $field['id'] . "' ] ?? 0;" );
$this->out( '$term = get_term( $term_id );' );
$this->out( '?>' );
$this->out( '<p><a href="<?php echo get_term_link( $term ) ?>"><?php echo $term->name ?></a></p>' );
$this->out( '<?php' );

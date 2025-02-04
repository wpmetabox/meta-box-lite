<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

$this->out( "\$user_ids = {$group_var}[ '" . $field['id'] . "' ] ?? [];" );
$this->out( '?>' );
$this->out( '<h3>Speakers</h3>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $user_ids as $user_id ) : ?>', 1 );
		$this->out( '<?php $user = get_userdata( $user_id ); ?>', 2 );
		$this->out( '<li><?php echo $user->display_name ?></li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>' );
$this->out( '<?php' );

<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

$this->out( "\$post_ids = {$group_var}[ '" . $field['id'] . "' ] ?? [];" );
$this->out( '?>' );
$this->out( '<h3>Related posts</h3>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $post_ids as $post_id ) : ?>', 1 );
		$this->out( '<li><a href="<?php echo get_permalink( $post_id ) ?>"><?php echo get_the_title( $post_id ); ?></a></li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>' );
$this->out( '<?php' );

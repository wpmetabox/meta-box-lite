<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

$this->out( "\$image_ids = {$group_var}[ '" . $field['id'] . "' ] ?? [];" );
$this->out( '?>' );
$this->out( '<h3>Uploaded images</h3>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $image_ids as $image_id ) : ?>', 1 );
		$this->out( '<?php $image = RWMB_Image_Field::file_info( $image_id, [ \'size\' => \'thumbnail\' ] ); ?>', 2 );
		$this->out( '<li><img src="<?php echo $image[\'url\']; ?>"></li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>' );
$this->out( '<?php' );

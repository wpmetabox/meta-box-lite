<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

$this->out( "\$image_id = {$group_var}[ '" . $field['id'] . "' ] ?? 0;" );
$this->out( '$image = RWMB_Image_Field::file_info( $image_id, [ \'size\' => \'thumbnail\' ] );' );
$this->out( '?>' );
$this->out( '<h3>Logo</h3>' );
$this->out( '<img src="<?php echo $image[\'url\']; ?>">' );
$this->out( '<?php' );

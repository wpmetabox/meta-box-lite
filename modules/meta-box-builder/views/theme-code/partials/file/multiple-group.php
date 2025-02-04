<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

$this->out( "\$file_ids = {$group_var}[ '" . $field['id'] . "' ] ?? [];" );
$this->out( '?>' );
$this->out( '<h3>Uploaded files</h3>' );
$this->out( '<ul>' );
	$this->out( '<?php foreach ( $file_ids as $file_id ) : ?>', 1 );
		$this->out( '<?php $file = RWMB_File_Field::file_info( $file_id ); ?>', 2 );
		$this->out( '<li><a href="<?php echo $file[\'url\']; ?>"><?php echo $file[\'name\']; ?></a></li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>' );
$this->out( '<?php' );

<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

$this->out( '?>' );
$this->out( "<p><a href='<?php echo {$group_var}[ '{$field['id']}' ] ?>'>Download file</a></p>" );
$this->out( '<?php' );

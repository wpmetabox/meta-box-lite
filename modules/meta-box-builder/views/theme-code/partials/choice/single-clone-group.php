<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

$this->out( '?>' );
$this->out( '<ul>' );
	$this->out( "<?php foreach ( {$group_var}[ '" . $field['id'] . '\' ] as $value ) : ?>', 1 );
		$this->out( '<li><?php echo $value ?></li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 1 );
$this->out( '<?php' );

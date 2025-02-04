<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBB\RestApi\ThemeCode\GroupVars;

$group_var = GroupVars::get_current_group_item_var();

$this->out( '?>' );
$this->out( '<ul>' );
	$this->out( "<?php foreach ( {$group_var}[ '" . $field['id'] . '\' ] as $clone ) : ?>', 1 );
		$this->out( '<li>', 2 );

			$this->out( '<ul>', 3 );
				$this->out( '<?php foreach ( $clone as $value ) : ?>', 4 );
					$this->out( '<li><?php echo $value ?></li>', 5 );
				$this->out( '<?php endforeach; ?>', 4 );
			$this->out( '</ul>', 3 );

		$this->out( '</li>', 2 );
	$this->out( '<?php endforeach; ?>', 1 );
$this->out( '</ul>', 0, 1 );
$this->out( '<?php', 0, 1 );

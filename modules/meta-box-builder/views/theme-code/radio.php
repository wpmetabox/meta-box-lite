<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$file  = empty( $field['clone'] ) ? 'single' : 'single-clone';
$file .= $in_group ? '-group' : '';

require __DIR__ . "/partials/choice/$file.php";

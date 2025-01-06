<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$file  = empty( $field['multiple'] ) ? 'single' : 'multiple';
$file .= empty( $field['clone'] ) ? '' : '-clone';
$file .= $in_group ? '-group' : '';

require __DIR__ . "/partials/post/$file.php";

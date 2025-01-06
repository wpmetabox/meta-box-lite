<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$file  = empty( $field['clone'] ) ? 'multiple' : 'multiple-clone';
$file .= $in_group ? '-group' : '';

require __DIR__ . "/partials/choice/$file.php";

<?php

if ( defined( 'ABSPATH' ) && ! defined( 'RWMB_VER' ) ) {
	require_once __DIR__ . '/inc/loader.php';
	$rwmb_loader = new RWMB_Loader();
	$rwmb_loader->init();
}

<?php

if ( ! class_exists( 'MB_Yoast_SEO' ) ) {
	require_once __DIR__ . '/class-mb-yoast-seo.php';
	$mb_yoast_seo = new MB_Yoast_SEO;
	add_action( 'rwmb_enqueue_scripts', [ $mb_yoast_seo, 'enqueue' ] );
}
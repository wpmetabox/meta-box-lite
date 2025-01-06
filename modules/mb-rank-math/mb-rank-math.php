<?php

// Prevent loading this file directly.
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists( 'mb_rank_math_load' ) ) {
	add_action( 'admin_init', 'mb_rank_math_load' );

	function mb_rank_math_load(){
		if ( ! class_exists( 'RankMath' ) ) {
			return;
		}
		require_once __DIR__ . '/class-mb-rank-math.php';
		$mb_rank_math = new MB_Rank_Math;
		add_action( 'rwmb_enqueue_scripts', [ $mb_rank_math, 'enqueue' ] );
	}
}
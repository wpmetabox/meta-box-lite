<?php

if ( ! class_exists( 'MB_FacetWP_Integrator' ) ) {
	require __DIR__ . '/class-mb-facetwp-integrator.php';
	new MB_FacetWP_Integrator;

	add_action( 'mb_relationships_init', function () {
		add_filter( 'facetwp_facet_types', function ($types) {
			require_once __DIR__ . '/class-mb-relationships-facetwp.php';

			$types[ MB_Relationships_FacetWP::FACET_TYPE ] = new MB_Relationships_FacetWP();
			return $types;
		} );
	} );
}



<?php
$folders = [
	'meta-box',
	'mb-acf-migration',
	'mb-comment-meta',
	'mb-custom-post-type',
	'mb-divi-integrator',
	'mb-elementor-integrator',
	'mb-rank-math',
	'mb-relationships',
	'mb-rest-api',
	'mb-toolset-migration',
	'mb-yoast-seo',
	'meta-box-beaver-themer-integrator',
	'meta-box-builder',
	'meta-box-facetwp-integrator',
	'text-limiter',
];

foreach ( $folders as $folder ) {
	$file = __DIR__ . "/modules/$folder/$folder.php";
	cleanup_header_comment( $file );
}

function cleanup_header_comment( string $file ): void {
	$content = (string) file_get_contents( $file, false, null, 0, 8 * KB_IN_BYTES );
	$content = trim( preg_replace( '/^\*\*.$/', '', $content ) );
	$content = trim( preg_replace( '/ \* .$/', '', $content ) );
	file_put_contents( $file, $content );
}

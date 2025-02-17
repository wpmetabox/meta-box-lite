<?php
function fix_language_files( string $dir ): void {
	$files = glob( "$dir/*.php" );
	foreach ( $files as $file ) {
		$data = include $file;
		$data = array_filter( $data );
		if ( isset( $data['messages'] ) && is_array( $data['messages'] ) ) {
			$data['messages'] = array_filter( $data['messages'] );
		}
		$data = "<?php\nreturn " . var_export( $data, true ) . ";\n";
		file_put_contents( $file, $data );
	}
}

fix_language_files( dirname( __DIR__ ) . '/languages/mb-custom-post-type' );
fix_language_files( dirname( __DIR__ ) . '/languages/meta-box' );
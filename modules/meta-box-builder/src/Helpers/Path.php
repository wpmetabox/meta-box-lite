<?php
namespace MBB\Helpers;

class Path {
	/**
	 * Check if the intended path is writable.
	 *
	 * Because is_writable() only checks the existing path, and returns false if the path doesn't exist,
	 * this method checks if we can create the path, also do the additional security check to make sure the path is inside
	 * the WordPress installation.
	 */
	public static function is_future_path_writable( string $path ): bool {
		$path = trailingslashit( $path );

		// For security, we only allow the path inside the current WordPress installation.
		if ( ! str_starts_with( $path, wp_normalize_path( ABSPATH ) ) ) {
			return false;
		}

		$paths = explode( '/', $path );

		// Traverse from the leaf to the root to get the first existing directory
		// and check if it's writable
		while ( count( $paths ) > 1 ) {
			array_pop( $paths );
			$path_str = implode( '/', $paths );

			if ( file_exists( $path_str ) ) {
				break;
			}
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
		return is_dir( $path_str ) && is_writable( $path_str );
	}
}
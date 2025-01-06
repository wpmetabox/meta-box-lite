<?php
namespace MBB\RestApi;

use MBB\Extensions\Blocks as BlockExtension;
use MBBParser\Parsers\Settings;

class Blocks extends Base {
	public function local_path_data( $request ) {
		$path    = $request->get_param( 'path' );
		$version = $request->get_param( 'version' ) ?? time();
		$name    = $request->get_param( 'postName' );

		// Parse the path to get the correct path
		$parser = new Settings();
		$path   = $parser->replace_variables( $path );

		$is_writable = BlockExtension::is_future_path_writable( $path );

		$path_to_block_json            = $path . '/' . $name . '/block.json';
		[ $block_settings, $is_newer ] = $this->get_local_block_settings( $path_to_block_json, $version );

		return new \WP_REST_Response( compact( 'is_writable', 'block_settings', 'is_newer' ) );
	}

	/**
	 * Check if the local file is newer than the working version
	 */
	private function get_local_block_settings( string $local_path, string $version ): array {
		// Bail if the file doesn't exist or is not readable
		if ( ! file_exists( $local_path ) || ! is_readable( $local_path ) ) {
			return [ [], false ];
		}

		$file_content     = file_get_contents( $local_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$block            = json_decode( $file_content, true );
		$local_version    = $block['version'] ?? '0';
		$local_version    = (int) str_replace( 'v', '', $local_version );
		$local_version_ts = filemtime( $local_path );
		$local_version    = max( $local_version, $local_version_ts );
		$version          = (int) str_replace( 'v', '', $version );

		return [ $block, $local_version > $version ];
	}
}

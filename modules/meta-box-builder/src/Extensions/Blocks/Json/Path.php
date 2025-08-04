<?php
namespace MBB\Extensions\Blocks\Json;

use MBB\Helpers\Path as PathHelper;
use MBBParser\Parsers\Settings;
use WP_REST_Request;
use WP_REST_Server;

class Path {
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	public function register_rest_routes(): void {
		register_rest_route( 'mbb', 'blocks/json/check-path', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'check_path' ],
			'permission_callback' => function (): bool {
				return current_user_can( 'edit_posts' );
			},
		] );
	}

	public function check_path( WP_REST_Request $request ): array {
		$path    = $request->get_param( 'path' );
		$version = $request->get_param( 'version' ) ?? time();
		$name    = $request->get_param( 'postName' );

		// Parse the path to get the correct path
		$parser = new Settings();
		$path   = $parser->replace_variables( $path );

		return [
			'is_writable' => PathHelper::is_future_path_writable( $path ),
			'is_newer'    => $this->is_newer( "$path/$name/block.json", $version ),
		];
	}

	private function is_newer( string $local_path, string $version ): bool {
		// Bail if the file doesn't exist or is not readable
		if ( ! file_exists( $local_path ) || ! is_readable( $local_path ) ) {
			return false;
		}

		$block = json_decode( file_get_contents( $local_path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! is_array( $block ) ) {
			return false;
		}

		$local_version    = $block['version'] ?? '0';
		$local_version    = (int) str_replace( 'v', '', $local_version );
		$local_version_ts = filemtime( $local_path );
		$local_version    = max( $local_version, $local_version_ts );
		$version          = (int) str_replace( 'v', '', $version );

		return $local_version > $version;
	}
}
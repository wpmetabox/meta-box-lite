<?php
namespace eLightUp\PluginSearch;

abstract class Base {
	/**
	 * Plugin API result.
	 *
	 * @var object|\WP_Error
	 */
	protected $result;

	/**
	 * Plugin API action.
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * Plugin API arguments.
	 *
	 * @var object
	 */
	protected $args;

	public function __construct() {
		add_filter( 'plugins_api_result', [ $this, 'process' ], 10, 3 );
	}

	/**
	 * Process the plugin API result.
	 *
	 * @param object|\WP_Error $result Plugin API result.
	 * @param string           $action Plugin API action.
	 * @param object           $args   Plugin API arguments.
	 */
	public function process( $result, string $action, $args ) {
		$this->result = $result;
		$this->action = $action;
		$this->args   = $args;

		if ( is_wp_error( $this->result ) || $this->action !== 'query_plugins' ) {
			return $result;
		}

		$this->remove();

		if ( ! $this->check() ) {
			return $result;
		}

		$this->suggests();
		return $this->result;
	}

	/**
	 * Remove plugins from the result.
	 */
	protected function remove(): void {
		$slugs = apply_filters( 'eps_remove', [ 'secure-custom-fields' ] );

		foreach ( $slugs as $slug ) {
			$index = array_search( $slug, wp_list_pluck( $this->result->plugins, 'slug' ), true );
			if ( $index !== false ) {
				array_splice( $this->result->plugins, $index, 1 );
			}
		}
	}

	/**
	 * Suggest plugins to insert into the result.
	 */
	abstract protected function suggests(): void;

	/**
	 * Check if the current request should be processed.
	 */
	protected function check(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only GET request in the admin plugin browser.
		$paged = isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1;
		if ( $paged !== 1 ) {
			return false;
		}

		if ( is_wp_error( $this->result ) || $this->action !== 'query_plugins' ) {
			return false;
		}

		return $this->validate();
	}

	/**
	 * Validate the current request.
	 */
	abstract protected function validate(): bool;

	/**
	 * Insert a plugin into the result at the given position.
	 *
	 * @param string $slug     Plugin slug.
	 * @param int    $position Position to insert the plugin at.
	 */
	protected function insert( string $slug, int $position ): void {
		$slugs = wp_list_pluck( $this->result->plugins, 'slug' );
		$index = array_search( $slug, $slugs, true );

		// Plugin not in the list? Add it.
		if ( $index === false ) {
			$plugin = $this->get_plugin_info( $slug );
			array_splice( $this->result->plugins, $position, 0, [ $plugin ] );
		}

		// Plugin already at a higher position: do nothing.
		if ( $index < $position ) {
			return;
		}

		// Move the plugin to the new position.
		$plugin = $this->result->plugins[ $index ];
		array_splice( $this->result->plugins, $index, 1 );
		array_splice( $this->result->plugins, $position, 0, [ $plugin ] );
	}

	/**
	 * Get plugin info from the WordPress.org API.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return array|null Plugin info or null if not found.
	 */
	private function get_plugin_info( $slug ) {
		$cache_key = 'eps_plugin_' . $slug;
		$info      = get_transient( $cache_key );
		if ( $info !== false ) {
			return $info;
		}

		$args = [
			'page'     => 1,
			'per_page' => 1,
			'locale'   => get_user_locale(),
			'search'   => $slug,
		];
		$url  = add_query_arg( [
			'action'  => 'query_plugins',
			'request' => $args,
		], 'https://api.wordpress.org/plugins/info/1.2/' );

		$request = wp_remote_get( $url, [ 'timeout' => 15 ] );
		$info    = wp_remote_retrieve_body( $request );
		if ( ! $info ) {
			return null;
		}

		$info = json_decode( $info, true );
		if ( ! isset( $info['plugins'][0] ) ) {
			return null;
		}
		$info = $info['plugins'][0];

		set_transient( $cache_key, $info, DAY_IN_SECONDS );
		return $info;
	}
}

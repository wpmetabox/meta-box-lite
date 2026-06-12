<?php
namespace MBB\Extensions\Blocks\Json;

use WP_Query;
use MetaBox\Support\Arr;
use MBB\Extensions\Blocks\CodeToCallbackTransformer;

class Register {
	public function __construct() {
		add_action( 'init', [ $this, 'register_blocks' ] );
	}

	public function register_blocks(): void {
		$query = new WP_Query( [
			'post_type'              => 'meta-box',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'fields'                 => 'ids',
			'update_post_term_cache' => false,
		] );

		foreach ( $query->posts as $post_id ) {
			$meta_box = get_post_meta( $post_id, 'meta_box', true );
			if ( ! $this->use_block_json( $meta_box ) ) {
				continue;
			}

			$path = Arr::get( $meta_box, 'block_json.path' );
			$args = $this->get_block_type_args( $post_id, $meta_box );
			register_block_type( trailingslashit( $path ) . $meta_box['id'], $args );
		}
	}

	private function use_block_json( $meta_box ): bool {
		if ( ! is_array( $meta_box ) ) {
			return false;
		}

		$path = Arr::get( $meta_box, 'block_json.path' );

		return Arr::get( $meta_box, 'type' ) === 'block'
			&& Arr::get( $meta_box, 'block_json.enable' )
			&& $path
			&& file_exists( $path );
	}

	private function get_block_type_args( int $post_id, array $meta_box ): array {
		$settings    = get_post_meta( $post_id, 'settings', true );
		$render_with = Arr::get( $settings, 'render_with' );

		$render_callback = null;
		if ( $render_with === 'callback' ) {
			$render_callback = $this->get_render_callback_for_callback( $meta_box );
		}
		if ( $render_with === 'template' ) {
			$render_callback = $this->get_render_callback_for_template( $meta_box );
		}
		if ( $render_with === 'code' ) {
			$render_callback = $this->get_render_callback_for_code( $meta_box );
		}

		return $render_callback ? compact( 'render_callback' ) : [];
	}

	private function get_render_callback_for_callback( array $meta_box ): ?callable {
		$callback = Arr::get( $meta_box, 'render_callback' );
		if ( ! $callback || ! is_callable( $callback ) ) {
			return null;
		}

		// render_callback must return a string, but we echo => capture via output buffering.
		return static function( $attributes, $content, $block ) use ( $callback ) {
			ob_start();
			call_user_func( $callback, $attributes, $content, $block );
			return ob_get_clean();
		};
	}

	private function get_render_callback_for_template( array $meta_box ): ?callable {
		$template = Arr::get( $meta_box, 'render_template' );
		if ( ! $template || ! is_string( $template ) ) {
			return null;
		}

		// Template with relative path to block.json: handled by WordPress
		if ( str_starts_with( $template, '.' ) ) {
			return null;
		}

		// Template with absolute path: include it inside a custom render_callback
		if ( ! file_exists( $template ) ) {
			return null;
		}
		return static function( $attributes, $content, $block ) use ( $template ) {
			ob_start();
			include $template;
			return ob_get_clean();
		};
	}

	private function get_render_callback_for_code( array $meta_box ): ?callable {
		if ( empty( $meta_box['render_code'] ) ) {
			return null;
		}

		// render_callback must return a string, but we echo => capture via output buffering.
		$callback = CodeToCallbackTransformer::get_render_callback( $meta_box );
		return static function( $attributes, $content, $block ) use ( $callback ) {
			ob_start();
			call_user_func( $callback, $attributes, $content, $block );
			return ob_get_clean();
		};
	}
}

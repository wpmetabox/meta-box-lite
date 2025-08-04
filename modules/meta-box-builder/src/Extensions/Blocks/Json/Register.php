<?php
namespace MBB\Extensions\Blocks\Json;

class Register {
	public function __construct() {
		add_action( 'init', [ $this, 'register_blocks' ] );
	}

	public function register_blocks(): void {
		$query = new \WP_Query( [
			'post_type'              => 'meta-box',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'fields'                 => 'ids',
			'update_post_term_cache' => false,
		] );

		foreach ( $query->posts as $post_id ) {
			$meta_box = get_post_meta( $post_id, 'meta_box', true );

			if ( empty( $meta_box ) ) {
				continue;
			}

			// Bail if this is not a block.
			if ( empty( $meta_box['type'] ) || 'block' !== $meta_box['type'] ) {
				continue;
			}

			// Bail if block path is empty.
			if ( empty( $meta_box['block_json'] ) || empty( $meta_box['block_json']['path'] ) ) {
				continue;
			}

			if ( empty( $meta_box['block_json']['enable'] )
				|| ! file_exists( $meta_box['block_json']['path'] )
				// Do not register block.json if its rendering method is via a callback, template or code.
				|| isset( $meta_box['function_name'] )
				|| isset( $meta_box['render_template'] )
				|| isset( $meta_box['render_code'] )
			) {
				continue;
			}

			// Now we register the block with the provided path
			register_block_type( trailingslashit( $meta_box['block_json']['path'] ) . $meta_box['id'] );
		}
	}
}

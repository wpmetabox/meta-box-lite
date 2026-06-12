<?php
namespace MBB\Extensions\Blocks;

use MBB\TwigProxy;

class CodeToCallbackTransformer {
	public function __construct() {
		add_action( 'mbb_before_register_meta_box', [ $this, 'transform_code_to_callback' ] );
	}

	public function transform_code_to_callback( array &$data ): void {
		$meta_box = &$data['meta_box'];

		if ( empty( $meta_box['type'] ) || 'block' !== $meta_box['type'] ) {
			return;
		}

		if ( empty( $meta_box['render_code'] ) ) {
			return;
		}

		$meta_box['render_callback'] = self::get_render_callback( $meta_box );
	}

	public static function get_render_callback( array $meta_box ): callable {
		return static function ( $attributes, $content, $block ) use ( $meta_box ) {
			$data               = $attributes;
			$data['is_preview'] = defined( 'REST_REQUEST' ) && REST_REQUEST;
			$data['post_id']    = get_the_ID();

			// Get all fields data.
			$fields = array_filter( $meta_box['fields'], [ __CLASS__, 'has_value' ] );
			foreach ( $fields as $field ) {
				$data[ $field['id'] ] = 'group' === $field['type'] ? mb_get_block_field( $field['id'], [] ) : mb_the_block_field( $field['id'], [], false );
			}

			$loader = new \eLightUp\Twig\Loader\ArrayLoader( [
				'block' => '{% autoescape false %}' . $meta_box['render_code'] . '{% endautoescape %}',
			] );
			$twig   = new \eLightUp\Twig\Environment( $loader );

			// Proxy for all PHP/WordPress functions.
			$data['mb'] = new TwigProxy();

			echo $twig->render( 'block', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		};
	}

	private static function has_value( array $field ): bool {
		return ! empty( $field['id'] ) && ! in_array( $field['type'], [ 'heading', 'divider', 'button', 'custom_html', 'tab' ], true );
	}
}

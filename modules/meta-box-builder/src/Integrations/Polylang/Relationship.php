<?php
namespace MBB\Integrations\Polylang;

use MetaBox\Support\Arr;
use WP_Post;

class Relationship {
	private $keys = [];

	public function __construct() {
		$this->keys = [
			'empty_message'           => __( 'Empty message', 'meta-box-builder' ),
			'admin_column.title'      => __( 'Admin column title', 'meta-box-builder' ),
			'meta_box.title'          => __( 'Meta box title', 'meta-box-builder' ),
			'field.name'              => __( 'Field name', 'meta-box-builder' ),
			'field.desc'              => __( 'Field description', 'meta-box-builder' ),
			'field.label_description' => __( 'Field label description', 'meta-box-builder' ),
			'field.placeholder'       => __( 'Field placeholder', 'meta-box-builder' ),
			'field.add_button'        => __( 'Field add button', 'meta-box-builder' ),
		];

		add_filter( 'mbb_relationship', [ $this, 'register_strings' ], 10, 2 );
		add_filter( 'mbb_relationship', [ $this, 'use_translations' ], 20 );
	}

	/**
	 * Register strings for translation.
	 *
	 * @param array $relationship Relationship data.
	 * @return array Modified relationship data.
	 */
	public function register_strings( array $relationship, WP_Post $post ): array {
		if ( empty( $relationship ) || ! is_array( $relationship ) ) {
			return $relationship;
		}

		$context = $this->get_context( $relationship, $post );

		// Register strings for both sides.
		if ( is_array( $relationship['from'] ) ) {
			$this->register_side_strings( 'from', $relationship['from'], $context );
		}
		if ( is_array( $relationship['to'] ) ) {
			$this->register_side_strings( 'to', $relationship['to'], $context );
		}

		return $relationship;
	}

	private function register_side_strings( string $side, array $data, string $context ): void {
		foreach ( $this->keys as $key => $label ) {
			$value = Arr::get( $data, $key, '' );
			if ( ! empty( $value ) ) {
				pll_register_string(
					$side . '_' . str_replace( '.', '_', $key ),
					$value,
					$context
				);
			}
		}
	}

	public function use_translations( array $relationship ): array {
		if ( is_array( $relationship['from'] ) ) {
			$this->use_side_translations( 'from', $relationship['from'] );
		}
		if ( is_array( $relationship['to'] ) ) {
			$this->use_side_translations( 'to', $relationship['to'] );
		}

		return $relationship;
	}

	private function use_side_translations( string $side, array &$data ): void {
		foreach ( $this->keys as $key => $label ) {
			$value = Arr::get( $data, $key );
			if ( ! empty( $value ) ) {
				Arr::set( $data, $side . '_' . str_replace( '.', '_', $key ), pll__( $value ) );
			}
		}
	}

	private function get_context( array $relationship, WP_Post $post ): string {
		// translators: %s is the title of the relationship.
		return sprintf( __( 'Meta Box Relationship: %s', 'meta-box-builder' ), $post->post_title );
	}
}
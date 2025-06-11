<?php
namespace MBB\Integrations\WPML;

use WP_Post;
use MetaBox\Support\Arr;

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

		add_action( 'save_post_mb-relationship', [ $this, 'register_package' ], 20, 2 );
		add_filter( 'mbb_relationship', [ $this, 'use_translations' ], 10, 2 );
		add_action( 'deleted_post_mb-relationship', [ $this, 'delete_package' ], 10, 2 );
	}

	public function register_package( int $post_id, WP_Post $post ): void {
		$relationship = get_post_meta( $post_id, 'relationship', true );
		if ( empty( $relationship ) || ! is_array( $relationship ) ) {
			return;
		}

		$package = $this->get_package( $post );

		do_action( 'wpml_start_string_package_registration', $package );

		$this->register_strings( $relationship, $post );

		do_action( 'wpml_delete_unused_package_strings', $package );
	}

	private function register_strings( array $relationship, WP_Post $post ): void {
		$package = $this->get_package( $post );

		do_action(
			'wpml_register_string',
			$relationship['title'] ?? '',
			'title',
			$package,
			__( 'Title', 'meta-box-builder' ),
			'LINE'
		);

		if ( is_array( $relationship['from'] ) ) {
			$this->register_side_strings( 'from', $relationship['from'], $package );
		}
		if ( is_array( $relationship['to'] ) ) {
			$this->register_side_strings( 'to', $relationship['to'], $package );
		}
	}

	private function register_side_strings( string $side, array $data, array $package ): void {
		foreach ( $this->keys as $key => $label ) {
			do_action(
				'wpml_register_string',
				Arr::get( $data, $key, '' ),
				$side . '_' . str_replace( '.', '_', $key ),
				$package,
				sprintf( '%s: %s', $side, $label ),
				'LINE'
			);
		}
	}

	public function use_translations( array $relationship, WP_Post $post ): array {
		$package = $this->get_package( $post );

		if ( ! empty( $relationship['title'] ) ) {
			$relationship['title'] = apply_filters( 'wpml_translate_string', $relationship['title'], 'title', $package );
		}

		if ( is_array( $relationship['from'] ) ) {
			$this->use_side_translations( 'from', $relationship['from'], $package );
		}
		if ( is_array( $relationship['to'] ) ) {
			$this->use_side_translations( 'to', $relationship['to'], $package );
		}

		return $relationship;
	}

	private function use_side_translations( string $side, array &$data, array $package ): void {
		foreach ( $this->keys as $key => $label ) {
			$value = Arr::get( $data, $key );
			if ( ! empty( $value ) ) {
				Arr::set( $data, $key, apply_filters( 'wpml_translate_string', $value, $side . '_' . str_replace( '.', '_', $key ), $package ) );
			}
		}
	}

	private function get_package( WP_Post $post ): array {
		return [
			'kind'      => 'Meta Box: Relationship',
			'name'      => urldecode( $post->post_name ),
			'title'     => $post->post_title,
			'edit_link' => get_edit_post_link( $post ),
		];
	}

	public function delete_package( int $post_id, WP_Post $post ) {
		$package = $this->get_package( $post );
		do_action( 'wpml_delete_package', $package['name'], $package['kind'] );
	}
}

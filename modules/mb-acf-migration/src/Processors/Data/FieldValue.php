<?php
namespace MetaBox\ACF\Processors\Data;

use WP_Query;

class FieldValue {
	private $key;
	private $storage;
	private $type;
	private $post_id;
	private $delete_key;

	public function __construct( $args ) {
		$this->key        = $args['key'];
		$this->delete_key = $args['delete_key'] ?? null;
		$this->storage    = $args['storage'];

		// For group, repeater, flexible content.
		$this->type    = $args['type'] ?? null;
		$this->post_id = $args['post_id'] ?? null;
	}

	public function get_value() {
		$method = "get_value_{$this->type}";
		$method = method_exists( $this, $method ) ? $method : 'get_value_general';

		$value = $this->$method();

		// Delete extra key.
		if ( $this->delete_key ) {
			$this->storage->delete( $this->delete_key );
		}

		return $value;
	}

	private function get_value_general() {
		// Get from backup key first.
		$backup_key = "_acf_bak_{$this->key}";
		$value      = $this->storage->get( $backup_key );
		if ( '' !== $value ) {
			return $value;
		}

		// Backup the value.
		$value = $this->storage->get( $this->key );
		if ( '' !== $value ) {
			$this->storage->update( $backup_key, $value );
		}

		return $value;
	}

	private function get_value_taxonomy() {
		$value = $this->get_value_general();
		return is_array( $value ) ? implode( ',', $value ) : '';
	}

	private function get_value_group() {
		$value      = [];
		$sub_fields = $this->get_sub_fields();

		foreach ( $sub_fields as $sub_field ) {
			$settings = unserialize( $sub_field->post_content ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
			$sub_key  = $sub_field->post_excerpt;
			$key      = $this->key . '_' . $sub_key;

			$field_value = new self( [
				'key'        => $key,
				'delete_key' => $key,
				'storage'    => $this->storage,
				'type'       => $settings['type'],
				'post_id'    => $sub_field->ID,
			] );

			$value[ $sub_key ] = $field_value->get_value();
		}

		return $value;
	}

	private function get_value_repeater() {
		$value      = [];
		$sub_fields = $this->get_sub_fields();
		$count      = (int) $this->get_value_general();

		if ( empty( $count ) ) {
			return $value;
		}

		for ( $i = 0; $i < $count; $i++ ) {
			$clone = [];
			foreach ( $sub_fields as $sub_field ) {
				$settings = unserialize( $sub_field->post_content ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
				$sub_key  = $sub_field->post_excerpt;
				$key      = "{$this->key}_{$i}_{$sub_key}";

				$field_value = new self( [
					'key'        => $key,
					'delete_key' => $key,
					'storage'    => $this->storage,
					'type'       => $settings['type'],
					'post_id'    => $sub_field->ID,
				] );

				$clone[ $sub_key ] = $field_value->get_value();
			}

			$value[] = $clone;
		}

		return $value;
	}

	private function get_value_flexible_content() {
		$value      = [];
		$sub_fields = $this->get_sub_fields();
		$layouts    = $this->get_value_general();

		if ( empty( $layouts ) ) {
			return $value;
		}

		$count = count( $layouts );
		for ( $i = 0; $i < $count; $i++ ) {
			$layout = $layouts[ $i ];
			$clone  = [
				"{$this->key}_layout" => $layout,
				$layout               => [],
			];

			foreach ( $sub_fields as $sub_field ) {
				$settings = unserialize( $sub_field->post_content ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
				$sub_key  = $sub_field->post_excerpt;
				$key      = "{$this->key}_{$i}_{$sub_key}";

				$field_value = new self( [
					'key'        => $key,
					'delete_key' => $key,
					'storage'    => $this->storage,
					'type'       => $settings['type'],
					'post_id'    => $sub_field->ID,
				] );

				$clone[ $layout ][ $sub_key ] = $field_value->get_value();
			}

			$value[] = $clone;
		}

		return $value;
	}

	private function get_sub_fields() {
		$query = new WP_Query( [
			'post_type'              => 'acf-field',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'order'                  => 'ASC',
			'orderby'                => 'menu_order',
			'post_parent'            => $this->post_id,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );

		return $query->posts;
	}
}

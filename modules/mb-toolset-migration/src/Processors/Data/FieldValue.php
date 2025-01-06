<?php
namespace MetaBox\TS\Processors\Data;

class FieldValue {
	private $key;
	private $storage;
	private $type;
	private $clone;
	private $field_id;

	public function __construct( $args ) {
		$this->key      = $args['key'];
		$this->storage  = $args['storage'];
		$this->type     = $args['type'] ?? 'text';
		$this->clone    = $args['clone'] ?? false;
		$this->field_id = $args['field_id'] ?? null;
	}

	public function get_value() {
		$method = $this->field_id ? 'get_value_group' : "get_value_{$this->type}";
		$method = method_exists( $this, $method ) ? $method : 'get_value_general';

		return $this->$method();
	}

	private function get_value_general() {
		// Get from backup key first.
		$backup_key = "_ts_bak_{$this->key}";
		$value      = $this->storage->get( $backup_key );
		if ( ! empty( $value ) ) {
			return $value;
		}

		// Backup the value.
		$ts_key = "wpcf-{$this->key}";
		$value  = $this->storage->get( $ts_key );
		if ( $this->clone && ! is_array( $value ) ) {
			$value = $this->storage->get( $ts_key, false );
		}
		if ( ! empty( $value ) ) {
			$this->storage->update( $backup_key, $value );
		}
		return $value;
	}

	private function get_value_group( $child = null ) {
		$post_type  = get_post_meta( $this->field_id, '_types_repeatable_field_group_post_type', true );
		$fields     = get_post_meta( $this->field_id, '_wp_types_group_fields', true );
		$fields     = array_filter( explode( ',', $fields ) );
		$sub_fields = toolset_get_related_posts( $child ?: $this->storage->item, $post_type, [
			'query_by_role' => 'parent',
			'return'        => 'post_id',
		] );

		$values     = [];
		$sort_order = [];
		foreach ( $sub_fields as $sub_field ) {
			$value        = [];
			$order        = get_post_meta( $sub_field, 'toolset-post-sortorder', true );
			$sort_order[] = (int) $order - 1;

			foreach ( $fields as $field ) {
				if ( ! preg_match( '/^_repeatable_group_/', $field ) ) {
					$value[ $field ] = $this->get_value_sub_field( $sub_field, $field );
					continue;
				}

				// For groups.
				$field_id             = explode( '_', $field );
				$field_id             = (int) end( $field_id );
				$field_value          = new self( [
					'key'      => null,
					'storage'  => $this->storage,
					'type'     => null,
					'clone'    => true,
					'field_id' => $field_id,
				] );
				$child_type           = get_post_meta( $field_id, '_types_repeatable_field_group_post_type', true );
				$value[ $child_type ] = $field_value->get_value_group( $sub_field );
			}

			$values[] = $value;
		}

		$value_group = [];
		$count       = count( $values );
		for ( $i = 0; $i < $count; $i++ ) {
			$value_group[ $sort_order[ $i ] ] = $values[ $i ];
		}
		ksort( $value_group );

		return $value_group;
	}

	private function get_value_sub_field( $id, $key ) {
		// Get from backup key first.
		$backup_key = "_ts_bak_{$key}";
		$value      = get_post_meta( $id, $backup_key, true );
		if ( ! empty( $value ) ) {
			return $value;
		}

		// Backup the value.
		$value    = get_post_meta( $id, "wpcf-{$key}", true );
		$settings = $this->get_all_field_settings();
		$settings = $settings[ $key ];
		$media    = [ 'image', 'file', 'video' ];
		if ( in_array( $settings['type'], $media, true ) ) {
			$value = attachment_url_to_postid( $value );
		}
		if ( $settings['type'] === 'checkboxes' ) {
			if ( empty( $value ) || ! is_array( $value ) ) {
				return;
			}
			delete_post_meta( $id, $key );
			foreach ( $value as $sub_value ) {
				foreach ( $sub_value as $sub_sub_value ) {
					add_post_meta( $id, $key, $sub_sub_value );
				}
			}
			$value = get_post_meta( $id, $key, false );
		}
		if ( ! empty( $value ) ) {
			update_post_meta( $id, $backup_key, $value );
		}
		return $value;
	}

	private function get_all_field_settings() {
		$fields   = get_option( 'wpcf-fields' ) ?: [];
		$termmeta = get_option( 'wpcf-termmeta' ) ?: [];
		$usermeta = get_option( 'wpcf-usermeta' ) ?: [];

		return array_merge( $fields, $termmeta, $usermeta );
	}
}

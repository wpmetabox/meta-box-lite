<?php
namespace MBB\Upgrade\Ver404;

use MetaBox\Support\Arr;

/**
 * Update fields settings from data for AngularJS to React.
 */
class Fields extends Base {
	public function update( $post ) {
		$data   = json_decode( $post->post_excerpt, true );
		$fields = Arr::get( $data, 'fields', [] );

		$this->update_fields( $fields );

		update_post_meta( $post->ID, 'fields', $fields );

		return $fields;
	}

	private function update_fields( &$fields ) {
		$new_fields = [];
		foreach ( $fields as &$field ) {
			$this->update_field( $field );
			$id                  = uniqid();
			$field['_id']        = $id;
			$field['save_field'] = $field['save_field'] ?? true;
			$new_fields[ $id ]   = $field;
		}
		$fields = $new_fields;
	}

	private function update_field( &$field ) {
		$updater = new Field();
		$updater->update( $field );

		if ( isset( $field['fields'] ) ) {
			$this->update_fields( $field['fields'] );
		}
	}
}

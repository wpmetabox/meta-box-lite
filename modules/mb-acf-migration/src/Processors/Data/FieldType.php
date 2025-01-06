<?php
namespace MetaBox\ACF\Processors\Data;

class FieldType {
	private $settings;
	private $storage;
	private $field_value;

	public function __construct( $args ) {
		$this->settings    = $args['settings'];
		$this->storage     = $args['storage'];
		$this->field_value = new FieldValue( [
			'key'     => $args['settings']['id'],
			'storage' => $args['storage'],
			'type'    => $args['settings']['type'],
			'post_id' => $args['post_id'],
		] );
	}

	public function migrate() {
		// Always delete redundant key.
		$this->storage->delete( "_{$this->settings['id']}" );

		$method = "migrate_{$this->settings['type']}";
		$method = method_exists( $this, $method ) ? $method : 'migrate_general';
		$this->$method();
	}

	private function migrate_gallery() {
		$this->migrate_multiple( true );
	}

	private function migrate_select() {
		$this->migrate_multiple();
	}

	private function migrate_checkbox() {
		$this->migrate_multiple( true );
	}

	private function migrate_post_object() {
		$this->migrate_multiple();
	}

	private function migrate_page_link() {
		$this->migrate_multiple();
	}

	private function migrate_relationship() {
		$this->migrate_multiple( true );
	}

	private function migrate_user() {
		$this->migrate_multiple();
	}

	private function migrate_google_map() {
		$value = $this->field_value->get_value();
		if ( empty( $value ) || ! is_array( $value ) ) {
			return;
		}
		$map = "{$value['lat']},{$value['lng']},{$value['zoom']}";
		$this->storage->update( $this->settings['id'], $map );
		$this->storage->update( $this->settings['id'] . '_address', $value['address'] );
	}

	private function migrate_general() {
		$value = $this->field_value->get_value();
		$this->storage->update( $this->settings['id'], $value );
	}

	private function migrate_multiple( $force_multiple = false ) {
		if ( ! $force_multiple && empty( $this->settings['multiple'] ) ) {
			return;
		}

		$value = $this->field_value->get_value();
		if ( empty( $value ) || ! is_array( $value ) ) {
			return;
		}
		$this->storage->delete( $this->settings['id'] );
		foreach ( $value as $sub_value ) {
			$this->storage->add( $this->settings['id'], $sub_value );
		}
	}
}

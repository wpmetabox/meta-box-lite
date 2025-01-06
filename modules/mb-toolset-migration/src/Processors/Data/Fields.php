<?php
namespace MetaBox\TS\Processors\Data;

class Fields {
	private $parent;
	private $storage;
	private $field;
	private $all_field_settings;

	public function __construct( $parent, $storage ) {
		$this->parent  = $parent;
		$this->storage = $storage;

		$this->all_field_settings = $this->get_all_field_settings();
	}

	private function get_all_field_settings() {
		$fields   = get_option( 'wpcf-fields' ) ?: [];
		$termmeta = get_option( 'wpcf-termmeta' ) ?: [];
		$usermeta = get_option( 'wpcf-usermeta' ) ?: [];

		return array_merge( $fields, $termmeta, $usermeta );
	}

	public function migrate_fields() {
		foreach ( $this->parent as $field_group_id ) {
			$fields = get_post_meta( $field_group_id, '_wp_types_group_fields', true );
			$fields = array_filter( explode( ',', $fields ) );
			foreach ( $fields as $field ) {
				$this->field = $field;
				$this->migrate_field();
			}
		}
	}

	private function migrate_field() {
		$settings     = $this->all_field_settings[ $this->field ];
		$ignore_types = [ 'skype' ];
		if ( in_array( $settings['type'], $ignore_types ) ) {
			return;
		}

		$field_id = '';
		if ( preg_match( '/^_repeatable_group_/', $this->field ) ) {
			$field_id = explode( '_', $this->field );
			$field_id = (int) end( $field_id );
		}

		$args = [
			'settings' => $settings,
			'storage'  => $this->storage,
			'field_id' => $field_id,
		];

		$field_type = new FieldType( $args );
		$field_type->migrate();
	}
}

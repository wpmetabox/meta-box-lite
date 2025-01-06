<?php
namespace MetaBox\ACF\Processors\FieldGroups;

use WP_Query;

class Fields {
	private $parent_id;
	private $fields = [];
	private $field;

	public function __construct( $parent_id ) {
		$this->parent_id = $parent_id;
	}

	public function migrate_fields() {
		$fields = $this->get_fields();
		foreach ( $fields as $field ) {
			$this->field = $field;
			$this->migrate_field();
		}

		return $this->fields;
	}

	private function get_fields() {
		$query = new WP_Query( [
			'post_type'              => 'acf-field',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'post_parent'            => $this->parent_id,
			'order'                  => 'ASC',
			'orderby'                => 'menu_order',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );

		return $query->posts;
	}

	private function migrate_field() {
		$settings = unserialize( $this->field->post_content ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize

		$ignore_types = [ 'link', 'accordion', 'clone' ];
		if ( in_array( $settings['type'], $ignore_types, true ) ) {
			return;
		}

		$settings['name'] = $this->field->post_title;
		$settings['id']   = $this->field->post_excerpt;

		if ( $settings['type'] === 'google_map' ) {
			$id                  = 'text_' . uniqid();
			$address_field       = [
				'id'         => $settings['id'] . '_address',
				'type'       => 'text',
				'name'       => $settings['name'] . ' ' . __( 'Address', 'mb-acf-migration' ),
				'_id'        => $id,
				'_state'     => 'collapse',
				'save_field' => true,
			];
			$this->fields[ $id ] = $address_field;

			$settings['address_field'] = $address_field['id'];
		}

		if ( $settings['type'] === 'time_picker' ) {
			$settings['type'] = 'time';
		}

		$field_type = new FieldType( $settings, $this->field->ID );
		$settings   = $field_type->migrate();

		$conditional_logic = new ConditionalLogic( $settings );
		$conditional_logic->migrate();

		$this->fields[ $settings['_id'] ] = $settings;
	}
}

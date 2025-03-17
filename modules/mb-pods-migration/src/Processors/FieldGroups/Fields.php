<?php
namespace MetaBox\Pods\Processors\FieldGroups;

use WP_Query;

class Fields {
	private $parent;
	private $group_id;
	private $fields = [];
	private $field;

	public function __construct( $parent, $group_id ) {
		$this->parent   = $parent;
		$this->group_id = $group_id;
	}


	public function migrate_fields() {
		$fields = $this->get_fields();
		foreach ( $fields as $field ) {
			$this->field = $field;
			$this->migrate_field( $this->group_id );
		}
		return $this->fields;
	}

	private function get_fields() {
		$query = new WP_Query( [
			'post_type'              => '_pods_field',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'post_parent'            => $this->parent,
			'order'                  => 'ASC',
			'orderby'                => 'menu_order',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );

		return $query->posts;
	}

	private function migrate_field( $group_id ) {
		if ( $group_id != get_post_meta( $this->field->ID, 'group', true ) ) {
			return;
		}
		$id           = $this->field->ID;
		$enable_logic = get_post_meta( $id, 'enable_conditional_logic', true );
		$settings     = [
			'name'              => $this->field->post_title,
			'id'                => $this->field->post_name,
			'type'              => get_post_meta( $id, 'type', true ),
			'desc'              => $this->field->post_content,
			'std'               => get_post_meta( $id, 'default_value', true ) ?: '',
			'placeholder'       => get_post_meta( $id, 'text_placeholder', true ),
			'readonly'          => get_post_meta( $id, 'read_only', true ) ? true : false,
			'required'          => get_post_meta( $id, 'required', true ) ? true : false,
			'clone'             => get_post_meta( $id, 'repeatable', true ) ? true : false,
			'add_button'        => get_post_meta( $id, 'repeatable_add_new_label', true ),
			'class'             => get_post_meta( $id, 'class', true ) ?: '',
			'sanitize_callback' => ( get_post_meta( $id, 'text_sanitize_html', true ) == 0 ) ? 'none' : '',
			'conditional_logic' => $enable_logic ? get_post_meta( $id, 'conditional_logic', true ) : [],
		];

		$type  = get_post_meta( $id, 'type', true );
		$check = get_post_meta( $id, 'pick_object', true ) ?: '';
		if ( $type == 'pick' && $check != 'custom-simple' ) {
			return;
		}

		$field_type = new FieldType( $settings, $id );
		$settings   = $field_type->migrate();

		if ( $settings['type'] == 'text' ) {
			$settings['text_limiter'] = [
				'limit'      => get_post_meta( $id, 'text_limit', true ) ?: '',
				'limit_type' => 'character',
			];
		}

		$conditional_logic = new ConditionalLogic( $settings );
		$conditional_logic->migrate();

		$this->fields[ $settings['_id'] ] = $settings;
	}
}

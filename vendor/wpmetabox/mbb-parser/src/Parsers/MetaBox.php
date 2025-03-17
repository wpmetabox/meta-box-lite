<?php
namespace MBBParser\Parsers;

use MetaBox\Support\Arr;

class MetaBox extends Base {
	protected $empty_keys = [ 'fields' ];
	private $settings_parser;
	private $validation = [
		'rules'    => [],
		'messages' => [],
	];

	public function parse() {
		$this->parse_settings();

		// Remove array keys.
		$this->fields = is_array( $this->fields ) ? $this->fields : [];
		$this->fields = array_values( $this->fields );

		$this->parse_boolean_values()
			->parse_numeric_values()
			->parse_fields( $this->settings['fields'] );

		$this->remove_empty_values();

		// Remove array keys again. Some methods like parse tabs change fields.
		$this->fields = array_values( $this->fields );

		$settings       = $this->settings_parser->get_settings();
		$this->settings = array_merge( $settings, [ 'fields' => $this->fields ] );
		if ( $this->validation['rules'] ) {
			if ( empty( $this->validation['messages'] ) ) {
				unset( $this->validation['messages'] );
			}
			$this->settings['validation'] = $this->validation;
		}

		$this->settings = apply_filters( 'mbb_meta_box_settings', $this->settings );
	}

	private function parse_settings() {
		$settings = [
			'title' => $this->post_title,
			'id'    => $this->post_name,
		];

		if ( isset( $this->settings['settings'] ) ) {
			$settings = array_merge( $settings, $this->settings['settings'] );
		}

		$this->settings_parser = new Settings( $settings );
		$this->settings_parser->parse();
	}

	private function parse_fields( &$fields ) {
		array_walk( $fields, [ $this, 'parse_field' ] );
		$fields = array_values( array_filter( $fields ) ); // Make sure to remove empty (such as empty groups) or "tab" fields.
	}

	private function parse_field( &$field ) {
		$parser = new Field( $field );
		$parser->parse();
		$field = $parser->get_settings();

		if ( $this->settings_parser->prefix && isset( $field['id'] ) ) {
			$field['id'] = $this->settings_parser->prefix . $field['id'];
		}

		$this->parse_field_validation( $field );

		if ( isset( $field['fields'] ) ) {
			$this->parse_fields( $field['fields'] );
		}
	}

	private function parse_field_validation( &$field ) {
		if ( empty( $field['validation'] ) ) {
			return;
		}

		$rules    = &$this->validation['rules'];
		$messages = &$this->validation['messages'];

		$key              = str_replace( $this->settings_parser->prefix, '', $field['id'] );
		$rules[ $key ]    = [];
		$messages[ $key ] = [];

		foreach ( $field['validation'] as $rule ) {
			$name  = $rule['name'];
			$value = $rule['value'];
			if ( in_array( $name, [ 'rangelength', 'range' ], true ) ) {
				$value = array_map( 'intval', Arr::from_csv( $value ) );
			}

			$rules[ $key ][ $name ] = $value;
			if ( ! empty( $rule['message'] ) ) {
				$messages[ $key ][ $name ] = $rule['message'];
			}
		}

		if ( empty( $rules[ $key ] ) ) {
			unset( $rules[ $key ] );
			unset( $messages[ $key ] );
		}
		if ( empty( $messages[ $key ] ) ) {
			unset( $messages[ $key ] );
		}

		unset( $field['validation'] );
	}
}

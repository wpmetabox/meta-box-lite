<?php
namespace MBBParser\Encoders;

use MBBParser\SettingsTrait;
use Riimu\Kit\PHPEncoder\PHPEncoder;

class MetaBox {
	use SettingsTrait;

	private $text_domain;
	private $prefix;
	private $function_name;
	private $encoded_string;

	public function __construct( $settings ) {
		$this->prefix        = $settings['prefix'] ?? '';
		$this->text_domain   = $settings['text_domain'] ?? 'your-text-domain';
		$this->function_name = $settings['function_name'] ?? 'your_prefix_register_meta_boxes';

		unset( $settings['text_domain'], $settings['prefix'], $settings['function_name'] );
		$this->settings = $settings;
	}

	public function get_encoded_string() {
		return $this->encoded_string;
	}

	public function encode() {
		$this->make_translatable( 'title' );
		$this->add_prefix_to_validation();

		if ( isset( $this->settings['fields'] ) && is_array( $this->settings['fields'] ) ) {
			$this->encode_fields( $this->settings['fields'] );
		}

		$encoder              = new PHPEncoder();
		$this->encoded_string = $encoder->encode( $this->settings, [
			'array.base'    => 4,
			'array.align'   => true,
			'string.escape' => false,
		] );

		$this->replace_placeholders()->wrap_function_call();
	}

	private function make_translatable( $name ) {
		if ( ! empty( $this->{$name} ) ) {
			$this->$name = sprintf( '{translate}%s{/translate}', $this->$name );
		}
	}

	private function add_prefix_to_validation() {
		if ( empty( $this->validation ) ) {
			return;
		}
		$validation = [
			'rules' => [],
		];
		if ( ! empty( $this->validation['messages'] ) ) {
			$validation['messages'] = [];
		}
		foreach ( $this->validation['rules'] as $key => $value ) {
			$new_key = substr( $key, strlen( $this->id_prefix ) );
			$new_key = '{prefix}' . $new_key;

			$validation['rules'][ $new_key ] = $value;

			if ( isset( $this->validation['messages'] ) && isset( $this->validation['messages'][ $key ] ) ) {
				$messages = $this->validation['messages'][ $key ];
				foreach ( $messages as $key => $value ) {
					$messages[ $key ] = sprintf( '{translate}%s{/translate}', $value );
				}
				$validation['messages'][ $new_key ] = $messages;
			}
		}
		$this->validation = $validation;
	}

	private function encode_fields( &$fields ) {
		array_walk( $fields, [ $this, 'encode_field' ] );
		$fields = array_values( array_filter( $fields ) ); // Make sure to remove empty (such as empty groups) or "tab" fields.
	}

	private function encode_field( &$field ) {
		$encoder = new Field( $field, $this->prefix );
		$encoder->encode();
		$field = $encoder->get_settings();

		if ( isset( $field['fields'] ) ) {
			$this->encode_fields( $field['fields'] );
		}
	}

	private function replace_placeholders() {
		// Translate.
		$this->encoded_string = preg_replace( "!'{translate}(.*){/translate}'!", "__( '$1', '" . $this->text_domain . "' )", $this->encoded_string );

		// Raw code.
		$this->encoded_string = preg_replace( "!'{raw}(.*){/raw}'!", '$1', $this->encoded_string );

		// Field ID prefix.
		$this->encoded_string = str_replace( '\'{prefix}', '$prefix . \'', $this->encoded_string );

		return $this;
	}

	private function wrap_function_call() {
		$this->encoded_string = sprintf(
			'<?php
add_filter( \'rwmb_meta_boxes\', \'%1$s\' );

function %1$s( $meta_boxes ) {
    $prefix = \'%3$s\';

    $meta_boxes[] = %2$s;

    return $meta_boxes;
}',
			$this->function_name,
			$this->encoded_string,
			$this->prefix
		);
		return $this;
	}
}

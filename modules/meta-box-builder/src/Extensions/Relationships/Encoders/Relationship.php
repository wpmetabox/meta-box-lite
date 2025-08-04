<?php
namespace MBB\Extensions\Relationships\Encoders;

use MBBParser\SettingsTrait;
use Riimu\Kit\PHPEncoder\PHPEncoder;

class Relationship {
	use SettingsTrait;

	private $text_domain;
	private $function_name;
	private $encoded_string;

	public function __construct( $settings ) {
		$this->text_domain   = $settings['text_domain'] ?? 'your-text-domain';
		$this->function_name = $settings['function_name'] ?? 'your_prefix_register_relationships';

		unset( $settings['text_domain'], $settings['function_name'] );
		$this->settings = $settings;
	}

	public function get_encoded_string() {
		return $this->encoded_string;
	}

	public function encode() {
		$this->make_translatable( 'empty_message' );

		if ( ! empty( $this->settings['meta_box'] ) ) {
			$this->encode_meta_box( $this->settings['meta_box'] );
		}
		if ( ! empty( $this->settings['field'] ) ) {
			$this->encode_field( $this->settings['field'] );
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

	private function encode_meta_box( &$meta_box ) {
		$encoder = new MetaBox( $meta_box );
		$encoder->encode();
		$meta_box = $encoder->get_settings();
	}

	private function encode_field( &$field ) {
		$encoder = new Field( $field );
		$encoder->encode();
		$field = $encoder->get_settings();
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
add_action( \'mb_relationships_init\', \'%1$s\' );

function %1$s() {
    MB_Relationships_API::register( %2$s );
}',
			$this->function_name,
			$this->encoded_string
		);
		return $this;
	}
}

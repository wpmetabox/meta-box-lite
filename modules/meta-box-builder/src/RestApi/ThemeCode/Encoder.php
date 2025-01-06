<?php
namespace MBB\RestApi\ThemeCode;

use Riimu\Kit\PHPEncoder\PHPEncoder;

class Encoder {
	private $text_domain;
	private $fields;
	private $encoded_string;
	public $settings;
	private $object_type;
	private $object_id;
	private $field_args;
	private $field_type;
	private $field_id;
	private $size_indent = 0;
	private $views_dir;

	public function __construct( $settings ) {
		$this->text_domain = $settings['text_domain'] ?? 'meta-box-builder';
		$this->fields      = $settings['fields'] ?? [];
		$this->object_type = $settings['object_type'] ?? '';
		$this->object_id   = $settings['object_id'] ?? '';
		$this->field_args  = $settings['args'] ?? [];
		$this->views_dir   = MBB_DIR . 'views/theme-code';

		unset( $settings['text_domain'], $settings['fields'] );
		$this->settings = $settings;
	}

	public function get_encoded_string() {
		return $this->fields;
	}

	public function encode() {
		foreach ( $this->fields as $key => $field ) {
			$field['id']    = ! empty( $this->settings['prefix'] ) ? $this->settings['prefix'] . $field['id'] : $field['id'];
			$encoded_string = $this->get_theme_code( $field );
			// Set theme code for view
			$this->fields[ $key ]['theme_code'] = $encoded_string;
		}
	}

	private function get_theme_code( array $field, bool $in_group = false ): string {
		if ( empty( $field['type'] ) ) {
			return '';
		}

		$view_file = $this->get_view_file( $field['type'] );

		ob_start();
		include $view_file;
		return ob_get_clean();
	}

	private function get_view_file( string $field_type ): string {
		return file_exists( "{$this->views_dir}/{$field_type}.php" ) ? "{$this->views_dir}/{$field_type}.php" : "{$this->views_dir}/default.php";
	}

	private function get_encoded_args( array $args = [] ): string {
		$return = [];
		$args   = array_merge( $this->field_args, $args );
		foreach ( $args as $key => $value ) {
			// value is numeric
			if ( is_numeric( $value ) ) {
				$return[] = "'$key' => $value";
				continue;
			}
			// value is boolean
			if ( is_bool( $value ) ) {
				$return[] = $value === true ? "'$key' => true" : "'$key' => false";
				continue;
			}
			// value is string
			$return[] = "'$key' => '$value'";
		}
		return empty( $return ) ? '' : ', [ ' . implode( ', ', $return ) . ' ]';
	}

	private function get_encoded_object_id(): string {
		return $this->object_id ? ", {$this->object_id}" : '';
	}

	private function get_encoded_value( string $field_id, $args = [] ): string {
		$arg_encode = is_array( $args ) ? $this->get_encoded_args( $args ) : ", $args";
		return $field_id . "'" . $arg_encode . $this->get_encoded_object_id();
	}

	private function indent( int $size = 1 ): string {
		return str_repeat( "\t", $size + $this->size_indent );
	}

	private function break( int $size = 1 ): string {
		return str_repeat( "\n", $size );
	}

	private function out( string $str, int $indent = 0, int $empty_lines = 1 ) {
		$output = $this->indent( $indent ) . $str . $this->break( $empty_lines );
		echo esc_html( $output );
	}

	private function format_variable( array $vars = [] ): string {
		if ( empty( $vars ) ) {
			return '[]';
		}

		$encoder = new PHPEncoder();
		return $encoder->encode( $vars, [
			'array.base'    => 0,
			'array.align'   => true,
			'string.escape' => false,
		] );
	}

	private function format_args( array $args = [] ): string {
		$args = array_merge( $this->field_args, $args );
		return $this->format_variable( $args );
	}
}

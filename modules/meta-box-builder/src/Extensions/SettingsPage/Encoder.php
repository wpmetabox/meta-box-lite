<?php
namespace MBB\Extensions\SettingsPage;

use MBBParser\SettingsTrait;
use Riimu\Kit\PHPEncoder\PHPEncoder;

class Encoder {
	use SettingsTrait;

	private $text_domain;
	private $function_name;
	private $encoded_string;

	public function __construct( $settings ) {
		$this->text_domain   = $settings['text_domain'] ?? 'your-text-domain';
		$this->function_name = $settings['function_name'] ?? 'your_prefix_register_settings_page';

		unset( $settings['text_domain'], $settings['function_name'] );
		$this->settings = $settings;
	}

	public function get_encoded_string() {
		return $this->encoded_string;
	}

	public function encode() {
		$this->make_translatable( 'menu_title' );
		$this->make_translatable( 'submit_button' );
		$this->make_translatable( 'message' );

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
add_filter( \'mb_settings_pages\', \'%1$s\' );

function %1$s( $settings_pages ) {
	$settings_pages[] = %2$s;

	return $settings_pages;
}',
			$this->function_name,
			$this->encoded_string
		);
		return $this;
	}
}

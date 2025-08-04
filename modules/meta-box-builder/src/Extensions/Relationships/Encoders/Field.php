<?php
namespace MBB\Extensions\Relationships\Encoders;

use MBBParser\SettingsTrait;

class Field {
	use SettingsTrait;

	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	public function encode() {
		$translatable_fields = [ 'name', 'desc', 'label_description', 'add_button', 'placeholder', 'before', 'after' ];
		array_walk( $translatable_fields, [ $this, 'make_translatable' ] );
	}

	private function make_translatable( $name ) {
		if ( ! empty( $this->$name ) && is_string( $this->$name ) ) {
			$this->$name = sprintf( '{translate}%s{/translate}', $this->$name );
		}
	}
}

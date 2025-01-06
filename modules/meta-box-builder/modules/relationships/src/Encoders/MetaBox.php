<?php
namespace MBB\Relationships\Encoders;

use MBBParser\SettingsTrait;

class MetaBox {
	use SettingsTrait;

	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	public function encode() {
		$translatable_fields = [ 'title' ];
		array_walk( $translatable_fields, [ $this, 'make_translatable' ] );
	}

	private function make_translatable( $name ) {
		if ( ! empty( $this->$name ) && is_string( $this->$name ) ) {
			$this->$name = sprintf( '{translate}%s{/translate}', $this->$name );
		}
	}
}

<?php
namespace MBBParser;

trait SettingsTrait {
	protected $settings;

	public function get_settings(): array {
		return $this->settings;
	}

	/**
	 * Use overloading magic methods for short syntax.
	 */

	public function __get( $key ) {
		return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : null;
	}

	public function __set( $key, $value ): void {
		$this->settings[ $key ] = $value;
	}

	public function __isset( $key ): bool {
		return isset( $this->settings[ $key ] );
	}

	public function __unset( $key ): void {
		unset( $this->settings[ $key ] );
	}
}

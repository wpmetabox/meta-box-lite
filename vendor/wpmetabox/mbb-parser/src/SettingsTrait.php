<?php
namespace MBBParser;

/**
 * Use overloading magic methods for short syntax.
 */
trait SettingsTrait {
	protected $settings;

	public function get_settings(): array {
		return $this->settings;
	}

	public function __get( string $key ) {
		return $this->settings[ $key ] ?? null;
	}

	public function __set( string $key, $value ): void {
		$this->settings[ $key ] = $value;
	}

	public function __isset( string $key ): bool {
		return isset( $this->settings[ $key ] );
	}

	public function __unset( string $key ): void {
		unset( $this->settings[ $key ] );
	}
}

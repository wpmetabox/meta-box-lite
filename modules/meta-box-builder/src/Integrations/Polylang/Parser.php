<?php
namespace MBB\Integrations\Polylang;

class Parser {
	const MODES = [ 'translate', 'copy' ];
	private $fields_translations = [];

	public function __construct() {
		if ( function_exists( 'pll_register_string' ) ) {
			add_filter( 'mbb_save_settings', [ $this, 'save_fields_translations_to_settings' ] );
			add_filter( 'mbb_meta_box_settings', [ $this, 'parse_translation_settings' ] );
		}

		add_filter( 'mbb_app_data', [ $this, 'filter_data_to_app' ] );
	}

	public function save_fields_translations_to_settings( array $settings ): array {
		// Don't parse settings per field if the translation mode for the field group is not 'advanced'.
		if ( empty( $settings['translation'] ) || $settings['translation'] !== 'advanced' ) {
			unset( $settings['fields_translations'] );
			return $settings;
		}

		if ( empty( $settings['fields_translations'] ) ) {
			unset( $settings['translation'] );
			unset( $settings['fields_translations'] );
			return $settings;
		}

		// Backward compatibility: previously the data was submitted as a JSON string, parse it.
		if ( isset( $settings['fields_translations'] ) && is_string( $settings['fields_translations'] ) ) {
			$settings['fields_translations'] = $this->parse_json( wp_unslash( $settings['fields_translations'] ) );
		}

		return $settings;
	}

	/**
	 * Parse translation settings for field groups and fields
	 *
	 * @param array $settings Meta box settings
	 * @return array Modified meta box settings
	 */
	public function parse_translation_settings( array $settings ): array {
		// Don't parse settings per field if the translation mode for the field group is not 'advanced'.
		if ( empty( $settings['translation'] ) || $settings['translation'] !== 'advanced' ) {
			return $settings;
		}

		// Store fields' translations for later parsing
		if ( isset( $settings['fields_translations'] ) && is_array( $settings['fields_translations'] ) ) {
			$this->fields_translations = $settings['fields_translations'];
			unset( $settings['fields_translations'] );
		}

		// Process fields to add translation settings
		if ( isset( $settings['fields'] ) && is_array( $settings['fields'] ) ) {
			$this->parse_fields( $settings['fields'] );
		}

		return $settings;
	}

	private function parse_fields( array &$fields ): void {
		foreach ( $fields as &$field ) {
			$this->parse_field( $field );
		}
	}

	/**
	 * Add translation setting for field if it exists in fields_translations
	 */
	private function parse_field( array &$field ): void {
		if ( empty( $field['id'] ) || empty( $this->fields_translations[ $field['id'] ] ) ) {
			return;
		}

		$mode = $this->fields_translations[ $field['id'] ];
		if ( in_array( $mode, self::MODES, true ) ) {
			$field['translation'] = $mode;
		}
	}

	public function filter_data_to_app( array $data ): array {
		if ( empty( $data['settings'] ) || ! is_array( $data['settings'] ) ) {
			return $data;
		}

		$settings = &$data['settings'];

		// Fix fields_translations data still stored in settings when Polylang is not active and it grows after saving field groups.
		if ( ! function_exists( 'pll_register_string' ) ) {
			unset( $settings['fields_translations'] );
			return $data;
		}

		$settings = $this->save_fields_translations_to_settings( $settings );

		return $data;
	}

	private function parse_json( string $json ): array {
		$array = json_decode( $json, true );
		return json_last_error() === JSON_ERROR_NONE && is_array( $array ) ? $array : [];
	}
}
<?php
namespace MBB\Integrations\Polylang;

class Parser {
	const MODES = [ 'translate', 'copy' ];
	private $fields_translations = [];

	public function __construct() {
		add_filter( 'mbb_meta_box_settings', [ $this, 'parse_translation_settings' ] );
		add_filter( 'mbb_app_data', [ $this, 'filter_data_to_app' ] );
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
		if ( ! empty( $settings['fields_translations'] ) && is_string( $settings['fields_translations'] ) ) {
			$this->fields_translations = json_decode( wp_unslash( $settings['fields_translations'] ), true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				$this->fields_translations = [];
			}
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

	/**
	 * Send fields_translations as an array to the JS app.
	 * It's stored as a JSON string in the database, we need to decode it to an array.
	 */
	public function filter_data_to_app( array $data ): array {
		if ( empty( $data['settings']['fields_translations'] ) ) {
			return $data;
		}

		$data['settings']['fields_translations'] = json_decode( $data['settings']['fields_translations'] ) ?: [];

		return $data;
	}
}
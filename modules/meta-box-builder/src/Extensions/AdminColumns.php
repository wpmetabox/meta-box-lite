<?php
namespace MBB\Extensions;

use MBB\Control;
use MetaBox\Support\Arr;

class AdminColumns {
	public function __construct() {
		add_filter( 'mbb_field_controls', [ $this, 'add_field_controls' ], 10, 2 );
		add_filter( 'mbb_field_settings', [ $this, 'parse_field_settings' ] );
	}

	public function add_field_controls( array $controls, string $type ): array {
		if ( in_array( $type, [ 'button', 'custom_html', 'divider', 'heading', 'hidden', 'tab' ] ) ) {
			return $controls;
		}

		$controls[] = Control::AdminColumns( 'admin_columns', '', [
			'enable'     => false,
			'position'   => [
				'type'   => 'after',
				'column' => '',
			],
			'title'      => '',
			'before'     => '',
			'after'      => '',
			'searchable' => false,
			'filterable' => false,
			'sort'       => 'false',
			'link'       => 'false',
			'width'      => '',
		], 'admin_columns' );

		return $controls;
	}

	public function parse_field_settings( $settings ) {
		$enable = Arr::get( $settings, 'admin_columns.enable', false );
		if ( ! $enable ) {
			unset( $settings['admin_columns'] );
			return $settings;
		}

		$admin_columns = &$settings['admin_columns'];
		unset( $admin_columns['enable'] );
		$admin_columns['position'] = $this->normalize_position( $admin_columns['position'] ?? '' );
		$admin_columns = array_filter( $admin_columns );
		if ( empty( $admin_columns ) ) {
			$admin_columns = true;
		}
		if ( is_array( $admin_columns ) && 1 === count( $admin_columns ) && isset( $admin_columns['position'] ) ) {
			$admin_columns = $admin_columns['position'];
		}

		return $settings;
	}

	/**
	 * Normalize the position value.
	 *
	 * Make static to be used in Relationship parser.
	 *
	 * @param string|array $position Can be array of type and column, or string of type and column separated by space. Both can be empty.
	 *
	 * @return string
	 */
	public static function normalize_position( $position ): string {
		$types = [ 'after', 'before', 'replace' ];
		if ( is_array( $position ) ) {
			$type   = isset( $position['type'] ) && in_array( $position['type'], $types ) ? $position['type'] : 'after';
			$column = $position['column'] ?? '';
			return trim( "{$type} {$column}" );
		}

		if ( ! is_string( $position ) ) {
			return '';
		}

		$parts = array_filter( explode( ' ', $position . ' ' ) );
		if ( empty( $parts ) || count( $parts ) > 2 ) {
			return '';
		}
		if ( count( $parts ) === 1 ) {
			// Only type.
			if ( in_array( $parts[0], $types ) ) {
				return '';
			}

			// Only column.
			return "after {$parts[0]}";
		}
		$type   = in_array( $parts[0], $types ) ? $parts[0] : 'after';
		$column = $parts[1];
		return trim( "{$type} {$column}" );
	}
}

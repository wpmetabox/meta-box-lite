<?php

namespace MBB\Extensions;

use MetaBox\Support\Arr;
use MBB\LocalJson;

class CustomTable {
	public function __construct() {
		add_action( 'mbb_after_save', [ $this, 'create_custom_table_after_save' ], 10, 3 );

		if ( LocalJson::is_enabled() ) {
			add_action( 'mbb_before_register_meta_box', [ $this, 'create_custom_table' ] );
		}
	}

	public function create_custom_table_after_save( $parser, $post_id, $submitted_data ): void {
		$this->create_custom_table( $submitted_data );
	}

	/**
	 * Create custom table
	 *
	 * @param array $data Must be either full data for a field group, or full unparsed data for a local JSON file.
	 *                    This data must contains: `settings.custom_table` settings (enable, create, name, prefix) and `fields` array.
	 * @return void
	 */
	public function create_custom_table( array &$data ): void {
		$settings = $data['settings'] ?? [];
		if ( ! Arr::get( $settings, 'custom_table.enable' ) || ! Arr::get( $settings, 'custom_table.create' ) ) {
			return;
		}

		$table = Arr::get( $settings, 'custom_table.name' );
		if ( Arr::get( $settings, 'custom_table.prefix' ) ) {
			global $wpdb;
			$table = $wpdb->prefix . $table;

			// Modify the table name in the parsed `meta_box` settings.
			Arr::set( $data, 'meta_box.table', $table );
		}

		$columns   = [];
		$id_prefix = Arr::get( $settings, 'prefix' );
		$fields    = array_filter( $data['fields'], [ $this, 'has_value' ] );
		foreach ( $fields as $field ) {
			$columns[ $id_prefix . $field['id'] ] = 'TEXT';
		}

		$cache_data = [
			'table'   => $table,
			'columns' => $columns,
		];
		$cache_key  = 'mb_create_table_' . md5( wp_json_encode( $cache_data ) );
		// Cache the table creation in production environment only.
		if ( get_transient( $cache_key ) !== false && wp_get_environment_type() === 'production' ) {
			return;
		}

		\MB_Custom_Table_API::create( $table, $columns );
		set_transient( $cache_key, 1, MONTH_IN_SECONDS );
	}

	private function has_value( $field ): bool {
		return ! empty( $field['id'] ) && ! in_array( $field['type'], [ 'heading', 'divider', 'button', 'custom_html', 'tab' ], true );
	}
}
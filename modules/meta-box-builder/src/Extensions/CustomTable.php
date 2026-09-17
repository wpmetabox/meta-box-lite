<?php

namespace MBB\Extensions;

use MetaBox\Support\Arr;
use MBB\Extensions\CustomModel\Register;
use MBB\Extensions\CustomModel\Save as CustomModelSave;
use MBB\Helpers\TableSchema;
use MBB\LocalJson;

class CustomTable {
	/**
	 * Last DDL error from create/sync during field group save (empty if none).
	 *
	 * @var string
	 */
	private static string $last_ddl_error = '';

	public function __construct() {
		add_action( 'mbb_after_save', [ $this, 'create_custom_table_after_save' ], 10, 3 );
		add_action( 'mbb_sync_json', [ $this, 'create_custom_table_after_sync' ], 10, 2 );

		if ( LocalJson::is_enabled() ) {
			add_action( 'mbb_before_register_meta_box', [ $this, 'create_custom_table' ] );
		}
	}

	/**
	 * DDL error from the latest field-group save hook, if any.
	 */
	public static function get_last_ddl_error(): string {
		return self::$last_ddl_error;
	}

	public function create_custom_table_after_save( $parser, $post_id, $submitted_data ): void {
		$this->create_custom_table( $submitted_data, (int) $post_id, true );
	}

	/**
	 * Create the custom table for a field group synced from a JSON file.
	 *
	 * @param array $data    Unparsed field group data.
	 * @param int   $post_id Field group post ID.
	 */
	public function create_custom_table_after_sync( array $data, int $post_id ): void {
		if ( 'mb-model' === ( $data['post_type'] ?? '' ) ) {
			return;
		}

		$this->create_custom_table( $data, $post_id, true );
	}

	/**
	 * Create custom table
	 *
	 * @param array $data Must be either full data for a field group, or full unparsed data for a local JSON file.
	 *                    This data must contains: `settings.custom_table` settings (enable, create, name, prefix) and `fields` array.
	 * @param int   $post_id Field group post ID.
	 * @param bool  $force   Ignore the cached DDL result. Saving and importing always run it.
	 * @return void
	 */
	public function create_custom_table( array &$data, int $post_id = 0, bool $force = false ): void {
		self::$last_ddl_error = '';

		$settings = $data['settings'] ?? [];
		$is_model = ! empty( $settings['models'] ) || 'model' === ( $settings['object_type'] ?? '' );

		if ( $is_model ) {
			$this->create_for_model( $data, $settings, $post_id, $force );
			return;
		}

		if ( ! Arr::get( $settings, 'custom_table.enable' ) || ! Arr::get( $settings, 'custom_table.create' ) ) {
			return;
		}

		$table = TableSchema::resolve_custom_table( (array) Arr::get( $settings, 'custom_table', [] ) );
		if ( '' === $table ) {
			return;
		}

		Arr::set( $data, 'meta_box.table', $table );

		$items = $this->create_table(
			$table,
			(array) Arr::get( $settings, 'custom_table.columns', [] ),
			$this->field_column_names( $data['fields'] ?? [], (string) Arr::get( $settings, 'prefix', '' ) ),
			$force
		);

		if ( '' !== self::$last_ddl_error ) {
			return;
		}

		$this->persist_field_group_columns( $data, $post_id, $items );
	}

	/**
	 * Create/update a model table from this field group.
	 *
	 * Builder models: merge missing field IDs into the model schema.
	 * Code models: only when the field group opted in via custom_table.enable/create.
	 */
	private function create_for_model( array &$data, array $settings, int $post_id, bool $force ): void {
		$models = array_filter( (array) ( $settings['models'] ?? [] ) );
		$first  = reset( $models );
		if ( ! $first ) {
			return;
		}

		$model_id = Register::get_model_post_id( (string) $first );
		if ( $model_id ) {
			$this->sync_builder_model( $model_id, $data['fields'] ?? [] );
			return;
		}

		$custom_table = (array) ( $settings['custom_table'] ?? [] );
		if ( empty( $custom_table['enable'] ) || empty( $custom_table['create'] ) ) {
			return;
		}

		$table = TableSchema::resolve_model_table( $settings );
		if ( '' === $table ) {
			return;
		}

		Arr::set( $data, 'meta_box.table', $table );

		$items = $this->create_table(
			$table,
			(array) ( $custom_table['columns'] ?? [] ),
			$this->field_column_names( $data['fields'] ?? [] ),
			$force
		);

		if ( '' !== self::$last_ddl_error ) {
			return;
		}

		$this->persist_field_group_columns( $data, $post_id, $items );
	}

	/**
	 * Merge missing field IDs as TEXT, then create or update the table.
	 *
	 * @param string   $table        Table name.
	 * @param array    $column_items Editor column items.
	 * @param string[] $field_names  Field column names.
	 * @param bool     $force        Ignore the cached DDL result.
	 * @return array Updated editor column items.
	 */
	private function create_table( string $table, array $column_items, array $field_names, bool $force ): array {
		$parsed  = TableSchema::parse_columns( $column_items );
		$columns = TableSchema::merge_field_columns( $parsed['columns'], $field_names );

		$result = TableSchema::create_cached( $table, $columns, $parsed['keys'], $force );
		if ( true !== $result ) {
			self::$last_ddl_error = is_string( $result ) && '' !== $result
				? $result
				: __( 'Could not create or update the database table.', 'meta-box-builder' );
		}

		return $this->append_field_columns( $column_items, $field_names );
	}

	/**
	 * Append TEXT editor items for field names missing from the schema.
	 *
	 * @param array    $items Editor column items.
	 * @param string[] $names Field column names.
	 * @return array
	 */
	private function append_field_columns( array $items, array $names ): array {
		$parsed = TableSchema::parse_columns( $items );

		foreach ( $names as $name ) {
			$name = TableSchema::sanitize_name( (string) $name );
			if ( ! $name || 'id' === $name || isset( $parsed['columns'][ $name ] ) ) {
				continue;
			}

			$id                         = 'col_' . $name;
			$items[ $id ]               = [
				'id'          => $id,
				'name'        => $name,
				'type'        => 'TEXT',
				'custom_type' => '',
				'index'       => false,
			];
			$parsed['columns'][ $name ] = 'TEXT';
		}

		return $items;
	}

	/**
	 * Persistable field IDs as table column names.
	 *
	 * @param array  $fields    Field list.
	 * @param string $id_prefix Field ID prefix (custom tables only).
	 * @return string[]
	 */
	private function field_column_names( array $fields, string $id_prefix = '' ): array {
		$names = [];
		foreach ( $fields as $field ) {
			if ( ! $this->has_value( $field ) ) {
				continue;
			}

			$name = TableSchema::sanitize_name( $id_prefix . $field['id'] );
			if ( $name ) {
				$names[] = $name;
			}
		}

		return $names;
	}

	/**
	 * Persist merged editor columns back onto the field group settings.
	 */
	private function persist_field_group_columns( array &$data, int $post_id, array $items ): void {
		Arr::set( $data, 'settings.custom_table.columns', $items );

		if ( $post_id <= 0 ) {
			return;
		}

		$saved = get_post_meta( $post_id, 'settings', true );
		if ( ! is_array( $saved ) ) {
			return;
		}

		Arr::set( $saved, 'custom_table.columns', $items );
		update_post_meta( $post_id, 'settings', $saved );
	}

	/**
	 * Merge field columns into a Builder model and persist it.
	 */
	private function sync_builder_model( int $model_id, array $fields ): void {
		$settings = get_post_meta( $model_id, 'settings', true );
		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		$model = get_post_meta( $model_id, 'model', true );
		if ( ! is_array( $model ) ) {
			$model = [];
		}

		$items = (array) ( $settings['columns'] ?? [] );
		if ( [] === $items && ! empty( $model['columns'] ) && is_array( $model['columns'] ) ) {
			$items = $this->editor_columns_from_sql_map(
				$model['columns'],
				isset( $model['keys'] ) && is_array( $model['keys'] ) ? $model['keys'] : []
			);
		}

		$next = $this->append_field_columns( $items, $this->field_column_names( $fields ) );
		if ( wp_json_encode( $items ) === wp_json_encode( $next ) ) {
			return;
		}

		$settings['columns'] = $next;

		$post      = get_post( $model_id );
		$post_name = $post ? $post->post_name : (string) ( $model['name'] ?? '' );
		$result    = CustomModelSave::persist_model( $model_id, $post_name, $settings );
		if ( empty( $result['success'] ) ) {
			self::$last_ddl_error = (string) ( $result['message'] ?? __( 'Could not create or update the database table.', 'meta-box-builder' ) );
		}
	}

	/**
	 * Convert a parsed SQL column map into editor column items.
	 *
	 * Keeps preset types, everything else stays custom with its raw SQL.
	 *
	 * @param array<string, string> $columns Column name => SQL type.
	 * @param string[]              $keys    Index column names.
	 * @return array
	 */
	private function editor_columns_from_sql_map( array $columns, array $keys ): array {
		$key_set = array_fill_keys( $keys, true );
		$presets = [
			'TINYINT',
			'SMALLINT',
			'MEDIUMINT',
			'INT',
			'BIGINT',
			'DECIMAL(10,2)',
			'FLOAT',
			'DOUBLE',
			'TINYINT(1)',
			'CHAR(1)',
			'VARCHAR(255)',
			'TINYTEXT',
			'TEXT',
			'MEDIUMTEXT',
			'LONGTEXT',
			'DATE',
			'TIME',
			'DATETIME',
		];
		$items   = [];

		foreach ( $columns as $name => $sql ) {
			$name = TableSchema::sanitize_name( (string) $name );
			if ( ! $name || 'id' === $name ) {
				continue;
			}

			$upper = strtoupper( trim( (string) $sql ) );
			$type  = in_array( $upper, $presets, true ) ? $upper : 'custom';

			$id           = 'col_' . $name;
			$items[ $id ] = [
				'id'          => $id,
				'name'        => $name,
				'type'        => $type,
				'custom_type' => 'custom' === $type ? (string) $sql : '',
				'index'       => ! empty( $key_set[ $name ] ),
			];
		}

		return $items;
	}

	private function has_value( $field ): bool {
		return ! empty( $field['id'] ) && ! in_array( $field['type'], [ 'heading', 'divider', 'button', 'custom_html', 'tab' ], true );
	}
}

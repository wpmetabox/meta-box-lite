<?php
namespace MBB\Helpers;

use MetaBox\CustomTable\API;

class TableSchema {
	/**
	 * Sanitize a table or column name and optionally prepend $wpdb->prefix.
	 */
	public static function apply_prefix( string $name, bool $use_prefix = false ): string {
		$name = self::sanitize_name( $name );
		if ( ! $name || ! $use_prefix ) {
			return $name;
		}

		global $wpdb;
		return $wpdb->prefix . $name;
	}

	/**
	 * Resolve a model table name from field-group settings.
	 *
	 * Prefers the live Factory model, then falls back to stored custom_table.name.
	 *
	 * @param array<string, mixed> $settings Field group settings.
	 */
	public static function resolve_model_table( array $settings ): string {
		$models = array_filter( (array) ( $settings['models'] ?? [] ) );
		$first  = reset( $models );

		if ( $first && class_exists( \MetaBox\CustomTable\Model\Factory::class ) ) {
			$model = \MetaBox\CustomTable\Model\Factory::get( $first );
			if ( $model && ! empty( $model->table ) ) {
				return self::sanitize_name( (string) $model->table );
			}
		}

		return self::resolve_custom_table( (array) ( $settings['custom_table'] ?? [] ) );
	}

	/**
	 * Resolve a field group custom table name (not model location).
	 */
	public static function resolve_custom_table( array $custom_table ): string {
		return self::apply_prefix(
			(string) ( $custom_table['name'] ?? '' ),
			! empty( $custom_table['prefix'] )
		);
	}

	/**
	 * Parse editor column items into SQL column definitions and index keys.
	 *
	 * @param array $column_items List or map of { name, type, custom_type, index }.
	 * @return array{columns: array<string, string>, keys: string[]}
	 */
	public static function parse_columns( array $column_items ): array {
		$columns = [];
		$keys    = [];

		foreach ( $column_items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$name = self::sanitize_name( (string) ( $item['name'] ?? '' ) );
			if ( ! $name || 'id' === $name ) {
				continue;
			}

			$type = (string) ( $item['type'] ?? 'TEXT' );
			if ( 'custom' === $type ) {
				$type = trim( (string) ( $item['custom_type'] ?? '' ) );
			}
			$type = self::sanitize_column_type( $type ) ?: 'TEXT';

			$columns[ $name ] = $type;

			if ( ! empty( $item['index'] ) && self::is_indexable( $type ) ) {
				$keys[] = $name;
			}
		}

		return [
			'columns' => $columns,
			'keys'    => array_values( array_unique( $keys ) ),
		];
	}

	/**
	 * Create or update a table via API::create and verify the DDL succeeded.
	 *
	 * DbDelta may run several ALTERs; $wpdb->last_error only reflects the last query,
	 * and table_exists() stays true when the table already exists. Verify column names.
	 *
	 * @param string                $table   Table name.
	 * @param array<string, string> $columns Column name => SQL type.
	 * @param string[]              $keys    Indexed column names.
	 * @return true|string True on success, error message on failure.
	 */
	public static function create( string $table, array $columns, array $keys = [] ) {
		global $wpdb;

		$wpdb->last_error = '';
		API::create( $table, $columns, $keys );

		if ( $wpdb->last_error ) {
			return $wpdb->last_error;
		}

		if ( ! self::table_exists( $table ) ) {
			return __( 'Could not create the database table.', 'meta-box-builder' );
		}

		$expected = array_merge( [ 'ID' ], array_keys( $columns ) );
		$missing  = self::missing_columns( $table, $expected );
		if ( $missing ) {
			return sprintf(
				/* translators: %s: comma-separated column names */
				__( 'Could not update the database table. Missing columns: %s', 'meta-box-builder' ),
				implode( ', ', $missing )
			);
		}

		return true;
	}

	/**
	 * Create or update a table, skipping the DDL when the same schema succeeded before.
	 *
	 * Registering field groups and models runs on every request, so cache the result
	 * for a month. Saving and importing must always run the DDL, otherwise a dropped
	 * table would never come back.
	 *
	 * @param string                $table   Table name.
	 * @param array<string, string> $columns Column name => SQL type.
	 * @param string[]              $keys    Indexed column names.
	 * @param bool                  $force   Ignore the cache and always run the DDL.
	 * @return true|string True on success, error message on failure.
	 */
	public static function create_cached( string $table, array $columns, array $keys = [], bool $force = false ) {
		$cache_key = 'mb_create_table_' . md5( wp_json_encode( compact( 'table', 'columns', 'keys' ) ) );

		if ( ! $force && get_transient( $cache_key ) !== false && wp_get_environment_type() === 'production' ) {
			return true;
		}

		$result = self::create( $table, $columns, $keys );
		if ( true === $result ) {
			set_transient( $cache_key, 1, MONTH_IN_SECONDS );
		}

		return $result;
	}

	public static function table_exists( string $table ): bool {
		global $wpdb;

		$like = $wpdb->esc_like( $table );

		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) ) === $table;
	}

	/**
	 * Column names from $expected that are missing from the table (name check only).
	 *
	 * @param string   $table    Table name.
	 * @param string[] $expected Expected column names.
	 * @return string[]
	 */
	private static function missing_columns( string $table, array $expected ): array {
		global $wpdb;

		$rows = $wpdb->get_results( $wpdb->prepare( 'SHOW COLUMNS FROM %i', $table ), ARRAY_A );
		if ( ! is_array( $rows ) ) {
			return array_values( array_unique( array_filter( $expected ) ) );
		}

		$present = [];
		foreach ( $rows as $row ) {
			$name = (string) ( $row['Field'] ?? '' );
			if ( $name ) {
				$present[ strtolower( $name ) ] = true;
			}
		}

		$missing = [];
		foreach ( $expected as $name ) {
			$name = self::sanitize_name( (string) $name );
			if ( $name && empty( $present[ strtolower( $name ) ] ) ) {
				$missing[] = $name;
			}
		}

		return array_values( array_unique( $missing ) );
	}

	/**
	 * Transliterate first: sanitize_key() drops accented characters, turning "đơn hàng" into
	 * "nhng". Match sanitizeSqlName() in JS, which slugifies before sanitizing.
	 */
	public static function sanitize_name( string $name ): string {
		$name = str_replace( [ ' ', '-' ], '_', remove_accents( $name ) );

		return sanitize_key( $name );
	}

	public static function sanitize_column_type( string $type ): string {
		$type = preg_replace( '/[^a-zA-Z0-9_(),\s\']/', '', $type );
		return is_string( $type ) ? trim( $type ) : '';
	}

	public static function is_indexable( string $type ): bool {
		$type = strtoupper( $type );

		return ! str_contains( $type, 'TEXT' )
			&& ! str_contains( $type, 'BLOB' )
			&& ! str_contains( $type, 'JSON' );
	}

	/**
	 * Add TEXT columns for field names missing from the schema.
	 *
	 * @param array<string, string> $columns Column name => SQL type.
	 * @param string[]              $names   Field column names.
	 * @return array<string, string>
	 */
	public static function merge_field_columns( array $columns, array $names ): array {
		foreach ( $names as $name ) {
			$name = self::sanitize_name( (string) $name );
			if ( ! $name || 'id' === $name || isset( $columns[ $name ] ) ) {
				continue;
			}
			$columns[ $name ] = 'TEXT';
		}

		return $columns;
	}
}

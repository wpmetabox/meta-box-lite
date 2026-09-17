<?php
namespace MBB\Extensions\CustomModel;

use MBB\Helpers\TableSchema;
use MetaBox\CustomTable\Model\Factory;
use MetaBox\CustomTable\Model\Model;

class TableColumns {
	/**
	 * Model features that add protected table columns (see mb-custom-table TableSchema).
	 */
	private const SUPPORT_FEATURES = [ 'author', 'published_date', 'modified_date' ];

	/**
	 * Create or update a database table from editor column items.
	 *
	 * @param string $table        Table name.
	 * @param array  $column_items Editor column items.
	 * @param string $model_name   Optional model name (for supports columns via TableSchema).
	 * @return array{success: bool, message?: string, columns?: array<string, string>, keys?: string[]}
	 */
	public static function create( string $table, array $column_items, string $model_name = '' ): array {
		$table = TableSchema::sanitize_name( $table );
		if ( ! $table ) {
			return self::table_error();
		}

		$supports = self::supports_for( $model_name );

		// Register first so mbct_table_schema can add AUTO_INCREMENT and support columns.
		if ( $model_name && ! Factory::get( $model_name ) ) {
			$args = [ 'table' => $table ];
			if ( $supports ) {
				$args['supports'] = $supports;
			}
			mb_register_model( $model_name, $args );
		}

		$parsed = TableSchema::parse_columns( $column_items );
		$result = TableSchema::create( $table, $parsed['columns'], $parsed['keys'] );
		if ( true !== $result ) {
			return self::fail( $result );
		}

		$inspected = self::inspect( $table, $supports );

		return [
			'success' => true,
			'message' => __( 'Table schema updated.', 'meta-box-builder' ),
			'columns' => $inspected['columns'],
			'keys'    => $inspected['keys'],
		];
	}

	/**
	 * List columns for a registered model.
	 *
	 * @return array{success: bool, message?: string, columns?: array<string, string>, keys?: string[]}
	 */
	public static function list_for_model( string $model_name, string $table = '' ): array {
		if ( $model_name ) {
			$model = Factory::get( $model_name );
			if ( $model && ! $table && ! empty( $model->table ) ) {
				$table = (string) $model->table;
			}
		}

		$table = TableSchema::sanitize_name( $table );
		if ( ! $table ) {
			return self::table_error();
		}

		$inspected = self::inspect( $table, self::supports_for( $model_name ) );

		return [
			'success' => true,
			'columns' => $inspected['columns'],
			'keys'    => $inspected['keys'],
		];
	}

	/**
	 * Read columns and indexes from a database table.
	 *
	 * @param string   $table    Table name.
	 * @param string[] $supports Model supports used to skip protected columns.
	 * @return array{columns: array<string, string>, keys: string[]}
	 */
	public static function inspect( string $table, array $supports = [] ): array {
		$protected = self::get_protected_columns( $supports );

		return [
			'columns' => array_diff_key( self::fetch( $table ), array_flip( $protected ) ),
			'keys'    => self::fetch_keys( $table ),
		];
	}

	/**
	 * Read column definitions from the database table.
	 *
	 * @return array<string, string> Column name => SQL type.
	 */
	private static function fetch( string $table ): array {
		global $wpdb;

		if ( ! TableSchema::table_exists( $table ) ) {
			return [];
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from parsed model settings.
		$rows = $wpdb->get_results( "SHOW COLUMNS FROM `{$table}`", ARRAY_A );
		if ( ! is_array( $rows ) ) {
			return [];
		}

		$columns = [];
		foreach ( $rows as $row ) {
			$name = (string) ( $row['Field'] ?? '' );
			$type = (string) ( $row['Type'] ?? '' );
			if ( ! $name || ! $type ) {
				continue;
			}
			$columns[ $name ] = strtoupper( $type );
		}

		return $columns;
	}

	/**
	 * Read non-primary index column names from the database table.
	 *
	 * @return string[]
	 */
	private static function fetch_keys( string $table ): array {
		global $wpdb;

		if ( ! TableSchema::table_exists( $table ) ) {
			return [];
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from parsed model settings.
		$rows = $wpdb->get_results( "SHOW INDEX FROM `{$table}`", ARRAY_A );
		if ( ! is_array( $rows ) ) {
			return [];
		}

		$rows = array_filter(
			$rows,
			static function ( array $row ): bool {
				return ! empty( $row['Column_name'] ) && 'PRIMARY' !== ( $row['Key_name'] ?? '' );
			}
		);

		return array_values( array_unique( array_column( $rows, 'Column_name' ) ) );
	}

	/**
	 * Get column names that inspect should hide (ID and support columns).
	 *
	 * @param string[] $supports Model support features.
	 * @return string[]
	 */
	private static function get_protected_columns( array $supports = [] ): array {
		return array_merge(
			[ 'ID' ],
			array_values( array_intersect( self::SUPPORT_FEATURES, $supports ) )
		);
	}

	/**
	 * Resolve model supports: Factory first, then builder post meta.
	 *
	 * @param string $model_name Model slug.
	 * @return string[]
	 */
	public static function supports_for( string $model_name ): array {
		$model_name = trim( $model_name );
		if ( ! $model_name ) {
			return [];
		}

		$from_factory = self::supports_from_factory( $model_name );
		if ( null !== $from_factory && $from_factory ) {
			return $from_factory;
		}

		$from_meta = self::supports_from_meta( $model_name );
		if ( $from_meta ) {
			return $from_meta;
		}

		return $from_factory ?? [];
	}

	/**
	 * Supports from a runtime-registered model, or null if not in Factory.
	 *
	 * @return string[]|null
	 */
	private static function supports_from_factory( string $model_name ): ?array {
		$model = Factory::get( $model_name );
		if ( ! $model instanceof Model ) {
			return null;
		}

		$features = [];
		foreach ( self::SUPPORT_FEATURES as $feature ) {
			if ( $model->supports( $feature ) ) {
				$features[] = $feature;
			}
		}

		return $features;
	}

	/**
	 * Supports from the builder mb-model post meta for this slug.
	 *
	 * @return string[]
	 */
	private static function supports_from_meta( string $model_name ): array {
		$post_id = Register::get_model_post_id( $model_name );
		if ( ! $post_id ) {
			return [];
		}

		$model = get_post_meta( $post_id, 'model', true );
		if ( ! is_array( $model ) || empty( $model['supports'] ) || ! is_array( $model['supports'] ) ) {
			return [];
		}

		return array_values( array_intersect( self::SUPPORT_FEATURES, $model['supports'] ) );
	}

	private static function table_error(): array {
		return self::fail( __( 'Could not resolve the model table.', 'meta-box-builder' ) );
	}

	private static function fail( string $message ): array {
		return [
			'success' => false,
			'message' => $message,
		];
	}
}

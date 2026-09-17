<?php
namespace MBBParser\Unparsers;

use MBBParser\Prefixer;

/**
 * This class is the inverse of the parser.
 * We convert the parsed data back to the format that can be saved to the database.
 * Basically, it takes the data from both old versions and new versions and converts them to the same format.
 * This is to compatibility and allow users to import/export data between different versions, even with other plugins like ACF.
 */
class MetaBox extends Base {
	/**
	 * Allows these keys to be empty as they are required to be compatible with the builder.
	 * @var string[]
	 */
	protected $empty_keys = [ 'fields', 'meta_box', 'settings', 'data', 'modified' ];

	private $settings_parser;

	private $validation = [
		'rules'    => [],
		'messages' => [],
	];

	/**
	 * The schemas for each post type
	 *
	 * @var array
	 */
	public const SCHEMAS = [
		'meta-box'         => 'https://schemas.metabox.io/field-group.json',
		'mb-relationship'  => 'https://schemas.metabox.io/relationships.json',
		'mb-settings-page' => 'https://schemas.metabox.io/settings-page.json',
		'mb-model'         => 'https://schemas.metabox.io/custom-model.json',
	];

	/**
	 * Match the post type with the meta key which is used to register the object
	 *
	 * @var array
	 */
	public const TYPE_META = [
		'meta-box'         => 'meta_box',
		'mb-relationship'  => 'relationship',
		'mb-settings-page' => 'settings_page',
		'mb-model'         => 'model',
	];

	public function unparse() {
		$this->unparse_schema();
		$this->unparse_meta_box();
		$this->unparse_relationship();
		$this->unparse_settings_page()->unparse_settings_page_tabs();
		$this->unparse_model()->unparse_model_settings()->unparse_model_settings_columns();
		$this->unparse_post_fields();
		$this->unparse_modified();
		$this->unparse_settings();

		// Settings page and block read the settings that unparse_settings() builds.
		$this->unparse_settings_page_menu();
		$this->unparse_block_icon();

		$this->unparse_fields();
		$this->unparse_custom_table();
		$this->unparse_tabs();
		$this->unparse_validation();
		$this->unparse_geo_location();
		$this->unparse_columns();
		$this->unparse_conditional_logic();
		$this->unparse_include_exclude();
		$this->unparse_show_hide();
	}

	public function to_minimal_format() {
		$settings = $this->get_settings();
		$meta_key = self::TYPE_META[ $settings['post_type'] ] ?? 'meta_box';
		$settings = array_merge( $settings, $settings[ $meta_key ] );

		$preserve_keys = [
			'custom_settings',
		];

		foreach ( $this->get_unneeded_keys() as $key ) {
			if ( $key === '$schema' ) {
				continue;
			}

			if ( $key === 'settings' ) {
				if ( empty( $settings ) || ! is_array( $settings ) ) {
					continue;
				}

				foreach ( $preserve_keys as $k ) {
					$v = $this->lookup( [ $k, "settings.$k" ], [] );

					if ( ! empty( $v ) ) {
						$settings[ $k ] = $v;
					}
				}
			}

			unset( $settings[ $key ] );
		}

		// Strip prefix from field IDs before exporting to JSON (minimal format)
		if ( ! empty( $settings['fields'] ) && is_array( $settings['fields'] ) ) {
			Prefixer::remove( $settings['fields'], $settings['prefix'] ?? '' );
		}

		ksort( $settings );

		return $settings;
	}

	public function unparse_schema() {
		$schema = $this->lookup( [ '$schema' ], '' );
		if ( ! empty( $schema ) ) {
			return $this;
		}

		$post_type                 = $this->detect_post_type();
		$this->settings['$schema'] = self::SCHEMAS[ $post_type ] ?? '';

		return $this;
	}

	private function unparse_tabs(): self {
		if ( $this->detect_post_type() !== 'meta-box' ) {
			return $this;
		}

		$tabs = $this->lookup( [ 'tabs', 'meta_box.tabs' ], [] );

		if ( empty( $tabs ) ) {
			return $this;
		}

		$tab_style          = $this->lookup( [ 'tab_style', 'meta_box.tab_style' ], 'default' );
		$tab_default_active = $this->lookup( [ 'tab_default_active', 'meta_box.tab_default_active' ], '' );
		$tab_remember       = $this->lookup( [ 'tab_remember', 'meta_box.tab_remember' ], false );

		// Store in custom_settings
		$custom_settings                               = $this->lookup( [ 'settings.custom_settings' ], [] );
		$custom_settings['tab_style']                  = [
			'id'    => 'tab_style',
			'key'   => 'tab_style',
			'value' => $tab_style,
		];
		$custom_settings['tab_default_active']         = [
			'id'    => 'tab_default_active',
			'key'   => 'tab_default_active',
			'value' => $tab_default_active,
		];
		$custom_settings['tab_remember']               = [
			'id'    => 'tab_remember',
			'key'   => 'tab_remember',
			'value' => $tab_remember,
		];
		$this->settings['settings']['custom_settings'] = $custom_settings;

		// Rebuild fields with tab fields first
		$added_tabs      = [];
		$original_fields = $this->settings['fields'] ?? [];
		$new_fields      = [];

		// Add fields under this tab
		foreach ( $original_fields as $id => $field ) {
			if ( ! isset( $field['tab'] ) ) {
				$new_fields[ $id ] = $field;
				continue;
			}

			$tab_data  = $tabs[ $field['tab'] ] ?? null;
			$label     = is_array( $tab_data ) ? $tab_data['label'] : $tab_data;
			$icon      = is_array( $tab_data ) ? ( $tab_data['icon'] ?? '' ) : '';
			$icon_type = 'dashicons';
			if ( strpos( $icon, 'fa-' ) === 0 ) {
				$icon_type = 'fontawesome';
			} elseif ( strpos( $icon, 'http' ) === 0 ) {
				$icon_type = 'url';
			}

			if ( $icon_type === 'dashicons' && str_starts_with( $icon, 'dashicons-' ) ) {
				$icon = substr( $icon, strlen( 'dashicons-' ) );
			}

			$tab_field = [
				'id'        => $field['tab'],
				'_id'       => $field['tab'],
				'type'      => 'tab',
				'name'      => $label,
				'icon_type' => $icon_type,
				'icon'      => $icon,
				'icon_fa'   => $icon_type === 'fontawesome' ? $icon : '',
				'icon_url'  => $icon_type === 'url' ? $icon : '',
			];

			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( ! in_array( $field['tab'], $added_tabs ) ) {
				$new_fields[ $field['tab'] ] = $tab_field;
				$added_tabs[]                = $tab_field;
			}

			$new_fields[ $id ] = $field;
		}

		$this->settings['fields'] = $new_fields;
		return $this;
	}

	public function unparse_custom_table() {
		$custom_table = $this->lookup( [ 'custom_table', 'settings.custom_table' ], [] );
		$post_type    = $this->detect_post_type();

		if ( $post_type !== 'meta-box' ) {
			return $this;
		}

		$default_custom_table = [
			'enable'  => false,
			'name'    => '',
			'prefix'  => false,
			'create'  => false,
			'columns' => [],
		];

		$this->settings['settings']['custom_table'] = array_merge( $default_custom_table, $custom_table );

		// For short reference.
		$custom_table = &$this->settings['settings']['custom_table'];

		$settings    = $this->settings['settings'] ?? [];
		$object_type = (string) ( $settings['object_type'] ?? '' );
		$models      = array_filter( (array) ( $settings['models'] ?? [] ) );
		$is_model    = 'model' === $object_type || ! empty( $models );

		// Post/term/user: recover enable + name from the resolved table.
		// Models: keep explicit manage/create/columns; do not force enable from the model table.
		if ( isset( $this->table ) && ! $is_model ) {
			$name = $this->table;

			if ( ! empty( $custom_table['prefix'] ) ) {
				global $wpdb;
				if ( str_starts_with( $name, $wpdb->prefix ) ) {
					$name = substr( $name, strlen( $wpdb->prefix ) );
				}
			}

			$custom_table['name']   = $name;
			$custom_table['enable'] = true;
		}

		if ( empty( $custom_table['enable'] ) ) {
			return $this;
		}

		// Survive minimal export (settings are stripped).
		$meta_box_custom_table = [];

		foreach ( [ 'enable', 'prefix', 'create' ] as $key ) {
			if ( ! empty( $custom_table[ $key ] ) ) {
				$meta_box_custom_table[ $key ] = true;
			}
		}

		if ( ! empty( $custom_table['columns'] ) && is_array( $custom_table['columns'] ) ) {
			$meta_box_custom_table['columns'] = $custom_table['columns'];
		}

		if ( ! empty( $meta_box_custom_table ) ) {
			$this->settings['meta_box']['custom_table'] = $meta_box_custom_table;
		}

		return $this;
	}

	public function unparse_modified() {
		$modified                   = $this->lookup( [ 'modified', 'meta_box.modified', 'settings.modified', 'model.modified' ], time() );
		$this->settings['modified'] = $modified;

		if ( $this->detect_post_type() === 'meta-box' ) {
			$this->settings['meta_box']['modified'] = $modified;
		}

		return $this;
	}

	/**
	 * Unparse the meta box.
	 *
	 * For fields:
	 * - Always keep it as a numeric array.
	 * - Remove prefix from field IDs for the settings, that can be used for export, builder, local JSON.
	 * - Add prefix to field IDs for parsed meta box, that's ready for registering.
	 */
	public function unparse_meta_box(): self {
		// If not meta box, return
		if ( $this->detect_post_type() !== 'meta-box' ) {
			return $this;
		}

		$prefix = $this->lookup( [ 'prefix', 'settings.prefix' ], '' );

		// If meta box is already parsed, normalize the fields array.
		if ( isset( $this->meta_box ) && is_array( $this->meta_box ) ) {
			// Fix: error on earlier versions that saved fields as object
			$fields = $this->meta_box['fields'] ?? [];
			$fields = array_values( $fields );

			// Remove prefix from field IDs for the settings, that can be used for export, builder, local JSON.
			Prefixer::remove( $fields, $prefix );
			$this->fields = $fields;

			// Add prefix to field IDs for parsed meta box, that's ready for registering.
			Prefixer::add( $fields, $prefix );
			$this->settings['meta_box']['fields'] = $fields;

			return $this;
		}

		// If meta box is not parsed, normalize the fields array.
		$fields = $this->fields ?: [];
		$fields = array_values( $fields );

		// Remove prefix from field IDs for the settings, that can be used for export, builder, local JSON.
		Prefixer::remove( $fields, $prefix );
		$this->fields = $fields;

		$meta_box = $this->get_settings();

		// Add prefix to field IDs for parsed meta box, that's ready for registering.
		Prefixer::add( $fields, $prefix );
		$meta_box['fields'] = $fields;

		foreach ( $this->get_unneeded_keys() as $key ) {
			unset( $meta_box[ $key ] );
		}

		$this->meta_box = $meta_box;
		$this->data     = [];

		return $this;
	}

	/**
	 * Restore the editor icon fields after importing a minimal block JSON.
	 * Blocks register either an SVG string or an array carrying the dashicon and its colors.
	 * A plain dashicon slug already matches the editor field, so it needs no mapping.
	 */
	private function unparse_block_icon(): self {
		$settings = $this->settings['settings'] ?? [];

		if ( ( $settings['object_type'] ?? '' ) !== 'block' || isset( $settings['icon_type'] ) ) {
			return $this;
		}

		$icon = $settings['icon'] ?? '';

		if ( is_array( $icon ) ) {
			$settings['icon_type']       = 'dashicons';
			$settings['icon']            = $icon['src'] ?? '';
			$settings['icon_background'] = $icon['background'] ?? '';
			$settings['icon_foreground'] = $icon['foreground'] ?? '';
		} elseif ( $this->is_svg_icon( (string) $icon ) ) {
			$settings['icon_type'] = 'svg';
			$settings['icon_svg']  = $icon;
		} else {
			return $this;
		}

		$this->settings['settings'] = $settings;

		return $this;
	}

	public function unparse_settings_page(): self {
		if ( $this->detect_post_type() !== 'mb-settings-page' ) {
			return $this;
		}

		// If already parsed, return
		if ( isset( $this->settings_page ) ) {
			return $this;
		}

		$settings_page = $this->get_settings();

		foreach ( $this->get_unneeded_keys() as $key ) {
			unset( $settings_page[ $key ] );
		}

		$this->settings_page = $settings_page;
		$this->post_title    = $this->lookup( [ 'menu_title', 'id' ] );

		return $this;
	}

	private function unparse_settings_page_tabs(): self {
		if ( $this->detect_post_type() !== 'mb-settings-page' ) {
			return $this;
		}

		$tabs = $this->lookup( [ 'tabs' ], [] );
		if ( empty( $tabs ) ) {
			return $this;
		}

		$tab_items = [];
		foreach ( $tabs as $key => $value ) {
			$id               = uniqid( 'tab_' );
			$tab_items[ $id ] = compact( 'id', 'key', 'value' );
		}

		$this->settings['tabs'] = $tab_items;

		return $this;
	}

	/**
	 * Restore the editor menu fields after importing a minimal settings page JSON.
	 * Registration keeps only parent and a single icon_url string, while the editor needs
	 * menu_type plus icon_type and the field matching that type.
	 */
	private function unparse_settings_page_menu(): self {
		$settings = $this->settings['settings'] ?? [];

		if ( $this->detect_post_type() !== 'mb-settings-page' || isset( $settings['menu_type'] ) ) {
			return $this;
		}

		$settings['menu_type'] = empty( $settings['parent'] ) ? 'top' : 'submenu';
		$settings             += $this->icon_to_editor_fields( (string) ( $settings['icon_url'] ?? '' ), [
			'dashicons'    => 'icon_dashicons',
			'svg'          => 'icon_svg',
			'custom'       => 'icon_custom',
			'font_awesome' => 'icon_font_awesome',
		] );

		$this->settings['settings'] = $settings;

		return $this;
	}

	public function unparse_relationship() {
		// If not meta box, return
		if ( $this->detect_post_type() !== 'mb-relationship' ) {
			return $this;
		}
		// If already parsed, return
		if ( isset( $this->relationship ) ) {
			return $this;
		}

		$relationship = $this->get_settings();

		foreach ( $this->get_unneeded_keys() as $key ) {
			unset( $relationship[ $key ] );
		}
		$this->relationship = $relationship;
		$this->post_title   = $this->lookup( [ 'menu_title', 'id' ] );

		return $this;
	}

	public function unparse_model(): self {
		if ( $this->detect_post_type() !== 'mb-model' ) {
			return $this;
		}

		if ( isset( $this->model ) ) {
			$model = $this->settings['model'];
			if ( empty( $model['id'] ) && ! empty( $model['name'] ) ) {
				$model['id'] = $model['name'];
			} elseif ( empty( $model['name'] ) && ! empty( $model['id'] ) ) {
				$model['name'] = $model['id'];
			}
			$this->settings['model'] = $model;
			$this->post_title        = $this->lookup( [
				'post_title',
				'model.labels.name',
				'model.labels.singular_name',
				'model.id',
				'id',
			] );

			return $this;
		}

		$model = $this->get_settings();

		foreach ( $this->get_unneeded_keys() as $key ) {
			unset( $model[ $key ] );
		}

		if ( empty( $model['id'] ) && ! empty( $model['name'] ) ) {
			$model['id'] = $model['name'];
		} elseif ( empty( $model['name'] ) && ! empty( $model['id'] ) ) {
			$model['name'] = $model['id'];
		}

		$this->model      = $model;
		$this->post_title = $this->lookup( [ 'post_title', 'labels.name', 'labels.singular_name', 'id' ] );

		return $this;
	}

	/**
	 * Build the editor settings from a registered model.
	 * Registration stores id/name, a prefixed table and a single menu_icon string, while the
	 * editor expects slug, an unprefixed table with a prefix flag, and icon_type + icon fields.
	 */
	private function unparse_model_settings(): self {
		global $wpdb;

		$model = $this->settings['model'] ?? [];

		if ( $this->detect_post_type() !== 'mb-model' || ! empty( $this->settings['settings'] ) || empty( $model ) || ! is_array( $model ) ) {
			return $this;
		}

		unset( $model['keys'], $model['post_id'] );

		$settings         = $model;
		$settings['slug'] = (string) ( $model['id'] ?? $model['name'] ?? '' );

		$table              = (string) ( $model['table'] ?? '' );
		$settings['prefix'] = str_starts_with( $table, $wpdb->prefix );
		$settings['table']  = $settings['prefix'] ? substr( $table, strlen( $wpdb->prefix ) ) : $table;

		// Submenu models register a parent slug, which the editor keeps in show_in_menu.
		if ( ! empty( $model['parent'] ) ) {
			$settings['show_in_menu'] = $model['parent'];
		}

		$settings += $this->icon_to_editor_fields( (string) ( $model['menu_icon'] ?? '' ), [
			'dashicons'    => 'icon',
			'svg'          => 'icon_svg',
			'custom'       => 'icon_custom',
			'font_awesome' => 'font_awesome',
		] );

		unset( $settings['id'], $settings['name'], $settings['menu_icon'], $settings['parent'] );

		$this->settings['settings'] = $settings;

		return $this;
	}

	/**
	 * Ensure settings.columns uses the editor shape after importing a minimal model JSON.
	 * Exported models store columns as { name: sqlType }; the UI expects { id: { name, type, … } }.
	 */
	private function unparse_model_settings_columns(): self {
		if ( $this->detect_post_type() !== 'mb-model' ) {
			return $this;
		}

		$settings = $this->settings['settings'] ?? [];
		$columns  = $settings['columns'] ?? [];
		if ( ! is_array( $columns ) || empty( $columns ) ) {
			$columns = $this->settings['model']['columns'] ?? [];
		}
		if ( ! is_array( $columns ) || empty( $columns ) ) {
			return $this;
		}

		$first = reset( $columns );
		if ( is_array( $first ) && array_key_exists( 'name', $first ) ) {
			return $this;
		}

		$keys    = $settings['keys'] ?? ( $this->settings['model']['keys'] ?? [] );
		$keys    = is_array( $keys ) ? $keys : [];
		$key_set = array_fill_keys( $keys, true );
		$editor  = [];

		foreach ( $columns as $name => $type ) {
			if ( is_array( $type ) ) {
				continue;
			}
			$name = (string) $name;
			if ( '' === $name || 'id' === strtolower( $name ) ) {
				continue;
			}
			$id             = 'col_' . str_replace( '-', '_', sanitize_key( $name ) );
			$editor_type    = $this->sql_type_to_editor_column( (string) $type );
			$editor[ $id ]  = [
				'id'          => $id,
				'name'        => $name,
				'type'        => $editor_type['type'],
				'custom_type' => $editor_type['custom_type'],
				'index'       => isset( $key_set[ $name ] ),
			];
		}

		if ( empty( $editor ) ) {
			return $this;
		}

		$this->settings['settings']['columns'] = $editor;
		unset( $this->settings['settings']['keys'] );

		return $this;
	}

	/**
	 * Map a SQL column type to editor type fields.
	 * Exact preset match only; variants keep custom_type so import/sync does not rewrite SQL.
	 * Keep in sync with dbTypeToEditorColumn() in meta-box-builder columnTypes.js.
	 *
	 * @return array{type: string, custom_type: string}
	 */
	private function sql_type_to_editor_column( string $sql_type ): array {
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

		$type  = trim( $sql_type );
		$upper = strtoupper( $type );

		if ( in_array( $upper, $presets, true ) ) {
			return [
				'type'        => $upper,
				'custom_type' => '',
			];
		}

		return [
			'type'        => 'custom',
			'custom_type' => $type,
		];
	}

	public function unparse_settings() {
		$settings = $this->settings['settings'] ?? [];

		if ( ! empty( $settings ) ) {
			return $this;
		}

		// Models build their settings in unparse_model_settings(), which runs earlier.
		if ( $this->detect_post_type() === 'mb-model' ) {
			return $this;
		}

		$id    = $this->id ?? uniqid();
		$title = $this->lookup( [ 'post_title', 'title' ], $id );

		// Basic settings.
		$settings = [
			'title'         => $title,
			'id'            => $id,
			'object_type'   => $this->object_type ?? 'post',
			'post_types'    => $this->post_types ?? [ 'post' ],
			'priority'      => $this->priority ?? 'high',
			'style'         => $this->style ?? 'default',
			'closed'        => $this->closed ?? false,
			'class'         => $this->class ?? '',
			'prefix'        => $this->prefix ?? '',
			'revision'      => $this->revision ?? false,
			'text_domain'   => $this->text_domain ?? 'your-text-domain',
			'function_name' => $this->function_name ?? '',
			'settings_page' => $this->settings_page ?? [],
		];

		$settings = array_merge( $this->lookup( [ 'settings' ], [] ), $settings );

		foreach ( $this->settings as $key => $value ) {
			if ( in_array( $key, $this->get_unneeded_keys(), true ) ) {
				continue;
			}

			$settings[ $key ] = $value;
		}

		unset( $settings['settings'] );
		unset( $settings['fields'] );

		// Merge custom settings
		$custom_settings = $this->lookup( [ 'custom_settings' ], [] );
		if ( ! empty( $custom_settings ) ) {
			$settings['custom_settings'] = $custom_settings;
		}

		$this->settings_parser = new Settings( $settings );
		$this->settings_parser->unparse();

		$this->settings['settings'] = $this->settings_parser->get_settings();
	}

	public function unparse_post_fields() {
		// Only set post_title from title if post_title isn't already set
		$this->post_title = $this->lookup( [ 'post_title', 'title' ], $this->id ?? uniqid() );
		$post_type        = $this->post_type ?? $this->detect_post_type();

		$this->post_type    = $post_type;
		$this->post_name    = $this->lookup( [ 'post_name', 'settings.id', 'relationship.id', 'meta_box.id', 'model.id', 'model.name', 'id' ] );
		$this->post_date    = $this->lookup( [ 'post_date' ], gmdate( 'Y-m-d H:i:s' ) );
		$this->post_status  = $this->lookup( [ 'post_status' ], 'publish' );
		$this->post_content = $this->lookup( [ 'post_content' ], '' );

		return $this;
	}

	public function detect_post_type(): string {
		if ( isset( $this->post_type ) ) {
			return $this->post_type;
		}

		// Detect post type from the schema (new format)
		if ( isset( $this->settings['$schema'] ) ) {
			$schema    = $this->settings['$schema'];
			$post_type = array_search( $schema, self::SCHEMAS, true );
			if ( $post_type ) {
				return $post_type;
			}
		}

		// If no schema (old format), check keys exist
		// - meta_box it's a meta box
		// - relationship it's a relationship
		// - settings_page it's a settings page
		foreach ( self::TYPE_META as $type => $meta_key ) {
			if ( isset( $this->settings[ $meta_key ] ) ) {
				return $type;
			}
		}

		return 'meta-box';
	}

	public function unparse_fields(): self {
		if ( empty( $this->fields ) ) {
			return $this;
		}

		$this->fields = $this->convert_fields_for_builder( $this->fields );

		return $this;
	}

	public function convert_fields_for_builder( $fields = [] ): array {
		foreach ( $fields as $id => $field ) {
			$unparser = new Field( $field );
			$unparser->unparse();

			$field = $unparser->get_settings();

			if ( isset( $field['fields'] ) && is_array( $field['fields'] ) ) {
				$field['fields'] = $this->convert_fields_for_builder( $field['fields'] );
			}

			unset( $fields[ $id ] );
			$fields[ $field['_id'] ] = $field;
		}

		return $fields;
	}

	public function unparse_validation() {
		$validation = $this->settings['validation'] ?? [];

		if ( empty( $validation ) || ! array_key_exists( 'rules', $validation ) ) {
			return $this;
		}

		$fields = $this->fields;
		foreach ( $validation['rules'] as $field_id => $rules ) {
			foreach ( $fields as $fid => $field ) {
				if ( $field['id'] !== $field_id ) {
					continue;
				}

				$field['validation'] = [];
				foreach ( $rules as $rule_name => $rule_value ) {
					$id = uniqid();

					$field['validation'][ $id ] = [
						'id'      => $id,
						'name'    => $rule_name,
						'value'   => $rule_value,
						'message' => $validation['messages'][ $field_id ][ $rule_name ] ?? '',
					];
				}

				$fields[ $field['id'] ] = $field;
			}
		}
		$this->fields = $fields;

		return $this;
	}

	public function unparse_geo_location() {
		$geo = $this->lookup( [ 'geo', 'meta_box.geo' ], [] );
		if ( empty( $geo ) && $geo !== false ) { // Allow false as a valid value
			return $this;
		}
		$custom_settings = $this->lookup( [ 'settings.custom_settings' ], [] );

		$custom_settings['geo']                        = [
			'id'    => 'geo',
			'key'   => 'geo',
			'value' => is_array( $geo ) ? wp_json_encode( $geo ) : $geo, // Keep booleans as-is
		];
		$this->settings['settings']['custom_settings'] = $custom_settings;
		return $this;
	}

	public function unparse_columns() {
		// Field-group admin "columns" setting — not model table schema columns.
		if ( $this->detect_post_type() !== 'meta-box' ) {
			return $this;
		}

		$columns = $this->lookup( [ 'columns', 'meta_box.columns' ], [] );
		if ( empty( $columns ) ) {
			return $this;
		}

		// Model-style map { name: sqlType } must not become custom_settings.
		$first = reset( $columns );
		if ( ! is_array( $first ) && ! is_object( $first ) ) {
			return $this;
		}

		$custom_settings        = $this->lookup( [ 'settings.custom_settings' ], [] );
		$id                     = uniqid();
		$custom_settings[ $id ] = [
			'id'    => $id,
			'key'   => 'columns',
			'value' => is_array( $columns ) ? wp_json_encode( $columns ) : $columns,
		];
		$this->settings['settings']['custom_settings'] = $custom_settings;
		return $this;
	}

	public function unparse_include_exclude() {
		$keywords        = [ 'include', 'exclude' ];
		$include_exclude = $this->lookup( [ 'settings.include_exclude' ], [] );
		foreach ( $keywords as $keyword ) {
			$data = $this->lookup( [ $keyword, "meta_box.$keyword" ], [] );
			if ( empty( $data ) ) {
				continue;
			}
			$setting_include_exclude = [
				'type'     => $keyword,
				'relation' => $include_exclude['relation'] ?? 'OR',
				'rules'    => [],
			];
			foreach ( $data as $rule_name => $rule_value ) {
				if ( $rule_name === 'relation' ) {
					$setting_include_exclude['relation'] = $rule_value;
					continue;
				}
				$setting_include_exclude['rules'][ $rule_name ] = [
					'id'    => $rule_name,
					'name'  => $rule_name,
					'value' => $rule_value,
				];
			}
			if ( ! empty( $setting_include_exclude['rules'] ) ) {
				$this->settings['settings']['include_exclude'] = $setting_include_exclude;
			}
		}
		return $this;
	}

	public function unparse_show_hide() {
		$keywords  = [ 'include', 'exclude' ];
		$show_hide = $this->lookup( [ 'settings.show_hide' ], [] );

		foreach ( $keywords as $keyword ) {
			$data = $this->lookup( [ $keyword, "meta_box.$keyword" ], [] );
			if ( empty( $data ) ) {
				continue;
			}
			$setting_show_hide = [
				'type'     => $keyword,
				'relation' => $show_hide['relation'] ?? 'OR',
				'rules'    => [],
			];
			foreach ( $data as $rule_name => $rule_value ) {
				if ( $rule_name === 'relation' ) {
					$setting_show_hide['relation'] = $rule_value;
					continue;
				}
				$setting_show_hide['rules'][ $rule_name ] = [
					'id'    => $rule_name,
					'name'  => $rule_name,
					'value' => $rule_value,
				];
			}
			if ( ! empty( $setting_show_hide['rules'] ) ) {
				$this->settings['settings']['show_hide'] = $setting_show_hide;
			}
		}
		return $this;
	}

	/**
	 * Editor icon fields for a registered icon string.
	 * Registration flattens every icon type into one string, so the type comes from its shape.
	 *
	 * @param string                $icon Registered icon value.
	 * @param array<string, string> $keys Editor field name for each icon type.
	 * @return array<string, string>
	 */
	private function icon_to_editor_fields( string $icon, array $keys ): array {
		if ( '' === $icon ) {
			return [];
		}

		$type = $this->detect_icon_type( $icon );

		return [
			'icon_type'    => $type,
			$keys[ $type ] => 'dashicons' === $type ? substr( $icon, strlen( 'dashicons-' ) ) : $icon,
		];
	}

	private function detect_icon_type( string $icon ): string {
		if ( str_starts_with( $icon, 'dashicons-' ) ) {
			return 'dashicons';
		}

		if ( $this->is_svg_icon( $icon ) ) {
			return 'svg';
		}

		if ( str_starts_with( $icon, 'http' ) || str_starts_with( $icon, '/' ) ) {
			return 'custom';
		}

		return 'font_awesome';
	}

	private function is_svg_icon( string $icon ): bool {
		return str_contains( $icon, '<svg' ) || str_starts_with( $icon, 'data:image/svg' );
	}

	/**
	 * By default, we move all keys under the root to the settings array.
	 * Except these keys
	 *
	 * @return string[]
	 */
	public function get_unneeded_keys(): array {
		$default = [
			'$schema',
			'ID',
			'post_name',
			'post_date',
			'post_status',
			'post_content',
			'settings',
			'meta_box',
			'data',
			'closed',
			'function_name',
			'text_domain',
			'post_type',
			'post_title',
			'settings_page',
			'menu_order',
			'ping_status',
			'pinged',
			'post_author',
			'post_content_filtered',
			'post_date_gmt',
			'post_excerpt',
			'post_mime_type',
			'post_modified',
			'post_modified_gmt',
			'post_parent',
			'post_password',
			'to_ping',
			'comment_count',
			'comment_status',
			'filter',
			'guid',
			'revision',
		];

		$post_type = $this->detect_post_type() ?? 'meta-box';

		// Add extra keys for other post types
		$extras = [
			'meta-box'         => [ 'relationship', 'model' ],
			'mb-relationship'  => [ 'fields', 'settings_page', 'relationship', 'meta_box', 'data', 'model' ],
			'mb-settings-page' => [ 'fields', 'settings_page', 'relationship', 'meta_box', 'data', 'model' ],
			'mb-model'         => [ 'fields', 'settings_page', 'relationship', 'meta_box', 'data', 'model' ],
		];

		return array_merge( $default, $extras[ $post_type ] ?? [] );
	}
}

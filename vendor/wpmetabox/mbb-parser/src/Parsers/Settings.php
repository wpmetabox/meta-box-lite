<?php
namespace MBBParser\Parsers;

use MetaBox\Support\Arr;

class Settings extends Base {
	/**
	 * Allow these settings to be empty.
	 * @var array
	 */
	protected $empty_keys = [ 'post_types', 'taxonomies', 'settings_pages', 'models' ];

	/**
	 * Remove these settings if they are false.
	 *
	 * @var array
	 */
	protected $remove_false = [
		'revision',
		'closed',
	];

	public function parse() {
		$this->remove_default( 'context', 'normal' )
			->parse_boolean_values()
			->parse_numeric_values()
			->parse_location()
			->parse_location_rules( 'show_hide' )
			->parse_location_rules( 'include_exclude' )
			->parse_conditional_logic()
			->parse_custom_table()
			->parse_block()
			->parse_custom_settings()
			->remove_empty_values();

		unset( $this->object_type );
	}

	private function parse_location(): self {
		$object_type = $this->object_type ?: 'post';

		if ( $object_type === 'post' ) {
			unset( $this->taxonomies );
			unset( $this->settings_pages );
			unset( $this->models );
			unset( $this->type );
		} elseif ( $object_type === 'term' ) {
			unset( $this->post_types );
			unset( $this->settings_pages );
			unset( $this->models );
			unset( $this->type );
		} elseif ( $object_type === 'setting' ) {
			unset( $this->post_types );
			unset( $this->taxonomies );
			unset( $this->models );
			unset( $this->type );
		} elseif ( $object_type === 'model' ) {
			unset( $this->post_types );
			unset( $this->taxonomies );
			unset( $this->settings_pages );
			unset( $this->type );
			if ( isset( $this->models ) ) {
				$this->models = array_filter( (array) $this->models );
			}
		} elseif ( in_array( $object_type, [ 'block', 'user', 'comment' ], true ) ) {
			unset( $this->post_types );
			unset( $this->taxonomies );
			unset( $this->settings_pages );
			unset( $this->models );
			$this->type = $object_type;
		}

		if ( 'post' !== $object_type ) {
			return $this;
		}

		$this->remove_default( 'post_types', [ 'post' ] );
		$this->remove_default( 'priority', 'high' );
		$this->remove_default( 'style', 'default' );
		$this->remove_default( 'position', 'normal' );

		if ( isset( $this->post_types ) ) {
			$this->post_types = array_filter( (array) $this->post_types );
		}

		return $this;
	}

	private function parse_location_rules( $key ) {
		if ( ! isset( $this->$key ) ) {
			return $this;
		}

		$data = $this->$key;
		unset( $this->$key );

		$rules = [];
		foreach ( $data['rules'] as $rule ) {
			$value = $rule['value'];
			if ( 'input_value' === $rule['name'] ) {
				$value = wp_list_pluck( $value, 'value', 'key' );
			}
			$rules[ $rule['name'] ] = $value;
		}
		$type     = $data['type'];
		$relation = isset( $data['relation'] ) ? strtoupper( $data['relation'] ) : 'OR';

		$this->$type = $rules;
		if ( 'AND' === $relation ) {
			$this->$type = array_merge( $this->$type, [ 'relation' => 'AND' ] );
		}

		return $this;
	}

	private function parse_custom_table() {
		$object_type = Arr::get( $this->settings, 'object_type', '' );
		$models      = array_filter( (array) Arr::get( $this->settings, 'models', [] ) );

		// Models own the table schema — inject storage from the registered model.
		if ( 'model' === $object_type || ! empty( $models ) ) {
			$table = $this->get_model_table( $models );
			if ( $table ) {
				$this->storage_type = 'custom_table';
				$this->table        = $table;
			} else {
				unset( $this->storage_type );
				unset( $this->table );
			}
			unset( $this->custom_table );
			return $this;
		}

		$enable = Arr::get( $this->settings, 'custom_table.enable', false );
		$name   = Arr::get( $this->settings, 'custom_table.name', '' );
		if ( $enable && $name ) {
			$this->storage_type = 'custom_table';

			global $wpdb;
			$prefix      = Arr::get( $this->settings, 'custom_table.prefix', false );
			$this->table = ( $prefix ? $wpdb->prefix : '' ) . $name;
		} else {
			// Remove custom table settings if it's not enabled.
			unset( $this->storage_type );
			unset( $this->table );
		}

		unset( $this->custom_table );
		return $this;
	}

	private function get_model_table( array $models ): string {
		$first = reset( $models );
		if ( ! $first ) {
			return '';
		}

		if ( class_exists( '\MetaBox\CustomTable\Model\Factory' ) ) {
			$model = \MetaBox\CustomTable\Model\Factory::get( $first );
			if ( $model && ! empty( $model->table ) ) {
				return (string) $model->table;
			}
		}

		$cache = get_option( 'mbb_models', [] );
		if ( is_array( $cache ) && ! empty( $cache[ $first ]['table'] ) ) {
			return (string) $cache[ $first ]['table'];
		}

		$query = new \WP_Query( [
			'posts_per_page'         => 1,
			'post_status'            => 'publish',
			'post_type'              => 'mb-model',
			'name'                   => $first,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		] );

		if ( empty( $query->posts ) ) {
			return '';
		}

		$model = get_post_meta( $query->posts[0]->ID, 'model', true );
		return is_array( $model ) && ! empty( $model['table'] ) ? (string) $model['table'] : '';
	}

	private function parse_block() {
		// Remove block settings.
		if ( 'block' !== $this->object_type ) {
			$params = [
				'description',
				'category',
				'keywords',
				'supports',
				'block_context',
				'icon',
				'icon_type',
				'icon_svg',
				'icon_background',
				'icon_foreground',
				'render_with',
				'render_template',
				'render_callback',
				'render_view',
				'render_code',
				'enqueue_style',
				'enqueue_script',
				'enqueue_assets',
				'block_json',
			];
			foreach ( $params as $param ) {
				unset( $this->{$param} );
			}
			return $this;
		}

		$this->keywords = Arr::from_csv( $this->keywords );

		// Icon.
		if ( 'dashicons' === $this->icon_type ) {
			if ( $this->icon_background || $this->icon_foreground ) {
				$this->icon = [
					'background' => $this->icon_background,
					'foreground' => $this->icon_foreground,
					'src'        => $this->icon,
				];
			}
		}
		if ( 'svg' === $this->icon_type ) {
			$this->icon = $this->icon_svg;
		}
		unset( $this->icon_svg );
		unset( $this->icon_background );
		unset( $this->icon_foreground );
		unset( $this->icon_type );

		// Render options.
		if ( 'callback' === $this->render_with ) {
			unset( $this->render_template );
			unset( $this->render_code );
			unset( $this->render_view );
		}
		if ( 'template' === $this->render_with ) {
			unset( $this->render_callback );
			unset( $this->render_code );
			unset( $this->render_view );
			$this->render_template = $this->replace_variables( $this->render_template );
		}
		if ( 'code' === $this->render_with ) {
			unset( $this->render_callback );
			unset( $this->render_template );
			unset( $this->render_view );
		}

		if ( 'view' === $this->render_with ) {
			if ( ! empty( $this->render_view ) ) {
				$this->render_callback = 'view:' . $this->render_view;
			}

			unset( $this->render_template );
			unset( $this->render_code );
			unset( $this->render_view );
		}

		$this->enqueue_style  = $this->replace_variables( $this->enqueue_style );
		$this->enqueue_script = $this->replace_variables( $this->enqueue_script );

		if ( isset( $this->settings['block_json'] ) && $this->settings['block_json']['enable'] ) {
			if ( ! empty( $this->settings['block_json']['path'] ) ) {
				$this->settings['block_json']['path'] = $this->replace_variables( $this->settings['block_json']['path'] );
			}
		} else {
			unset( $this->block_json );
		}

		unset( $this->render_with );

		// Context.
		$this->context = $this->block_context;
		unset( $this->block_context );

		return $this;
	}

	public function replace_variables( $text ) {
		if ( empty( $text ) ) {
			return $text;
		}

		return strtr( $text, [
			'{{ site.path }}'  => wp_normalize_path( ABSPATH ),
			'{{ site.url }}'   => untrailingslashit( home_url( '/' ) ),
			'{{ theme.path }}' => wp_normalize_path( get_stylesheet_directory() ),
			'{{ theme.url }}'  => get_stylesheet_directory_uri(),
		] );
	}
}

<?php
namespace MBB\Helpers;

use MBB\Extensions\CustomModel\Register;
use MetaBox\CustomTable\Model\Factory;
use MetaBox\Support\Data as DataHelper;
use WP_Block_Type_Registry;
use WP_Query;

class Data {
	public static function get_post_types() {
		$post_types = DataHelper::get_post_types();
		$post_types = array_map( function ( $post_type ) {
			return [
				'slug'         => $post_type->name,
				'name'         => $post_type->labels->singular_name,
				'hierarchical' => $post_type->hierarchical,
				'block_editor' => function_exists( 'use_block_editor_for_post_type' ) && use_block_editor_for_post_type( $post_type->name ),
			];
		}, $post_types );

		return array_values( $post_types );
	}

	public static function get_taxonomies() {
		$taxonomies = DataHelper::get_taxonomies();
		$taxonomies = array_map( function ( $taxonomy ) {
			return [
				'slug'         => $taxonomy->name,
				'name'         => $taxonomy->labels->singular_name,
				'hierarchical' => $taxonomy->hierarchical,
			];
		}, $taxonomies );

		return array_values( $taxonomies );
	}

	public static function get_page_templates() {
		return array_flip( wp_get_theme()->get_page_templates() );
	}

	public static function get_templates() {
		$post_types = self::get_post_types();

		$templates = [];
		foreach ( $post_types as $post_type ) {
			$post_type_templates = array_flip( wp_get_theme()->get_page_templates( null, $post_type['slug'] ) );

			foreach ( $post_type_templates as $name => $file ) {
				$templates[] = [
					'name'           => $name,
					'file'           => $file,
					'post_type'      => $post_type['slug'],
					'post_type_name' => $post_type['name'],
					'id'             => "{$post_type['slug']}:{$file}",
				];
			}
		}

		return $templates;
	}

	public static function get_views(): array {
		$query = new WP_Query( [
			'post_type'              => 'mb-views',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query'             => [
				[
					'key'   => 'type',
					'value' => 'block',
				],
			],

		] );

		$views = [];

		foreach ( $query->posts as $post ) {
			$views[ $post->ID ] = [
				'ID'         => $post->ID,
				'post_title' => $post->post_title,
				'post_name'  => $post->post_name,
			];
		}

		return $views;
	}

	public static function get_post_formats() {
		if ( ! current_theme_supports( 'post-formats' ) ) {
			return [];
		}
		$post_formats = get_theme_support( 'post-formats' );

		return is_array( $post_formats[0] ) ? $post_formats[0] : [];
	}

	public static function get_setting_pages() {
		$pages          = [];
		$settings_pages = apply_filters( 'mb_settings_pages', [] );
		foreach ( $settings_pages as $settings_page ) {
			$title = '';
			if ( ! empty( $settings_page['menu_title'] ) ) {
				$title = $settings_page['menu_title'];
			} elseif ( ! empty( $settings_page['page_title'] ) ) {
				$title = $settings_page['page_title'];
			}

			$tabs = [];
			if ( ! empty( $settings_page['tabs'] ) ) {
				foreach ( $settings_page['tabs'] as $id => $tab ) {
					if ( is_string( $tab ) ) {
						$tab = [ 'label' => $tab ];
					}
					$tab         = wp_parse_args( $tab, [
						'icon'  => '',
						'label' => '',
					] );
					$tabs[ $id ] = $tab['label'];
				}
			}

			$pages[] = array_merge(
				// Default settings.
				[
					'style'     => 'boxes',
					'columns'   => 2,
					'tab_style' => 'default',
				],
				$settings_page,
				[
					'id'    => $settings_page['id'],
					'title' => $title,
					'tabs'  => $tabs,
				]
			);
		}
		return $pages;
	}

	public static function get_models(): array {
		if ( ! class_exists( Factory::class ) ) {
			return [];
		}

		$cache = get_option( Register::CACHE_OPTION, [] );
		if ( ! is_array( $cache ) ) {
			$cache = [];
		}

		$models = [];
		foreach ( Factory::get() as $name => $model ) {
			$models[] = self::format_model( $name, $model, $cache );
		}

		return $models;
	}

	/**
	 * Build the editor payload for one registered model (no database inspect).
	 *
	 * @param string                    $name  Model slug.
	 * @param object|null               $model Factory model instance.
	 * @param array<string, mixed>|null $cache mbb_models option.
	 * @return array<string, mixed>
	 */
	public static function format_model( string $name, $model = null, ?array $cache = null ): array {
		if ( ! class_exists( Factory::class ) ) {
			return [];
		}

		if ( null === $model ) {
			$model = Factory::get( $name );
		}
		if ( ! $model ) {
			return [];
		}

		if ( ! is_array( $cache ) ) {
			$cache = get_option( Register::CACHE_OPTION, [] );
			$cache = is_array( $cache ) ? $cache : [];
		}

		$cached  = $cache[ $name ] ?? [];
		$post_id = isset( $cached['post_id'] ) ? (int) $cached['post_id'] : 0;
		$columns = isset( $cached['columns'] ) && is_array( $cached['columns'] ) ? $cached['columns'] : [];
		$keys    = isset( $cached['keys'] ) && is_array( $cached['keys'] ) ? $cached['keys'] : [];

		if ( $post_id <= 0 && empty( $columns ) && isset( $model->columns ) && is_array( $model->columns ) ) {
			$columns = $model->columns;
		}

		return [
			'name'       => $name,
			'label'      => $model->labels['singular_name'] ?? $model->labels['name'] ?? $name,
			'table'      => $model->table,
			'columns'    => $columns,
			'db_columns' => [],
			'keys'       => $keys,
			'post_id'    => $post_id,
		];
	}

	public static function is_extension_active( $extension ) {
		$functions = [
			'mb-admin-columns'           => 'mb_admin_columns_load',
			'mb-blocks'                  => 'mb_blocks_load',
			'mb-comment-meta'            => 'mb_comment_meta_load',
			'mb-custom-table'            => 'mb_custom_table_load',
			'mb-frontend-submission'     => 'mb_frontend_submission_load',
			'mb-rest-api'                => 'mb_rest_api_load',
			'mb-settings-page'           => 'mb_settings_page_load',
			'mb-term-meta'               => 'mb_term_meta_load',
			'mb-user-meta'               => 'mb_user_meta_load',
			'meta-box-columns'           => 'mb_columns_add_markup',
			'meta-box-conditional-logic' => 'mb_conditional_logic_load',
			'mb-revision'                => 'mb_revision_init',
			'mb-views'                   => 'mb_views_load',
		];
		$classes   = [
			'mb-relationships'         => 'MBR_Loader',
			'meta-box-group'           => 'RWMB_Group',
			'meta-box-include-exclude' => 'MB_Include_Exclude',
			'meta-box-show-hide'       => 'MB_Show_Hide',
			'meta-box-tabs'            => 'MB_Tabs',
			'meta-box-tooltip'         => 'MB_Tooltip',
			'meta-box-text-limiter'    => 'MB_Text_Limiter',
		];

		if ( isset( $functions[ $extension ] ) ) {
			return function_exists( $functions[ $extension ] );
		}
		if ( isset( $classes[ $extension ] ) ) {
			return class_exists( $classes[ $extension ] );
		}
		return false;
	}

	public static function tooltip( $content ) {
		return '<button type="button" class="mbb-tooltip" data-tippy-content="' . esc_attr( $content ) . '"><span class="dashicons dashicons-editor-help"></span></button>';
	}

	public static function get_field_categories(): array {
		return [
			[
				'slug'  => 'basic',
				'title' => __( 'Basic', 'meta-box-builder' ),
			],
			[
				'slug'  => 'advanced',
				'title' => __( 'Advanced', 'meta-box-builder' ),
			],
			[
				'slug'  => 'html5',
				'title' => __( 'HTML5', 'meta-box-builder' ),
			],
			[
				'slug'  => 'wordpress',
				'title' => __( 'WordPress', 'meta-box-builder' ),
			],
			[
				'slug'  => 'upload',
				'title' => __( 'Upload', 'meta-box-builder' ),
			],
			[
				'slug'  => 'layout',
				'title' => __( 'Layout', 'meta-box-builder' ),
			],
		];
	}

	public static function get_blocks(): array {
		$registry = WP_Block_Type_Registry::get_instance();
		$blocks   = $registry->get_all_registered();
		$options  = [];

		foreach ( $blocks as $name => $block ) {
			$title     = empty( $block->title ) ? $name : $block->title;
			$options[] = [
				'value' => $name,
				'label' => sprintf( '%s (%s)', $title, $name ),
			];
		}

		usort(
			$options,
			static function ( $a, $b ) {
				return strcmp( strtolower( $a['label'] ), strtolower( $b['label'] ) );
			}
		);

		return $options;
	}
}

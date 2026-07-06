<?php
namespace MBB;

use MBB\RestApi\Save;
use MBB\JsonService;
use MBB\LocalJson;
use MBBParser\Unparsers\MetaBox;
use MBBParser\Unparsers\Field as FieldUnparser;
use WP_REST_Request;
use WP_Error;

class Abilities {
	private const CATEGORY = 'meta-box';

	public function __construct() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}
		add_action( 'wp_abilities_api_categories_init', [ $this, 'register_category' ] );
		add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );
	}

	public function register_category(): void {
		if ( wp_has_ability_category( self::CATEGORY ) ) {
			return;
		}

		wp_register_ability_category( self::CATEGORY, [
			'label'       => __( 'Meta Box', 'meta-box-builder' ),
			'description' => __( 'Abilities for Meta Box data (post types, taxonomies, fields, etc.).', 'mb-custom-post-type' ),
		] );
	}

	public function register_abilities(): void {
		$this->register_list_field_groups_ability();
		$this->register_get_field_group_ability();
		$this->register_create_field_group_ability();
		$this->register_update_field_group_ability();
		$this->register_delete_field_group_ability();
		$this->register_list_fields_ability();
		$this->register_get_field_ability();
		$this->register_create_field_ability();
		$this->register_update_field_ability();
		$this->register_delete_field_ability();
		$this->register_move_field_ability();
	}

	private function register_list_field_groups_ability(): void {
		wp_register_ability( 'meta-box/list-field-groups', [
			'label'               => __( 'List field groups', 'meta-box-builder' ),
			'description'         => __( 'List all custom field groups stored in the database.', 'meta-box-builder' ),
			'category'            => self::CATEGORY,
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'search'   => [
						'type'        => 'string',
						'description' => __( 'Search field group titles.', 'meta-box-builder' ),
					],
					'status'   => [
						'type'        => 'string',
						'description' => __( 'Post status to filter by.', 'meta-box-builder' ),
						'enum'        => [ 'publish', 'draft', 'pending', 'trash', 'any' ],
						'default'     => 'publish',
					],
					'per_page' => [
						'type'    => 'integer',
						'default' => 20,
					],
					'page'     => [
						'type'    => 'integer',
						'default' => 1,
					],
				],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'field_groups' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'id'          => [ 'type' => 'integer' ],
								'title'       => [ 'type' => 'string' ],
								'slug'        => [ 'type' => 'string' ],
								'status'      => [ 'type' => 'string' ],
								'field_count' => [ 'type' => 'integer' ],
								'created_at'  => [ 'type' => 'string' ],
								'modified_at' => [ 'type' => 'string' ],
							],
						],
					],
					'total'        => [ 'type' => 'integer' ],
					'total_pages'  => [ 'type' => 'integer' ],
				],
			],
			'execute_callback'    => [ $this, 'list_field_groups' ],
			'permission_callback' => [ $this, 'permission_callback' ],
			'meta'                => [
				'annotations' => [
					'readonly'    => true,
					'destructive' => false,
					'idempotent'  => true,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
		] );
	}

	private function register_get_field_group_ability(): void {
		wp_register_ability( 'meta-box/get-field-group', [
			'label'               => __( 'Get field group', 'meta-box-builder' ),
			'description'         => __( 'Get a field group with all its fields and settings.', 'meta-box-builder' ),
			'category'            => self::CATEGORY,
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'id' => [
						'type'        => 'integer',
						'description' => __( 'The field group post ID.', 'meta-box-builder' ),
					],
				],
				'required'   => [ 'id' ],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'id'          => [ 'type' => 'integer' ],
					'title'       => [ 'type' => 'string' ],
					'slug'        => [ 'type' => 'string' ],
					'status'      => [ 'type' => 'string' ],
					'field_count' => [ 'type' => 'integer' ],
					'fields'      => [
						'type'  => 'array',
						'items' => [ 'type' => 'object' ],
					],
					'settings'    => [ 'type' => 'object' ],
					'created_at'  => [ 'type' => 'string' ],
					'modified_at' => [ 'type' => 'string' ],
				],
			],
			'execute_callback'    => [ $this, 'get_field_group' ],
			'permission_callback' => [ $this, 'permission_callback' ],
			'meta'                => [
				'annotations' => [
					'readonly'    => true,
					'destructive' => false,
					'idempotent'  => true,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
		] );
	}

	private function register_create_field_group_ability(): void {
		wp_register_ability( 'meta-box/create-field-group', [
			'label'               => __( 'Create field group', 'meta-box-builder' ),
			'description'         => __( 'Create a new field group.', 'meta-box-builder' ),
			'category'            => self::CATEGORY,
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'title'    => [
						'type'        => 'string',
						'description' => __( 'Field group title.', 'meta-box-builder' ),
					],
					'slug'     => [
						'type'        => 'string',
						'description' => __( 'Field group slug.', 'meta-box-builder' ),
					],
					'status'   => [
						'type'        => 'string',
						'description' => __( 'Post status.', 'meta-box-builder' ),
						'enum'        => [ 'publish', 'draft' ],
					],
					'fields'   => [
						'type'        => 'array',
						'description' => __( 'Array of field definitions. See https://github.com/wpmetabox/schema/blob/main/field-group.json for full schema.', 'meta-box-builder' ),
						'items'       => [
							'type'                 => 'object',
							'additionalProperties' => true,
						],
					],
					'settings' => [
						'type' => 'object',
					],
				],
				'required'   => [ 'title' ],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success' => [ 'type' => 'boolean' ],
					'message' => [ 'type' => 'string' ],
					'id'      => [ 'type' => 'integer' ],
				],
			],
			'execute_callback'    => [ $this, 'create_field_group' ],
			'permission_callback' => [ $this, 'permission_callback' ],
			'meta'                => [
				'annotations' => [
					'readonly'    => false,
					'destructive' => false,
					'idempotent'  => false,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
		] );
	}

	private function register_update_field_group_ability(): void {
		wp_register_ability( 'meta-box/update-field-group', [
			'label'               => __( 'Update field group', 'meta-box-builder' ),
			'description'         => __( 'Update an existing field group.', 'meta-box-builder' ),
			'category'            => self::CATEGORY,
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'id'       => [
						'type'        => 'integer',
						'description' => __( 'The field group post ID.', 'meta-box-builder' ),
					],
					'title'    => [
						'type'        => 'string',
						'description' => __( 'Field group title.', 'meta-box-builder' ),
					],
					'slug'     => [
						'type'        => 'string',
						'description' => __( 'Field group slug.', 'meta-box-builder' ),
					],
					'status'   => [
						'type'        => 'string',
						'description' => __( 'Post status.', 'meta-box-builder' ),
						'enum'        => [ 'publish', 'draft' ],
					],
					'fields'   => [
						'type'        => 'array',
						'description' => __( 'Array of field definitions. See https://github.com/wpmetabox/schema/blob/main/field-group.json for full schema.', 'meta-box-builder' ),
						'items'       => [
							'type'                 => 'object',
							'additionalProperties' => true,
						],
					],
					'settings' => [
						'type' => 'object',
					],
				],
				'required'   => [ 'id' ],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success' => [ 'type' => 'boolean' ],
					'message' => [ 'type' => 'string' ],
					'id'      => [ 'type' => 'integer' ],
				],
			],
			'execute_callback'    => [ $this, 'update_field_group' ],
			'permission_callback' => [ $this, 'permission_callback' ],
			'meta'                => [
				'annotations' => [
					'readonly'    => false,
					'destructive' => false,
					'idempotent'  => true,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
		] );
	}

	private function register_delete_field_group_ability(): void {
		wp_register_ability( 'meta-box/delete-field-group', [
			'label'               => __( 'Delete field group', 'meta-box-builder' ),
			'description'         => __( 'Delete a field group. Supports trashing or permanent deletion.', 'meta-box-builder' ),
			'category'            => self::CATEGORY,
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'id'    => [
						'type' => 'integer',
					],
					'force' => [
						'type'    => 'boolean',
						'default' => false,
					],
				],
				'required'   => [ 'id' ],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success' => [ 'type' => 'boolean' ],
					'message' => [ 'type' => 'string' ],
				],
			],
			'execute_callback'    => [ $this, 'delete_field_group' ],
			'permission_callback' => [ $this, 'permission_callback' ],
			'meta'                => [
				'annotations' => [
					'readonly'    => false,
					'destructive' => true,
					'idempotent'  => false,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
		] );
	}

	private function register_list_fields_ability(): void {
		wp_register_ability( 'meta-box/list-fields', [
			'label'               => __( 'List fields', 'meta-box-builder' ),
			'description'         => __( 'List all fields within a specific field group.', 'meta-box-builder' ),
			'category'            => self::CATEGORY,
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'field_group_id' => [
						'type' => 'integer',
					],
				],
				'required'   => [ 'field_group_id' ],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'fields'   => [
						'type'  => 'array',
						'items' => [ 'type' => 'object' ],
					],
					'settings' => [ 'type' => 'object' ],
				],
			],
			'execute_callback'    => [ $this, 'list_fields' ],
			'permission_callback' => [ $this, 'permission_callback' ],
			'meta'                => [
				'annotations' => [
					'readonly'    => true,
					'destructive' => false,
					'idempotent'  => true,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
		] );
	}

	private function register_get_field_ability(): void {
		wp_register_ability( 'meta-box/get-field', [
			'label'               => __( 'Get field', 'meta-box-builder' ),
			'description'         => __( 'Get a single field definition from a field group by its field ID.', 'meta-box-builder' ),
			'category'            => self::CATEGORY,
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'field_group_id' => [
						'type' => 'integer',
					],
					'field_id'       => [
						'type'        => 'string',
						'description' => __( 'The field ID (without prefix).', 'meta-box-builder' ),
					],
				],
				'required'   => [ 'field_group_id', 'field_id' ],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'field' => [ 'type' => 'object' ],
				],
			],
			'execute_callback'    => [ $this, 'get_field' ],
			'permission_callback' => [ $this, 'permission_callback' ],
			'meta'                => [
				'annotations' => [
					'readonly'    => true,
					'destructive' => false,
					'idempotent'  => true,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
		] );
	}

	private function register_create_field_ability(): void {
		wp_register_ability( 'meta-box/create-field', [
			'label'               => __( 'Create field', 'meta-box-builder' ),
			'description'         => __( 'Add a new field to a field group.', 'meta-box-builder' ),
			'category'            => self::CATEGORY,
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'field_group_id' => [
						'type' => 'integer',
					],
					'field'          => [
						'type'                 => 'object',
						'description'          => __( 'Field definition. Must include id, type. Accepts all Meta Box field properties (e.g. options, std, placeholder, required, desc, clone, etc.). See https://github.com/wpmetabox/schema/blob/main/field-group.json for full schema.', 'meta-box-builder' ),
						'properties'           => [
							'id'   => [ 'type' => 'string' ],
							'type' => [ 'type' => 'string' ],
							'name' => [ 'type' => 'string' ],
						],
						'additionalProperties' => true,
					],
				],
				'required'   => [ 'field_group_id', 'field' ],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success' => [ 'type' => 'boolean' ],
					'message' => [ 'type' => 'string' ],
				],
			],
			'execute_callback'    => [ $this, 'create_field' ],
			'permission_callback' => [ $this, 'permission_callback' ],
			'meta'                => [
				'annotations' => [
					'readonly'    => false,
					'destructive' => false,
					'idempotent'  => false,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
		] );
	}

	private function register_update_field_ability(): void {
		wp_register_ability( 'meta-box/update-field', [
			'label'               => __( 'Update field', 'meta-box-builder' ),
			'description'         => __( 'Update an existing field in a field group. Use field_id to identify the field to update. The field.id in the field object can be changed to rename the field.', 'meta-box-builder' ),
			'category'            => self::CATEGORY,
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'field_group_id' => [
						'type' => 'integer',
					],
					'field_id'       => [
						'type'        => 'string',
						'description' => __( 'The current field ID to identify which field to update.', 'meta-box-builder' ),
					],
					'field'          => [
						'type'                 => 'object',
						'description'          => __( 'Field definition. Accepts all Meta Box field properties. The id property can be changed to rename the field. See https://github.com/wpmetabox/schema/blob/main/field-group.json for full schema.', 'meta-box-builder' ),
						'properties'           => [
							'id'   => [ 'type' => 'string' ],
							'type' => [ 'type' => 'string' ],
							'name' => [ 'type' => 'string' ],
						],
						'additionalProperties' => true,
					],
				],
				'required'   => [ 'field_group_id', 'field_id', 'field' ],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success' => [ 'type' => 'boolean' ],
					'message' => [ 'type' => 'string' ],
				],
			],
			'execute_callback'    => [ $this, 'update_field' ],
			'permission_callback' => [ $this, 'permission_callback' ],
			'meta'                => [
				'annotations' => [
					'readonly'    => false,
					'destructive' => false,
					'idempotent'  => true,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
		] );
	}

	private function register_delete_field_ability(): void {
		wp_register_ability( 'meta-box/delete-field', [
			'label'               => __( 'Delete field', 'meta-box-builder' ),
			'description'         => __( 'Remove a field from a field group by its field ID.', 'meta-box-builder' ),
			'category'            => self::CATEGORY,
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'field_group_id' => [
						'type' => 'integer',
					],
					'field_id'       => [
						'type' => 'string',
					],
				],
				'required'   => [ 'field_group_id', 'field_id' ],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success' => [ 'type' => 'boolean' ],
					'message' => [ 'type' => 'string' ],
				],
			],
			'execute_callback'    => [ $this, 'delete_field' ],
			'permission_callback' => [ $this, 'permission_callback' ],
			'meta'                => [
				'annotations' => [
					'readonly'    => false,
					'destructive' => true,
					'idempotent'  => false,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
		] );
	}

	private function register_move_field_ability(): void {
		wp_register_ability( 'meta-box/move-field', [
			'label'               => __( 'Move field', 'meta-box-builder' ),
			'description'         => __( 'Move a field to a new position within a field group. Specify before or after a reference field ID.', 'meta-box-builder' ),
			'category'            => self::CATEGORY,
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'field_group_id' => [
						'type' => 'integer',
					],
					'field_id'       => [
						'type'        => 'string',
						'description' => __( 'The field ID to move.', 'meta-box-builder' ),
					],
					'before'         => [
						'type'        => 'string',
						'description' => __( 'Move before this field ID.', 'meta-box-builder' ),
					],
					'after'          => [
						'type'        => 'string',
						'description' => __( 'Move after this field ID.', 'meta-box-builder' ),
					],
				],
				'required'   => [ 'field_group_id', 'field_id' ],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => [
					'success' => [ 'type' => 'boolean' ],
					'message' => [ 'type' => 'string' ],
				],
			],
			'execute_callback'    => [ $this, 'move_field' ],
			'permission_callback' => [ $this, 'permission_callback' ],
			'meta'                => [
				'annotations' => [
					'readonly'    => false,
					'destructive' => false,
					'idempotent'  => true,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
		] );
	}

	/**
	 * Execute callbacks.
	 */
	public function list_field_groups( array $input ): array {
		$search   = $input['search'] ?? '';
		$status   = $input['status'] ?? 'publish';
		$per_page = max( 1, min( 100, (int) ( $input['per_page'] ?? 20 ) ) );
		$page     = max( 1, (int) ( $input['page'] ?? 1 ) );

		$args = [
			'post_type'              => 'meta-box',
			'post_status'            => $status,
			'posts_per_page'         => $per_page,
			'paged'                  => $page,
			'no_found_rows'          => false,
			'update_post_term_cache' => false,
		];

		if ( $search ) {
			$args['s'] = $search;
		}

		$query  = new \WP_Query( $args );
		$groups = [];

		foreach ( $query->posts as $post ) {
			if ( ! $this->is_database_only( $post ) ) {
				continue;
			}
			$fields   = get_post_meta( $post->ID, 'fields', true ) ?: [];
			$groups[] = $this->build_field_group_summary( $post, $fields );
		}

		return [
			'field_groups' => $groups,
			'total'        => $query->found_posts,
			'total_pages'  => $query->max_num_pages,
		];
	}

	/**
	 * Get a field group with all its fields and settings.
	 *
	 * @param array{id: int} $input The input arguments.
	 * @return array|WP_Error
	 */
	public function get_field_group( array $input ) {
		$post = $this->get_field_group_post( $input['id'] );
		if ( ! $post ) {
			return new WP_Error( 'not_found', __( 'Field group not found.', 'meta-box-builder' ) );
		}

		return $this->build_field_group_response( $post );
	}

	public function create_field_group( array $input ): array {
		$unparsed = $this->unparse_input( $input );
		$title    = $unparsed['title'];
		$slug     = $unparsed['slug'];
		$fields   = $unparsed['fields'];
		$settings = $unparsed['settings'];
		$status   = $unparsed['status'] ?? 'publish';

		$post_id = wp_insert_post( [
			'post_type'   => 'meta-box',
			'post_title'  => $title,
			'post_name'   => $slug ? $slug : sanitize_title( $title ),
			'post_status' => $status,
		] );

		if ( is_wp_error( $post_id ) ) {
			return $this->error( $post_id->get_error_message(), [ 'id' => 0 ] );
		}

		$result = $this->save_field_group( $post_id, $title, $slug, $fields, $settings );

		return array_merge( $result, [ 'id' => $post_id ] );
	}

	public function update_field_group( array $input ): array {
		$post_id = $input['id'];
		$post    = $this->get_field_group_post( $post_id );
		if ( ! $post ) {
			return $this->error( __( 'Field group not found.', 'meta-box-builder' ) );
		}

		$existing_fields   = get_post_meta( $post->ID, 'fields', true ) ?: [];
		$existing_settings = get_post_meta( $post->ID, 'settings', true ) ?: [];

		$title      = empty( $input['title'] ) ? $post->post_title : $input['title'];
		$slug       = empty( $input['slug'] ) ? $post->post_name : $input['slug'];
		$has_status = array_key_exists( 'status', $input );
		$status     = $input['status'] ?? null;

		$unparsed = $this->unparse_input( $input );
		$fields   = array_key_exists( 'fields', $input )
			? $this->merge_fields( $existing_fields, $unparsed['fields'] )
			: $existing_fields;
		$settings = array_key_exists( 'settings', $input )
			? $this->merge_settings( $existing_settings, $unparsed['settings'] )
			: $existing_settings;

		$result = $this->save_field_group( $post_id, $title, $slug, $fields, $settings );

		if ( ! empty( $result['success'] ) && $has_status && $status !== $post->post_status ) {
			wp_update_post( [
				'ID'          => $post_id,
				'post_status' => $status,
			] );
		}

		return array_merge( $result, [ 'id' => $post_id ] );
	}

	public function delete_field_group( array $input ): array {
		$post = $this->get_field_group_post( $input['id'] );
		if ( ! $post ) {
			return $this->error( __( 'Field group not found.', 'meta-box-builder' ) );
		}

		$force = $input['force'] ?? false;

		if ( ! $force && $post->post_status === 'trash' ) {
			return $this->error( __( 'Field group is already in trash.', 'meta-box-builder' ) );
		}

		$result = wp_delete_post( $post->ID, $force );

		if ( ! $result ) {
			return $this->error( __( 'Failed to delete field group.', 'meta-box-builder' ) );
		}

		return $this->success( $force
			? __( 'Field group permanently deleted.', 'meta-box-builder' )
			: __( 'Field group moved to trash.', 'meta-box-builder' )
		);
	}

	/**
	 * List all fields within a specific field group.
	 *
	 * @param array{field_group_id: int} $input The input arguments.
	 * @return array|WP_Error
	 */
	public function list_fields( array $input ) {
		$post = $this->get_field_group_post( $input['field_group_id'] );
		if ( ! $post ) {
			return new WP_Error( 'not_found', __( 'Field group not found.', 'meta-box-builder' ) );
		}

		$fields   = get_post_meta( $post->ID, 'fields', true ) ?: [];
		$settings = get_post_meta( $post->ID, 'settings', true ) ?: [];

		return [
			'fields'   => array_values( $fields ),
			'settings' => $settings,
		];
	}

	/**
	 * Get a single field definition from a field group by its field ID.
	 *
	 * @param array{field_group_id: int, field_id: string} $input The input arguments.
	 * @return array|WP_Error
	 */
	public function get_field( array $input ) {
		$post = $this->get_field_group_post( $input['field_group_id'] );
		if ( ! $post ) {
			return new WP_Error( 'not_found', __( 'Field group not found.', 'meta-box-builder' ) );
		}

		$fields = get_post_meta( $post->ID, 'fields', true ) ?: [];
		$index  = $this->find_field_index( $fields, $input['field_id'] );

		if ( ! $index ) {
			return new WP_Error( 'not_found', __( 'Field not found.', 'meta-box-builder' ) );
		}

		return [ 'field' => $fields[ $index ] ];
	}

	public function create_field( array $input ): array {
		$post = $this->get_field_group_post( $input['field_group_id'] );
		if ( ! $post ) {
			return $this->error( __( 'Field group not found.', 'meta-box-builder' ) );
		}

		$field_data = $this->unparse_field( $input['field'] );
		if ( empty( $field_data['id'] ) || empty( $field_data['type'] ) ) {
			return $this->error( __( 'Field must have id and type.', 'meta-box-builder' ) );
		}

		$fields   = get_post_meta( $post->ID, 'fields', true ) ?: [];
		$settings = get_post_meta( $post->ID, 'settings', true ) ?: [];

		$existing_index = $this->find_field_index( $fields, $field_data['id'] );
		if ( $existing_index ) {
			return $this->error( __( 'Field with this ID already exists. Use update field instead.', 'meta-box-builder' ) );
		}

		$field_data = $this->ensure_unique_field_id( $field_data, $fields );
		$fields[]   = $field_data;
		Save::parse( $post, $fields, $settings );

		return $this->success( __( 'Field added successfully.', 'meta-box-builder' ) );
	}

	public function update_field( array $input ): array {
		$post = $this->get_field_group_post( $input['field_group_id'] );
		if ( ! $post ) {
			return $this->error( __( 'Field group not found.', 'meta-box-builder' ) );
		}

		$field_data = $this->unparse_field( $input['field'] );
		$field_id   = $input['field_id'];
		if ( empty( $input['field']['id'] ) ) {
			$field_data['id'] = $field_id;
		}

		$fields   = get_post_meta( $post->ID, 'fields', true ) ?: [];
		$settings = get_post_meta( $post->ID, 'settings', true ) ?: [];

		$found_index = $this->find_field_index( $fields, $field_id );

		if ( ! $found_index ) {
			return $this->error( __( 'Field not found. Use create field instead.', 'meta-box-builder' ) );
		}

		$new_id = $field_data['id'];
		if ( $new_id !== $field_id ) {
			$collision_index = $this->find_field_index( $fields, $new_id );
			if ( $collision_index && $collision_index !== $found_index ) {
				return $this->error( sprintf(
					/* translators: %s: The new field ID. */
					__( 'Another field with ID %s already exists.', 'meta-box-builder' ),
					$new_id
				) );
			}
		}

		$replaced = array_replace( $fields[ $found_index ], $field_data );
		if ( isset( $field_data['fields'] ) ) {
			$existing_sub       = $fields[ $found_index ]['fields'] ?? [];
			$replaced['fields'] = $this->merge_fields( $existing_sub, $field_data['fields'] );
		}
		$fields[ $found_index ] = $this->ensure_unique_field_id( $replaced, $fields, $found_index );

		Save::parse( $post, $fields, $settings );

		return $this->success( __( 'Field updated successfully.', 'meta-box-builder' ) );
	}

	public function delete_field( array $input ): array {
		$post = $this->get_field_group_post( $input['field_group_id'] );
		if ( ! $post ) {
			return $this->error( __( 'Field group not found.', 'meta-box-builder' ) );
		}

		$fields   = get_post_meta( $post->ID, 'fields', true ) ?: [];
		$settings = get_post_meta( $post->ID, 'settings', true ) ?: [];

		$index = $this->find_field_index( $fields, $input['field_id'] );
		if ( ! $index ) {
			return $this->error( __( 'Field not found.', 'meta-box-builder' ) );
		}

		array_splice( $fields, $index, 1 );
		Save::parse( $post, $fields, $settings );

		return $this->success( __( 'Field deleted successfully.', 'meta-box-builder' ) );
	}

	public function move_field( array $input ): array {
		$post = $this->get_field_group_post( $input['field_group_id'] );
		if ( ! $post ) {
			return $this->error( __( 'Field group not found.', 'meta-box-builder' ) );
		}

		$fields   = get_post_meta( $post->ID, 'fields', true ) ?: [];
		$settings = get_post_meta( $post->ID, 'settings', true ) ?: [];

		$field_index = $this->find_field_index( $fields, $input['field_id'] );
		if ( ! $field_index ) {
			return $this->error( __( 'Field not found.', 'meta-box-builder' ) );
		}

		if ( isset( $input['before'] ) && isset( $input['after'] ) ) {
			return $this->error( __( 'Only one of before or after may be specified.', 'meta-box-builder' ) );
		}

		$target_id = $input['before'] ?? $input['after'] ?? null;

		if ( $target_id !== null && $target_id === $input['field_id'] ) {
			return $this->error( __( 'Cannot move a field relative to itself.', 'meta-box-builder' ) );
		}

		if ( $target_id !== null ) {
			$target_index = $this->find_field_index( $fields, $target_id );
			if ( ! $target_index ) {
				return $this->error( sprintf(
					/* translators: %s: The field ID to move before/after. */
					__( 'Target field %s not found.', 'meta-box-builder' ),
					$target_id
				) );
			}
			$position = isset( $input['before'] ) ? $target_index : $target_index + 1;
		} else {
			$position = count( $fields );
		}

		// Check if the field is already at the target position (no-op).
		$target_index = $position;
		if ( $target_index > $field_index ) {
			--$target_index;
		}
		if ( $field_index === $target_index ) {
			return $this->success( __( 'Field is already at the target position.', 'meta-box-builder' ) );
		}

		$field_to_move = $fields[ $field_index ];
		array_splice( $fields, $field_index, 1 );
		array_splice( $fields, $target_index, 0, [ $field_to_move ] );

		Save::parse( $post, $fields, $settings );

		return $this->success( __( 'Field moved successfully.', 'meta-box-builder' ) );
	}

	/**
	 * Helpers.
	 */
	private function get_field_group_post( int $id ): ?\WP_Post {
		$post = get_post( $id );

		return $post && $post->post_type === 'meta-box' ? $post : null;
	}

	private function find_field_index( array $fields, string $field_id ) {
		foreach ( $fields as $index => $field ) {
			$id = $field['id'] ?? $field['_id'] ?? '';
			if ( $id === $field_id ) {
				return $index;
			}
		}

		return null;
	}

	private function ensure_unique_field_id( array $field_data, array $existing_fields, ?int $exclude_index = null ): array {
		$existing_ids = [];
		foreach ( $existing_fields as $index => $field ) {
			if ( $exclude_index !== null && $index === $exclude_index ) {
				continue;
			}
			if ( ! empty( $field['_id'] ) ) {
				$existing_ids[] = $field['_id'];
			}
		}

		while ( in_array( $field_data['_id'] ?? '', $existing_ids, true ) ) {
			$field_data['_id'] = ( $field_data['type'] ?? 'field' ) . '_' . uniqid();
		}

		return $field_data;
	}

	private function save_field_group( int $post_id, string $title, string $slug, array $fields, array $settings ): array {
		$request = new WP_REST_Request( 'POST', '/mbb/save' );
		$request->set_param( 'post_id', $post_id );
		$request->set_param( 'post_title', $title );
		$request->set_param( 'post_name', $slug );
		$request->set_param( 'fields', $fields );
		$request->set_param( 'settings', $settings );

		return ( new Save() )->save( $request );
	}

	private function success( string $message, array $extra = [] ): array {
		return array_merge( [
			'success' => true,
			'message' => $message,
		], $extra );
	}

	private function error( string $message, array $extra = [] ): array {
		return array_merge( [
			'success' => false,
			'message' => $message,
		], $extra );
	}

	public function permission_callback(): bool {
		return current_user_can( 'manage_options' );
	}

	private function is_database_only( \WP_Post $post ): bool {
		if ( ! LocalJson::is_enabled() ) {
			return true;
		}

		$json = JsonService::get_json( [
			'id'        => $post->post_name,
			'post_type' => $post->post_type,
		] );

		return empty( $json );
	}

	/**
	 * Build a lightweight summary array for a field group.
	 *
	 * @param \WP_Post $post   The field group post object.
	 * @param array    $fields The field definitions.
	 * @return array
	 */
	private function build_field_group_summary( \WP_Post $post, array $fields = [] ): array {
		return [
			'id'          => $post->ID,
			'title'       => $post->post_title,
			'slug'        => $post->post_name,
			'status'      => $post->post_status,
			'field_count' => count( $fields ),
			'created_at'  => $post->post_date,
			'modified_at' => $post->post_modified,
		];
	}

	private function build_field_group_response( \WP_Post $post ): array {
		$fields  = get_post_meta( $post->ID, 'fields', true ) ?: [];
		$summary = $this->build_field_group_summary( $post, $fields );

		return array_merge( $summary, [
			'fields'   => array_values( $fields ),
			'settings' => get_post_meta( $post->ID, 'settings', true ) ?: [],
		] );
	}

	/**
	 * Unparse input data from parsed format to builder format.
	 *
	 * Accepts input in the schema format (https://github.com/wpmetabox/schema/blob/main/field-group.json)
	 * and converts it to the builder format used internally.
	 */
	private function unparse_input( array $input ): array {
		$unparser = new MetaBox( $input );
		$unparser->unparse();

		$settings = $unparser->get_settings();

		return [
			'title'    => $settings['post_title'] ?? $input['title'] ?? '',
			'slug'     => $settings['post_name'] ?? $input['slug'] ?? '',
			'fields'   => $settings['fields'] ?? $input['fields'] ?? [],
			'settings' => $settings['settings'] ?? $input['settings'] ?? [],
			'status'   => $settings['post_status'] ?? $input['status'] ?? 'publish',
		];
	}

	/**
	 * Unparse a single field from parsed format to builder format.
	 */
	private function unparse_field( array $field ): array {
		$unparser = new FieldUnparser( $field );
		$unparser->unparse();

		return $unparser->get_settings();
	}

	/**
	 * Merge new settings into existing settings recursively.
	 *
	 * Existing keys not in the new settings are preserved.
	 * Nested arrays are merged recursively.
	 *
	 * @param array $existing The existing settings.
	 * @param array $incoming The new settings to merge.
	 * @return array
	 */
	private function merge_settings( array $existing, array $incoming ): array {
		foreach ( $incoming as $key => $value ) {
			if ( is_array( $value ) && isset( $existing[ $key ] ) && is_array( $existing[ $key ] ) ) {
				$existing[ $key ] = $this->merge_settings( $existing[ $key ], $value );
			} else {
				$existing[ $key ] = $value;
			}
		}

		return $existing;
	}

	/**
	 * Merge new fields into existing fields by ID.
	 *
	 * Existing fields not in the new list are preserved.
	 * Fields with matching IDs are updated.
	 * New fields are appended.
	 * For group-type fields, sub-fields are merged recursively.
	 *
	 * @param array $existing The existing fields.
	 * @param array $incoming The new fields to merge.
	 * @return array
	 */
	private function merge_fields( array $existing, array $incoming ): array {
		if ( empty( $incoming ) ) {
			return $existing;
		}

		$new_by_id = [];
		foreach ( $incoming as $field ) {
			$id = $field['id'] ?? $field['_id'] ?? '';
			if ( $id ) {
				$new_by_id[ $id ] = $field;
			}
		}

		$merged = [];
		foreach ( $existing as $field ) {
			$id = $field['id'] ?? $field['_id'] ?? '';
			if ( isset( $new_by_id[ $id ] ) ) {
				$merged_field = $new_by_id[ $id ];
				if ( ( $merged_field['type'] ?? '' ) === 'group' ) {
					$existing_sub = $field['fields'] ?? [];
					if ( isset( $merged_field['fields'] ) ) {
						$merged_field['fields'] = $this->merge_fields( $existing_sub, $merged_field['fields'] );
					} else {
						$merged_field['fields'] = $existing_sub;
					}
				}
				$merged[] = $merged_field;
				unset( $new_by_id[ $id ] );
			} else {
				$merged[] = $field;
			}
		}

		// Append remaining new fields.
		foreach ( $new_by_id as $field ) {
			$merged[] = $field;
		}

		return $merged;
	}
}

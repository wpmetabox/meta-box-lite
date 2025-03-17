<?php
namespace MetaBox\Pods\Processors;

use WP_Query;
use MetaBox\Support\Data;
use MBBParser\Parsers\MetaBox;

class FieldGroups extends Base {
	private $post_id;
	private $settings = [];
	private $fields   = [];

	protected function get_items() {
		// Process all field groups at once.
		if ( ! empty( $_SESSION['processed'] ) ) {
			return [];
		}

		$query = new WP_Query( [
			'post_type'              => '_pods_group',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );

		return $query->posts;
	}

	protected function migrate_item() {
		$this->post_id  = null;
		$this->settings = [];
		$this->fields   = [];
		$this->create_post();
		$this->migrate_settings();
		$this->migrate_fields();
		$this->save_id();

		$data = [
			'post_name'  => $this->item->post_name,
			'post_title' => $this->item->post_title,
			'fields'     => $this->fields,
			'settings'   => $this->settings,
		];

		$parser = new MetaBox( $data );
		$parser->parse();
		update_post_meta( $this->post_id, 'meta_box', $parser->get_settings() );
	}

	private function create_post() {
		$data = [
			'post_title'        => $this->item->post_title,
			'post_type'         => 'meta-box',
			'post_status'       => $this->item->post_status,
			'post_name'         => "pods_{$this->item->post_name}",
			'post_content'      => $this->item->post_content,
			'post_date'         => $this->item->post_date,
			'post_date_gmt'     => $this->item->post_date_gmt,
			'post_modified'     => $this->item->post_modified,
			'post_modified_gmt' => $this->item->post_modified_gmt,
		];

		$post_id = get_post_meta( $this->item->ID, 'meta_box_id', true );
		if ( $post_id ) {
			$this->post_id = $data['ID'] = $post_id;
			wp_update_post( $data );
		} else {
			$this->post_id = wp_insert_post( $data );
		}

		update_post_meta( $this->item->ID, 'meta_box_id', $this->post_id );
		$this->delete_post( $this->item->ID );
	}

	private function migrate_settings() {
		$this->migrate_location();

		update_post_meta( $this->post_id, 'settings', $this->settings );
	}

	private function migrate_location() {
		$post_parent = $this->item->post_parent;
		$object_type = get_post_meta( $post_parent, 'type', true );
		$context     = get_post_meta( $post_parent, 'meta_box_context', true ) ?: 'advanced';
		$priority    = get_post_meta( $post_parent, 'meta_box_priority', true ) ?: 'default';

		switch ( $object_type ) {
			case 'post_type':
				$type                         = 'post';
				$this->settings['post_types'] = [ get_post( $post_parent )->post_name ];
				$this->settings['context']    = ( $context == 'advanced' ) ? 'normal' : $context;
				$this->settings['priority']   = ( $priority == 'default' ) ? 'high' : $priority;
				break;
			case 'taxonomy':
				$type                         = 'term';
				$this->settings['taxonomies'] = [ get_post( $post_parent )->post_name ];
				break;
			case 'settings':
				$type                             = 'setting';
				$this->settings['settings_pages'] = [ get_post( $post_parent )->post_name ];
				break;
			default:
				$type = $object_type;
				break;
		}
		$roles = get_post_meta( $this->item->ID, 'roles_allowed', true );
		if ( empty( $roles ) ) {
			$this->settings['object_type'] = $type;
			return;
		}
		if ( is_string( $roles ) ) {
			$id                                = uniqid();
			$this->settings['include_exclude'] = [
				'type'     => 'include',
				'relation' => 'OR',
				'rules'    => [
					$id => [
						'id'    => $id,
						'name'  => 'user_role',
						'value' => [ 'administrator' ],
						'label' => [ 'Administrator' ],
					],
				],
			];
			$this->settings['object_type']     = $type;
			return;
		}
		$role_names                        = wp_roles()->get_names();
		$role_names                        = array_intersect_key( $role_names, array_flip( $roles ) );
		$labels                            = array_values( $role_names );
		$id                                = uniqid();
		$this->settings['include_exclude'] = [
			'type'     => 'include',
			'relation' => 'OR',
			'rules'    => [
				$id => [
					'id'    => $id,
					'name'  => 'user_role',
					'value' => $roles,
					'label' => $labels,
				],
			],
		];

		$this->settings['object_type'] = $type;
	}

	private function migrate_fields() {
		$fields       = new FieldGroups\Fields( $this->item->post_parent, $this->item->ID );
		$this->fields = $fields->migrate_fields();

		update_post_meta( $this->post_id, 'fields', $this->fields );
	}

	private function save_id() {
		$object_type = $this->settings['object_type'];

		if ( empty( $_SESSION['field_groups'] ) ) {
			$_SESSION['field_groups'] = [];
		}
		if ( empty( $_SESSION['field_groups'][ $object_type ] ) ) {
			$_SESSION['field_groups'][ $object_type ] = [];
		}
		$_SESSION['field_groups'][ $object_type ][] = $this->item->ID;
	}
}

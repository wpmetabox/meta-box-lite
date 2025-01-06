<?php
namespace MetaBox\ACF\Processors;

use WP_Query;
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
			'post_type'              => 'acf-field-group',
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

		$this->disable_post();
	}

	private function create_post() {
		$data = [
			'post_title'        => $this->item->post_title,
			'post_type'         => 'meta-box',
			'post_status'       => $this->item->post_status === 'acf-disabled' ? 'draft' : 'publish',
			'post_name'         => "acf_{$this->item->post_name}",
			'post_content'      => $this->item->post_content,
			'post_date'         => $this->item->post_date,
			'post_date_gmt'     => $this->item->post_date_gmt,
			'post_modified'     => $this->item->post_modified,
			'post_modified_gmt' => $this->item->post_modified_gmt,
		];

		$post_id = get_post_meta( $this->item->ID, 'meta_box_id', true );
		if ( $post_id ) {
			$data['ID']    = $post_id;
			$this->post_id = $post_id;
			wp_update_post( $data );
		} else {
			$this->post_id = wp_insert_post( $data );
		}

		update_post_meta( $this->item->ID, 'meta_box_id', $this->post_id );
	}

	private function disable_post() {
		$data = [
			'ID'          => $this->item->ID,
			'post_status' => 'acf-disabled',
		];
		wp_update_post( $data );
	}

	private function delete_post() {
		wp_delete_post( $this->item->ID );
	}

	private function migrate_settings() {
		$this->settings = unserialize( $this->item->post_content ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize

		// Context.
		if ( ! empty( $this->settings['position'] ) ) {
			$this->settings['context'] = $this->settings['position'] === 'acf_after_title' ? 'after_title' : $this->settings['position'];
			unset( $this->settings['position'] );
		}

		$this->migrate_location();

		update_post_meta( $this->post_id, 'settings', $this->settings );
	}

	private function migrate_location() {
		$location       = $this->settings['location'];
		$object_type    = null;
		$post_types     = [];
		$taxonomies     = [];
		$settings_pages = [];

		$object_types = [
			'post_type'    => 'post',
			'attachment'   => 'post',
			'taxonomy'     => 'term',
			'user_form'    => 'user',
			'user_role'    => 'user',
			'options_page' => 'setting',
		];

		foreach ( $location as $group ) {
			foreach ( $group as $rule ) {
				$object_type = $object_types[ $rule['param'] ] ?? $rule['param'];

				if ( $object_type === 'post' && $rule['operator'] === '==' ) {
					$post_types[] = $rule['value'];
				}

				if ( $object_type === 'post' ) {
					$post_types[] = 'attachment';
				}

				if ( $object_type === 'term' ) {
					$taxonomies[] = $rule['value'];
				}

				if ( $object_type === 'setting' ) {
					$settings_pages[] = $rule['value'];
				}
			}
		}

		$post_types = array_unique( $post_types );
		$taxonomies = array_unique( $taxonomies );

		$this->settings['object_type'] = $object_type;

		if ( $object_type === 'post' ) {
			$this->settings['post_types'] = $post_types;
		} elseif ( $object_type === 'term' ) {
			$this->settings['taxonomies'] = $taxonomies;
		} elseif ( $object_type === 'setting' ) {
			$this->settings['settings_pages'] = $settings_pages;
		}

		$include_exclude                   = new FieldGroups\IncludeExclude( $location );
		$this->settings['include_exclude'] = $include_exclude->migrate();

		unset( $this->settings['location'] );
	}

	private function migrate_fields() {
		$fields       = new FieldGroups\Fields( $this->item->ID );
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

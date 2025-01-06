<?php
namespace MetaBox\ACF\Processors;

use MetaBox\Support\Arr;

class SettingsPages extends Base {
	private $post_id;
	protected $object_type = 'setting';

	protected function get_items() {
		$field_group_ids = $this->get_field_group_ids();
		if ( empty( $field_group_ids ) ) {
			return [];
		}

		// Process all settings pages at once.
		if ( ! empty( $_SESSION['processed'] ) ) {
			return [];
		}

		// Free version doesn't have Options pages.
		if ( ! function_exists( 'acf_options_page' ) ) {
			return [];
		}

		$settings_pages = acf_options_page()->get_pages();

		return $settings_pages;
	}

	protected function migrate_item() {
		$this->create_settings_page();
		$this->migrate_fields();
	}

	private function create_settings_page() {
		if ( ! class_exists( 'MBB\SettingsPage\Parser' ) ) {
			return;
		}

		$settings = $this->item;
		Arr::change_key( $settings, 'post_id', 'option_name' );
		Arr::change_key( $settings, 'menu_slug', 'id' );
		Arr::change_key( $settings, 'parent_slug', 'parent' );
		Arr::change_key( $settings, 'update_button', 'submit_button' );
		Arr::change_key( $settings, 'updated_message', 'message' );

		$data = [
			'post_title'  => $settings['menu_title'],
			'post_type'   => 'mb-settings-page',
			'post_status' => 'publish',
			'post_name'   => $settings['id'],
		];

		global $wpdb;
		$post_id = $wpdb->get_var( $wpdb->prepare( " SELECT ID FROM $wpdb->posts WHERE post_name = %s ", $settings['id'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Error.
		if ( $post_id ) {
			$data['ID']    = $post_id;
			$this->post_id = $post_id;
			wp_update_post( $data );
		} else {
			$this->post_id = wp_insert_post( $data );
		}

		$parser = new \MBB\SettingsPage\Parser( $settings );
		$parser->parse_boolean_values()->parse_numeric_values();
		update_post_meta( $this->post_id, 'settings', $parser->get_settings() );

		$parser->parse();
		update_post_meta( $this->post_id, 'settings_page', $parser->get_settings() );
	}

	private function migrate_fields() {
		$fields = new Data\Fields( $this->get_field_group_ids(), $this );
		$fields->migrate_fields();
	}

	public function get( $key ) {
		$option_name = "{$this->item['post_id']}_{$key}";
		return get_option( $option_name, '' );
	}

	public function add( $key, $value ) {
		$option = (array) get_option( $this->item['post_id'], [] );
		if ( ! isset( $option[ $key ] ) ) {
			$option[ $key ] = [];
		}
		$option[ $key ][] = $value;
		update_option( $this->item['post_id'], $option );
	}

	public function update( $key, $value ) {
		// For backup value.
		if ( strpos( $key, '_acf_bak' ) === 0 ) {
			update_option( $key, $value );
			return;
		}

		// For normal option value.
		$option         = (array) get_option( $this->item['post_id'], [] );
		$option[ $key ] = $value;
		update_option( $this->item['post_id'], $option );
	}

	public function delete( $key ) {
		// Delete option first.
		$option_name = "{$this->item['post_id']}_{$key}";
		delete_option( $option_name );

		$option_name = "_{$this->item['post_id']}{$key}";
		delete_option( $option_name );

		// Delete value in the option.
		$option = (array) get_option( $this->item['post_id'], [] );
		unset( $option[ $key ] );
		update_option( $this->item['post_id'], $option );
	}
}

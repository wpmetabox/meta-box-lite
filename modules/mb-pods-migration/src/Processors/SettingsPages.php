<?php
namespace MetaBox\Pods\Processors;

use MetaBox\Support\Arr;
use WP_Query;
use MBBParser\Parsers\MetaBox;

class SettingsPages extends Base {
	private $post_id;
	private $settings      = [];
	private $fields        = [];
	protected $object_type = 'setting';

	protected function get_items() {

		// Process all settings pages at once.
		if ( ! empty( $_SESSION['processed'] ) ) {
			return [];
		}

		$query = new WP_Query( [
			'post_type'              => '_pods_pod',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );

		return $query->posts;
	}

	protected function migrate_item() {
		$this->create_settings_page();
	}

	private function create_settings_page() {
		if ( ! class_exists( 'MBB\SettingsPage\Parser' ) ) {
			return;
		}
		$settings = $this->item;
		$type     = get_post_meta( $settings->ID, 'type', true );
		if ( $type != 'settings' ) {
			return;
		}

		$data    = [
			'post_title'  => $settings->post_title,
			'post_type'   => 'mb-settings-page',
			'post_status' => 'publish',
			'post_name'   => $settings->post_name,
		];
		$post_id = $this->get_id_by_slug( $settings->post_name, 'mb-settings-page' );
		if ( $post_id ) {
			$this->post_id = $data['ID'] = $post_id;
			wp_update_post( $data );
		} else {
			$this->post_id = wp_insert_post( $data );
		}

		$menu_location = get_post_meta( $settings->ID, 'menu_location', true );
		$menu_type     = '';
		switch ( $menu_location ) {
			case 'appearances':
				$parent = 'themes.php';
				break;
			case 'submenu':
				$parent = get_post_meta( $settings->ID, 'menu_location_custom', true );
				break;
			case 'top':
				$parent    = '';
				$menu_type = 'top';
				break;
			default:
				$parent = 'options-general.php';
				break;
		}
		$menu_icon = get_post_meta( $settings->ID, 'menu_icon', true ) ?: 'dashicons-admin-generic';
		if ( strpos( $menu_icon, 'http' ) !== false ) {
			$icon_type = 'custom';
		}
		$settings_page = [
			'menu_title'     => get_post_meta( $settings->ID, 'menu_name', true ) ?: $settings->post_title,
			'id'             => $settings->post_name,
			'menu_type'      => $menu_type,
			'capability'     => 'manage_options',
			'parent'         => $parent,
			'icon_type'      => $icon_type ?? 'dashicons',
			'icon_dashicons' => str_replace( 'dashicons-', '', $menu_icon ),
			'icon_custom'    => $menu_icon,
			'icon_url'       => $menu_icon,
			'position'       => (int) get_post_meta( $settings->ID, 'menu_position', true ) ?: '',
			'style'          => 'no-boxes',
			'columns'        => 1,
		];
		$parser        = new \MBB\SettingsPage\Parser( $settings_page );
		$parser->parse_boolean_values()->parse_numeric_values();
		update_post_meta( $this->post_id, 'settings', $parser->get_settings() );
		$parser->parse();
		update_post_meta( $this->post_id, 'settings_page', $parser->get_settings() );
		$this->migrate_value( $settings->ID );

		$this->delete_post( $settings->ID );
	}

	private function migrate_value( $id ) {
		$query = new WP_Query( [
			'post_type'              => '_pods_field',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'post_parent'            => $id,
			'order'                  => 'ASC',
			'orderby'                => 'menu_order',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );

		$fields = $query->posts;
		$args   = [];
		foreach ( $fields as $field ) {
			$slug        = $field->post_name;
			$option_name = $this->item->post_name . '_' . $slug;
			$value       = get_option( $option_name );

			$args[ $slug ] = $value;
		}
		update_option( $this->item->post_name, $args );
	}
}

<?php
namespace MBB\Extensions;

use MBB\Control;
use MetaBox\Support\Arr;
use MBB\Helpers\Data;

class Tabs {
	public function __construct() {
		add_filter( 'mbb_field_types', [ $this, 'add_field_type' ] );
		if ( ! Data::is_extension_active( 'meta-box-tabs' ) ) {
			return;
		}
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_font_awesome' ] );
		add_filter( 'mbb_meta_box_settings', [ $this, 'parse_meta_box_settings' ] );
	}

	public function add_field_type( $field_types ) {
		$field_types['tab'] = [
			'title'    => __( 'Tab', 'meta-box-builder' ),
			'category' => 'layout',
			'disabled' => ! Data::is_extension_active( 'meta-box-tabs' ),
			'controls' => [
				'type',
				Control::Name( 'name', [
					'required' => true,
					'label'    => __( 'Label', 'meta-box-builder' ),
				] ),
				Control::Id( 'id', [
					'label'       => __( 'ID', 'meta-box-builder' ),
					'required'    => true,
					'description' => __( 'Use only lowercase letters, numbers, underscores (and be careful dashes).', 'meta-box-builder' ),
				] ),
				Control::Radio( 'icon_type', [
					'label'   => __( 'Icon type', 'meta-box-builder' ),
					'options' => [
						'dashicons'   => __( 'Dashicons', 'meta-box-builder' ),
						'fontawesome' => __( 'Font Awesome', 'meta-box-builder' ),
						'url'         => __( 'Custom', 'meta-box-builder' ),
					],
				], 'dashicons' ),
				Control::DashiconPicker( 'icon', [
					'label'      => __( 'Icon', 'meta-box-builder' ),
					'dependency' => 'icon_type:dashicons',
				], '' ),
				Control::Fontawesome( 'icon_fa', [
					'label'       => __( 'Icon', 'meta-box-builder' ),
					'description' => __( 'Enter <a target="_blank" href="https://fontawesome.com/search?o=r&m=free">Font Awesome</a> icon class here. Supports the free version only.', 'meta-box-builder' ),
					'dependency'  => 'icon_type:fontawesome',
				], '' ),
				Control::Input( 'icon_url', [
					'label'      => __( 'Icon URL', 'meta-box-builder' ),
					'dependency' => 'icon_type:url',
				], '' ),
			],
		];

		return $field_types;
	}

	public function enqueue_font_awesome(): void {
		if ( get_current_screen()->id === 'meta-box' ) {
			wp_enqueue_style( 'font-awesome', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css', [], ' 6.7.2' );
		}
	}

	public function parse_meta_box_settings( array $settings ): array {
		$this->parse_tabs( $settings );
		$this->set_fields_tab( $settings );
		return $settings;
	}

	private function parse_tabs( &$settings ): void {
		$tabs = [];

		$prefix = $settings['prefix'] ?? '';

		$fields = $settings['fields'];
		foreach ( $fields as $field ) {
			if ( 'tab' !== Arr::get( $field, 'type' ) ) {
				continue;
			}

			$type   = Arr::get( $field, 'icon_type', 'dashicons' );
			$label  = Arr::get( $field, 'name', '' );
			$params = [
				'dashicons'   => 'icon',
				'url'         => 'icon_url',
				'fontawesome' => 'icon_fa',
			];
			$icon   = Arr::get( $field, $params[ $type ], '' );

			if ( $type === 'dashicons' && $icon ) {
				$icon = "dashicons-$icon";
			}

			// Remove field ID prefix for tabs.
			$field['id'] = $this->get_field_id_without_prefix( $field['id'], $prefix );

			if ( ! $icon ) {
				$tabs[ $field['id'] ] = $label;
			} else {
				$tabs[ $field['id'] ] = compact( 'label', 'icon' );
			}
		}

		if ( 'default' === Arr::get( $settings, 'tab_style' ) ) {
			unset( $settings['tab_style'] );
		}

		if ( empty( $tabs ) ) {
			unset( $settings['tab_style'] );
			unset( $settings['tab_default_active'] );
		} else {
			$settings['tabs'] = $tabs;

			// Move 'fields' to bottom.
			unset( $settings['fields'] );
			$settings['fields'] = $fields;
		}
	}

	private function set_fields_tab( array &$settings ): void {
		if ( empty( $settings['fields'] ) || ! is_array( $settings['fields'] ) ) {
			return;
		}
		$fields = &$settings['fields'];
		if ( 'tab' !== Arr::get( $fields[0], 'type' ) ) {
			return;
		}

		$prefix = $settings['prefix'] ?? '';

		$previous_tab = null;
		foreach ( $fields as $k => &$field ) {
			if ( 'tab' === $field['type'] ) {
				$previous_tab = $this->get_field_id_without_prefix( $field['id'], $prefix );
				unset( $fields[ $k ] );
			} else {
				$field['tab'] = $previous_tab;
			}
		}
	}

	private function get_field_id_without_prefix( string $field_id, string $prefix ): string {
		if ( $prefix && str_starts_with( $field_id, $prefix ) ) {
			return substr( $field_id, strlen( $prefix ) );
		}
		return $field_id;
	}
}

<?php
namespace MBB\Extensions;

use MBB\Control;
use MetaBox\Support\Arr;
use MBB\Helpers\Data;

class Tabs {
	public function __construct() {
		if ( ! Data::is_extension_active( 'meta-box-tabs' ) ) {
			return;
		}
		add_action( 'mbb_field_types', [ $this, 'add_field_type' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_font_awesome' ] );
		add_filter( 'mbb_meta_box_settings', [ $this, 'parse_meta_box_settings' ] );

		add_filter( 'mbb_settings_controls', [ $this, 'add_settings_controls' ] );
	}

	public function add_field_type( $field_types ) {
		$field_types['tab'] = [
			'title'    => __( 'Tab', 'meta-box-builder' ),
			'category' => 'layout',
			'controls' => [
				'name',
				'id',
				'type',
				Control::Select( 'icon_type', [
					'label'   => __( 'Icon type', 'meta-box-builder' ),
					'options' => [
						'dashicons'   => __( 'Dashicons', 'meta-box-builder' ),
						'fontawesome' => __( 'Font Awesome', 'meta-box-builder' ),
						'url'         => __( 'Custom URL', 'meta-box-builder' ),
					],
				], 'dashicons' ),
				Control::Icon( 'icon', [
					'label'      => __( 'Icon', 'meta-box-builder' ),
					'dependency' => 'icon_type:dashicons',
				] ),
				Control::Fontawesome( 'icon_fa', [
					'label'       => __( 'Icon', 'meta-box-builder' ),
					'tooltip'     => __( 'The icon to be used for the admin menu (FontAwesome)', 'meta-box-builder' ),
					'description' => __( 'Enter <a target="_blank" href="https://fontawesome.com/search?o=r&m=free">FontAwesome</a> icon class here. Supports FontAwesome free version only.', 'meta-box-builder' ),
					'dependency'  => 'icon_type:fontawesome',
				] ),
				Control::Input( 'icon_url', [
					'label'      => __( 'Icon URL', 'meta-box-builder' ),
					'dependency' => 'icon_type:url',
				] ),
			],
		];

		return $field_types;
	}

	public function enqueue_font_awesome(): void {
		if ( get_current_screen()->id === 'meta-box' ) {
			wp_enqueue_style( 'font-awesome', MBB_URL . 'assets/fontawesome/css/all.min.css', [], '6.6.0' );
		}
	}

	public function parse_meta_box_settings( $settings ) {
		$this->parse_tabs( $settings );
		$this->set_fields_tab( $settings['fields'] );
		return $settings;
	}

	private function parse_tabs( &$settings ) {
		$tabs = [];

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

			$tabs[ $field['id'] ] = compact( 'label', 'icon' );
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

	private function set_fields_tab( &$fields ) {
		if ( empty( $fields ) ) {
			return;
		}
		if ( 'tab' !== Arr::get( $fields[0], 'type' ) ) {
			return;
		}

		$previous_tab = null;
		foreach ( $fields as $k => &$field ) {
			if ( 'tab' === $field['type'] ) {
				$previous_tab = $field['id'];
				unset( $fields[ $k ] );
			} else {
				$field['tab'] = $previous_tab;
			}
		}
	}

	public function add_settings_controls( $controls ) {
		$controls['14.1'] = Control::Select( 'tab_style', [
			'label'   => __( 'Tab style', 'meta-box-builder' ),
			'tooltip' => __( 'Change how look and feel of tabs in Meta Box Tabs', 'meta-box-builder' ),
			'options' => [
				'default' => __( 'Default', 'meta-box-builder' ),
				'box'     => __( 'Box', 'meta-box-builder' ),
				'left'    => __( 'Left', 'meta-box-builder' ),
			],
		] );
		$controls['14.2'] = Control::Input( 'tab_default_active', __( 'Default active tab ID', 'meta-box-builder' ) );

		return $controls;
	}
}

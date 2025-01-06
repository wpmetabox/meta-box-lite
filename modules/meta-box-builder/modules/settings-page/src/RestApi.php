<?php
namespace MBB\SettingsPage;

use MBB\RestApi\Base;
use MBB\Control;

class RestApi extends Base {
	public function get_settings_page_controls() {
		$controls = [
			// Menu.
			Control::Select( 'menu_type', [
				'label'   => __( 'Menu type', 'meta-box-builder' ),
				'options' => [
					'top'     => __( 'Top-level menu', 'meta-box-builder' ),
					'submenu' => __( 'Submenu', 'meta-box-builder' ),
				],
			], 'top' ),
			Control::MenuPosition( 'position', [
				'label'      => __( 'Show menu after', 'meta-box-builder' ),
				'dependency' => 'menu_type:top',
			], 25 ),
			Control::Input( 'submenu_title', [
				'label'      => __( 'Default first submenu title', 'meta-box-builder' ),
				'dependency' => 'menu_type:top',
			] ),
			Control::MenuParent( 'parent', [
				'label'      => __( 'Parent menu', 'meta-box-builder' ),
				'dependency' => 'menu_type:submenu',
			], 'index.php' ),

			// Icon, only if menu_type = top.
			Control::Select( 'icon_type', [
				'label'      => __( 'Icon type', 'meta-box-builder' ),
				'options'    => [
					'dashicons'    => __( 'Dashicons', 'meta-box-builder' ),
					'font_awesome' => __( 'Font Awesome', 'meta-box-builder' ),
					'svg'          => __( 'SVG', 'meta-box-builder' ),
					'custom'       => __( 'Custom URL', 'meta-box-builder' ),
				],
				'dependency' => 'menu_type:top',
			], 'dashicons' ),
			Control::Icon( 'icon_dashicons', [
				'label'      => __( 'Icon', 'meta-box-builder' ),
				'dependency' => 'icon_type:dashicons',
			], 'admin-generic' ),
			Control::Input( 'icon_svg', [
				'label'      => __( 'Icon SVG', 'meta-box-builder' ),
				'tooltip'    => __( 'Must be in base64 encoded format', 'meta-box-builder' ),
				'dependency' => 'icon_type:svg',
			] ),
			Control::Input( 'icon_custom', [
				'label'      => __( 'Icon URL', 'meta-box-builder' ),
				'dependency' => 'icon_type:custom',
			] ),
			Control::Fontawesome( 'icon_font_awesome', [
				'label'       => __( 'Icon', 'meta-box-builder' ),
				'tooltip'     => __( 'The icon to be used for the admin menu (FontAwesome)', 'meta-box-builder' ),
				'description' => __( 'Enter <a target="_blank" href="https://fontawesome.com/search?o=r&m=free">FontAwesome</a> icon class here. Supports FontAwesome free version only.', 'meta-box-builder' ),
				'dependency'  => 'icon_type:font_awesome',
			] ),

			Control::Select( 'capability', [
				'label'   => __( 'Required capability', 'meta-box-builder' ),
				'options' => $this->get_capabilities(),
			], 'edit_theme_options' ),
			Control::Input( 'class', [
				'label'   => __( 'Custom CSS class', 'meta-box-builder' ),
				'tooltip' => __( 'Custom CSS for the wrapper div', 'meta-box-builder' ),
			] ),
			Control::Select( 'style', [
				'label'   => __( 'Style', 'meta-box-builder' ),
				'options' => [
					'boxes'    => __( 'Boxes', 'meta-box-builder' ),
					'no-boxes' => __( 'No boxes', 'meta-box-builder' ),
				],
			], 'no-boxes' ),
			Control::Select( 'columns', [
				'label'   => __( 'Columns', 'meta-box-builder' ),
				'options' => [
					1 => 1,
					2 => 2,
				],
			], 1 ),
			Control::KeyValue( 'tabs', __( 'Tabs', 'meta-box-builder' ) ),
			Control::Select( 'tab_style', [
				'label'   => __( 'Tab style', 'meta-box-builder' ),
				'options' => [
					'default' => __( 'Top', 'meta-box-builder' ),
					'left'    => __( 'Left', 'meta-box-builder' ),
				],
			], 'default' ),
			Control::Input( 'submit_button', __( 'Custom submit button', 'meta-box-builder' ) ),
			Control::Input( 'message', [
				'label'   => __( 'Custom message', 'meta-box-builder' ),
				'tooltip' => __( 'The custom message displayed when saving options', 'meta-box-builder' ),
			] ),
			Control::KeyValue( 'help_tabs', [
				'label'            => __( 'Help tabs', 'meta-box-builder' ),
				'tooltip'          => __( 'The content displayed when clicking on the Help button on the top right (near the Screen Options button). HTML is allowed.', 'meta-box-builder' ),
				'keyPlaceholder'   => __( 'Title', 'meta-box-builder' ),
				'valuePlaceholder' => __( 'Content', 'meta-box-builder' ),
			] ),
			Control::Checkbox( 'customizer', [
				'label'   => __( 'Customizer', 'meta-box-builder' ),
				'tooltip' => __( 'Show this settings page as a panel in the Customizer', 'meta-box-builder' ),
			] ),
			Control::Checkbox( 'customizer_only', [
				'label'   => __( 'Customizer only', 'meta-box-builder' ),
				'tooltip' => __( 'Show only in the Customizer, no admin settings page', 'meta-box-builder' ),
			] ),
			Control::Checkbox( 'network', [
				'label'   => __( 'Network', 'meta-box-builder' ),
				'tooltip' => __( 'Make the settings page network-wide (in multisite environment)', 'meta-box-builder' ),
			] ),
		];

		return $controls;
	}

	private function get_capabilities() {
		$caps  = [];
		$roles = wp_roles();
		foreach ( $roles->roles as $role ) {
			$caps = array_merge( $caps, array_keys( $role['capabilities'] ) );
		}

		$caps = array_unique( $caps );
		sort( $caps );

		return array_combine( $caps, $caps );
	}
}

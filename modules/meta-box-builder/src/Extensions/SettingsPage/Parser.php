<?php
namespace MBB\Extensions\SettingsPage;

use MBBParser\Parsers\Base;
use MetaBox\Support\Arr;

class Parser extends Base {
	public function parse() {
		$this->parse_menu_icon()
			->remove_menu_keys()
			->parse_help_tabs()
			->parse_tabs();

		$this->parse_boolean_values()
			->parse_numeric_values()
			->remove_empty_values()
			->remove_default( 'capability', 'edit_theme_options' )
			->remove_default( 'style', 'boxes' )
			->remove_default( 'columns', 2 )
			->remove_default( 'tab_style', 'default' );
	}

	private function parse_menu_icon(): self {
		// Submenu.
		$menu_type = Arr::get( $this->settings, 'menu_type', 'top' );
		if ( 'top' !== $menu_type ) {
			return $this;
		}

		// Top level menu.
		unset( $this->parent );

		// Setup icon URL.
		$type           = Arr::get( $this->settings, 'icon_type', 'dashicons' );
		$this->icon_url = Arr::get( $this->settings, "icon_$type" );

		if ( 'dashicons' === $type ) {
			$this->icon_url = 'dashicons-' . $this->icon_url;
		}

		return $this;
	}

	private function remove_menu_keys(): self {
		$keys = [ 'menu_type', 'icon_type', 'icon_dashicons', 'icon_svg', 'icon_custom', 'icon_font_awesome' ];
		foreach ( $keys as $key ) {
			unset( $this->$key );
		}

		return $this;
	}

	private function parse_help_tabs() {
		$help_tabs = $this->help_tabs;
		if ( ! ( $help_tabs ) ) {
			return $this;
		}
		$new_help_tabs = [];
		foreach ( $help_tabs as $key => $value ) {
			$value['title']   = $value['key'];
			$value['content'] = $value['value'];
			unset( $value['id'] );
			unset( $value['key'] );
			unset( $value['value'] );
			$new_help_tabs[] = $value;
		}
		$this->help_tabs = $new_help_tabs;

		return $this;
	}

	private function parse_tabs() {
		if ( empty( $this->tabs ) ) {
			return;
		}
		$tabs = [];
		foreach ( $this->tabs as $tab ) {
			if ( empty( $tab['key'] ) || empty( $tab['value'] ) ) {
				continue;
			}
			$tabs[ $tab['key'] ] = $tab['value'];
		}
		$this->tabs = $tabs;

		return $this;
	}
}

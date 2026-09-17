<?php
namespace MBB\Extensions\CustomModel;

use MBB\Helpers\TableSchema;
use MBBParser\Parsers\Base;
use MetaBox\Support\Arr;

class Parser extends Base {
	public function parse(): void {
		$this->parse_boolean_values()
			->parse_numeric_values()
			->parse_table()
			->parse_columns()
			->parse_menu()
			->parse_menu_icon()
			->parse_labels()
			->parse_supports()
			->strip_ui_keys()
			->remove_empty_values()
			->remove_default( 'capability', 'edit_posts' )
			->remove_default( 'show_in_menu', true )
			->remove_default( 'menu_icon', 'dashicons-admin-post' );
	}

	private function parse_table(): self {
		$table = Arr::get( $this->settings, 'table', '' );
		if ( empty( $table ) ) {
			return $this;
		}

		$this->table = TableSchema::apply_prefix( $table, Arr::get( $this->settings, 'prefix', false ) );
		unset( $this->prefix );

		return $this;
	}

	private function parse_columns(): self {
		$parsed = TableSchema::parse_columns( (array) Arr::get( $this->settings, 'columns', [] ) );

		$this->columns = $parsed['columns'];
		$this->keys    = $parsed['keys'];

		return $this;
	}

	private function parse_menu(): self {
		$show_in_menu = Arr::get( $this->settings, 'show_in_menu', true );

		// Submenu: show_in_menu holds the parent slug.
		if ( is_string( $show_in_menu ) && '' !== $show_in_menu ) {
			$this->parent       = $show_in_menu;
			$this->show_in_menu = true;
			unset( $this->menu_position, $this->menu_icon );
			return $this;
		}

		if ( false === $show_in_menu ) {
			$this->show_in_menu = false;
			unset( $this->parent, $this->menu_position, $this->menu_icon );
			return $this;
		}

		$this->show_in_menu = true;
		unset( $this->parent );
		$position = Arr::get( $this->settings, 'menu_position', '' );
		if ( '' === $position || null === $position ) {
			unset( $this->menu_position );
		} else {
			$this->menu_position = (int) $position;
		}

		return $this;
	}

	private function parse_menu_icon(): self {
		if ( empty( $this->show_in_menu ) || ! empty( $this->parent ) ) {
			unset( $this->menu_icon, $this->icon_type, $this->icon, $this->icon_svg, $this->icon_custom, $this->font_awesome );
			return $this;
		}

		$type = Arr::get( $this->settings, 'icon_type', 'dashicons' );
		$map  = [
			'dashicons'    => 'icon',
			'svg'          => 'icon_svg',
			'custom'       => 'icon_custom',
			'font_awesome' => 'font_awesome',
		];
		$key  = $map[ $type ] ?? 'icon';

		if ( 'dashicons' === $type ) {
			$icon            = (string) Arr::get( $this->settings, $key, 'admin-post' );
			$icon            = preg_replace( '/^dashicons-/', '', $icon );
			$this->menu_icon = 'dashicons-' . $icon;
		} else {
			$this->menu_icon = Arr::get( $this->settings, $key, '' );
		}

		unset( $this->icon_type, $this->icon, $this->icon_svg, $this->icon_custom, $this->font_awesome );

		return $this;
	}

	private function parse_labels(): self {
		$labels = Arr::get( $this->settings, 'labels', [] );
		if ( ! is_array( $labels ) ) {
			$labels = [];
		}

		// Keep only non-empty labels.
		$labels = array_filter( $labels );

		$this->labels = $labels;

		return $this;
	}

	private function parse_supports(): self {
		$supports = Arr::get( $this->settings, 'supports', [] );
		if ( ! is_array( $supports ) ) {
			$supports = [];
		}
		$this->supports = array_values( array_unique( array_filter( $supports ) ) );

		if ( empty( $this->supports ) ) {
			unset( $this->supports );
		}

		return $this;
	}

	private function strip_ui_keys(): self {
		unset(
			$this->slug,
			$this->id,
			$this->text_domain,
			$this->function_name,
			$this->_slug_changed,
			$this->_table_changed,
			$this->modified
		);

		return $this;
	}
}

<?php
namespace MBBParser\Encoders;

use MBBParser\SettingsTrait;

class Field {
	use SettingsTrait;

	private $id_prefix;

	public function __construct( $settings, $id_prefix ) {
		$this->settings  = $settings;
		$this->id_prefix = $id_prefix;
	}

	public function encode() {
		$translatable_fields = [ 'name', 'desc', 'label_description', 'add_button', 'placeholder', 'prefix', 'suffix', 'before', 'after', 'std', 'group_title', 'prepend', 'append' ];
		array_walk( $translatable_fields, [ $this, 'make_translatable' ] );

		$this->transform_id_prefix();
		$this->make_options_translatable();
		$this->make_admin_columns_translatable();
		$this->make_tooltip_translatable();
	}

	private function transform_id_prefix() {
		if ( empty( $this->id ) ) {
			return;
		}
		$this->id = substr( $this->id, strlen( $this->id_prefix ) );
		$this->id = '{prefix}' . $this->id;
	}

	private function make_options_translatable() {
		$choice_types = [ 'select', 'radio', 'checkbox_list', 'select_advanced', 'button_group', 'image_select', 'autocomplete' ];
		if ( ! in_array( $this->type, $choice_types, true ) ) {
			return;
		}

		if ( $this->_callback ) {
			$this->options = "{raw}{$this->_callback}(){/raw}";
			unset( $this->_callback );
			return;
		}

		if ( empty( $this->options ) || ! is_array( $this->options ) ) {
			return;
		}
		$options = $this->options;
		foreach ( $options as &$label ) {
			$label = sprintf( '{translate}%s{/translate}', $label );
		}
		$this->options = $options;
	}

	private function make_admin_columns_translatable() {
		if ( empty( $this->admin_columns ) || ! is_array( $this->admin_columns ) ) {
			return;
		}
		$admin_columns = $this->admin_columns;
		foreach ( $admin_columns as $key => &$label ) {
			if ( in_array( $key, [ 'title', 'before', 'after' ], true ) ) {
				$label = sprintf( '{translate}%s{/translate}', $label );
			}
		}
		$this->admin_columns = $admin_columns;
	}

	private function make_tooltip_translatable() {
		if ( empty( $this->tooltip ) || ! is_array( $this->tooltip ) ) {
			return;
		}
		$tooltips = $this->tooltip;
		foreach ( $tooltips as $key => &$label ) {
			if ( $key === 'content' ) {
				$label = sprintf( '{translate}%s{/translate}', $label );
			}
		}
		$this->tooltip = $tooltips;
	}

	private function make_translatable( $name ) {
		if ( ! empty( $this->$name ) && is_string( $this->$name ) ) {
			$this->$name = sprintf( '{translate}%s{/translate}', $this->$name );
		}
	}
}

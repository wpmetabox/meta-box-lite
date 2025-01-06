<?php
namespace MetaBox\TS\Processors\FieldGroups;

use MetaBox\Support\Arr;

class FieldType {
	private $settings;
	private $post_id;

	public function __construct( $settings, $post_id ) {
		$this->settings = $settings;
		$this->post_id  = $post_id;
	}

	public function __get( $name ) {
		return $this->settings[ $name ] ?? null;
	}

	public function __set( $name, $value ): void {
		$this->settings[ $name ] = $value;
	}

	public function __isset( $name ): bool {
		return isset( $this->settings[ $name ] );
	}

	public function __unset( $name ) {
		unset( $this->settings[ $name ] );
	}

	public function migrate() {
		$this->migrate_general_settings();

		$method = "migrate_{$this->type}";
		if ( method_exists( $this, $method ) ) {
			$this->$method();
		}

		if ( $this->post_id ) {
			$this->migrate_group();
		}

		return $this->settings;
	}

	private function migrate_general_settings() {
		Arr::change_key( $this->settings, 'description', 'label_description' );
		$this->std         = Arr::get( $this->settings, 'data.user_default_value' );
		$this->placeholder = Arr::get( $this->settings, 'data.placeholder' );

		if ( Arr::get( $this->settings, 'data.repetitive' ) ) {
			$this->clone         = true;
			$this->sort_clone    = true;
			$this->clone_default = true;
		} else {
			unset( $this->clone );
		}

		if ( Arr::get( $this->settings, 'data.validate.required' ) ) {
			$this->required = true;
		} else {
			unset( $this->required );
		}

		$this->_id        = $this->type . '_' . uniqid();
		$this->_state     = 'collapse';
		$this->save_field = true;
		unset( $this->meta_key );
		unset( $this->meta_type );
	}

	private function migrate_group() {
		$this->type          = 'group';
		$this->id            = get_post_meta( $this->post_id, '_types_repeatable_field_group_post_type', true );
		$this->_id           = $this->type . '_' . uniqid();
		$this->name          = get_the_title( $this->post_id );
		$this->clone         = true;
		$this->sort_clone    = true;
		$this->clone_default = true;
		$fields              = new Fields( $this->post_id );
		$this->fields        = $fields->migrate_fields();
	}

	private function migrate_phone() {
		$this->type = 'text';
	}

	private function migrate_textfield() {
		$this->type = 'text';
	}

	private function migrate_embed() {
		$this->type = 'oembed';
	}

	private function migrate_image() {
		$this->type = 'single_image';
	}

	private function migrate_audio() {
		$this->type = 'file_input';
	}

	private function migrate_numeric() {
		$this->type = 'number';
	}

	private function migrate_select() {
		$this->migrate_choices();
	}

	private function migrate_radio() {
		$this->migrate_choices();
	}

	private function migrate_checkbox() {
		$this->std = Arr::get( $this->settings, 'data.checked' );
	}

	private function migrate_checkboxes() {
		$this->type = 'checkbox_list';
		$values     = [];
		$default    = [];
		$options    = Arr::get( $this->settings, 'data.options' );

		foreach ( $options as $option ) {
			$title   = Arr::get( $option, 'title' );
			$value   = Arr::get( $option, 'set_value' );
			$checked = Arr::get( $option, 'checked' );
			if ( $title && $value ) {
				$values[] = "$value: $title";
			}
			if ( $checked ) {
				$default[] = $value;
			}
		}
		$this->options = implode( "\n", $values );
		$this->std     = implode( "\n", $default );
	}

	private function migrate_choices() {
		$values        = [];
		$options       = Arr::get( $this->settings, 'data.options' );
		$default       = Arr::get( $options, 'default' );
		$default_value = '';

		foreach ( $options as $key => $option ) {
			$title = Arr::get( $option, 'title' );
			$value = Arr::get( $option, 'value' );
			if ( $title && $value ) {
				$values[] = "$value: $title";
			}
			if ( $key == $default ) {
				$default_value = Arr::get( $option, 'value' );
			}
		}
		$this->options = implode( "\n", $values );
		$this->std     = $default_value;
	}

	private function migrate_date() {
		$date_and_time   = Arr::get( $this->settings, 'data.date_and_time' );
		$this->type      = $date_and_time === 'date' ? 'date' : 'datetime';
		$this->timestamp = true;
	}

	private function migrate_colorpicker() {
		$this->type = 'color';
	}

	private function migrate_post() {
		$this->type       = 'post';
		$this->post_type  = [ Arr::get( $this->settings, 'data.post_reference_type' ) ];
		$this->field_type = 'select_advanced';
	}
}

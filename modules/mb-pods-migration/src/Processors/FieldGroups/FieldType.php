<?php
namespace MetaBox\Pods\Processors\FieldGroups;

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

	public function __set( $name, $value ) {
		return $this->settings[ $name ] = $value;
	}

	public function __isset( $name ) {
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

		return $this->settings;
	}

	private function migrate_general_settings() {
		if ( Arr::get( $this->settings, 'clone' ) ) {
			$this->clone             = true;
			$this->sort_clone        = true;
			$this->clone_default     = true;
			$this->clone_as_multiple = true;
			$this->add_button        = Arr::get( $this->settings, 'add_button' );
		} else {
			unset( $this->clone );
			unset( $this->add_button );
		}

		if ( Arr::get( $this->settings, 'required' ) ) {
			$this->required = true;
		} else {
			unset( $this->required );
		}

		$this->_id        = $this->type . '_' . uniqid();
		$this->_state     = 'collapse';
		$this->save_field = true;
	}

	private function migrate_website() {
		$this->type = 'url';
	}

	private function migrate_phone() {
		$this->type = 'text';
	}

	private function migrate_paragraph() {
		$this->type = 'textarea';
	}

	private function migrate_code() {
		$this->type = 'textarea';
	}

	private function migrate_currency() {
		$this->type = 'text';
	}

	private function migrate_pick() {
		$format_type = get_post_meta( $this->post_id, 'pick_format_type', true );
		$format      = get_post_meta( $this->post_id, 'pick_format_single', true );
		if ( $format_type == 'multi' ) {
			$format         = get_post_meta( $this->post_id, 'pick_format_multi', true );
			$this->multiple = true;
		}

		switch ( $format ) {
			case 'dropdown':
				$type              = 'select';
				$this->placeholder = get_post_meta( $this->post_id, 'pick_select_text', true ) ?: '-- Select One --';
				break;
			case 'radio':
				$type = 'radio';
				break;
			case 'checkbox':
				$type = 'checkbox_list';
				break;
			case 'multiselect':
				$type = 'select';
				break;
			case 'autocomplete':
				$type = 'autocomplete';
				break;
			default:
				$type = 'select_advanced';
				break;
		}

		$this->type = $type;

		$pick_custom   = get_post_meta( $this->post_id, 'pick_custom', true );
		$this->options = str_replace( '|', ': ', $pick_custom );
	}

	private function migrate_file() {
		$file_uploader          = get_post_meta( $this->post_id, 'file_uploader', true );
		$this->max_file_uploads = get_post_meta( $this->post_id, 'file_format_type', true ) == 'single' ? 1 : '';
		$this->type             = ( $file_uploader == 'attachment' ) ? 'file_advanced' : 'file_upload';
		$file_type              = get_post_meta( $this->post_id, 'file_uploader', true );
		$image                  = [ 'images', 'images-any' ];
		$video                  = [ 'video', 'video-any' ];
		if ( in_array( $file_type, $image ) ) {
			$this->type = ( $file_uploader == 'attachment' ) ? 'image_advanced' : 'image_upload';
		}
		if ( in_array( $file_type, $video ) ) {
			$this->type = 'video';
		}
	}

	private function migrate_boolean() {
		$type       = get_post_meta( $this->post_id, 'boolean_format_type', true ) ?: 'radio';
		$this->type = $type;
		if ( $type == 'checkbox' ) {
			$this->type = 'checkbox_list';
		}
		if ( $type == 'dropdown' ) {
			$this->type = 'select';
		}
		$options = [
			'1' => get_post_meta( $this->post_id, 'boolean_yes_label', true ) ?: 'Yes',
			'0' => get_post_meta( $this->post_id, 'boolean_no_label', true ) ?: 'No',
		];
		$values  = [];
		foreach ( $options as $key => $value ) {
			$values[] = "$key: $value";
		}
		$this->options = implode( "\n", $values );
	}

	private function migrate_html() {
		$this->type = 'custom_html';
		$this->std  = get_post_meta( $this->post_id, 'html_content', true );
	}

	private function migrate_heading() {
		$this->desc = get_post_meta( $this->post_id, 'html_tag', true );
	}
}

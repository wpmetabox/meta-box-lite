<?php
namespace MBBParser\Unparsers;

class Field extends Base {
	/**
	 * Allow these settings to be empty.
	 * @var array
	 */
	protected $empty_keys = [ 'save_field' ];

	private $choice_types = [ 'select', 'radio', 'checkbox_list', 'select_advanced', 'button_group', 'image_select', 'autocomplete' ];

	/** Keys from the original imported JSON, captured before any default values are injected. */
	private array $original_keys = [];

	/**
	 * This is revert of parse method. While parse method converts to the minimal format,
	 * this method converts back to the original format.
	 *
	 * Used when importing JSON to the builder.
	 *
	 * @return void
	 */
	public function unparse() {
		$this->original_keys = array_keys( $this->settings );

		$this->unparse_default_values()
			->unparse_boolean_values()
			->unparse_numeric_values()
			->unparse_datalist()
			->unparse_choice_options()
			->unparse_choice_std()
			->unparse_clone()
			->unparse_array_attributes( 'options' )
			->unparse_array_attributes( 'js_options' )
			->unparse_array_attributes( 'query_args' )
			->unparse_array_attributes( 'attributes' )
			->unparse_text_limiter()
			->unparse_conditional_logic()
			->unparse_tooltip()
			->unparse_admin_columns()
			->ensure_boolean( 'save_field' );

		$func = "unparse_field_{$this->type}";
		if ( method_exists( $this, $func ) ) {
			$this->$func();
		}
		$this->unparse_custom_settings();
	}

	private function unparse_datalist() {
		if ( empty( $this->datalist ) ) {
			return $this;
		}

		$this->settings['datalist_choices'] = implode( "\n", $this->datalist['options'] );

		return $this;
	}

	/**
	 * Inverse of parse_choice_options.
	 *
	 * Convert options array to string.
	 *
	 * @return static
	 */
	private function unparse_choice_options() {
		if ( ! in_array( $this->type, $this->choice_types, true ) ) {
			return $this;
		}

		if ( empty( $this->options ) || ! is_array( $this->options ) ) {
			return $this;
		}

		$options = [];
		foreach ( $this->options as $key => $value ) {
			$options[] = "{$key}:{$value}";
		}
		$options = implode( "\r\n", $options );

		$this->options = $options;

		return $this;
	}

	private function unparse_choice_std() {
		if ( ! in_array( $this->type, $this->choice_types, true ) ) {
			return $this;
		}

		$this->std = is_array( $this->std ) ? implode( "\r\n", $this->std ) : $this->std;

		return $this;
	}

	private function unparse_clone() {
		if ( ! $this->clone ) {
			return $this;
		}

		$keys = [ 'sort_clone', 'clone_default', 'clone_as_multiple', 'min_clone', 'max_clone', 'add_button', 'clone_empty_start' ];

		foreach ( $keys as $key ) {
			if ( isset( $this->$key ) ) {
				continue;
			}

			$numerics = [ 'min_clone', 'max_clone' ];
			$strings  = [ 'add_button' ];
			if ( in_array( $key, $numerics, true ) ) {
				$this->$key = 0;
			} elseif ( in_array( $key, $strings, true ) ) {
				$this->$key = '';
			} else {
				$this->$key = false;
			}
		}

		return $this;
	}

	private function unparse_field_key_value() {
		$placeholder = $this->placeholder;
		if ( empty( $placeholder ) ) {
			return $this;
		}

		$this->placeholder_key   = $placeholder['key'] ?? '';
		$this->placeholder_value = $placeholder['value'] ?? '';

		return $this;
	}

	private function unparse_field_group() {
		$this->default_state = 'expanded';

		$keys = [ 'default_state', 'save_state', 'group_title' ];

		foreach ( $keys as $key ) {
			$this->$key = $this->$key ?? '';
		}

		return $this;
	}

	private function unparse_text_limiter() {
		if ( ! isset( $this->limit ) ) {
			return $this;
		}

		$this->text_limiter = [
			'limit'      => $this->limit,
			'limit_type' => $this->limit_type ?? 'word',
		];

		return $this;
	}

	private function unparse_tooltip() {
		if ( ! isset( $this->tooltip ) ) {
			return $this;
		}

		$defaults = [
			'enable'     => true,
			'icon'       => 'info',
			'position'   => 'top',
			'content'    => '',
			'allow_html' => true,
		];

		if ( is_string( $this->tooltip ) ) {
			$this->tooltip = [
				'content' => $this->tooltip,
			];
		}

		$this->tooltip = array_merge( $defaults, $this->tooltip );

		return $this;
	}

	private function unparse_admin_columns() {
		if ( ! isset( $this->admin_columns ) ) {
			return $this;
		}

		$defaults = [
			'enable'     => true,
			'position'   => 'after title',
			'title'      => '',
			'before'     => '',
			'after'      => '',
			'sort'       => false,
			'searchable' => false,
			'filterable' => false,
			'link'       => false,
		];

		if ( is_bool( $this->admin_columns ) ) {
			$this->admin_columns = [ 'enable' => $this->admin_columns ];
		} elseif ( is_string( $this->admin_columns ) ) {
			$this->admin_columns = [
				'enable'   => true,
				'position' => $this->admin_columns,
			];
		}

		$this->admin_columns = array_merge( $defaults, $this->admin_columns );
		return $this;
	}

	public function unparse_default_values() {
		$this->id  = $this->id ?? uniqid();
		$this->_id = $this->_id ?? $this->id;

		$key_defaults = [
			'id'                => $this->id,
			'_id'               => $this->_id,
			'save_field'        => true,
			'label_description' => '',
			'desc'              => '',
			'size'              => '',
			'hide_from_rest'    => false,
			'hide_from_front'   => false,
			'before'            => '',
			'after'             => '',
			'class'             => '',
			'sanitize_callback' => '',
			'required'          => false,
			'disabled'          => false,
			'readonly'          => false,
			'prepend'           => '',
			'append'            => '',
		];

		foreach ( $key_defaults as $key => $default ) {
			$this->add_default( $key, $default );
		}

		return $this;
	}

	private function unparse_field_block_editor(): self {
		if ( ! class_exists( '\MBB\Helpers\AllowedBlockLists' ) ) {
			return $this;
		}

		if ( ! empty( $this->allowed_block_list ) || empty( $this->allowed_blocks ) ) {
			return $this;
		}

		$lists  = \MBB\Helpers\AllowedBlockLists::get_lists();
		$blocks = (array) $this->allowed_blocks;

		// Check if a list with the same blocks already exists.
		foreach ( $lists as $id => $list ) {
			if ( isset( $list['blocks'] ) && $list['blocks'] === $blocks ) {
				$this->allowed_block_list = $id;
				unset( $this->allowed_blocks );
				return $this;
			}
		}

		// Create a new list.
		$list_name = sprintf( __( '%s: allowed blocks', 'meta-box-builder' ), $this->name ?? $this->id ?? uniqid() );
		$list_id   = \MBB\Helpers\AllowedBlockLists::generate_id( $list_name );
		\MBB\Helpers\AllowedBlockLists::update_list( $list_id, $list_name, $blocks );
		$this->allowed_block_list = $list_id;
		unset( $this->allowed_blocks );
		return $this;
	}

	protected function ensure_boolean( $key ) {
		if ( isset( $this->$key ) ) {
			$this->$key = (bool) $this->$key;
		}
		return $this;
	}

	private function unparse_field_datetime(): self {
		if ( empty( $this->js_options ) ) {
			return $this;
		}

		$js_options  = is_array( $this->js_options ) ? $this->js_options : [];
		$date_format = $this->get_js_option_value( $js_options, 'dateFormat' );
		$time_format = $this->get_js_option_value( $js_options, 'timeFormat' );
		$separator   = $this->get_js_option_value( $js_options, 'separator' ) ?? ' ';

		if ( $date_format && $time_format ) {
			$this->datetime_format = "{$date_format}{$separator}{$time_format}";
		} elseif ( $date_format ) {
			$this->datetime_format = $date_format;
		} elseif ( $time_format ) {
			$this->datetime_format = $time_format;
		}

		foreach ( $js_options as $key => $option ) {
			if ( isset( $option['key'] ) && in_array( $option['key'], [ 'dateFormat', 'timeFormat', 'separator' ], true ) ) {
				unset( $js_options[ $key ] );
			}
		}
		$this->js_options = $js_options;

		return $this;
	}

	private function get_js_option_value( array $js_options, string $key ) {
		foreach ( $js_options as $option ) {
			if ( isset( $option['key'] ) && $option['key'] === $key ) {
				return $option['value'] ?? null;
			}
		}

		return null;
	}

	/**
	 * Detect keys in the JSON that are not native MetaBox field settings
	 * and move them into the custom_settings array format that the Builder
	 * UI can read and display in the Advanced tab.
	 *
	 * Requires meta-box-builder plugin to be active (safe fallback if not).
	 */
	private function unparse_custom_settings(): self {
		// Safe fallback: only run when Builder's FieldKeys helper is available.
		if ( ! class_exists( '\\MBB\\Helpers\\FieldKeys' ) ) {
			return $this;
		}

		$all_keys = \MBB\Helpers\FieldKeys::all();

		// Move any unrecognized key into custom_settings format.
		$custom_settings = $this->settings['custom_settings'] ?? [];

		foreach ( $this->original_keys as $key ) {
			if ( in_array( $key, $all_keys, true ) || ! isset( $this->settings[ $key ] ) ) {
				continue;
			}

			$value  = $this->settings[ $key ];
			$values = $this->array_to_dot_notation( [ $key => $value ] );
			foreach ( $values as $k => $v ) {
				$uid                     = uniqid();
				$custom_settings[ $uid ] = [
					'id'    => $uid,
					'key'   => $k,
					'value' => $v,
				];
			}

			unset( $this->settings[ $key ] );
		}

		if ( ! empty( $custom_settings ) ) {
			$this->settings['custom_settings'] = $custom_settings;
		}

		return $this;
	}
}

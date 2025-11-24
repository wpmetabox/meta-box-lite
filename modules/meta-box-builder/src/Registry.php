<?php
/**
 * Registry to store all registered fields and default controls.
 */

namespace MBB;

use MetaBox\Support\Arr;
use MBB\Helpers\Data;

class Registry {
	private $field_types = [];
	private $controls    = [];

	/**
	 * Register all default controls, so we can refer to them by id later.
	 *
	 * @param array $field_types Array of field types to generate the type selector options.
	 */
	public function register_default_controls( array $field_types = [] ): void {
		// Generate field type options grouped by category
		$type_options = [];

		// Get field categories with their labels
		$field_categories = Data::get_field_categories();
		$category_labels = [];
		foreach ( $field_categories as $cat ) {
			$category_labels[ $cat['slug'] ] = $cat['title'];
			// Initialize categories in the defined order
			$type_options[ $cat['title'] ] = [];
		}

		foreach ( $field_types as $type => $field_type ) {
			$category = $field_type['category'] ?? 'other';
			$category_label = $category_labels[ $category ] ?? ucfirst( $category );

			// If category doesn't exist in our predefined order, add it at the end
			if ( ! isset( $type_options[ $category_label ] ) ) {
				$type_options[ $category_label ] = [];
			}
			$type_options[ $category_label ][ $type ] = $field_type['title'];
		}

		// In the same order as in Fields class.
		$controls = [
			// General.
			Control::Select( 'type', [
				'label'   => __( 'Type', 'meta-box-builder' ),
				'options' => $type_options,
			], '', 'general' ),
			Control::Name( 'name', [
				'label'       => __( 'Label', 'meta-box-builder' ),
				'description' => __( 'Leave empty to make the input 100% width.', 'meta-box-builder' ),
			] ),
			Control::Id( 'id', [
				'label'       => __( 'ID', 'meta-box-builder' ),
				'required'    => true,
				'tooltip'     => __( 'Must be unique, will be used as the key when saving to the database', 'meta-box-builder' ),
				'description' => __( 'Use only lowercase letters, numbers, underscores (and be careful dashes).', 'meta-box-builder' ),
			] ),

			// Appearance
			Control::Input( 'label_description', [
				'label'       => __( 'Label description', 'meta-box-builder' ),
				'description' => __( 'Display below the field label.', 'meta-box-builder' ),
			], '', 'appearance' ),
			Control::Input( 'desc', [
				'label'       => __( 'Input description', 'meta-box-builder' ),
				'description' => __( 'Display below the field input.', 'meta-box-builder' ),
			], '', 'appearance' ),
			Control::Input( 'placeholder', __( 'Placeholder', 'meta-box-builder' ), '', 'appearance' ),
			Control::InputGroup( 'prepend_append', [
				'label'  => __( 'Text wrap', 'meta-box-builder' ),
				'label1' => __( 'Prepend', 'meta-box-builder' ),
				'label2' => __( 'Append', 'meta-box-builder' ),
				'key1'   => 'prepend',
				'key2'   => 'append',
			], [ 'prepend' => '', 'append' => '' ], 'appearance' ),
			Control::Input( 'size', [
				'type'        => 'number',
				'label'       => __( 'Input size', 'meta-box-builder' ),
				'description' => __( 'Leave empty to make the input 100% width.', 'meta-box-builder' ),
			], '', 'appearance' ),


			// Validation.
			Control::Validation( 'validation', [], [], 'validation' ),

			// Advanced.
			Control::Input( 'class', [
				'label'       => __( 'Custom CSS class', 'meta-box-builder' ),
				'description' => __( 'Custom CSS class for the field wrapper div.', 'meta-box-builder' ),
			], '', 'advanced' ),
			Control::Textarea( 'before', [
				'label'       => __( 'HTML before', 'meta-box-builder' ),
				'description' => __( 'Custom HTML displayed before the field wrapper HTML.', 'meta-box-builder' ),
			], '', 'advanced' ),
			Control::Textarea( 'after', [
				'label'       => __( 'HTML after', 'meta-box-builder' ),
				'description' => __( 'Custom HTML displayed after the field wrapper HTML.', 'meta-box-builder' ),
			], '', 'advanced' ),
			Control::Toggle( 'save_field', [
				'label'       => __( 'Save field value', 'meta-box-builder' ),
				'description' => __( 'Uncheck this checkbox to prevent the field from saving its value into the database. Use only when you want to save the data yourself with code.', 'meta-box-builder' ),
			], true, 'advanced' ),
			Control::Input( 'sanitize_callback', [
				'label'       => __( 'Custom sanitize callback', 'meta-box-builder' ),
				'description' => __( 'Enter a PHP function name to manually sanitize the value before saving it to the database. Enter "none" to bypass sanitization.', 'meta-box-builder' ),
			], '', 'advanced' ),
			Control::KeyValue( 'attributes', [
				'label'       => __( 'Custom attributes', 'meta-box-builder' ),
				'description' => __( 'Add custom attributes (like data-*) to the input. Work only for text fields.', 'meta-box-builder' ),
				'keys'        => [ 'disabled', 'max', 'maxlength', 'min', 'minlength', 'pattern', 'readonly', 'required', 'step', 'type' ],
				'values'      => [
					'disabled' => [ 'true', 'false' ],
					'readonly' => [ 'true', 'false' ],
					'required' => [ 'true', 'false' ],
				],
			], [], 'advanced' ),
			Control::KeyValue( 'custom_settings', [
				'label'       => __( 'Custom settings', 'meta-box-builder' ),
				'description' => __( 'Add custom settings to the field. Will overwrite existing settings if they have the <a href="https://docs.metabox.io/field-settings/" target="_blank">same key</a>. Use <a href="https://docs.metabox.io/extensions/meta-box-builder/#custom-settings" target="_blank">dot/JSON notation</a> to add nested settings.', 'meta-box-builder' ),
			], [], 'advanced' ),

			// Clone.
			Control::CloneSettings( 'clone_settings', [
				'label'   => __( 'Cloneable', 'meta-box-builder' ),
				'tooltip' => __( 'Make field cloneable (repeatable)', 'meta-box-builder' ),
			] ),

			// Date.
			Control::Input( 'std', __( 'Default value', 'meta-box-builder' ) ),
			Control::SelectWithInput( 'format', [
				'label' => __( 'Display format', 'meta-box-builder' ),
				// Translators: %s - URL to jQueryUI date picker page.
				'description' => sprintf( __( '<a href="%s" target="_blank">jQueryUI date format</a> (not PHP) to show in the input.', 'meta-box-builder' ), 'https://api.jqueryui.com/datepicker/#utility-formatDate' ),
				'options'     => [
					'yy-mm-dd'  => '2024-03-28 (yy-mm-dd)',
					'dd-mm-yy'  => '28-03-2024 (dd-mm-yy)',
					'mm/dd/yy'  => '03/28/2024 (mm/dd/yy)',
					'dd MM yy'  => '28 March 2024 (dd MM yy)',
					'M dd, yy' => 'Mar 03, 2024 (M dd, yy)',
					'MM dd, yy' => 'March 28, 2024 (MM dd, yy)',
				],
			] ),
			'save_format_date' => Control::SelectWithInput( 'save_format', [
				'label'         => __( 'Save format', 'meta-box-builder' ),
				// Translators: %s - URL to PHP's date() function page.
				'description'   => sprintf( __( '<a href="%s" target="_blank">PHP date format</a> for the value saved in the database. Leave empty to save as it is.', 'meta-box-builder' ), 'https://www.php.net/manual/en/datetime.format.php' ),
				'dependency'    => 'timestamp:false',
				'hide_in_group' => true,
				'options'       => [
					'Y-m-d'  => '2024-03-28 (Y-m-d)',
					'd-m-Y'  => '28-03-2024 (d-m-Y)',
					'm/d/Y'  => '03/28/2024 (m/d/Y)',
					'd F Y'  => '28 March 2024 (d F Y)',
					'M j, Y' => 'Mar 03, 2024 (M j, Y)',
					'F j, Y' => 'March 28, 2024 (F j, Y)',
				],
			] ),
			'save_format_datetime' => Control::SelectWithInput( 'save_format', [
				'label'         => __( 'Save format', 'meta-box-builder' ),
				// Translators: %s - URL to PHP's date() function page.
				'description'   => sprintf( __( '<a href="%s" target="_blank">PHP date format</a> for the value saved in the database. Leave empty to save as it is.', 'meta-box-builder' ), 'https://www.php.net/manual/en/datetime.format.php' ),
				'dependency'    => 'timestamp:false',
				'hide_in_group' => true,
				'options'       => [
					'Y-m-d H:i'    => '2024-03-28 09:20 (Y-m-d H:i)',
					'd-m-Y H:i'    => '28-03-2024 09:20 (d-m-Y H:i)',
					'm/d/Y H:i'    => '03/28/2024 09:20 (m/d/Y H:i)',
					'M j, Y h:i A' => 'Mar 28, 2024 09:20 AM (M j, Y h:i A)',
				],
			] ),
			Control::Toggle( 'timestamp', [
				'label'         => __( 'Save value as timestamp', 'meta-box-builder' ),
				'hide_in_group' => true,
			] ),
			'inline_date'     => Control::Toggle( 'inline', __( 'Display the date picker inline with the input', 'meta-box-builder' ), false, 'appearance' ),
			'inline_datetime' => Control::Toggle( 'inline', __( 'Display the date picker inline with the input', 'meta-box-builder' ), false, 'appearance' ),
			Control::Toggle( 'disabled', __( 'Disabled', 'meta-box-builder' ) ),
			Control::Required( 'required', __( 'Required', 'meta-box-builder' ) ),
			Control::Toggle( 'readonly', __( 'Read only', 'meta-box-builder' ) ),
			Control::InputAttributes( 'input_attributes', __( 'Attributes', 'meta-box-builder' ) ),
			'js_options_date'              => Control::KeyValue( 'js_options', [
				'label'       => __( 'Date picker options', 'meta-box-builder' ),
				// Translators: %s - URL to the jQueryUI date picker page.
				'description' => sprintf( __( 'Custom options for the <a href="%s" target="_blank">jQueryUI date picker</a> library.', 'meta-box-builder' ), 'https://api.jqueryui.com/datepicker/' ),
				'keys'        => [
					'buttonText',
					'changeMonth',
					'changeYear',
					'closeText',
					'currentText',
					'dateFormat',
					'dayNames',
					'dayNamesShort',
					'maxDate',
					'minDate',
					'monthNames',
					'monthNamesShort',
					'nextText',
					'numberOfMonths',
					'prevText',
					'showButtonPanel',
					'stepMonths',
					'yearRange',
				],
				'values'  => [
					'changeMonth'     => [ 'true', 'false' ],
					'changeYear'      => [ 'true', 'false' ],
					'dateFormat'      => [ 'yy-mm-dd', 'mm/dd/yy', 'dd-mm-yy' ],
					'showButtonPanel' => [ 'true', 'false' ],
				],
			], [], 'advanced' ),
			'js_options_datetime'          => Control::KeyValue( 'js_options', [
				'label'   => __( 'Datetime picker options', 'meta-box-builder' ),
				// Translators: %1$s - URL to the jQueryUI date picker page, %2$s - URL to the jQueryUI time picker page.
				'description' => sprintf( __( 'Custom options for the jQueryUI <a href="%1$s" target="_blank">date picker</a> and <a href="%2$s" target="_blank">time picker</a> libraries.', 'meta-box-builder' ), 'https://api.jqueryui.com/datepicker/', 'https://trentrichardson.com/examples/timepicker/' ),
				'keys'    => [
					'buttonText',
					'changeMonth',
					'changeYear',
					'closeText',
					'controlType',
					'currentText',
					'dateFormat',
					'dayNames',
					'dayNamesShort',
					'maxDate',
					'minDate',
					'monthNames',
					'monthNamesShort',
					'nextText',
					'numberOfMonths',
					'prevText',
					'showButtonPanel',
					'stepMonths',
					'timeFormat',
					'yearRange',
				],
				'values'  => [
					'changeMonth'     => [ 'true', 'false' ],
					'changeYear'      => [ 'true', 'false' ],
					'dateFormat'      => [ 'yy-mm-dd', 'mm/dd/yy', 'dd-mm-yy' ],
					'showButtonPanel' => [ 'true', 'false' ],
				],
			], [], 'advanced' ),

			// Map.
			'std_map'                      => Control::Input( 'std', [
				'label'       => __( 'Default location', 'meta-box-builder' ),
				'description' => __( 'Format: latitude,longitude.', 'meta-box-builder' ),
			] ),
			'std_osm'                      => Control::Input( 'std', [
				'label'       => __( 'Default location', 'meta-box-builder' ),
				'description' => __( 'Format: latitude,longitude.', 'meta-box-builder' ),
			] ),
			Control::AddressField( 'address_field', [
				'label'       => __( 'Address field', 'meta-box-builder' ),
				'description' => __( 'The ID of the address field. For multiple fields, separate them by comma.', 'meta-box-builder' ),
				'placeholder' => __( 'Enter or select a field ID', 'meta-box-builder' ),
				'required'    => true,
			] ),
			Control::Select( 'language', [
				'label'   => __( 'Language', 'meta-box-builder' ),
				'options' => $this->get_languages(),
			] ),
			Control::Select( 'region', [
				'label'   => __( 'Region', 'meta-box-builder' ),
				'options' => $this->get_regions(),
			] ),
			Control::Toggle( 'marker_draggable', __( 'Marker draggable', 'meta-box-builder' ), true ),

			// Taxonomy.
			Control::ReactSelect( 'taxonomy', [
				'name'    => 'taxonomy',
				'label'   => __( 'Taxonomies', 'meta-box-builder' ),
				'options' => $this->get_taxonomies(),
			] ),
			Control::Select( 'field_type', [
				'label'   => __( 'Field type', 'meta-box-builder' ),
				'options' => [
					'select'          => __( 'Select', 'meta-box-builder' ),
					'select_advanced' => __( 'Select advanced', 'meta-box-builder' ),
					'select_tree'     => __( 'Select tree', 'meta-box-builder' ),
					'checkbox_list'   => __( 'Checkbox list', 'meta-box-builder' ),
					'checkbox_tree'   => __( 'Checkbox tree', 'meta-box-builder' ),
					'radio_list'      => __( 'Radio list', 'meta-box-builder' ),
				],
			], 'select_advanced' ),
			Control::Toggle( 'add_new', __( 'Allow to create a new item', 'meta-box-builder' ) ),
			Control::Toggle( 'remove_default', __( 'Remove default meta box', 'meta-box-builder' ) ),
			Control::Toggle( 'multiple', __( 'Allow to select multiple items', 'meta-box-builder' ) ),
			Control::Toggle( 'select_all_none', __( 'Display "Toggle All" button', 'meta-box-builder' ), false, 'appearance' ),
			'select_all_none_post' => Control::Toggle( 'select_all_none', [
				'label'      => __( 'Display "Toggle All" button', 'meta-box-builder' ),
				'dependency' => 'multiple:true',
			], false, 'appearance' ),
			'select_all_none_taxonomy' => Control::Toggle( 'select_all_none', [
				'label'      => __( 'Display "Toggle All" button', 'meta-box-builder' ),
				'dependency' => 'multiple:true',
			], false, 'appearance' ),
			'select_all_none_taxonomy_advanced' => Control::Toggle( 'select_all_none', [
				'label'      => __( 'Display "Toggle All" button', 'meta-box-builder' ),
				'dependency' => 'multiple:true',
			], false, 'appearance' ),
			'select_all_none_user' => Control::Toggle( 'select_all_none', [
				'label'      => __( 'Display "Toggle All" button', 'meta-box-builder' ),
				'dependency' => 'multiple:true',
			], false, 'appearance' ),
			'select_all_none_select' => Control::Toggle( 'select_all_none', [
				'label'      => __( 'Display "Select: All | None" button', 'meta-box-builder' ),
				'dependency' => 'multiple:true',
			], false, 'appearance' ),
			'query_args_taxonomy'          => Control::KeyValue( 'query_args', [
				'label'       => __( 'Query args', 'meta-box-builder' ),
				// Translators: %s - URL to the get_terms() docs.
				'description' => sprintf( __( 'Query arguments for getting terms. Same as in the <a href="%s" target="_blank">get_terms()</a> function.', 'meta-box-builder' ), 'https://developer.wordpress.org/reference/classes/wp_term_query/__construct/' ),
				'keys'        => [
					'object_ids',
					'orderby',
					'order',
					'hide_empty',
					'include',
					'exclude',
					'exclude_tree',
					'number',
					'offset',
					'name',
					'slug',
					'hierarchical',
					'search',
					'name__like',
					'description__like',
					'child_of',
					'parent',
					'childless',
					'meta_key',
					'meta_value',
					'meta_compare',
				],
				'values'  => [
					'order'                => [ 'ASC', 'DESC' ],
					'hide_empty'           => [ 'true', 'false' ],
					'hierarchical'         => [ 'true', 'false' ],
					'childless'            => [ 'true', 'false' ],
					'meta_compare'         => [ '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'NOT EXISTS', 'REGEXP', 'NOT REGEXP', 'RLIKE' ],
					'meta_query.relation'  => [ 'AND', 'OR' ],
					'meta_query.0.compare' => [ '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS', 'NOT EXISTS' ],
					'meta_query.0.type'    => [ 'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED' ],
				],
			] ),
			'query_args_taxonomy_advanced' => Control::KeyValue( 'query_args', [
				'label'       => __( 'Query args', 'meta-box-builder' ),
				// Translators: %s - URL to the get_terms() docs.
				'description' => sprintf( __( 'Query arguments for getting terms. Same as in the <a href="%s" target="_blank">get_terms()</a> function.', 'meta-box-builder' ), 'https://developer.wordpress.org/reference/classes/wp_term_query/__construct/' ),
				'keys'    => [
					'object_ids',
					'orderby',
					'order',
					'hide_empty',
					'include',
					'exclude',
					'exclude_tree',
					'number',
					'offset',
					'name',
					'slug',
					'hierarchical',
					'search',
					'name__like',
					'description__like',
					'child_of',
					'parent',
					'childless',
					'meta_key',
					'meta_value',
					'meta_compare',
				],
				'values'  => [
					'order'                => [ 'ASC', 'DESC' ],
					'hide_empty'           => [ 'true', 'false' ],
					'hierarchical'         => [ 'true', 'false' ],
					'childless'            => [ 'true', 'false' ],
					'meta_compare'         => [ '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'NOT EXISTS', 'REGEXP', 'NOT REGEXP', 'RLIKE' ],
					'meta_query.relation'  => [ 'AND', 'OR' ],
					'meta_query.0.compare' => [ '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS', 'NOT EXISTS' ],
					'meta_query.0.type'    => [ 'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED' ],
				],
			] ),

			// Upload.
			Control::Input( 'max_file_uploads', [
				'type'        => 'number',
				'label'       => __( 'Max number of files', 'meta-box-builder' ),
				'description' => __( 'Leave empty for unlimited uploads.', 'meta-box-builder' ),
			] ),
			Control::Toggle( 'max_status', [
				'label'   => __( 'Show status', 'meta-box-builder' ),
				'tooltip' => __( 'Display how many files uploaded/remaining', 'meta-box-builder' ),
			], true ),
			Control::Toggle( 'force_delete', [
				'label'   => __( 'Force delete', 'meta-box-builder' ),
				'tooltip' => __( 'Delete files when deleting them from post meta', 'meta-box-builder' ),
			] ),

			// Field specific.

			// Autocomplete.
			Control::Textarea( 'options', [
				'label'       => __( 'Choices', 'meta-box-builder' ),
				'description' => __( 'Enter each choice per line. Use <code>value: Label</code> format for both value and label or <code>callback: function_name</code> for a PHP callback (the function must exist).', 'meta-box-builder' ),
			] ),

			// Button.
			'std_button'                   => Control::Input( 'std', __( 'Button text', 'meta-box-builder' ), __( 'Click me', 'meta-box-builder' ) ),

			// Button group.
			'options_button_group'         => Control::Textarea( 'options', [
				'label'       => __( 'Buttons', 'meta-box-builder' ),
				'description' => __( 'Enter each button text per line. Use <code>value: Label</code> format for both value and label or <code>callback: function_name</code> for a PHP callback (the function must exist).', 'meta-box-builder' ),
			] ),
			'inline_button_group'          => Control::Toggle( 'inline', __( 'Display buttons horizontally', 'meta-box-builder' ), true, 'appearance' ),

			// Checkbox.
			'std_checkbox'                 => Control::Toggle( 'std', __( 'Checked by default', 'meta-box-builder' ) ),

			// Checkbox list.
			'std_checkbox_list'            => Control::Textarea( 'std', [
				'label'       => __( 'Default value', 'meta-box-builder' ),
				'description' => __( 'Enter each value on a line.', 'meta-box-builder' ),
			] ),
			Control::Toggle( 'inline', __( 'Display choices horizontally', 'meta-box-builder' ), false, 'appearance' ),

			// Color.
			Control::Toggle( 'alpha_channel', __( 'Allow to select opacity', 'meta-box-builder' ) ),
			'js_options_color'             => Control::KeyValue( 'js_options', [
				'label'       => __( 'Color picker options', 'meta-box-builder' ),
				'description' => __( 'Custom options for the color picker library. <a href="https://automattic.github.io/Iris/" target="_blank">See here</a>.', 'meta-box-builder' ),
				'keys'        => [ 'mode', 'width', 'palettes' ],
				'values'      => [
					'mode' => [ 'hsl', 'hsv' ],
				],
			], [], 'advanced' ),

			// Custom HTML.
			'std_custom_html'              => Control::Textarea( 'std', __( 'Content (HTML allowed)', 'meta-box-builder' ) ),
			Control::Input( 'callback', [
				'label'       => __( 'Custom callback', 'meta-box-builder' ),
				'description' => __( 'A PHP function that is called to show custom HTML content. Will overwrite the content setting above.', 'meta-box-builder' ),
			] ),

			// Fieldset text.
			'options_fieldset_text'        => Control::KeyValue( 'options', [
				'label'            => __( 'Inputs', 'meta-box-builder' ),
				'valuePlaceholder' => __( 'Enter label', 'meta-box-builder' ),
			] ),

			// File.
			Control::Input( 'upload_dir', [
				'label'       => __( 'Custom upload folder', 'meta-box-builder' ),
				'description' => __( 'Relatively to the WordPress root path.', 'meta-box-builder' ),
			] ),

			// File advanced.
			Control::Input( 'mime_type', [
				'label'       => __( 'MIME types', 'meta-box-builder' ),
				'description' => __( 'Filters items in the Media Library popup. Does not restrict file types when upload. Separate by commas.', 'meta-box-builder' ),
			] ),

			// File upload.
			Control::FileSize( 'max_file_size', __( 'Max file size', 'meta-box-builder' ) ),

			// Map.
			Control::Input( 'api_key', [
				'label'       => __( 'Google Maps API key', 'meta-box-builder' ),
				'description' => sprintf( __( 'If you don\'t have one, <a href="%s" target="_blank">create one here</a>.', 'meta-box-builder' ), 'https://developers.google.com/maps/documentation/javascript/get-api-key' ),
				'required'    => true,
			] ),

			// Heading.
			'name_heading' => Control::Name( 'name', [
				'label' => __( 'Heading text', 'meta-box-builder' ),
			] ),
			'desc_heading' => Control::Input( 'desc', [
				'label'       => __( 'Description', 'meta-box-builder' ),
				'description' => __( 'Display below the heading text.', 'meta-box-builder' ),
			], '', 'general' ),

			// Image select.
			'options_image_select' => Control::Textarea( 'options', [
				'label'       => __( 'Choices', 'meta-box-builder' ),
				'description' => __( 'Enter each choice per line. Use <code>value: image URL</code> format or <code>callback: function_name</code> for a PHP callback (the function must exist).', 'meta-box-builder' ),
			] ),

			// Image advanced.
			Control::Select( 'image_size', [
				'label'   => __( 'Image size', 'meta-box-builder' ),
				'tooltip' => __( 'Image size that displays in the edit page, used to make sure images are not blurry. It\'s not meant to display images with the exact width and height.', 'meta-box-builder' ),
				'options' => $this->get_image_sizes(),
			], 'thumbnail' ),
			Control::Radio( 'add_to', [
				'label'   => __( 'New image placement', 'meta-box-builder' ),
				'options' => [
					'beginning' => __( 'Beginning of the list', 'meta-box-builder' ),
					'end'       => __( 'End of the list', 'meta-box-builder' ),
				],
			], 'end' ),

			// Key value.
			Control::Input( 'placeholder_key', __( 'Placeholder for key', 'meta-box-builder' ), '', 'appearance' ),
			Control::Input( 'placeholder_value', __( 'Placeholder for value', 'meta-box-builder' ), '', 'appearance' ),

			// Number.
			Control::InputGroup( 'minmax', [
				'label'  => __( 'Limit', 'meta-box-builder' ),
				'label1' => __( 'Min', 'meta-box-builder' ),
				'label2' => __( 'Max', 'meta-box-builder' ),
				'key1'   => 'min',
				'key2'   => 'max',
			] ),
			Control::Input( 'step', [
				'label'       => __( 'Step', 'meta-box-builder' ),
				'description' => __( "Set the increments at which a numeric value can be set. Enter 'any' to accept any number.", 'meta-box-builder' ),
			] ),

			// Oembed.
			Control::Input( 'not_available_string', [
				'label'       => __( 'Not available text', 'meta-box-builder' ),
				'description' => __( 'The text message displayed to users when the embed media is not available. Accepts HTML.', 'meta-box-builder' ),
			] ),

			// Post.
			Control::ReactSelect( 'post_type', [
				'name'    => 'post_type',
				'label'   => __( 'Post types', 'meta-box-builder' ),
				'options' => $this->get_post_types(),
			], [ 'post' ] ),
			Control::Toggle( 'parent', [
				'label'   => __( 'Set as parent', 'meta-box-builder' ),
				'tooltip' => __( 'Set the selected post as the parent of current post being edited.', 'meta-box-builder' ),
			] ),
			'query_args_post'              => Control::KeyValue( 'query_args', [
				'label'       => __( 'Query args', 'meta-box-builder' ),
				'description' => sprintf( __( 'Query arguments for getting posts. Same as in the <a href="%s" target="_blank">WP_Query</a> class.', 'meta-box-builder' ), 'https://developer.wordpress.org/reference/classes/wp_query/' ),
				'keys'        => [
					'author',
					'author_name',
					'author__in',
					'author__not_in',
					'cat',
					'category_name',
					'category__and',
					'category__in',
					'category__not_in',
					'tag',
					'tag_id',
					'tag__and',
					'tag__in',
					'tag__not_in',
					'tag_slug__and',
					'tag_slug__in',
					'tax_query.relation',
					'tax_query.0.taxonomy',
					'tax_query.0.field',
					'tax_query.0.terms',
					'tax_query.0.include_children',
					'tax_query.0.operator',
					's',
					'p',
					'name',
					'page_id',
					'pagename',
					'post_parent',
					'post_parent__in',
					'post_parent__not_in',
					'post__in',
					'post__not_in',
					'post_name__in',
					'has_password',
					'post_type',
					'post_status',
					'nopaging',
					'posts_per_page',
					'offset',
					'paged',
					'ignore_sticky_posts',
					'order',
					'orderby',
					'year',
					'monthnum',
					'date_query',
					'meta_key',
					'meta_value',
					'meta_value_num',
					'meta_compare',
					'meta_query.relation',
					'meta_query.0.key',
					'meta_query.0.value',
					'meta_query.0.compare',
					'meta_query.0.type',
				],
				'values'  => [
					'author_name'          => [ 'user_nicename' ],
					'has_password'         => [ 'true', 'false' ],
					'nopaging'             => [ 'true', 'false' ],
					'ignore_sticky_posts'  => [ 'true', 'false' ],
					'order'                => [ 'ASC', 'DESC' ],
					'tax_query.relation'   => [ 'AND', 'OR' ],
					'meta_compare'         => [ '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'NOT EXISTS', 'REGEXP', 'NOT REGEXP', 'RLIKE' ],
					'meta_query.relation'  => [ 'AND', 'OR' ],
					'meta_query.0.compare' => [ '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS', 'NOT EXISTS' ],
					'meta_query.0.type'    => [ 'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED' ],
					'default'              => [
						'AND',
						'OR',
						'term_id',
						'name',
						'slug',
						'term_taxonomy_id',
						'publish',
						'pending',
						'draft',
						'future',
						'private',
						'any',
						'author',
						'title',
						'type',
						'date',
						'modified',
						'parent',
						'comment_count',
						'relevance',
						'menu_order',
						'meta_value',
						'meta_value_num',
						'post__in',
						'post_name__in',
						'post_parent__in',
					],
				],
			] ),

			// Select advanced.
			'js_options_select_advanced'   => Control::KeyValue( 'js_options', [
				'label'       => __( 'Select2 options', 'meta-box-builder' ),
				'description' => sprintf( __( 'Advanced options for the <a href="%s" target="_blank">select2</a> library.', 'meta-box-builder' ), 'https://select2.org/configuration/options-api' ),
				'keys'        => [
					'ajax',
					'allowClear',
					'closeOnSelect',
					'dir',
					'disabled',
					'dropdownAutoWidth',
					'dropdownCssClass',
					'language',
					'maximumInputLength',
					'maximumSelectionLength',
					'minimumInputLength',
					'minimumResultsForSearch',
					'scrollAfterSelect',
					'selectionCssClass',
					'selectOnClose',
					'width',
				],
				'values'  => [
					'allowClear'        => [ 'true', 'false' ],
					'closeOnSelect'     => [ 'true', 'false' ],
					'disabled'          => [ 'true', 'false' ],
					'scrollAfterSelect' => [ 'true', 'false' ],
				],
			], [], 'advanced' ),

			// Icon.
			Control::Radio( 'icon_set', [
				'label'   => __( 'Icon set', 'meta-box-builder' ),
				'options' => [
					'font-awesome-free' => __( 'Font Awesome Free', 'meta-box-builder' ),
					'font-awesome-pro'  => __( 'Font Awesome Pro', 'meta-box-builder' ),
					'custom'            => __( 'Custom', 'meta-box-builder' ),
				],
			], 'font-awesome-free' ),
			Control::Input( 'icon_file', [
				'type'        => 'text',
				'label'       => __( 'Icon file', 'meta-box-builder' ),
				'description' => __( 'The full path to the icon file definition, which can be a text or JSON file.', 'meta-box-builder' ),
				'dependency'  => 'icon_set:[font-awesome-pro,custom]',
			] ),
			Control::Input( 'icon_dir', [
				'type'        => 'text',
				'label'       => __( 'Icon dir', 'meta-box-builder' ),
				'description' => __( 'Full path to the folder that contains all SVGs of icons.', 'meta-box-builder' ),
				'dependency'  => 'icon_set:custom',
			] ),
			Control::Input( 'icon_css', [
				'label'       => __( 'Icon CSS URL', 'meta-box-builder' ),
				'description' => __( 'Required only when you use icons as an icon font (e.g. no SVG).', 'meta-box-builder' ),
				'dependency'  => 'icon_set:custom',
			] ),

			// Slider.
			Control::InputGroup( 'prefix_suffix', [
				'label'  => __( 'Text wrap', 'meta-box-builder' ),
				'label1' => __( 'Prefix', 'meta-box-builder' ),
				'label2' => __( 'Suffix', 'meta-box-builder' ),
				'key1'   => 'prefix',
				'key2'   => 'suffix',
			], ['prefix' => '', 'suffix' => ''], 'appearance' ),
			'js_options_slider' => Control::KeyValue( 'js_options', [
				'label'       => __( 'Slider options', 'meta-box-builder' ),
				'description' => sprintf( __( 'Custom options for the <a href="%s" target="_blank">jQueryUI slider</a>.', 'meta-box-builder' ), 'https://api.jqueryui.com/slider' ),
				'keys'        => [ 'animate', 'max', 'min', 'orientation', 'step' ],
				'values'      => [
					'orientation' => [ 'horizontal', 'vertical' ],
					'animate'     => [ 'true', 'false', 'fast', 'slow' ],
				],
			], [], 'advanced' ),

			// Switch.
			Control::ToggleGroup( 'style', [
				'label'   => __( 'Style', 'meta-box-builder' ),
				'options' => [
					'rounded' => __( 'Rounded', 'meta-box-builder' ),
					'square'  => __( 'Square', 'meta-box-builder' ),
				],
			], 'rounded', 'appearance' ),
			Control::InputGroup( 'on_off', [
				'label'  => __( 'Labels', 'meta-box-builder' ),
				'label1' => __( 'ON', 'meta-box-builder' ),
				'label2' => __( 'OFF', 'meta-box-builder' ),
				'key1'   => 'on_label',
				'key2'   => 'off_label',
			], [ 'on_label' => '', 'off_label' => '' ], 'appearance' ),
			'std_switch' => Control::Toggle( 'std', __( 'ON by default', 'meta-box-builder' ) ),

			// Text.
			Control::Textarea( 'datalist_choices', [
				'label'       => __( 'Predefined values', 'meta-box-builder' ),
				'description' => __( 'Known as "datalist", these are values that users can select from (they still can enter text if they want). Enter each value on a line.', 'meta-box-builder' ),
			], '', 'advanced' ),

			// Text list.
			'options_text_list'            => Control::KeyValue( 'options', [
				'label'            => __( 'Inputs', 'meta-box-builder' ),
				'keyPlaceholder'   => __( 'Placeholder', 'meta-box-builder' ),
				'valuePlaceholder' => __( 'Label', 'meta-box-builder' ),
			] ),

			// Textarea.
			'std_textarea' => Control::Textarea( 'std', __( 'Default value', 'meta-box-builder' ) ),
			Control::InputGroup( 'textarea_size', [
				'label'  => __( 'Size', 'meta-box-builder' ),
				'label1' => __( 'Rows', 'meta-box-builder' ),
				'label2' => __( 'Columns', 'meta-box-builder' ),
				'key1'   => 'rows',
				'key2'   => 'cols',
			], [ 'rowls' => '', 'cols' => '' ], 'appearance' ),

			// Time.
			'inline_time'                  => Control::Toggle( 'inline', __( 'Display the time picker inline with the input', 'meta-box-builder' ), false, 'appearance' ),
			'js_options_time'              => Control::KeyValue( 'js_options', [
				'label'       => __( 'Time picker options', 'meta-box-builder' ),
				// Translators: %s - URL to the time picker page.
				'description' => sprintf( __( 'Custom options for the <a href="%s" target="_blank">jQueryUI time picker</a> library.', 'meta-box-builder' ), 'http://trentrichardson.com/examples/timepicker' ),
				'keys'        => [ 'controlType', 'timeFormat' ],
				'values'      => [
					'controlType' => [ 'select', 'slider' ],
					'timeFormat'  => [ 'HH:mm', 'HH:mm T' ],
				],
			], [], 'advanced' ),

			// User.
			'query_args_user'              => Control::KeyValue( 'query_args', [
				'label'       => __( 'Query args', 'meta-box-builder' ),
				// Translators: %s - URL to the get_users() page.
				'description' => sprintf( __( 'Query arguments for getting user. Same as in the <a href="%s target="_blank">get_user()</a> function.', 'meta-box-builder' ), 'https://developer.wordpress.org/reference/classes/wp_user_query/prepare_query/' ),
				'keys'        => [
					'blog_id',
					'role',
					'role__in',
					'role__not_in',
					'meta_key',
					'meta_value',
					'meta_compare',
					'meta_compare_key',
					'meta_type',
					'meta_type_key',
					'meta_query.relation',
					'meta_query.0.key',
					'meta_query.0.value',
					'meta_query.0.compare',
					'meta_query.0.type',
					'capability',
					'capability__in',
					'capability__not_in',
					'include',
					'exclude',
					'search',
					'search_columns',
					'orderby',
					'order',
					'offset',
					'number',
					'paged',
					'who',
					'has_published_posts',
					'nicename',
					'nicename__in',
					'nicename__not_in',
					'login',
					'login__in',
					'login__not_in',
				],
				'values'  => [
					'order'                => [ 'ASC', 'DESC' ],
					'nicename'             => 'user_nicename',
					'meta_compare'         => [ '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'NOT EXISTS', 'REGEXP', 'NOT REGEXP', 'RLIKE' ],
					'meta_query.relation'  => [ 'AND', 'OR' ],
					'meta_query.0.compare' => [ '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS', 'NOT EXISTS' ],
					'meta_query.0.type'    => [ 'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED' ],
					'default'              => [
						'ID',
						'display_name',
						'include',
						'user_login',
						'login__in',
						'nicename__in',
						'user_email',
						'user_url',
						'user_registered',
						'post_count',
						'meta_value',
						'meta_value_num',
					],
				],
			] ),

			// Wysiwyg.
			Control::Toggle( 'raw', [
				'label'   => __( 'Save data in the raw format.', 'meta-box-builder' ),
				'tooltip' => __( 'Do not apply wpautop() to the value before saving to the database', 'meta-box-builder' )
			] ),
			'options_wysiwyg'              => Control::KeyValue( 'options', [
				'label'       => __( 'Editor options', 'meta-box-builder' ),
				// Translators: %s - URL to the wp_editor() page.
				'description' => sprintf( __( 'The editor options, the same as settings for the <a href="%s" target="_blank">wp_editor()</a> function', 'meta-box-builder' ), 'https://developer.wordpress.org/reference/functions/wp_editor/' ),
				'keys'        => [ 'media_buttons', 'default_editor', 'drag_drop_upload', 'quicktags', 'textarea_rows', 'teeny' ],
				'values'      => [
					'media_buttons'    => [ 'true', 'false' ],
					'drag_drop_upload' => [ 'true', 'false' ],
					'teeny'            => [ 'true', 'false' ],
					'default'          => [ 'true', 'false', 'tinymce', 'html' ],
				],
			], [], 'advanced' ),
		];

		foreach ( $controls as $id => $control ) {
			$id = is_string( $id ) ? $id : $control['setting'];
			$this->add_control( $id, $control );
		}
	}

	/**
	 * Add a new control to the registry.
	 *
	 * @param string $id      Control id.
	 * @param array  $control Control parameters.
	 *
	 * [
	 *     'setting'  => 'id',      // Setting name
	 *     'name'     => 'Input',   // Control name
	 *     'tab'      => 'general', // Tab: general or advanced
	 *     'default'  => '',        // Default value
	 *     'props'    => [          // Control props
	 *         'label'      => __( 'ID', 'meta-box-builder' ),            // Control label
	 *         'required'   => true,                                      // Is it required?
	 *         'tooltip'    => __( 'Must be unique, 'meta-box-builder' ), // Optional tooltip
	 *         'name'       => 'custom_input[name]',                      // Custom input name if different from the setting name. Optional.
	 *         'dependency' => 'other_field:value',                       // Show only another field has a specific value.
	 *         'options'    => ['key' => 'value'],                        // Options for Select, SelectReact controls.
	 *     ],
	 * ],
	 */
	private function add_control( $id, $control ) {
		$this->controls[ $id ] = $control;
	}

	/**
	 * Transform fields controls to proper format (array).
	 */
	public function transform_controls(): void {
		foreach ( $this->field_types as $type => &$field_type ) {
			foreach ( $field_type['controls'] as &$control ) {
				$control = $this->get_control( $control, $type );
			}

			// Remove empty controls like 'clone', 'sort_clone', as some controls are merged into a single control ('clone_settings').
			$field_type['controls'] = array_values( array_filter( $field_type['controls'] ) );
		}
	}

	/**
	 * Get control array by its id (string) and field type. Order:
	 * - If array, do nothing
	 * - Get control with id = control_type
	 * - Get control with id = control
	 */
	private function get_control( $control, $type ) {
		if ( is_array( $control ) ) {
			return $control;
		}
		return Arr::get( $this->controls, "{$control}_{$type}", Arr::get( $this->controls, $control ) );
	}

	/**
	 * Add a new field type.
	 *
	 * @param string $type Field type.
	 * @param array  $args Field type parameters.
	 *
	 * [
	 *     'title'    => __( 'Text', 'meta-box-builder' ),
	 *     'category' => 'basic',
	 *     'controls' => [$control_1, $control_2]
	 * ],
	 */
	public function add_field_type( $type, $args ) {
		$this->field_types[ $type ] = $args;
	}

	public function get_field_types() {
		return $this->field_types;
	}

	private function get_image_sizes() {
		$image_sizes    = [];
		$wp_image_sizes = get_intermediate_image_sizes();
		foreach ( $wp_image_sizes as $size_name ) {
			$image_sizes[ $size_name ] = ucwords( str_replace( [ '_', '-' ], ' ', $size_name ) );
		}
		return $image_sizes;
	}

	private function get_languages() {
		$language_codes = 'ar,bg,bn,ca,cs,da,de,el,en,en-AU,en-GB,es,eu,eu,fa,fi,fil,fr,gl,gu,hi,hr,hu,id,it,iw,ja,kn,ko,lt,lv,ml,mr,nl,no,pl,pt,pt-BR,pt-PT,ro,ru,sk,sl,sr,sv,ta,te,th,tl,tr,uk,vi,zh-CN,zh-TW';
		$language_names = 'Arabic,Bulgarian,Bengali,Catalan,Czech,Danish,German,Greek,English,English (Australian),English (Great Britain),Spanish,Basque,Basque,Farsi,Finnish,Filipino,French,Galician,Gujarati,Hindi,Croatian,Hungarian,Indonesian,Italian,Hebrew,Japanese,Kannada,Korean,Lithuanian,Latvian,Malayalam,Marathi,Dutch,Norwegian,Polish,Portuguese,Portuguese (Brazil),Portuguese (Portugal),Romanian,Russian,Slovak,Slovenian,Serbian,Swedish,Tamil,Telugu,Thai,Tagalog,Turkish,Ukrainian,Vietnamese,Chinese (Simplified),Chinese (Traditional)';
		$language_codes = explode( ',', $language_codes );
		$language_names = explode( ',', $language_names );
		$languages      = array_combine( $language_codes, $language_names );

		return $languages;
	}

	private function get_regions() {
		$region_codes = 'ac,ad,ae,af,ag,ai,al,am,ao,aq,ar,as,at,au,aw,ax,az,ba,bb,bd,be,bf,bg,bh,bi,bj,bm,bn,bo,bq,br,bs,bt,bw,by,bz,ca,cc,cd,cf,cg,ch,ci,ck,cl,cm,cn,co,cr,cu,cv,cw,cx,cy,cz,de,dj,dk,dm,do,dz,ec,ee,eg,eh,er,es,et,eu,fi,fj,fk,fm,fo,fr,ga,gd,ge,gf,gg,gh,gi,gl,gm,gn,gp,gq,gr,gs,gt,gu,gw,gy,hk,hm,hn,hr,ht,hu,id,ie,il,im,in,io,iq,ir,is,it,je,jm,jo,jp,ke,kg,kh,ki,km,kn,kp,kr,kw,ky,kz,la,lb,lc,li,lk,lr,ls,lt,lu,lv,ly,ma,mc,md,me,mg,mh,mk,ml,mm,mn,mo,mp,mq,mr,ms,mt,mu,mv,mw,mx,my,mz,na,nc,ne,nf,ng,ni,nl,no,np,nr,nu,nz,om,pa,pe,pf,pg,ph,pk,pl,pm,pn,pr,ps,pt,pw,py,qa,re,ro,rs,ru,rw,sa,sb,sc,sd,se,sg,sh,si,sk,sl,sm,sn,so,sr,ss,st,su,sv,sx,sy,sz,tc,td,tf,tg,th,tj,tk,tl,tm,tn,to,tr,tt,tv,tw,tz,ua,ug,uk,us,uy,uz,va,vc,ve,vg,vi,vn,vu,wf,ws,ye,yt,za,zm,zw';
		$region_names = 'Ascension Island (United Kingdom),Andorra,United Arab Emirates,Afghanistan,Antigua and Barbuda,Anguilla (United Kingdom),Albania,Armenia,Angola,Antarctica,Argentina,American Samoa (United States),Austria,Australia,Aruba (Kingdom of the Netherlands),Åland (Finland),Azerbaijan,Bosnia and Herzegovina,Barbados,Bangladesh,Belgium,Burkina Faso,Bulgaria,Bahrain,Burundi,Benin,Bermuda (United Kingdom),Brunei,Bolivia,Caribbean Netherlands (Bonaire - Saba -Sint Eustatius),Brazil,Bahamas,Bhutan,Botswana,Belarus,Belize,Canada,Cocos (Keeling) Islands (Australia),Democratic Republic of the Congo,Central African Republic,Republic of the Congo,Switzerland,Ivory Coast,Cook Islands,Chile,Cameroon,People\'s Republic of China,Colombia,Costa Rica,Cuba,Cape Verde,Curaçao (Kingdom of the Netherlands),Christmas Island,Cyprus,Czech Republic,Germany,Djibouti,Denmark,Dominica,Dominican Republic,Algeria,Ecuador,Estonia,Egypt,Western Sahara,Eritrea,Spain,Ethiopia,European Union,Finland,Fiji,Falkland Islands (United Kingdom),Federated States of Micronesia,Faroe Islands (Kingdom of Denmark),France,Gabon,Grenada,Georgia,French Guiana (France),Guernsey (United Kingdom),Ghana,Gibraltar (United Kingdom),Greenland (Kingdom of Denmark),The Gambia,Guinea,Guadeloupe (France),Equatorial Guinea,Greece,South Georgia and the South Sandwich Islands (United Kingdom),Guatemala,Guam (United States),Guinea-Bissau,Guyana,Hong Kong,Heard Island and McDonald Islands,Honduras,Croatia,Haiti,Hungary,Indonesia,Ireland,Israel,Isle of Man (United Kingdom),India,British Indian Ocean Territory (United Kingdom),Iraq,Iran,Iceland,Italy,Jersey (United Kingdom),Jamaica,Jordan,Japan,Kenya,Kyrgyzstan,Cambodia,Kiribati,Comoros,Saint Kitts and Nevis,North Korea,South Korea,Kuwait,Cayman Islands (United Kingdom),Kazakhstan,Laos,Lebanon,Saint Lucia,Liechtenstein,Sri Lanka,Liberia,Lesotho,Lithuania,Luxembourg,Latvia,Libya,Morocco,Monaco,Moldova,Montenegro,Madagascar,Marshall Islands,North Macedonia,Mali,Myanmar,Mongolia,Macau,Northern Mariana Islands (United States),Martinique (France),Mauritania,Montserrat (United Kingdom),Malta,Mauritius,Maldives,Malawi,Mexico,Malaysia,Mozambique,Namibia,New Caledonia (France),Niger,Norfolk Island,Nigeria,Nicaragua,Netherlands,Norway,Nepal,Nauru,Niue,New Zealand,Oman,Panama,Peru,French Polynesia (France),Papua New Guinea,Philippines,Pakistan,Poland,Saint-Pierre and Miquelon (France),Pitcairn Islands (United Kingdom),Puerto Rico (United States),Palestine[34],Portugal,Palau,Paraguay,Qatar,Réunion (France),Romania,Serbia,Russia,Rwanda,Saudi Arabia,Solomon Islands,Seychelles,Sudan,Sweden,Singapore,Saint Helena (United Kingdom),Slovenia,Slovakia,Sierra Leone,San Marino,Senegal,Somalia,Suriname,South Sudan,São Tomé and Príncipe,Soviet Union,El Salvador,Sint Maarten (Kingdom of the Netherlands),Syria,Eswatini,Turks and Caicos Islands (United Kingdom),Chad,French Southern and Antarctic Lands,Togo,Thailand,Tajikistan,Tokelau,East Timor,Turkmenistan,Tunisia,Tonga,Turkey,Trinidad and Tobago,Tuvalu,Taiwan,Tanzania,Ukraine,Uganda,United Kingdom,United States of America,Uruguay,Uzbekistan,Vatican City,Saint Vincent and the Grenadines,Venezuela,British Virgin Islands (United Kingdom),United States Virgin Islands (United States),Vietnam,Vanuatu,Wallis and Futuna,Samoa,Yemen,Mayotte,South Africa,Zambia,Zimbabwe';
		$region_codes = explode( ',', $region_codes );
		$region_names = explode( ',', $region_names );
		$regions      = array_combine( $region_codes, $region_names );

		return $regions;
	}

	private function get_post_types() {
		$post_types = Helpers\Data::get_post_types();
		$options    = [];
		foreach ( $post_types as $post_type ) {
			$options[ $post_type['slug'] ] = sprintf( '%s (%s)', $post_type['name'], $post_type['slug'] );
		}
		return $options;
	}

	private function get_taxonomies() {
		$taxonomies = Helpers\Data::get_taxonomies();
		$options    = [];
		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy['slug'] ] = sprintf( '%s (%s)', $taxonomy['name'], $taxonomy['slug'] );
		}
		return $options;
	}
}

<?php
/**
 * Registry to store all registered fields and default controls.
 */

namespace MBB;

use MetaBox\Support\Arr;

class Registry {
	private $field_types = [];
	private $controls    = [];

	/**
	 * Register all default controls, so we can refer to them by id later.
	 */
	public function register_default_controls() {
		// In the same order as in Fields class.
		$controls = [
			// General.
			Control::Type( 'type', __( 'Type', 'meta-box-builder' ) ),
			Control::Name( 'name', [
				'label'   => __( 'Label', 'meta-box-builder' ),
				'tooltip' => __( 'Leave empty to make the input 100% width.', 'meta-box-builder' ),
			] ),
			Control::Id( 'id', [
				'label'       => __( 'ID', 'meta-box-builder' ),
				'required'    => true,
				'tooltip'     => __( 'Must be unique, will be used as meta key when saving to the database. Recommended to use only lowercase letters, numbers, and underscores.', 'meta-box-builder' ),
				'description' => __( 'Use only lowercase letters, numbers, underscores (and be careful dashes).', 'meta-box-builder' ),
			] ),
			Control::Input( 'label_description', [
				'label'   => __( 'Label description', 'meta-box-builder' ),
				'tooltip' => __( 'Display below the field label', 'meta-box-builder' ),
			] ),
			Control::Input( 'desc', [
				'label'   => __( 'Input description', 'meta-box-builder' ),
				'tooltip' => __( 'Display below the field input', 'meta-box-builder' ),
			] ),

			// Advanced.
			Control::Validation( 'validation', [
				'label'   => '<a href="https://docs.metabox.io/validation/" target="_blank" rel="noreffer noopener">' . __( 'Validation', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'Advanced validation rules powered by jQuery validation', 'meta-box-builder' ),
			], [], 'advanced' ),
			Control::Textarea( 'before', [
				'label'   => __( 'Before', 'meta-box-builder' ),
				'tooltip' => __( 'Custom HTML displayed before the field output', 'meta-box-builder' ),
			], '', 'advanced' ),
			Control::Textarea( 'after', [
				'label'   => __( 'After', 'meta-box-builder' ),
				'tooltip' => __( 'Custom HTML displayed after the field output', 'meta-box-builder' ),
			], '', 'advanced' ),
			Control::Input( 'class', __( 'Custom CSS class', 'meta-box-builder' ), '', 'advanced' ),
			Control::Checkbox( 'save_field', [
				'label'   => __( 'Save field value', 'meta-box-builder' ),
				'tooltip' => __( 'Uncheck this checkbox to prevent the field from saving its value into the database. Useful when you want to save yourself. Note: not working in the block editor.', 'meta-box-builder' ),
			], true, 'advanced' ),
			Control::Input( 'sanitize_callback', [
				'label'   => '<a href="https://docs.metabox.io/sanitization/" target="_blank" rel="noreferrer noopener">' . __( 'Custom sanitize callback', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'Enter PHP function name for custom sanitization. Enter "none" to disable sanitization.', 'meta-box-builder' ),
			], '', 'advanced' ),
			Control::KeyValue( 'attributes', [
				'label'   => '<a href="https://docs.metabox.io/custom-attributes/" target="_blank" rel="noreferrer noopener">' . __( 'Custom HTML5 attributes', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'Use this to add custom HTML5 attributes (like data-*). Work only for text input fields.', 'meta-box-builder' ),
				'keys'    => [ 'disabled', 'max', 'maxlength', 'min', 'minlength', 'pattern', 'readonly', 'required', 'step', 'type' ],
				'values'  => [
					'disabled' => [ 'true', 'false' ],
					'readonly' => [ 'true', 'false' ],
					'required' => [ 'true', 'false' ],
				],
			], [], 'advanced' ),
			Control::KeyValue( 'custom_settings', [
				'label'   => '<a href="https://docs.metabox.io/extensions/meta-box-builder/#custom-attributes">' . __( 'Custom settings', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'Use this to add custom settings for the field. The custom settings will overwrite existing settings if they have the same key.', 'meta-box-builder' ),
			], [], 'advanced' ),

			// Clone.
			Control::Checkbox( 'clone', [
				'label'   => __( 'Cloneable', 'meta-box-builder' ),
				'tooltip' => __( 'Make field cloneable (repeatable)', 'meta-box-builder' ),
			] ),
			Control::Checkbox( 'sort_clone', [
				'label'      => __( 'Sortable', 'meta-box-builder' ),
				'tooltip'    => __( 'Allows to drag-and-drop reorder clones', 'meta-box-builder' ),
				'dependency' => 'clone:true',
			] ),
			Control::Checkbox( 'clone_default', [
				'label'      => __( 'Clone default value', 'meta-box-builder' ),
				'dependency' => 'clone:true',
			] ),
			Control::Checkbox( 'clone_as_multiple', [
				'label'      => __( 'Clone as multiple', 'meta-box-builder' ),
				'tooltip'    => __( 'Save clones in multiple rows in the database', 'meta-box-builder' ),
				'dependency' => 'clone:true',
			] ),
			Control::Checkbox( 'clone_empty_start', [
				'label'      => __( 'Clone empty start', 'meta-box-builder' ),
				'tooltip'    => __( 'Start from no items except the "+ Add more" button', 'meta-box-builder' ),
				'dependency' => 'clone:true',
			] ),
			Control::Input( 'min_clone', [
				'type'       => 'number',
				'label'      => __( 'Min number of clones', 'meta-box-builder' ),
				'dependency' => 'clone:true',
			] ),
			Control::Input( 'max_clone', [
				'type'       => 'number',
				'label'      => __( 'Max number of clones', 'meta-box-builder' ),
				'tooltip'    => __( 'Leave empty for unlimited clones', 'meta-box-builder' ),
				'dependency' => 'clone:true',
			] ),
			Control::Input( 'add_button', [
				'label'      => __( 'Add more text', 'meta-box-builder' ),
				'tooltip'    => __( 'Custom text for the the "+ Add more" button. Leave empty to use the default text.', 'meta-box-builder' ),
				'dependency' => 'clone:true',
			] ),

			// Date.
			Control::Input( 'std', __( 'Default value', 'meta-box-builder' ) ),
			Control::Input( 'placeholder', __( 'Placeholder', 'meta-box-builder' ) ),
			Control::Input( 'size', [
				'type'  => 'number',
				'label' => __( 'Size of the input box', 'meta-box-builder' ),
			] ),
			Control::DateTime( 'save_format', [
				'label'       => __( 'Save format', 'meta-box-builder' ),
				'description' => __( 'Custom format for the value saved in the database. Accepts same formats as the PHP date() function. Leave empty to save as it is.', 'meta-box-builder' ),
				'date'        => [
					'Y-m-d'  => '2024-03-28 (Y-m-d)',
					'd-m-Y'  => '28-03-2024 (d-m-Y)',
					'm/d/Y'  => '03/28/2024 (m/d/Y)',
					'd F Y'  => '28 March 2024 (d F Y)',
					'M j, Y' => 'Mar 03, 2024 (M j, Y)',
					'F j, Y' => 'March 28, 2024 (F j, Y)',
				],
				'time'        => [
					'H:i'   => '09:20 (H:i)',
					'h:i A' => '04:20 AM (h:i A)',
				],
				'datetime'    => [
					'd-m-Y H:i'    => '28-03-2024 09:20 (d-m-Y H:i)',
					'm/d/Y H:i'    => '03/28/2024 09:20 (m/d/Y H:i)',
					'Y-m-d H:i'    => '2024-03-28 09:20 (Y-m-d H:i)',
					'M j, Y h:i A' => 'Mar 28, 2024 09:20 AM (M j, Y h:i A)',
				],
			] ),
			Control::Checkbox( 'timestamp', __( 'Save value as timestamp', 'meta-box-builder' ) ),
			'inline_date'                  => Control::Checkbox( 'inline', [
				'label'   => __( 'Inline', 'meta-box-builder' ),
				'tooltip' => __( 'Display the date picker inline with the input. Do not require to click the input field to trigger the date picker.', 'meta-box-builder' ),
			] ),
			'inline_datetime'              => Control::Checkbox( 'inline', [
				'label'   => __( 'Inline', 'meta-box-builder' ),
				'tooltip' => __( 'Display the date picker inline with the input. Do not require to click the input field to trigger the date picker.', 'meta-box-builder' ),
			] ),
			Control::Checkbox( 'disabled', __( 'Disabled', 'meta-box-builder' ) ),
			Control::Checkbox( 'required', __( 'Required', 'meta-box-builder' ) ),
			Control::Checkbox( 'readonly', __( 'Read only', 'meta-box-builder' ) ),
			'js_options_date'              => Control::KeyValue( 'js_options', [
				'label'   => '<a href="https://api.jqueryui.com/datepicker/" target="_blank" rel="nofollow noopenner">' . __( 'Date picker options', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'jQueryUI date picker options', 'meta-box-builder' ),
				'keys'    => [
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
			] ),
			'js_options_datetime'          => Control::KeyValue( 'js_options', [
				'label'   => '<a href="https://api.jqueryui.com/datepicker/" target="_blank" rel="nofollow noopenner">' . __( 'Date picker options', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'jQueryUI date and time picker options', 'meta-box-builder' ),
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
					'dateFormat'      => [ 'yy-mm-dd HH:mm', 'mm/dd/yy HH:mm', 'dd-mm-yy HH:mm' ],
					'showButtonPanel' => [ 'true', 'false' ],
				],
			] ),

			// Map.
			'std_map'                      => Control::Input( 'std', [
				'label'   => __( 'Default location', 'meta-box-builder' ),
				'tooltip' => __( 'Format: latitude,longitude.', 'meta-box-builder' ),
			] ),
			Control::AddressField( 'address_field', [
				'label'       => __( 'Address field', 'meta-box-builder' ),
				'tooltip'     => __( 'The ID of address field. For multiple fields, separate them by comma.', 'meta-box-builder' ),
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

			// Taxonomy.
			Control::ReactSelect( 'taxonomy', [
				'name'    => 'taxonomy[]',
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
			Control::Checkbox( 'add_new', [
				'label'   => __( 'Add new', 'meta-box-builder' ),
				'tooltip' => __( 'Allow users to create a new item', 'meta-box-builder' ),
			] ),
			Control::Checkbox( 'remove_default', __( 'Remove default meta box', 'meta-box-builder' ) ),
			Control::Checkbox( 'multiple', [
				'label'   => __( 'Multiple', 'meta-box-builder' ),
				'tooltip' => __( 'Allow to select multiple choices', 'meta-box-builder' ),
			] ),
			Control::Checkbox( 'select_all_none', __( 'Display "Toggle All" button', 'meta-box-builder' ) ),
			'query_args_taxonomy'          => Control::KeyValue( 'query_args', [
				'label'   => '<a href="https://developer.wordpress.org/reference/classes/wp_term_query/__construct/" target="_blank" rel="nofollow noreferrer">' . __( 'Query args', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'Query arguments for getting terms. Same as in the get_terms() function.', 'meta-box-builder' ),
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
			'query_args_taxonomy_advanced' => Control::KeyValue( 'query_args', [
				'label'   => '<a href="https://developer.wordpress.org/reference/classes/wp_term_query/__construct/" target="_blank" rel="nofollow noreferrer">' . __( 'Query args', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'Query arguments for getting terms. Same as in the get_terms() function.', 'meta-box-builder' ),
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
				'type'    => 'number',
				'label'   => __( 'Max number of files', 'meta-box-builder' ),
				'tooltip' => __( 'Leave empty for unlimited uploads', 'meta-box-builder' ),
			] ),
			Control::Checkbox( 'max_status', [
				'label'   => __( 'Show status', 'meta-box-builder' ),
				'tooltip' => __( 'Display how many files uploaded/remaining', 'meta-box-builder' ),
			], true ),
			Control::Checkbox( 'force_delete', [
				'label'   => __( 'Force delete', 'meta-box-builder' ),
				'tooltip' => __( 'Delete files from the Media Library when deleting them from post meta', 'meta-box-builder' ),
			] ),

			// Field specific.

			// Autocomplete.
			Control::Textarea( 'options', [
				'label'       => __( 'Choices', 'meta-box-builder' ),
				'description' => __( 'Enter each choice per line. You can also set both value and label like <code>red: Red</code>.', 'meta-box-builder' ) .
					'<br>' . __( 'To use a PHP function that returns an array of options, enter <code>callback: function_name</code>.', 'meta-box-builder' ) .
					'<br>' . __( 'The callback function must be declared before adding to the box.', 'meta-box-builder' ),
			] ),

			// Button.
			'std_button'                   => Control::Input( 'std', __( 'Button text', 'meta-box-builder' ) ),

			// Button group.
			'options_button_group'         => Control::Textarea( 'options', [
				'label'       => __( 'Buttons', 'meta-box-builder' ),
				'description' => __( 'Enter each button text per line. You can also set both value and label like <code>red: Red</code>.', 'meta-box-builder' ) .
					'<br>' . __( 'To use a PHP function that returns an array of options, enter <code>callback: function_name</code>.', 'meta-box-builder' ) .
					'<br>' . __( 'The callback function must be declared before adding to the box.', 'meta-box-builder' ),
			] ),
			'inline_button_group'          => Control::Checkbox( 'inline', __( 'Display buttons horizontally', 'meta-box-builder' ), true ),

			// Checkbox.
			'std_checkbox'                 => Control::Checkbox( 'std', __( 'Checked by default', 'meta-box-builder' ) ),

			// Checkbox list.
			'std_checkbox_list'            => Control::Textarea( 'std', [
				'label'   => __( 'Default value', 'meta-box-builder' ),
				'tooltip' => __( 'Enter each value on a line', 'meta-box-builder' ),
			] ),
			Control::Checkbox( 'inline', [
				'label'   => __( 'Inline', 'meta-box-builder' ),
				'tooltip' => __( 'Display choices on a single line', 'meta-box-builder' ),
			] ),

			// Color.
			Control::Checkbox( 'alpha_channel', __( 'Allow to select opacity', 'meta-box-builder' ) ),
			'js_options_color'             => Control::KeyValue( 'js_options', [
				'label'   => '<a href="https://automattic.github.io/Iris/" target="_blank" rel="nofollow noopenner">' . __( 'Color picker options', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'Color picker options', 'meta-box-builder' ),
				'keys'    => [ 'mode', 'width', 'palettes' ],
				'values'  => [
					'mode' => [ 'hsl', 'hsv' ],
				],
			] ),

			// Custom HTML.
			'std_custom_html'              => Control::Textarea( 'std', __( 'Content (HTML allowed)', 'meta-box-builder' ) ),
			Control::Input( 'callback', [
				'label'   => __( 'PHP Callback', 'meta-box-builder' ),
				'tooltip' => __( 'PHP function that is called to show custom HTML content. Will overwrite the content setting above.', 'meta-box-builder' ),
			] ),

			// Fieldset text.
			'options_fieldset_text'        => Control::KeyValue( 'options', [
				'label'            => __( 'Inputs', 'meta-box-builder' ),
				'valuePlaceholder' => __( 'Enter label', 'meta-box-builder' ),
			] ),

			// File.
			Control::Input( 'upload_dir', [
				'label'   => __( 'Custom upload folder', 'meta-box-builder' ),
				'tooltip' => __( 'Relatively to the WordPress root path', 'meta-box-builder' ),
			] ),

			// File advanced.
			Control::Input( 'mime_type', [
				'label'   => __( 'MIME types', 'meta-box-builder' ),
				'tooltip' => __( 'Filters items in the Media Library popup. Does not restrict file types when upload. Separate by commas.', 'meta-box-builder' ),
			] ),

			// File upload.
			Control::Input( 'max_file_size', [
				'label'   => __( 'Max file size', 'meta-box-builder' ),
				'tooltip' => __( 'Supports b, kb, mb, gb, tb suffixes. e.g. "10mb" or "1gb".', 'meta-box-builder' ),
			] ),

			// Map.
			Control::Input( 'api_key', [
				'label'    => '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank" rel="noopenner noreferrer">' . __( 'Google Maps API key', 'meta-box-builder' ) . '</a>',
				'tooltip'  => __( 'Your unique API Key for Google Maps Platform', 'meta-box-builder' ),
				'required' => true,
			] ),

			// Heading.
			'desc_heading'                 => Control::Input( 'desc', [
				'label'   => __( 'Input description', 'meta-box-builder' ),
				'tooltip' => __( 'Display below the field input', 'meta-box-builder' ),
			] ),

			// Image advanced.
			Control::Select( 'image_size', [
				'label'   => __( 'Image size', 'meta-box-builder' ),
				'tooltip' => __( 'Image size that displays in the edit page, used to make sure images are not blurry. It\'s not meant to display images with the exact width and height.', 'meta-box-builder' ),
				'options' => $this->get_image_sizes(),
			], 'thumbnail' ),
			Control::Select( 'add_to', [
				'label'   => __( 'New image placement', 'meta-box-builder' ),
				'options' => [
					'beginning' => __( 'Beginning of the list', 'meta-box-builder' ),
					'end'       => __( 'End of the list', 'meta-box-builder' ),
				],
			], 'end' ),

			// Key value.
			Control::Input( 'placeholder_key', __( 'Placeholder for key', 'meta-box-builder' ) ),
			Control::Input( 'placeholder_value', __( 'Placeholder for value', 'meta-box-builder' ) ),

			// Number.
			Control::Input( 'min', [
				'type'  => 'number',
				'label' => __( 'Min value', 'meta-box-builder' ),
			] ),
			Control::Input( 'max', [
				'type'  => 'number',
				'label' => __( 'Max value', 'meta-box-builder' ),
			] ),
			Control::Input( 'step', [
				'label'   => __( 'Step', 'meta-box-builder' ),
				'tooltip' => __( "Set the increments at which a numeric value can be set. Enter 'any' to accept any number.", 'meta-box-builder' ),
			] ),

			// Oembed.
			Control::Input( 'not_available_string', [
				'label'   => __( 'Not available text', 'meta-box-builder' ),
				'tooltip' => __( 'The text message displayed to users when the embed media is not available. Accepts HTML.', 'meta-box-builder' ),
			] ),

			// Post.
			Control::ReactSelect( 'post_type', [
				'name'    => 'post_type[]',
				'label'   => __( 'Post types', 'meta-box-builder' ),
				'options' => $this->get_post_types(),
			], [ 'post' ] ),
			Control::Checkbox( 'parent', [
				'label'   => __( 'Set as parent', 'meta-box-builder' ),
				'tooltip' => __( 'Set the selected post as the parent of current post being edited.', 'meta-box-builder' ),
			] ),
			'query_args_post'              => Control::KeyValue( 'query_args', [
				'label'   => '<a href="https://developer.wordpress.org/reference/classes/wp_query/" target="_blank" rel="nofollow noopenner">' . __( 'Query args', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'Query arguments for getting posts. Same as in the WP_Query class.', 'meta-box-builder' ),
				'keys'    => [
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
				'label'   => '<a href="https://select2.org/configuration/options-api" target="_blank" rel="nofollow noopenner">' . __( 'Select2 options', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'Select2 options', 'meta-box-builder' ),
				'keys'    => [
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
			] ),

			// Icon.
			'js_options_icon'              => Control::KeyValue( 'js_options', [
				'label'   => '<a href="https://select2.org/configuration/options-api" target="_blank" rel="nofollow noopenner">' . __( 'Select2 options', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'Select2 options', 'meta-box-builder' ),
				'keys'    => [
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
			] ),
			Control::Select( 'icon_set', [
				'label'   => __( 'Icon set', 'meta-box-builder' ),
				'options' => [
					'font-awesome-free' => __( 'Font Awesome Free', 'meta-box-builder' ),
					'font-awesome-pro'  => __( 'Font Awesome Pro', 'meta-box-builder' ),
					'custom'            => __( 'Custom', 'meta-box-builder' ),
				],
			], 'font-awesome-free' ),
			Control::Input( 'icon_file', [
				'type'       => 'text',
				'label'      => __( 'Icon file', 'meta-box-builder' ),
				'tooltip'    => __( 'The full path to the icon file definition, which can be a text or JSON file.', 'meta-box-builder' ),
				'dependency' => 'icon_set:[font-awesome-pro,custom]',
			] ),
			Control::Input( 'icon_dir', [
				'type'       => 'text',
				'label'      => __( 'Icon dir', 'meta-box-builder' ),
				'tooltip'    => __( 'Full path to the folder that contains all SVGs of icons.', 'meta-box-builder' ),
				'dependency' => 'icon_set:custom',
			] ),
			Control::Input( 'icon_css', [
				'label'      => __( 'Icon CSS', 'meta-box-builder' ),
				'tooltip'    => __( 'URL to the icon CSS file. It\'s required only when you use icons as an icon font (e.g. no SVG).', 'meta-box-builder' ),
				'dependency' => 'icon_set:custom',
			] ),

			// Slider.
			Control::Input( 'prefix', [
				'label'   => __( 'Prefix', 'meta-box-builder' ),
				'tooltip' => __( 'Text displayed before the field value', 'meta-box-builder' ),
			] ),
			Control::Input( 'suffix', [
				'label'   => __( 'Suffix', 'meta-box-builder' ),
				'tooltip' => __( 'Text displayed after the field value', 'meta-box-builder' ),
			] ),
			'js_options_slider'            => Control::KeyValue( 'js_options', [
				'label'   => '<a href="https://api.jqueryui.com/slider" target="_blank" rel="nofollow noopenner">' . __( 'Slider options', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'jQueryUI slider options', 'meta-box-builder' ),
				'keys'    => [ 'animate', 'max', 'min', 'orientation', 'step' ],
				'values'  => [
					'orientation' => [ 'horizontal', 'vertical' ],
					'animate'     => [ 'true', 'false', 'fast', 'slow' ],
				],
			] ),

			// Switch.
			Control::Select( 'style', [
				'label'   => __( 'Style', 'meta-box-builder' ),
				'options' => [
					'rounded' => __( 'Rounded', 'meta-box-builder' ),
					'square'  => __( 'Square', 'meta-box-builder' ),
				],
			], 'rounded' ),
			Control::Input( 'on_label', __( 'Custom ON status label', 'meta-box-builder' ) ),
			Control::Input( 'off_label', __( 'Custom OFF status label', 'meta-box-builder' ) ),
			'std_switch'                   => Control::Checkbox( 'std', __( 'ON by default', 'meta-box-builder' ) ),

			// Text.
			Control::Input( 'prepend', __( 'Prepend text', 'meta-box-builder' ) ),
			Control::Input( 'append', __( 'Append text', 'meta-box-builder' ) ),
			Control::Textarea( 'datalist_choices', [
				'label'   => __( 'Predefined values', 'meta-box-builder' ),
				'tooltip' => __( 'Known as "datalist", these are values that users can select from (they still can enter text if they want). Enter each value on a line.', 'meta-box-builder' ),
			] ),

			// Text list.
			'options_text_list'            => Control::KeyValue( 'options', [
				'label'            => __( 'Inputs', 'meta-box-builder' ),
				'keyPlaceholder'   => __( 'Placeholder', 'meta-box-builder' ),
				'valuePlaceholder' => __( 'Label', 'meta-box-builder' ),
			] ),

			// Textarea.
			'std_textarea'                 => Control::Textarea( 'std', __( 'Default value', 'meta-box-builder' ) ),
			Control::Input( 'rows', [
				'type'  => 'number',
				'label' => __( 'Rows', 'meta-box-builder' ),
			] ),
			Control::Input( 'cols', [
				'type'  => 'number',
				'label' => __( 'Columns', 'meta-box-builder' ),
			] ),

			// Time.
			'inline_time'                  => Control::Checkbox( 'inline', [
				'label'   => __( 'Inline', 'meta-box-builder' ),
				'tooltip' => __( 'Display the time picker inline with the input. Do not require to click the input field to trigger the time picker.', 'meta-box-builder' ),
			] ),
			'js_options_time'              => Control::KeyValue( 'js_options', [
				'label'   => '<a href="http://trentrichardson.com/examples/timepicker" target="_blank" rel="nofollow noopenner">' . __( 'Time picker options', 'meta-box-builder' ) . '<a/>',
				'tooltip' => __( 'jQueryUI time picker options', 'meta-box-builder' ),
				'keys'    => [ 'controlType', 'timeFormat' ],
				'values'  => [
					'controlType' => [ 'select', 'slider' ],
					'timeFormat'  => [ 'HH:mm', 'HH:mm T' ],
				],
			] ),

			// User.
			'query_args_user'              => Control::KeyValue( 'query_args', [
				'label'   => '<a href="https://developer.wordpress.org/reference/classes/wp_user_query/prepare_query/" target="_blank" rel="nofollow noopenner">' . __( 'Query args', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'Query arguments for getting user. Same as in the get_user() function.', 'meta-box-builder' ),
				'keys'    => [
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
			Control::Checkbox( 'raw', __( 'Save data in the raw format', 'meta-box-builder' ) ),
			'options_wysiwyg'              => Control::KeyValue( 'options', [
				'label'   => '<a href="https://developer.wordpress.org/reference/functions/wp_editor/" target="_blank" rel="nofollow noopenner">' . __( 'Editor options', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'The editor options, the same as settings for wp_editor() function', 'meta-box-builder' ),
				'keys'    => [ 'media_buttons', 'default_editor', 'drag_drop_upload', 'quicktags', 'textarea_rows', 'teeny' ],
				'values'  => [
					'media_buttons'    => [ 'true', 'false' ],
					'drag_drop_upload' => [ 'true', 'false' ],
					'teeny'            => [ 'true', 'false' ],
					'default'          => [ 'true', 'false', 'tinymce', 'html' ],
				],
			] ),
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
	public function transform_controls() {
		foreach ( $this->field_types as $type => &$field_type ) {
			foreach ( $field_type['controls'] as &$control ) {
				$control = $this->get_control( $control, $type );
			}
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
